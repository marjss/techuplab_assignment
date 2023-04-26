<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require __DIR__ . '/classes/Database.php';
$db_connection = new Database(TRUE); //the true parameter will check if db exists if not then it will be created first.
$connection = $db_connection->dbConnection();
// Create Users table
$userTableSql = "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";


$insertUser = 'INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, "sud", "sud@test.com", "$2y$10$jPwHUbwvvUpKYUb8l7M/teoB7E6/uhr6TNMHJILwihxJjb5MXMVIm")';
// Create tasks table if it doesn't exist
$taskTableSql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject TEXT NOT NULL,
    description TEXT,
    start_date DATE,
    due_date DATE,
    status ENUM('New', 'Incomplete', 'Complete') NOT NULL,
    priority ENUM('High', 'Medium', 'Low') NOT NULL
)";
// Create notes table if it doesn't exist
$notesTableSql = "CREATE TABLE IF NOT EXISTS notes (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT(6) UNSIGNED NOT NULL,
    subject TEXT NOT NULL,
    attachment TEXT,
    note TEXT,
    FOREIGN KEY (task_id) REFERENCES tasks(id)
)";
// Create notes table if it doesn't exist
$attachmentTableSql = "CREATE TABLE IF NOT EXISTS notes_attachments (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attachment_path TEXT,
    created_at DATE
)";
$userTable = $connection->prepare($userTableSql);
if($userTable->execute()){
    echo "Users table created successfully \n";
    $insert = $connection->prepare($insertUser);
    $insert->execute();
    $taskTable = $connection->prepare($taskTableSql);
    if($taskTable->execute()){
        echo "Table tasks created successfully \n";
        $notesTable = $connection->prepare($notesTableSql);
        if($notesTable->execute()){ 
            echo "Table notes created successfully \n";
            $attachmentTable = $connection->prepare($attachmentTableSql);
            if($attachmentTable->execute()){
                echo "Table notes_attachments created successfully \n";
                
            }
        }
   }
}

//bulk upload data after table creations
$insertTasks = "INSERT INTO `tasks` (`id`, `subject`, `description`, `start_date`, `due_date`, `status`, `priority`) VALUES
(1, 'test subject', 'this is test description. Lorem ipsum doler sit amet. ', '2023-04-23', '2023-04-30', 'New', 'Medium'),
(2, 'new subject again', 'this is new description on the over. ', '2023-04-08', '2023-04-18', 'Incomplete', 'High'),
(3, 'relevance on pro', 'the quick fox jump over the lazy dog. ', '2023-04-10', '2023-04-30', 'Complete', 'Low'),
(4, 'this is new one', 'thoery of relativity is still in under progress', '2023-04-12', '2023-04-25', 'New', 'High'),
(5, 'hello world subject', 'Govt will be publishing new papers masks for covid. ', '2023-04-23', '2023-04-30', 'Incomplete', 'Low'),
(6, 'not cup of tea', 'more subject description on the testing environment. ', '2023-04-21', '2023-04-29', 'Complete', 'High');";
$insertTaskTable = $connection->prepare($insertTasks);
$insertTaskTable->execute();
$insertNotes = "INSERT INTO `notes` (`task_id`, `subject`, `attachment`, `note`) VALUES
(1, 'Lorem ipsum doler sit amet.', '1,2', 'This is note on note'),
(1, 'this is new ', '3', 'New note on the note of note'),
(2, 'quick note one', '4,5', 'Keynote on the piano will be there.'),
(2, 'new note for reference', '4,5', 'Sit on the couch'),
(2, 'falls on the river note', '4,5', 'There is not a show tonight.'),
(2, 'quit India movement notes', '4,5', 'Power of the universe.'),
(3, 'thoery of relativity', '6', 'High note in the books'),
(4, 'Govt will be publishing news ', '7,8', 'Maintainence of the show.'),
(5, 'There will be one good news.', '9', 'Low description on the testing'),
(6, 'more subject description', '10', 'High description on the testing');";
$insertNotesTable = $connection->prepare($insertNotes);
$insertNotesTable->execute();
$insertAttachments = "INSERT INTO notes_attachments (attachment_path, created_at) VALUES
('attachment1.jpg','2023-04-12'),
('attachment2.jpg','2023-04-13'),
('attachment3.jpg','2023-04-14'),
('attachment4.jpg','2023-04-10'),
('attachment5.jpg','2023-04-11'),
('attachment6.jpg','2023-03-18'),
('attachment7.jpg','2023-03-02'),
('attachment8.jpg','2023-02-24'),
('attachment9.jpg','2023-04-05'),
('attachment10.jpg','2023-04-01');";
$insertNotesAttachmentTable = $connection->prepare($insertAttachments);
$insertNotesAttachmentTable->execute();