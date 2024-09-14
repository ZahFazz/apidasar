<?php
include_once '../models/user.php';
include_once '../conf/db.php';
include_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class UserController {
    private $db;
    private $requestMethod;
    private $userId;

    public function __construct($db, $requestMethod, $userId = null) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                };
                break;
            case 'POST':
                $response = $this->createUser();
                break;
            case 'PUT':
                $response = $this->updateUser($this->userId);
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->userId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllUsers() {
        $query = "SELECT id, name, email, avatar FROM users";
        $stmt = $this->db->query($query);

        $users = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
        return $this->okResponse($users);
    }

    private function getUser($id) {
        error_log("Fetching user with ID: " . $id); // Log the ID
        $query = "SELECT id, name, email, avatar FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return $this->notFoundResponse();
        }
        return $this->okResponse($row);
    }

    private function createUser() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }

        $user = new User($this->db);
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->avatar = $input['avatar'];

        if ($user->create()) {
            return $this->okResponse(['message' => 'User created']);
        }
        return $this->unprocessableEntityResponse();
    }

    private function updateUser($id) {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }

        $user = new User($this->db);
        $user->id = $id;
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = $input['password']; // Consider hashing the password if it's provided
        $user->avatar = $input['avatar'];

        if ($user->update()) {
            return $this->okResponse(['message' => 'User updated']);
        }
        return $this->unprocessableEntityResponse();
    }

    private function deleteUser($id) {
        $user = new User($this->db);
        $user->id = $id;

        if ($user->delete()) {
            return $this->okResponse(['message' => 'User deleted']);
        }
        return $this->notFoundResponse();
    }

    private function validateUser($input) {
        if (!isset($input['name']) || !isset($input['email']) || !isset($input['password']) || !isset($input['avatar'])) {
            return false;
        }
        return true;
    }

    private function okResponse($body) {
        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode($body)
        ];
    }

    private function unprocessableEntityResponse() {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode(['error' => 'Invalid input'])
        ];
    }

    private function notFoundResponse() {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['error' => 'Not found'])
        ];
    }
}
?>
