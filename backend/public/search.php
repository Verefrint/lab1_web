<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Referrer-Policy: no-referrer-when-downgrade');

// Разрешаем preflight запросы OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Получаем данные из POST или GET (для тестирования)
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$searchQuery = trim($data['query'] ?? '');

if (empty($searchQuery)) {
    http_response_code(400);
    echo json_encode(['error' => 'Search query is required']);
    exit;
}

// Подключение к БД
$conn = new mysqli('db', 'user', 'password', 'legal_services');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset('utf8mb4');

// Подготовленный запрос
$stmt = $conn->prepare("
    SELECT id, title, description, price, link 
    FROM services 
    WHERE title LIKE CONCAT('%', ?, '%') 
    OR description LIKE CONCAT('%', ?, '%') 
    OR keywords LIKE CONCAT('%', ?, '%')
");
$stmt->bind_param('sss', $searchQuery, $searchQuery, $searchQuery);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Query execution failed']);
    exit;
}

$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'results' => $services
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>