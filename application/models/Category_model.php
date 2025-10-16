<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {
    protected $table = 'categories';

    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get($id) {
        return $this->db->get_where($this->table, array('id'=>$id))->row();
    }

    public function all() {
        return $this->db->get($this->table)->result();
    }
}
