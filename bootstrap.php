<?php
ini_set('display_errors', true);
// Define path to application directory
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/vendor/autoload.php';

if (file_exists('config.php')) {
	require_once 'config.php';
}

defined('PROVISIONING_API_URL')
	|| define('PROVISIONING_API_URL', getenv('PROVISIONING_API_URL') ? getenv('PROVISIONING_API_URL') : 'url');

defined('PROVISIONING_API_TOKEN')
	|| define('PROVISIONING_API_TOKEN', getenv('PROVISIONING_API_TOKEN') ? getenv('PROVISIONING_API_TOKEN') : 'token');

