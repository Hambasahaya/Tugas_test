<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reimbursement_model extends CI_Model {
    protected $table = 'reimbursements';

    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get($id) {
        return $this->db->get_where($this->table, array('id'=>$id))->row();
    }

    public function get_by_user($user_id) {
        return $this->db->order_by('created_at','DESC')->get_where($this->table, array('user_id'=>$user_id))->result();
    }

    public function get_all($show_deleted = FALSE) {
        if (!$show_deleted) {
            $this->db->where('deleted_at IS NULL', null, FALSE);
        }
        return $this->db->order_by('created_at','DESC')->get($this->table)->result();
    }

    public function update($id, $data) {
        $this->db->where('id',$id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function soft_delete($id) {
        $this->db->where('id',$id)->update($this->table, array('deleted_at'=>date('Y-m-d H:i:s')));
    }

    public function monthly_total_by_user_category($userId, $categoryId, $monthYear) {
        $this->db->select_sum('amount');
        $this->db->where('user_id', $userId);
        $this->db->where('category_id', $categoryId);
        $this->db->where("DATE_FORMAT(submitted_at, '%Y-%m') = '".$monthYear."'", null, FALSE);
        $q = $this->db->get($this->table)->row();
        return $q ? $q->amount : 0;
    }
}
