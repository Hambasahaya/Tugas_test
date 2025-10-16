<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_email_queue extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type'=>'INT','unsigned'=>TRUE,'auto_increment'=>TRUE),
            'payload' => array('type'=>'TEXT'),
            'status' => array('type'=>'ENUM','constraint'=>"'pending','sent','failed'", 'default'=>'pending'),
            'created_at' => array('type'=>'DATETIME','null'=>TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('email_queue');
    }
    public function down() {
        $this->dbforge->drop_table('email_queue');
    }
}
