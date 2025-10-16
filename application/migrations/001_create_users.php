<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_users extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type'=>'INT','unsigned'=>TRUE,'auto_increment'=>TRUE),
            'name' => array('type'=>'VARCHAR','constraint'=>150),
            'email' => array('type'=>'VARCHAR','constraint'=>150),
            'password' => array('type'=>'VARCHAR','constraint'=>255),
            'role' => array('type'=>'ENUM','constraint'=>"'admin','manager','employee'", 'default'=>'employee'),
            'created_at' => array('type'=>'DATETIME','null'=>TRUE),
            'updated_at' => array('type'=>'DATETIME','null'=>TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
        $this->db->query("ALTER TABLE users ADD UNIQUE (email);"); 
        // seed admin user (password: admin123)
        $pass = password_hash('admin123', PASSWORD_BCRYPT);
        $this->db->insert('users', array('name'=>'Administrator','email'=>'admin@example.com','password'=>$pass,'role'=>'admin','created_at'=>date('Y-m-d H:i:s')));
    }
    public function down() {
        $this->dbforge->drop_table('users');
    }
}
