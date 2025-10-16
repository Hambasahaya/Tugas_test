<?php

class Reimbursement extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Reimbursement_model', 'reim');
        $this->load->model('Category_model', 'cat');
        $this->load->model('Activitylog_model', 'log');
        $this->load->library('emailqueue');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper(array('url', 'file', 'security'));
    }

    public function submit()
    {
        $user_id = $this->session->userdata('user_id');
        $this->form_validation->set_rules('title', 'Title', 'required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
        $this->form_validation->set_rules('category_id', 'Category', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(array('errors' => validation_errors())));
        }

        $post = $this->input->post(NULL, TRUE);
        $category = $this->cat->get($post['category_id']);
        if (!$category) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Category not found']));
        }

        $month = date('Y-m');
        $existing = $this->reim->monthly_total_by_user_category($user_id, $category->id, $month);

        $newTotal = bcadd((string)$existing, (string)$post['amount'], 2);
        if (bccomp($newTotal, $category->limit_per_month, 2) === 1) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['error' => 'Limit per month exceeded for category']));
        }

        $config['upload_path'] = FCPATH . 'writable/uploads/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);

        $file_path = null;
        if (!empty($_FILES['file']['name'])) {
            if (!$this->upload->do_upload('file')) {
                return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['error' => $this->upload->display_errors('', '')]));
            } else {
                $f = $this->upload->data();
                $file_path = 'writable/uploads/' . $f['file_name'];
            }
        }

        $data = array(
            'user_id' => $user_id,
            'title' => $post['title'],
            'description' => $post['description'] ?? null,
            'amount' => $post['amount'],
            'category_id' => $post['category_id'],
            'status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
            'file_path' => $file_path,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $id = $this->reim->insert($data);
        $this->log->insert($this->session->userdata('user_id'), 'new_reimbursement', array('reimbursement_id' => $id));

        $this->emailqueue->enqueue(array(
            'to_role' => 'manager',
            'subject' => 'New reimbursement #' . $id,
            'body' => 'User #' . $user_id . ' submitted reimbursement #' . $id
        ));

        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'id' => $id]));
    }

    public function my()
    {
        $user_id = $this->session->userdata('user_id');
        $rows = $this->reim->get_by_user($user_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($rows));
    }

    public function all()
    {
        if ($this->session->userdata('role') !== 'admin') {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => 'Forbidden']));
        }
        $show_deleted = $this->input->get('show_deleted') ? TRUE : FALSE;
        $rows = $this->reim->get_all($show_deleted);
        $this->output->set_content_type('application/json')->set_output(json_encode($rows));
    }

    public function change_status($id)
    {
        if (!in_array($this->session->userdata('role'), array('manager', 'admin'))) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => 'Forbidden']));
        }
        $status = $this->input->post('status', TRUE);
        if (!in_array($status, array('approved', 'rejected'))) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['error' => 'Invalid status']));
        }
        $update = array('status' => $status);
        if ($status === 'approved') $update['approved_at'] = date('Y-m-d H:i:s');
        $this->reim->update($id, $update);
        $this->log->insert($this->session->userdata('user_id'), 'status_changed', array('reimbursement_id' => $id, 'new_status' => $status));
        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
    }

    public function remove($id)
    {
        $user_id = $this->session->userdata('user_id');
        $row = $this->reim->get($id);
        if (!$row) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Not found']));
        }
        if ($row->user_id != $user_id && $this->session->userdata('role') !== 'admin') {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => 'Forbidden']));
        }
        $this->reim->soft_delete($id);
        $this->log->insert($user_id, 'deleted', array('reimbursement_id' => $id));
        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
    }
}
