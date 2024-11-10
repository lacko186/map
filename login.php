<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'volan_app';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kapcsolódási hiba: " . $e->getMessage());
}

// Regisztráció kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'registerUsername', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'registerEmail', FILTER_SANITIZE_EMAIL);
    $password = $_POST['registerPassword'];
    $passwordConfirm = $_POST['registerPasswordConfirm'];

    $errors = [];
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "A felhasználónévnek legalább 3 karakter hosszúnak kell lennie!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen email cím!";
    }
    if (strlen($password) < 6) {
        $errors[] = "A jelszónak legalább 6 karakter hosszúnak kell lennie!";
    }
    if ($password !== $passwordConfirm) {
        $errors[] = "A jelszavak nem egyeznek!";
    }

    // Email és felhasználónév ellenőrzése
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Ez az email cím vagy felhasználónév már foglalt!";
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $_SESSION['success_message'] = "Sikeres regisztráció! Most már bejelentkezhetsz.";
            header("Location: index.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Hiba történt a regisztráció során: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: index.php");
        exit();
    }
}

// Bejelentkezés kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'loginEmail', FILTER_SANITIZE_EMAIL);
    $password = $_POST['loginPassword'];
    
    try {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                setcookie('remember_token', $token, time() + (86400 * 30), '/');
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Helytelen email/felhasználónév vagy jelszó!";
            header("Location: index.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Hiba történt a bejelentkezés során!";
        header("Location: index.php");
        exit();
    }
}

// Kijelentkezés kezelése
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    header("Location: index.php");
    exit();
}

// Remember me ellenőrzése
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE remember_token = ? AND token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
}
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaposvári Volán - Bejelentkezés</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dark-blue: #1a237e;
            --yellow: #ffd700;
            --black: #212121;
            --gray: #757575;
            --light-gray: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--dark-blue) 0%, #000051 100%);
            padding: 1rem;
        }

        .container {
            background-color: white;
            border-radius: 1.5rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.4s ease forwards;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--dark-blue);
        }

        .logo i {
            font-size: 3rem;
            color: var(--yellow);
            margin-bottom: 1rem;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .form-header::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background-color: var(--yellow);
            margin: 0.5rem auto;
            border-radius: 2px;
        }

        .form-header h1 {
            font-size: 1.75rem;
            color: var(--dark-blue);
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .social-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .google-btn {
            background-color: #fff;
            color: var(--black);
            border: 1px solid var(--gray);
        }

        .facebook-btn {
            background-color: #1877f2;
            color: white;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: var(--gray);
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.3s ease forwards;
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--dark-blue);
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .forgot-password {
            color: var(--dark-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background-color: var(--dark-blue);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover {
            background-color: #000051;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .toggle-form {
            text-align: center;
            margin-top: 1.5rem;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: var(--dark-blue);
            font-size: 0.875rem;
            cursor: pointer;
            transition: color 0.2s;
            font-weight: 500;
        }

        .toggle-btn:hover {
            color: #000051;
            text-decoration: underline;
        }

        .hidden {
            display: none;
        }

        /* Reszponzív design */
        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            .social-login {
                flex-direction: column;
            }

            .social-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="error-messages">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="logo">
            <i class="fas fa-bus"></i>
            <h2>Kaposvári Volán</h2>
        </div>
        
        <div class="form-header">
            <h1 id="formTitle">Bejelentkezés</h1>
        </div>

        <div class="social-login">
            <button class="social-btn google-btn">
                <i class="fab fa-google"></i>
                Google
            </button>
            <button class="social-btn facebook-btn">
                <i class="fab fa-facebook-f"></i>
                Facebook
            </button>
        </div>

        <div class="divider">
            <span>vagy</span>
        </div>
        
        <!-- Módosított login form -->
        <form id="loginForm" action="index.php" method="POST">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label for="loginEmail">Email cím vagy felhasználónév</label>
                <input type="text" id="loginEmail" name="loginEmail" placeholder="pelda@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="loginPassword">Jelszó</label>
                <input type="password" id="loginPassword" name="loginPassword" placeholder="••••••••" required>
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember" checked>
                    <span>Emlékezz rám</span>
                </label>
                <a href="#" class="forgot-password">Elfelejtett jelszó?</a>
            </div>
            
            <button type="submit" class="submit-btn">Bejelentkezés</button>
        </form>

        <!-- Módosított register form -->
        <form id="registerForm" action="index.php" method="POST" class="hidden">
            <input type="hidden" name="register" value="1">
            <div class="form-group">
                <label for="registerUsername">Felhasználónév</label>
                <input type="text" id="registerUsername" name="registerUsername" placeholder="Felhasználónév" required>
            </div>

            <div class="form-group">
                <label for="registerEmail">Email cím</label>
                <input type="email" id="registerEmail" name="registerEmail" placeholder="pelda@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="registerPassword">Jelszó</label>
                <input type="password" id="registerPassword" name="registerPassword" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="registerPasswordConfirm">Jelszó megerősítése</label>
                <input type="password" id="registerPasswordConfirm" name="registerPasswordConfirm" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="submit-btn">Regisztráció</button>
        </form>

        <div class="toggle-form">
            <button type="button" class="toggle-btn" id="toggleForm">
                Nincs még fiókod? Regisztrálj!
            </button>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const toggleBtn = document.getElementById('toggleForm');
        const formTitle = document.getElementById('formTitle');
        let isLoginForm = true;

        toggleBtn.addEventListener('click', () => {
            isLoginForm = !isLoginForm;
            
            if (isLoginForm) {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                formTitle.textContent = 'Bejelentkezés';
                toggleBtn.textContent = 'Nincs még fiókod? Regisztrálj!';
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                formTitle.textContent = 'Regisztráció';
                toggleBtn.textContent = 'Már van fiókod? Jelentkezz be!';
            }

            // Reset forms
            loginForm.reset();
            registerForm.reset();

            // Replay animations
            document.querySelector('.container').style.animation = 'none';
            document.querySelector('.container').offsetHeight;
            document.querySelector('.container').style.animation = '';
        });
    </script>
</body>
</html>
