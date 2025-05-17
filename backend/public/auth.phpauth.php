<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ".$_SERVER['HTTP_REFERER']);
        } else {
            // Invalid password
            header("Location: ".$_SERVER['HTTP_REFERER']."?error=1");
        }
    } else {
        // User not found
        header("Location: ".$_SERVER['HTTP_REFERER']."?error=1");
    }
    
    $stmt->close();
    $conn->close();
    exit();
}

header("Location: ".$_SERVER['HTTP_REFERER']);
?>