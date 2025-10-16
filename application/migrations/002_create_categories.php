<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_categories extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type'=>'INT','unsigned'=>TRUE,'auto_increment'=>TRUE),
            'name' => array('type'=>'VARCHAR','constraint'=>100),
            'limit_per_month' => array('type'=>'DECIMAL','constraint'=>'12,2','default'=>'0'),
            'created_at' => array('type'=>'DATETIME','null'=>TRUE),
            'updated_at' => array('type'=>'DATETIME','null'=>TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('categories');
        // seed common categories
        $this->db->insert('categories', array('name'=>'transportasi','limit_per_month'=>'1000000','created_at'=>date('Y-m-d H:i:s')));
        $this->db->insert('categories', array('name'=>'kesehatan','limit_per_month'=>'2000000','created_at'=>date('Y-m-d H:i:s')));
        $this->db->insert('categories', array('name'=>'makan','limit_per_month'=>'500000','created_at'=>date('Y-m-d H:i:s')));
    }
    public function down() {
        $this->dbforge->drop_table('categories');
    }
}
