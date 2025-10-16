<?php
// Minimal CodeIgniter 3 index.php (expects 'system' folder placed next to this file)
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

$system_path = 'system';
$application_folder = 'application';

if (realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path).'/';
}

$system_path = rtrim($system_path, '/').'/';
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);
define('BASEPATH', str_replace('\\', '/', $system_path));
define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please download CodeIgniter 3.1.13 and set $system_path in index.php';
    exit(3);
}

require_once BASEPATH.'core/CodeIgniter.php';
