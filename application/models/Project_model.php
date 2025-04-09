<?php
class Project_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        return $this->db->insert('projects', $data);
    }

    public function getByUser($user_id) {
        return $this->db->get_where('projects', ['user_id' => $user_id])->result();
    }

    public function update($project_id, $user_id, $data) {
        $this->db->where('id', $project_id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('projects', $data);
    }

    public function delete($project_id, $user_id) {
        return $this->db->delete('projects', ['id' => $project_id, 'user_id' => $user_id]);
    }

 
public function getById($project_id)
{
    return $this->db->get_where('projects', ['id' => $project_id])->row();
}

}
