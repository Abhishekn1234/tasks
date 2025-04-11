<?php
class Task_model extends CI_Model {

    public function getByProject($project_id) {
        return $this->db->get_where('tasks', ['project_id' => $project_id])->result_array();
    
    }

    public function getById($task_id) {
        return $this->db->get_where('tasks', ['id' => $task_id])->row_array();
    }
    public function getStatusHistory($task_id)
{
    return $this->db
        ->where('task_id', $task_id)
        ->order_by('logged_at', 'DESC')
        ->get('task_logs')
        ->result_array();
}


    public function insert($data) {
        return $this->db->insert('tasks', $data);
    }

    public function update($task_id, $data) {
        return $this->db->update('tasks', $data, ['id' => $task_id]);
    }
}
