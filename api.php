<?php

/*
 * API class with all the end points and validation to perform various tasks and rest api process
 *
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/Task.php';
require __DIR__ . '/classes/Note.php';
require __DIR__ . '/AuthMiddleware.php';

class Api {

    private $db;
    protected $auth;
    private $url;

    public function __construct($bypass = false) {
        try {
            $allHeaders = getallheaders();
            $db_connection = new Database();
            $this->db = $db_connection->dbConnection();
            $this->url = $db_connection->url;
            if ($bypass) {
                return;
            }
            $this->auth = new Auth($this->db, $allHeaders);
            if (!$this->auth->isValid()) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 403 You are not allowed to perform this action', true, 403);
                echo json_encode(array("success" => false, "message" => '403 You are not allowed to perform this action'));
                exit();
            }
        } catch (Exception $e) {
            echo json_encode(array("success" => false, "message" => $e->getMessage()));
            return;
        }
    }

    /**
     * Api Function to create new user in the system
     * @Signature JSON {"name": "sud","email": "sud@test.com","password": "test@123"}
     * @endpoint Text http://localhost/assignment/api.php?action=register
     */
    public function registerUser($data) {
        try {

            $returnData = [];
            if (is_object($data) && !empty($data)) {
                $name = trim($data->name);
                $email = trim($data->email);
                $password = trim($data->password);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $returnData = self::message(0, 422, 'Invalid Email Address!');
                } elseif (strlen($password) < 8) {
                    $returnData = self::message(0, 422, 'Your password must be at least 8 characters long!');
                } elseif (strlen($name) < 3) {
                    $returnData = self::message(0, 422, 'Your name must be at least 3 characters long!');
                } else {
                    $check_email = "SELECT `email` FROM `users` WHERE `email`=:email";
                    $check_email_stmt = $this->db->prepare($check_email);
                    $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $check_email_stmt->execute();

                    if ($check_email_stmt->rowCount()) {
                        $returnData = self::message(0, 422, 'This E-mail already in use!');
                    } else {
                        $insert_query = "INSERT INTO `users`(`name`,`email`,`password`) VALUES(:name,:email,:password)";

                        $insert_stmt = $this->db->prepare($insert_query);

                        // DATA BINDING
                        $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                        $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                        $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);

                        $insert_stmt->execute();

                        $returnData = self::message(1, 201, 'You have successfully registered.');
                    }
                }
            }
        } catch (Exception $e) {
            $returnData = self::message(0, 500, $e->getMessage());
            return null;
        }
        echo json_encode($returnData);
        exit();
    }

    /**
     * Api Function to login user in the system and generate JWT token
     * @Signature JSON {"email": "sud@test.com","password": "test@123"}
     * @endpoint Text http://localhost/assignment/api.php?action=login
     * @return text JWT Token
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public function loginUser($data) {
        $returnData = [];
        if (is_object($data) && !empty($data)) {
            $email = trim($data->email);
            $password = trim($data->password);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $returnData = self::message(0, 422, 'Invalid Email Address!');

                // IF PASSWORD IS LESS THAN 8 THE SHOW THE ERROR
            } elseif (strlen($password) < 8) {
                $returnData = self::message(0, 422, 'Your password must be at least 8 characters long!');
            } else {
                try {
                    $fetch_user_by_email = "SELECT * FROM `users` WHERE `email`=:email";
                    $query_stmt = $this->db->prepare($fetch_user_by_email);
                    $query_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $query_stmt->execute();

                    if ($query_stmt->rowCount()) {
                        $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                        $check_password = password_verify($password, $row['password']);
                        if ($check_password) {
                            $jwt = new JwtHandler();
                            $token = $jwt->jwtEncodeData(
                                    $this->url, array("user_id" => $row['id'])
                            );

                            $returnData = [
                                'success' => 1,
                                'message' => 'You have successfully logged in.',
                                'token' => $token
                            ];

                            // IF INVALID PASSWORD
                        } else {
                            $returnData = self::message(0, 422, 'Invalid Password!');
                        }

                        // IF THE USER IS NOT FOUNDED BY EMAIL THEN SHOW THE FOLLOWING ERROR
                    } else {
                        $returnData = self::message(0, 422, 'User not found or Invalid Email Address!');
                    }
                } catch (PDOException $e) {
                    $returnData = self::message(0, 500, $e->getMessage());
                }
            }
        } else {
            $returnData = self::message(0, 422, 'Invalid or Blank Email Address or Password ');
        }
        echo json_encode($returnData);
        exit();
    }

    public function getUsers($data) {
        // logic to retrieve a list of users from the database
        try {
            $email = trim($data->email);
            if (!empty($email)) {
                $fetch_user_by_id = "SELECT `name`,`email` FROM `users` WHERE `email`=:email";
                $query_stmt = $this->db->prepare($fetch_user_by_id);
                $query_stmt->bindValue(':email', $email, PDO::PARAM_INT);
                $query_stmt->execute();
                if ($query_stmt->rowCount()) {
                    $users = $query_stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return false;
                }
            }
        } catch (PDOException $e) {
            echo json_encode(array("success" => false, "message" => $e->getMessage()));
            return null;
        }
        // return the list of users in JSON format
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    /**
     * Api Function to create new Task along with Notes in the system
     * @Signature FORM Data     subject:Example Task
      description:Description of example task
      start_date:2023-05-01
      due_date:2023-05-10
      status:New
      priority:High
      notes: [{"subject": "test test test trst","note":"Hello this is test note"},{"subject": "Example note 2","note":"The world is not enough."}]
     * @endpoint Text http://localhost/assignment/api.php?action=create_task
     * 
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public function createTask($data) {
        $task = new Task();
        $taskId = $task->create($data);
        $returnData = [];
        if ($taskId) {
            $returnData = self::message(1, 201, 'Task created successfully.');
            //release saved data from array 
            unset($data['subject'], $data['description'], $data['start_date'], $data['due_date'], $data['status'], $data['priority']);
            if (!empty($data['notes'])) {
                $notes = new Note();
                $noteSuccess = $notes->create($taskId, $data);
                if ($noteSuccess['success']) {
                    $returnData = self::message(1, 201, 'Task created successfully.');
                } else {
                    $returnData = self::message(0, 500, $noteSuccess['message']);
                }
            }
        }
        echo json_encode($returnData);
        exit();
    }

    /**
     * Api function to fetch task and notes records from the database
     * @param type $data
     * @Signature Query action:get_task
      status:incomplete
      due_date:30-04-2023
      priority:medium
      notes:true
     * @endpoint Text http://localhost/assignment/api.php?action=get_task&status=incomplete&due_date=30-04-2023&priority=medium&notes=true
     * @return ARRAY Fetched array of tasks data with applied conditions and filters
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public function getData($data) {
        try {

            $query = "SELECT t.*, COUNT(n.id) AS note_count,GROUP_CONCAT(n.note SEPARATOR '| ') AS notes
                    FROM tasks t
                    LEFT JOIN notes n ON t.id = n.task_id where 1";
            if (isset($data['status']) && "" != $data['status']) {
                $status = ucfirst($data['status']);
                $query .= " and t.status = '$status' ";
            }
            if (isset($data['due_date']) && "" != $data['due_date']) {
                $due_date = $data['due_date'];
                $query .= " and t.due_date = '$due_date' ";
            }
            if (isset($data['priority']) && "" != $data['priority']) {
                $priority = ucfirst($data['priority']);
                $query .= " and t.priority = '$priority' ";
            }
            $query .= " GROUP BY t.id";

            if (isset($data['notes']) && "" != $data['notes']) {
                $notesBoolean = strtolower($data['notes']);
                if ($notesBoolean == "true") {
                    $query .= " HAVING COUNT(note_count) > 1 ";
                }
                if ($notesBoolean == "false") {
                    $query .= " HAVING COUNT(note_count) <= 1 ";
                }
            }
            $query .= " ORDER BY t.priority ASC, note_count DESC";
            $query_stmt = $this->db->prepare($query);

            $query_stmt->execute();
            $returnData = [];
            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetchAll(PDO::FETCH_ASSOC);
                $dataArray = [];
                foreach ($row as $k => $task) {
                    $dataArray[$k] = $task;
                    if (isset($task['notes'])) {
                        $dataArray[$k]['notes'] = explode("|", $task['notes']);
                    }
                }
                $returnData = self::message(1, 200, 'Tasks fetched successfully.', ["data" => $dataArray]);
            }
        } catch (PDOException $e) {
            echo json_encode(array("success" => false, "message" => $e->getMessage()));
            return null;
        }
        // return the list of users in JSON format
        echo json_encode($returnData);
        exit();
    }

    /**
     * Static function to return passed parameters as combined array 
     * @param int $success 1 for success 0 for failure
     * @param int $status various http status like 200,404 etc.
     * @param text $message output message to be shown
     * @param array $extra any extra array parameters 
     * @return array Array of combined params
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public static function message($success, $status, $message, $extra = []) {
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message
                ], $extra);
    }

}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (empty($action)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' Missing action in request', true, 422);
    echo json_encode(API::message(0, 422, 'Missing action in request'));
}
if (empty($method)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' Missing request method', true, 422);
    echo json_encode(API::message(0, 422, 'Missing request method'));
}
if ($method == 'POST' && $action == 'register') {
    $register = TRUE;
    $usersEndpoint = new Api($register);
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->name) || !isset($data->email) || !isset($data->password) || empty(trim($data->name)) || empty(trim($data->email)) || empty(trim($data->password))
    ) {

        $fields = ['fields' => ['name', 'email', 'password']];
        $returnData = $usersEndpoint->message(0, 422, 'Please Fill in all Required Fields!', $fields);
        echo json_encode($returnData);
        exit();
// IF THERE ARE NO EMPTY FIELDS THEN-
    } else {
        $usersEndpoint->registerUser($data);
    }
}
if ($method == 'POST' && $action == 'login') {

    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->email) || !isset($data->password) || empty(trim($data->email)) || empty(trim($data->password))) {
        $fields = ['fields' => ['email', 'password']];
        $returnData = Api::message(0, 422, 'Please Fill in all Required Fields!', $fields);
        echo json_encode($returnData);
        exit();
    } else {
        $usersEndpoint = new Api(TRUE);
        $usersEndpoint->loginUser($data);
    }
}
// create an defatul instance of the UsersEndpoint class with authentication token
$usersEndpoint = new Api();

// define a route for the getUsers method
if ($method == 'POST' && $action == 'users') {
    $data = json_decode(file_get_contents("php://input"));
    $usersEndpoint->getUsers($data);
}

//create tasks and notes 
if ($method == 'POST' && $action == 'create_task') {
    // Get the data from the request payload
    $data = [
        'subject' => isset($_POST['subject']) ? $_POST['subject'] : "",
        'description' => isset($_POST['description']) ? $_POST['description'] : "",
        'start_date' => isset($_POST['start_date']) ? $_POST['start_date'] : "",
        'due_date' => isset($_POST['due_date']) ? $_POST['due_date'] : "",
        'status' => isset($_POST['status']) ? $_POST['status'] : "",
        'priority' => isset($_POST['priority']) ? $_POST['priority'] : "",
        'notes' => isset($_POST['notes']) ? $_POST['notes'] : "",
        'attachments' => isset($_FILES['attachments']) ? $_FILES['attachments'] : ""
    ];
    if (empty($data['subject']) || empty($data['status']) || empty($data['priority'])) {
        $fields = ['fields' => ['subject', 'status', 'priority']];
        $returnData = Api::message(0, 422, 'Please Fill in all Required Fields!', $fields);
        echo json_encode($returnData);
        exit();
    }
    $attachments = isset($_FILES['attachments']) ? $_FILES['attachments'] : "";
    if (!empty($attachments)) {
        //validating file size
        $max_size = 1000000; // 1 MB in bytes
        $inValidAttachments = array();

        for ($i = 0; $i < count($attachments["name"]); $i++) {
            $file_size = $attachments["size"][$i];
            if ($file_size > $max_size) {
                $inValidAttachments[] = $attachments;
            }
        }
        if (!empty($inValidAttachments)) {
            $returnData = Api::message(0, 422, 'One or more attachments exceed the maximum size of 1MB.');
            echo json_encode($returnData);
            exit();
        }
    }
    $usersEndpoint->createTask($data);
}

//fetch tasks and notes
if ($method == 'GET' && $action == 'get_task') {
    $data['status'] = isset($_GET['status']) ? $_GET['status'] : "";
    $data['due_date'] = isset($_GET['due_date']) ? $_GET['due_date'] : "";
    $data['priority'] = isset($_GET['priority']) ? $_GET['priority'] : "";
    $data['notes'] = isset($_GET['notes']) ? $_GET['notes'] : "";
    if ("" != $data['due_date']) {
        $data['due_date'] = date("Y-m-d", strtotime($data['due_date']));
    }
    $usersEndpoint->getData($data);
}
