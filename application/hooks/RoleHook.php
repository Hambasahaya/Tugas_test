<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RoleHook {
    public function check_role() {
        $CI =& get_instance();
        $uri = $CI->uri->uri_string();
        // do not enforce on auth controller
        if (strpos($uri, 'login') === 0 || strpos($uri, 'logout') === 0) return;

        // Load session library if not loaded
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }

        // For API endpoints (reimbursements), require authenticated session
        if (strpos($uri, 'reimbursements') === 0) {
            $role = $CI->session->userdata('role');
            if (!$role) {
                // JSON response
                $CI->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(array('error'=>'Unauthorized')));
                exit;
            }
        }
    }
}
