<?php
require_once 'config.php';

$categories = [
    'Корпоративное право',
    'Налоговое консультирование',
    'Трудовое право',
    'Недвижимость и строительство',
    'Банкротство',
    'Интеллектуальная собственность'
];

foreach ($categories as $category) {
    $stmt = $conn->prepare("INSERT INTO service_categories (name) VALUES (?)");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $stmt->close();
}

$services = [
    [
        'category_id' => 1,
        'name' => 'Регистрация ООО',
        'alias' => 'corporate',
        'short_description' => 'Полная юридическая регистрация Общества с Ограниченной Ответственностью "под ключ"',
        'description' => 'Detailed description of the service...',
        'price' => 15000.00,
        'image' => 'static/lawyers.jpg',
        'meta_keywords' => 'регистрация ооо создание фирмы',
        'meta_description' => 'Professional company registration services',
        'meta_title' => 'Регистрация ООО | Гестион'
    ],
];

foreach ($services as $service) {
    $stmt = $conn->prepare("INSERT INTO services (category_id, name, alias, short_description, description, price, image, meta_keywords, meta_description, meta_title) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdssss", 
        $service['category_id'],
        $service['name'],
        $service['alias'],
        $service['short_description'],
        $service['description'],
        $service['price'],
        $service['image'],
        $service['meta_keywords'],
        $service['meta_description'],
        $service['meta_title']
    );
    $stmt->execute();
    $stmt->close();
}

echo "Database initialized successfully!";
$conn->close();
?>