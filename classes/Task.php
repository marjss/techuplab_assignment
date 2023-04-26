<?php

// Define the Task class
class Task extends Database {

    private $db;

    function __construct() {
        $db_connection = new Database();
        $this->db = $db_connection->dbConnection();
    }

    function create($data) {
        try {
            $stmt = $this->db->prepare('INSERT INTO tasks (subject, description, start_date, due_date, status, priority) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$data['subject'], $data['description'], $data['start_date'], $data['due_date'], $data['status'], $data['priority']]);

            // Get the ID of the newly created task
            $task_id = $this->db->lastInsertId();
            return $task_id;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            return null;
        }
    }

}
