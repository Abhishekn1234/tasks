<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class Auth extends CI_Controller {

    private $key = "your-secret-key"; 

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        header('Content-Type: application/json');
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'], $data['email'], $data['password'])) {
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        if ($this->User_model->getByEmail($data['email'])) {
            echo json_encode(['error' => 'Email already registered']);
            return;
        }

        $insert = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT)
        ];

        $this->User_model->register($insert);

        echo json_encode(['message' => 'User registered successfully']);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['email'], $data['password'])) {
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }

        $user = $this->User_model->getByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user->password)) {
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $payload = [
            'id'    => $user->id,
            'email' => $user->email,
            'iat'   => time(),
            'exp'   => time() + (60 * 60 * 24) 
        ];

        $token = JWT::encode($payload, $this->key, 'HS256');

        echo json_encode(['token' => $token]);
    }

   
    public function me() {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            echo json_encode(['error' => 'Missing token']);
            return;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            $user = $this->User_model->getById($decoded->id);
            echo json_encode(['user' => $user]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Invalid token']);
        }
    }
}
