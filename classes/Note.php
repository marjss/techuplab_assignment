<?php

class Note extends Database {

    private $db;

    function __construct() {
        $db_connection = new Database();
        $this->db = $db_connection->dbConnection();
    }

    function create($task_id, $data) {
        try {
            $return = array("success" => false, "message" => "Error in saving notes.");
            $notes = isset($data['notes']) ? json_decode($data['notes'], true) : "";
            $attachments = isset($data['attachments']) ? $data['attachments'] : "";
            $task_id = isset($task_id) ? $task_id : "";
            $attachmentIds = [];
            $implodedIds = '';
            // Insert each attachment into the attachments table
            $target_dir = "attachments/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            if (!empty($attachments['name'][0])) {
                for ($i = 0; $i < count($attachments["name"]); $i++) {
                    if ('' !== $attachments["tmp_name"][$i]) {
                        $attachmentName = time() . "_" . basename($attachments["name"][$i]);
                        $currentDate = date("Y-m-d");
                        $target_file = $target_dir . $attachmentName;
                        if (move_uploaded_file($attachments["tmp_name"][$i], $target_file)) {
                            // echo "The file ". htmlspecialchars(basename($attachments["name"][$i])) . " has been uploaded.";
                            $stmt = $this->db->prepare('INSERT INTO notes_attachments (attachment_path, created_at) VALUES (?, ?)');
                            $stmt->execute([$attachmentName, $currentDate]);
                            $attachmentIds[] = $this->db->lastInsertId();
                        }
                    }
                }
            }
            if (!empty($attachmentIds)) {
                $implodedIds = implode(",", $attachmentIds);
            }
            if (is_array($notes) && !empty($notes) && ($task_id != "")) {
                // Insert each note into the notes table
                foreach ($notes as $note) {
                    $stmt = $this->db->prepare('INSERT INTO notes (task_id, subject,attachment, note) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$task_id, $note['subject'], $implodedIds, $note['note']]);
                }
                $return = ["success" => true, "message" => "Record created successfully."];
            } else {
                $return = ["success" => false, "message" => "Notes must be array of objects."];
            }
        } catch (Exception $e) {

            $return = ["success" => false, "message" => $e->getMessage()];
        }
        return $return;
    }

}
