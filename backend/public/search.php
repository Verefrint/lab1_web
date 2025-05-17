<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $query = isset($data['query']) ? $conn->real_escape_string(trim($data['query'])) : '';
    
    if (strlen($query) < 2) {
        echo json_encode(['error' => 'Query too short']);
        exit();
    }
    
    $searchQuery = "%$query%";
    $stmt = $conn->prepare("SELECT id, name AS title, short_description AS description, price, CONCAT('services.html#', alias) AS link 
                           FROM services 
                           WHERE name LIKE ? OR short_description LIKE ? OR description LIKE ? OR meta_keywords LIKE ?
                           LIMIT 10");
    $stmt->bind_param("ssss", $searchQuery, $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    echo json_encode(['results' => $services]);
    $stmt->close();
    $conn->close();
    exit();
}

echo json_encode(['error' => 'Invalid request']);
?>