<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
class Tasks extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_model');
        $this->load->model('Project_model');
        $this->load->helper(['url', 'form']);
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
    
    public function index($project_id) {
        $user_id = $this->verifyToken();
    
        $project = $this->Project_model->getById($project_id, $user_id);
        if (!$project) {
            return $this->response(['status' => 'error', 'message' => 'Project not found or unauthorized'], 403);
        }
    
        $tasks = $this->Task_model->getByProject($project_id);
        return $this->response(['status' => 'success', 'project' => $project, 'tasks' => $tasks]);
    }
    

   
    public function create($project_id) {
        $user_id = $this->verifyToken(); 
    
                $project = $this->Project_model->getById($project_id, $user_id);
        if (!$project) {
            return $this->response(['status' => 'error', 'message' => 'Project not found or unauthorized'], 403);
        }
    
        $input = json_decode(trim(file_get_contents('php://input')), true);
        if (!$input) {
            return $this->response(['status' => 'error', 'message' => 'Invalid JSON input'], 400);
        }
    
        $data = [
            'project_id' => $project_id,
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'status' => $input['status'] ?? 'Pending',
            'remarks' => $input['remarks'] ?? ''
        ];
    
        $task_id = $this->Task_model->insert($data);
    
        return $this->response([
            'status' => 'success',
            'message' => 'Task created successfully',
            'task_id' => $task_id
        ]);
    }
    
    public function updateStatus($task_id)
    {
        $input = json_decode(trim(file_get_contents('php://input')), true);

        if (!$input || !isset($input['status'])) {
            return $this->response([
                'status' => 'error',
                'message' => 'Status field is required'
            ], 400);
        }

        $allowed_statuses = ['Pending', 'In Progress', 'Completed'];

        if (!in_array($input['status'], $allowed_statuses)) {
            return $this->response([
                'status' => 'error',
                'message' => 'Invalid status value. Allowed: Pending, In Progress, Completed'
            ], 400);
        }

        $task = $this->Task_model->getById($task_id);
        if (!$task) {
            return $this->response([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        $this->Task_model->update($task_id, ['status' => $input['status']]);

        return $this->response([
            'status' => 'success',
            'message' => 'Task status updated to ' . $input['status']
        ]);
    }

    
    public function update($task_id) {
        $task = $this->Task_model->getById($task_id);
        if (!$task) {
            return $this->response(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        $input = json_decode(trim(file_get_contents('php://input')), true);
        if (!$input) {
            return $this->response(['status' => 'error', 'message' => 'Invalid JSON input'], 400);
        }

        $update_data = [
            'title' => $input['title'] ?? $task['title'],
            'description' => $input['description'] ?? $task['description'],
            'status' => $input['status'] ?? $task['status'],
            'remarks' => $input['remarks'] ?? $task['remarks']
        ];

        $this->Task_model->update($task_id, $update_data);

        return $this->response([
            'status' => 'success',
            'message' => 'Task updated successfully'
        ]);
    }

   
    private function response($data, $code = 200) {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
    public function report($project_id) {
        $user_id = $this->verifyToken();
    
        $project = $this->Project_model->getById($project_id, $user_id);
        if (!$project) {
            return $this->response([
                'status' => 'error',
                'message' => 'Project not found or unauthorized'
            ], 403);
        }
    
        $tasks = $this->Task_model->getByProject($project_id);
    
        return $this->response([
            'status' => 'success',
            'project' => $project,
            'tasks' => $tasks
        ]);
    }
    


}
