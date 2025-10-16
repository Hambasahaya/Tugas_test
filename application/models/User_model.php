<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    protected $table = 'users';

    public function insert($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get($id) {
        return $this->db->get_where($this->table, array('id'=>$id))->row();
    }

    public function get_by_email($email) {
        return $this->db->get_where($this->table, array('email'=>$email))->row();
    }

    public function get_by_role($role) {
        return $this->db->get_where($this->table, array('role'=>$role))->result();
    }

    public function all() {
        return $this->db->get($this->table)->result();
    }
}
