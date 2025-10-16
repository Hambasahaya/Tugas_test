<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_activity_logs extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type'=>'INT','unsigned'=>TRUE,'auto_increment'=>TRUE),
            'user_id' => array('type'=>'INT','constraint'=>11,'unsigned'=>TRUE,'null'=>TRUE),
            'action' => array('type'=>'VARCHAR','constraint'=>255),
            'meta' => array('type'=>'TEXT','null'=>TRUE),
            'created_at' => array('type'=>'DATETIME','null'=>TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('activity_logs');
    }
    public function down() {
        $this->dbforge->drop_table('activity_logs');
    }
}
