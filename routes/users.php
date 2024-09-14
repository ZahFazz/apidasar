<?php
include_once '../conf/db.php';
include_once '../api/usercontrol.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$userId = null;
if (isset($uri[3])) {
    $userId = (int) $uri[3];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

$controller = new UserController($db, $requestMethod, $userId);
$controller->processRequest();
?>
