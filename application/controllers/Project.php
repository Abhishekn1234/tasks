
<?php
defined('BASEPATH') OR exit('No direct script access allowed');


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Project extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Project_model');
        $this->load->database();
        header('Content-Type: application/json');
    }

    private function verifyToken() {
      
        $headers = apache_request_headers();
    
       
        if (!isset($headers['Authorization'])) {
            show_error('Unauthorized: Missing Authorization Header', 401);
        }
    
        
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    
        try {
            
            $secretKey = 'your-secret-key';
    
         
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    
            
            return $decoded->id;
    
        } catch (ExpiredException $e) {
            show_error('Token expired: ' . $e->getMessage(), 401);
        } catch (Exception $e) {
            show_error('Invalid token: ' . $e->getMessage(), 401);
        }
    }

    public function create() {
        $id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['title']) || !isset($data['description'])) {
            show_error('Invalid input data', 400);
        }

        $insert = [
            'user_id' => $id,
            'name' => $data['title'],
            'description' => $data['description']
        ];

        $this->Project_model->create($insert);

        echo json_encode(['message' => 'Project created successfully']);
    }



    public function list() {
        $user_id = $this->verifyToken();
        $projects = $this->Project_model->getByUser($user_id);
        echo json_encode($projects);
    }

    public function update($id) {
        $user_id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"), true);

        $update = [
            'name' => $data['title'],
            'description' => $data['description']
        ];

        $this->Project_model->update($id, $user_id, $update);
        echo json_encode(['message' => 'Project updated']);
    }

    public function delete($id) {
        header('Content-Type: application/json');
        
        $user_id = $this->verifyToken();
    
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid project ID']);
            return;
        }
    
        $this->load->model('Project_model');
    
        $deleted = $this->Project_model->delete($id, $user_id);
    
        if ($deleted) {
            echo json_encode(['message' => 'Project deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete project. It may not exist or you may not have permission.']);
        }
    }
    
}
