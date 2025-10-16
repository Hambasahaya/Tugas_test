<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activitylog_model extends CI_Model {
    protected $table = 'activity_logs';

    public function insert($user_id, $action, $meta = array()) {
        $data = array(
            'user_id' => $user_id,
            'action' => $action,
            'meta' => json_encode($meta),
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
}
