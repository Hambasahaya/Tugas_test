<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_reimbursements extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type'=>'INT','unsigned'=>TRUE,'auto_increment'=>TRUE),
            'user_id' => array('type'=>'INT','constraint'=>11,'unsigned'=>TRUE),
            'title' => array('type'=>'VARCHAR','constraint'=>255),
            'description' => array('type'=>'TEXT','null'=>TRUE),
            'amount' => array('type'=>'DECIMAL','constraint'=>'12,2','default'=>'0'),
            'category_id' => array('type'=>'INT','constraint'=>11,'unsigned'=>TRUE),
            'status' => array('type'=>'ENUM','constraint'=>"'pending','approved','rejected'", 'default'=>'pending'),
            'file_path' => array('type'=>'VARCHAR','constraint'=>255,'null'=>TRUE),
            'submitted_at' => array('type'=>'DATETIME','null'=>TRUE),
            'approved_at' => array('type'=>'DATETIME','null'=>TRUE),
            'deleted_at' => array('type'=>'DATETIME','null'=>TRUE),
            'created_at' => array('type'=>'DATETIME','null'=>TRUE),
            'updated_at' => array('type'=>'DATETIME','null'=>TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('reimbursements');
    }
    public function down() {
        $this->dbforge->drop_table('reimbursements');
    }
}
