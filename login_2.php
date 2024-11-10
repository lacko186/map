// 1. Első lépés: config.php a database kapcsolathoz
<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'kaposvar_transport');

// Kapcsolódás a MySQL szerverhez
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Adatbázis létrehozása
$sql = "CREATE DATABASE IF NOT EXISTS kaposvar_transport";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->select_db(DB_NAME);
?>

// 2. Lépés: register.php - Regisztrációs űrlap
<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    
    if($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name) 
                VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);
            
            if($stmt->execute()) {
                header("location: login.php");
            } else {
                echo "Valami hiba történt. Próbáld újra később.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font: 14px sans-serif; }
        .wrapper { width: 360px; padding: 20px; margin: auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Regisztráció</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Felhasználónév</label>
                <input type="text" name="username" class="form-control" required>
            </div>    
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jelszó</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jelszó megerősítése</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Keresztnév</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Vezetéknév</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Regisztráció">
                <input type="reset" class="btn btn-secondary ml-2" value="Adatok törlése">
            </div>
            <p>Már van fiókod? <a href="login.php">Jelentkezz be itt</a>.</p>
        </form>
    </div>
</body>
</html>

// 3. Lépés: login.php - Bejelentkezési űrlap
<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ?";
    
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        
        if($stmt->execute()) {
            $stmt->store_result();
            
            if($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username, $hashed_password);
                if($stmt->fetch()) {
                    if(password_verify($password, $hashed_password)) {
                        session_start();
                        
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $id;
                        $_SESSION["username"] = $username;
                        
                        header("location: index.php");
                    } else {
                        echo "Érvénytelen jelszó.";
                    }
                }
            } else {
                echo "Nem létező felhasználónév.";
            }
        } else {
            echo "Hiba történt. Próbáld újra később.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font: 14px sans-serif; }
        .wrapper { width: 360px; padding: 20px; margin: auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Bejelentkezés</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Felhasználónév</label>
                <input type="text" name="username" class="form-control" required>
            </div>    
            <div class="form-group">
                <label>Jelszó</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Bejelentkezés">
            </div>
            <p>Nincs még fiókod? <a href="register.php">Regisztrálj most</a>.</p>
        </form>
    </div>
</body>
</html>