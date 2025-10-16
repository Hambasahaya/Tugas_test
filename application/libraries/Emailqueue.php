<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emailqueue {

    protected $CI;
    protected $table = 'email_queue';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        // ensure table exists
        $this->CI->db->query("CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payload TEXT,
            status ENUM('pending','sent','failed') DEFAULT 'pending',
            created_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"); 
    }

    public function enqueue($payload = array())
    {
        $this->CI->db->insert($this->table, array(
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function process_pending()
    {
        $rows = $this->CI->db->get_where($this->table, array('status'=>'pending'))->result();
        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true);
            $this->CI->load->model('User_model','user');
            $recipients = $this->CI->user->get_by_role($payload['to_role']);
            $emails = array_map(function($r){ return $r->email; }, $recipients);
            if (empty($emails)) {
                $this->CI->db->where('id',$row->id)->update($this->table, array('status'=>'failed'));
                continue;
            }
            $this->CI->load->library('email');
            $this->CI->email->from('no-reply@example.com', 'Reimbursement App');
            $this->CI->email->to($emails);
            $this->CI->email->subject($payload['subject']);
            $this->CI->email->message($payload['body']);
            if ($this->CI->email->send()) {
                $this->CI->db->where('id',$row->id)->update($this->table, array('status'=>'sent'));
            } else {
                $this->CI->db->where('id',$row->id)->update($this->table, array('status'=>'failed'));
            }
        }
    }
}
