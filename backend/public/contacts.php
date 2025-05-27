<?php
require_once 'config.php';
session_start();

// Handle Authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
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
            exit();
        }
    }
    
    // If authentication fails
    header("Location: ".$_SERVER['HTTP_REFERER']."?error=1");
    exit();
}

// Initialize variables for contact form
$contactErrors = [];
$contactSuccess = false;
$contactFormData = [];
$newsletterError = '';
$newsletterSuccess = false;

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $phone = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
    $subject = $conn->real_escape_string(trim($_POST['subject'] ?? ''));
    $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    
    // Validation
    if (empty($name)) $contactErrors['name'] = 'Пожалуйста, введите ваше имя';
    if (empty($email)) {
        $contactErrors['email'] = 'Пожалуйста, введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactErrors['email'] = 'Неверный формат email';
    }
    if (empty($message)) $contactErrors['message'] = 'Пожалуйста, введите сообщение';
    
    // If no errors, save to database
    if (empty($contactErrors)) {
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $contactSuccess = true;
            $contactFormData = [];
        } else {
            $contactErrors['database'] = 'Ошибка при сохранении данных. Пожалуйста, попробуйте позже.';
        }
        
        $stmt->close();
    } else {
        $contactFormData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message
        ];
    }
}

// Handle Newsletter Subscription
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты | Гестион</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-welcome">
                    Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>
                    <a href="logout.php">Выйти</a>
                </div>
            <?php else: ?>
                <form action="" method="post">
                    <input type="text" name="username" placeholder="Логин" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit">Войти</button>
                    <a href="register.php">Регистрация</a>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <ul class="main-menu">
            <li><a href="main.html">Главная</a></li>
            <li><a href="services.html">Услуги</a></li>
            <li><a href="team.html">Команда</a></li>
            <li class="active"><a href="contacts.php">Контакты</a></li>
            <li><a href="feedback.php">Отзывы</a></li>
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
                <li><a href="#">Запись на консультацию</a></li>
                <li><a href="#">Частые вопросы</a></li>
                <li><a href="#">Отзывы клиентов</a></li>
                <li><a href="#">Вакансии</a></li>
            </ul>
            
            <div class="sidebar-banner">
                <h4>Экстренная помощь</h4>
                <p>Круглосуточная поддержка по телефону:</p>
                <p class="emergency-phone">+7 (495) 987-65-43</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main>
            <h2>Напишите нам</h2>
            <hr>
            
            <?php if ($contactSuccess): ?>
                <div class="alert success">
                    Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.
                </div>
            <?php elseif (!empty($contactErrors)): ?>
                <div class="alert error">
                    <?php foreach ($contactErrors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        
            <section class="contact-form-section">
                <form method="post">
                    <div class="form-group">
                        <label for="name">Ваше имя:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($contactFormData['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($contactFormData['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон:</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($contactFormData['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Тема обращения:</label>
                        <select id="subject" name="subject">
                            <option value="consultation" <?= ($contactFormData['subject'] ?? '') === 'consultation' ? 'selected' : '' ?>>Консультация</option>
                            <option value="service" <?= ($contactFormData['subject'] ?? '') === 'service' ? 'selected' : '' ?>>Заказ услуги</option>
                            <option value="feedback" <?= ($contactFormData['subject'] ?? '') === 'feedback' ? 'selected' : '' ?>>Обратная связь</option>
                            <option value="other" <?= ($contactFormData['subject'] ?? '') === 'other' ? 'selected' : '' ?>>Другое</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Сообщение:</label>
                        <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($contactFormData['message'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-button">Отправить</button>
                </form>
            </section>
            
            <section class="contact-info-section">
                <h3>Наши контакты</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <h4>Адрес:</h4>
                        <p>г. Москва, ул. Юридическая, 15</p>
                        <p>Бизнес-центр "Правовой", офис 405</p>
                    </div>
                    
                    <div class="contact-item">
                        <h4>Телефоны:</h4>
                        <p>+7 (495) 123-45-67 (основной)</p>
                        <p>+7 (495) 987-65-43 (круглосуточный)</p>
                    </div>
                    
                    <div class="contact-item">
                        <h4>Email:</h4>
                        <p>info@gestion.ru (общие вопросы)</p>
                        <p>consult@gestion.ru (консультации)</p>
                    </div>
                    
                    <div class="contact-item">
                        <h4>Режим работы:</h4>
                        <p>Пн-Пт: 9:00 - 18:00</p>
                        <p>Сб: 10:00 - 15:00</p>
                        <p>Вс: выходной</p>
                    </div>
                </div>
                
                <h3>Как добраться</h3>
                <div class="map-container">
                    <!-- Yandex Map Embed -->
                    <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3A1234567890abcdef&amp;source=constructor" width="100%" height="400" frameborder="0"></iframe>
                </div>
            </section>
        </main>
    </div>

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
<?php
$conn->close();
?>