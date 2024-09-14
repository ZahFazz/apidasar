<?php
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$request_path = $request_uri[0];

error_log("Requested Path: " . $request_path);

switch ($request_uri[0]) {
    case '/users':
        require 'routes/users.php';
        break;
    case '/tasks':
        require 'routes/tasks.php';
        break;
    case '/projects':
        require 'routes/projects.php';
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}
?>
