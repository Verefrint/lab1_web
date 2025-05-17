<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }
    
    $check = $conn->prepare("SELECT id FROM legal_services.newsletter WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        header("Location: ".$_SERVER['HTTP_REFERER']."?subscribed=1");
    } else {
        $stmt = $conn->prepare("INSERT INTO legal_services.newsletter (email, active) VALUES (?, true)");
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            header("Location: ".$_SERVER['HTTP_REFERER']."?success=1");
        } else {
            header("Location: ".$_SERVER['HTTP_REFERER']."?error=1");
        }
    }
    
    $check->close();
    $stmt->close();
    $conn->close();
    exit();
} else {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}