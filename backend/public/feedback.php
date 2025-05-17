<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Initialize variables
$feedbackErrors = [];
$feedbackSuccess = false;
$feedbackFormData = [];
$clientReviews = [];

// Process feedback form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    // Sanitize inputs
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $rating = (int)($_POST['rating'] ?? 0);
    $services = isset($_POST['services']) ? $_POST['services'] : [];
    $lawyer = $conn->real_escape_string(trim($_POST['lawyer'] ?? ''));
    $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    $improvements = isset($_POST['improvements']) ? $_POST['improvements'] : [];
    
    // Validate inputs
    if (empty($name)) $feedbackErrors['name'] = 'Пожалуйста, введите ваше имя';
    if (empty($email)) {
        $feedbackErrors['email'] = 'Пожалуйста, введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedbackErrors['email'] = 'Неверный формат email';
    }
    if ($rating < 1 || $rating > 5) $feedbackErrors['rating'] = 'Пожалуйста, оцените нашу работу';
    if (empty($message)) $feedbackErrors['message'] = 'Пожалуйста, оставьте ваш отзыв';
    
    if (empty($feedbackErrors)) {
        // Convert arrays to strings for database storage
        $servicesStr = !empty($services) ? implode(',', $services) : '';
        $improvementsStr = !empty($improvements) ? implode(',', $improvements) : '';
        
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, rating, services, lawyer, message, improvements) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $name, $email, $rating, $servicesStr, $lawyer, $message, $improvementsStr);
        
        if ($stmt->execute()) {
            $feedbackSuccess = true;
            $feedbackFormData = [];
        } else {
            $feedbackErrors['database'] = 'Ошибка при сохранении отзыва. Пожалуйста, попробуйте позже.';
        }
        
        $stmt->close();
    } else {
        // Preserve form data for re-display
        $feedbackFormData = [
            'name' => $name,
            'email' => $email,
            'rating' => $rating,
            'services' => $services,
            'lawyer' => $lawyer,
            'message' => $message,
            'improvements' => $improvements
        ];
    }
}

// Process newsletter form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $newsletterEmail = $conn->real_escape_string(trim($_POST['newsletter_email'] ?? ''));
    
    if (empty($newsletterEmail)) {
        $_SESSION['newsletter_error'] = 'Пожалуйста, введите email';
    } elseif (!filter_var($newsletterEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['newsletter_error'] = 'Неверный формат email';
    } else {
        $check = $conn->prepare("SELECT id FROM newsletter WHERE email = ?");
        $check->bind_param("s", $newsletterEmail);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $_SESSION['newsletter_error'] = 'Этот email уже подписан';
        } else {
            $stmt = $conn->prepare("INSERT INTO newsletter (email, active) VALUES (?, 1)");
            $stmt->bind_param("s", $newsletterEmail);
            
            if ($stmt->execute()) {
                $_SESSION['newsletter_success'] = 'Спасибо за подписку!';
            } else {
                $_SESSION['newsletter_error'] = 'Ошибка при подписке. Пожалуйста, попробуйте позже.';
            }
            $stmt->close();
        }
        $check->close();
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch client reviews from database
$reviewsQuery = $conn->query("SELECT * FROM feedback ORDER BY submission_date DESC LIMIT 5");
if ($reviewsQuery) {
    while ($review = $reviewsQuery->fetch_assoc()) {
        $clientReviews[] = [
            'name' => htmlspecialchars($review['name']),
            'rating' => (int)$review['rating'],
            'date' => date('d.m.Y', strtotime($review['submission_date'])),
            'message' => htmlspecialchars($review['message']),
            'lawyer' => htmlspecialchars($review['lawyer'])
        ];
    }
    $reviewsQuery->free();
}

// Close connection at the very end
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы | Гестион</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo-container">
            <h1>Гестион</h1>
            <p>Профессиональные юридические услуги</p>
        </div>
        <div class="auth-container">
            <form action="auth.php" method="post">
                <input type="text" placeholder="Логин" required>
                <input type="password" placeholder="Пароль" required>
                <button type="submit">Войти</button>
                <a href="#">Регистрация</a>
            </form>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <ul class="main-menu">
            <li><a href="main.html">Главная</a></li>
            <li><a href="services.html">Услуги</a></li>
            <li><a href="team.html">Команда</a></li>
            <li><a href="contacts.php">Контакты</a></li>
            <li class="active"><a href="feedback.php">Отзывы</a></li>
            <li class="search-container">
                <input type="text" placeholder="Поиск...">
                <button type="submit">Найти</button>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Быстрые ссылки</h3>
            <ul>
                <li><a href="services.html">Наши услуги</a></li>
                <li><a href="team.html">Наши юристы</a></li>
                <li><a href="feedback.php">Оставить отзыв</a></li>
                <li><a href="#">Частые вопросы</a></li>
            </ul>
            
            <div class="sidebar-banner">
                <h4>Акция для клиентов</h4>
                <p>Оставьте отзыв и получите скидку 10% на следующую услугу!</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main>
            <h2>Оставить отзыв</h2>
            <hr>
            
            <?php if ($feedbackSuccess): ?>
                <div class="alert success">
                    Спасибо! Ваш отзыв успешно отправлен. Мы ценим ваше мнение!
                </div>
            <?php elseif (!empty($feedbackErrors)): ?>
                <div class="alert error">
                    <?php foreach ($feedbackErrors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="feedback-intro">
                <p>Мы ценим ваше мнение и стремимся улучшать качество наших услуг. Пожалуйста, поделитесь вашими впечатлениями о работе с нашей компанией.</p>
            </div>
            
            <form class="feedback-form" method="post">
                <!-- Name field -->
                <div class="form-group">
                    <label for="name">Ваше имя:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($feedbackFormData['name'] ?? '') ?>" required placeholder="Иванов Иван">
                </div>
                
                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($feedbackFormData['email'] ?? '') ?>" required placeholder="example@mail.ru">
                </div>
                
                <!-- Rating radio buttons -->
                <div class="form-group">
                    <label>Оцените нашу работу:</label>
                    <div class="radio-group">
                        <?php $currentRating = $feedbackFormData['rating'] ?? 0; ?>
                        <label>
                            <input type="radio" name="rating" value="5" <?= $currentRating == 5 ? 'checked' : '' ?>> Отлично
                        </label>
                        <label>
                            <input type="radio" name="rating" value="4" <?= $currentRating == 4 ? 'checked' : '' ?>> Хорошо
                        </label>
                        <label>
                            <input type="radio" name="rating" value="3" <?= $currentRating == 3 ? 'checked' : '' ?>> Удовлетворительно
                        </label>
                        <label>
                            <input type="radio" name="rating" value="2" <?= $currentRating == 2 ? 'checked' : '' ?>> Плохо
                        </label>
                        <label>
                            <input type="radio" name="rating" value="1" <?= $currentRating == 1 ? 'checked' : '' ?>> Очень плохо
                        </label>
                    </div>
                </div>
                
                <!-- Services checkboxes -->
                <div class="form-group">
                    <label>Какими услугами вы пользовались? (отметьте все подходящие):</label>
                    <div class="checkbox-group">
                        <?php 
                        $selectedServices = $feedbackFormData['services'] ?? [];
                        $serviceOptions = [
                            'corporate' => 'Корпоративное право',
                            'tax' => 'Налоговое консультирование',
                            'labor' => 'Трудовое право',
                            'realty' => 'Недвижимость',
                            'bankruptcy' => 'Банкротство'
                        ];
                        
                        foreach ($serviceOptions as $value => $label): ?>
                            <label>
                                <input type="checkbox" name="services[]" value="<?= $value ?>" 
                                    <?= in_array($value, $selectedServices) ? 'checked' : '' ?>>
                                <?= $label ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Lawyer dropdown -->
                <div class="form-group">
                    <label for="lawyer">С каким юристом вы работали?</label>
                    <select id="lawyer" name="lawyer">
                        <?php $selectedLawyer = $feedbackFormData['lawyer'] ?? ''; ?>
                        <option value="">-- Выберите юриста --</option>
                        <option value="ivanov" <?= $selectedLawyer == 'ivanov' ? 'selected' : '' ?>>Иванов Петр Сергеевич</option>
                        <option value="smirnova" <?= $selectedLawyer == 'smirnova' ? 'selected' : '' ?>>Смирнова Елена Владимировна</option>
                        <option value="kuznetsov" <?= $selectedLawyer == 'kuznetsov' ? 'selected' : '' ?>>Кузнецов Андрей Михайлович</option>
                        <option value="petrova" <?= $selectedLawyer == 'petrova' ? 'selected' : '' ?>>Петрова Ольга Игоревна</option>
                        <option value="other" <?= $selectedLawyer == 'other' ? 'selected' : '' ?>>Другой/Не помню</option>
                    </select>
                </div>
                
                <!-- Message textarea -->
                <div class="form-group">
                    <label for="message">Ваш отзыв:</label>
                    <textarea id="message" name="message" rows="5" required placeholder="Опишите ваши впечатления..."><?= htmlspecialchars($feedbackFormData['message'] ?? '') ?></textarea>
                </div>
                
                <!-- Improvements multiselect -->
                <div class="form-group">
                    <label for="improvements">Что нам улучшить? (выберите до 3 пунктов):</label>
                    <select id="improvements" name="improvements[]" multiple size="4">
                        <?php 
                        $selectedImprovements = $feedbackFormData['improvements'] ?? [];
                        $improvementOptions = [
                            'speed' => 'Скорость работы',
                            'communication' => 'Коммуникацию с клиентом',
                            'price' => 'Ценовую политику',
                            'quality' => 'Качество услуг',
                            'info' => 'Информативность',
                            'other' => 'Другое'
                        ];
                        
                        foreach ($improvementOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= in_array($value, $selectedImprovements) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>Удерживайте Ctrl для выбора нескольких пунктов</small>
                </div>
                
                <!-- Submit button -->
                <div class="form-group">
                    <button type="submit" class="submit-button">Отправить отзыв</button>
                </div>
            </form>

          
            
            <!-- Existing reviews section -->
            <section class="reviews-section">
            <h3>Отзывы наших клиентов</h3>
            
            <?php if (empty($clientReviews)): ?>
                <div class="alert info">
                    Пока нет отзывов. Будьте первым!
                </div>
            <?php else: ?>
                <?php foreach ($clientReviews as $review): ?>
                    <div class="review">
                        <div class="review-header">
                            <h4><?= $review['name'] ?></h4>
                            <div class="rating">
                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                            </div>
                            <time datetime="<?= $review['date'] ?>"><?= $review['date'] ?></time>
                        </div>
                        <p><?= $review['message'] ?></p>
                        <?php if (!empty($review['lawyer'])): ?>
                            <div class="lawyer-mention">
                                Юрист: <?= $review['lawyer'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <a href="#" class="all-reviews-link">Все отзывы</a>
        </section>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Контакты</h4>
                <p>г. Москва, ул. Юридическая, 15</p>
                <p>Телефон: +7 (495) 123-45-67</p>
                <p>Email: info@gestion.ru</p>
            </div>
            <div class="footer-section">
                <h4>Режим работы</h4>
                <p>Пн-Пт: 9:00 - 18:00</p>
                <p>Сб: 10:00 - 15:00</p>
                <p>Вс: выходной</p>
            </div>
            <div class="footer-section">
                <h4>Подписаться на новости</h4>
                <form method="post">
                    <?php if (isset($_SESSION['newsletter_success'])): ?>
                        <div class="alert success">
                            <?= htmlspecialchars($_SESSION['newsletter_success']) ?>
                            <?php unset($_SESSION['newsletter_success']); ?>
                        </div>
                    <?php elseif (isset($_SESSION['newsletter_error'])): ?>
                        <div class="alert error">
                            <?= htmlspecialchars($_SESSION['newsletter_error']) ?>
                            <?php unset($_SESSION['newsletter_error']); ?>
                        </div>
                    <?php endif; ?>
                    <input type="email" name="newsletter_email" placeholder="Ваш email" required>
                    <button type="submit">Подписаться</button>
                </form>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 Юридическая компания "Гестион". Все права защищены.</p>
        </div>
    </footer>
</body>
</html>