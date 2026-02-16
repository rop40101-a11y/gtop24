<?php
// Simple JSON Login API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include '../../common/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    // Support both JSON and Form Data
    $email = isset($data['email']) ? $data['email'] : $_POST['email'];
    $pass = isset($data['password']) ? $data['password'] : $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' OR phone='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            echo json_encode([
                'status' => true,
                'message' => 'Login Successful',
                'user_id' => $user['id'],
                'name' => $user['name'],
                'balance' => $user['balance']
            ]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Invalid Password']);
        }
    } else {
        echo json_encode(['status' => false, 'message' => 'User not found']);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid Request Method']);
}
?>
