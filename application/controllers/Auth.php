<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model','user');
        $this->load->library('form_validation');
        $this->load->helper(array('url','security'));
    }

    public function login()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email','Email','required|valid_email');
            $this->form_validation->set_rules('password','Password','required');
            if ($this->form_validation->run() === FALSE) {
                return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['errors'=>validation_errors()]));
            }
            $email = $this->input->post('email', TRUE);
            $password = $this->input->post('password', TRUE);
            $user = $this->user->get_by_email($email);
            if (!$user) {
                return $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['error'=>'Invalid credentials']));
            }
            if (!password_verify($password, $user->password)) {
                return $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['error'=>'Invalid credentials']));
            }
            // set session
            $this->load->library('session');
            $this->session->set_userdata(array(
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $user->name,
                'logged_in' => TRUE
            ));
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'user'=>array('id'=>$user->id,'email'=>$user->email,'role'=>$user->role)]));
        }

        // For GET - show simple login info
        echo "Authentication endpoint. POST email & password to login.";
    }

    public function logout()
    {
        $this->load->library('session');
        $this->session->sess_destroy();
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true]));
    }
}
