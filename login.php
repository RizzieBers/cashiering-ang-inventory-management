<?php
session_start(); // Start the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WEB";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error and success messages
$usernameError = $phoneError = $passwordError = $successMessage = $loginError = "";

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Signup process
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_username'], $_POST['phone'], $_POST['signup_password'])) {
    $signupUsername = trim($_POST['signup_username']);
    $signupPhone = trim($_POST['phone']);
    $signupPassword = trim($_POST['signup_password']);
    
    // Validation checks
    if (strlen($signupUsername) < 3) {
        $usernameError = "Username must be at least 3 characters.";
    } elseif (!preg_match('/^\d{10}$/', $signupPhone)) {
        $phoneError = "Phone number must be 10 digits.";
    } elseif (strlen($signupPassword) < 6) {
        $passwordError = "Password must be at least 6 characters.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($signupPassword, PASSWORD_BCRYPT);
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (username, phone, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Error in prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $signupUsername, $signupPhone, $hashedPassword);
        
        if ($stmt->execute()) {
            $successMessage = "Account created successfully!";
        } else {
            $usernameError = "Error inserting data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Login process
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password']) && !isset($_POST['signup_username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Admin login
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['logged_in'] = true;
        $_SESSION['display_name'] = 'Admin';
        header("Location: bar.php");
        exit;
    }

    // Regular user login
    $stmt = $conn->prepare("SELECT id, username, password, phone FROM users WHERE username = ?");
    if (!$stmt) {
        die("Error in prepare statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $dbUsername, $hashedPassword, $phone);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['display_name'] = $dbUsername;
            $_SESSION['phone'] = $phone;
            header("Location: lang.php");
            exit;
        } else {
            $loginError = "Invalid username or password.";
        }
    } else {
        $loginError = "Invalid username or password.";
    }
    $stmt->close();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
<title>Log in</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
       body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #1a1a2e;
    color: white;
    padding-top: 80px; /* Adjust this based on the header height */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    width: 100%;
    max-width: 400px;
}

.login-container, .signup-container {
    background-color: #29293d;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    text-align: center;
    display: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-container.active, .signup-container.active {
    display: block;
}

h2 {
    color: white;
    font-weight: 600;
    margin-bottom: 25px;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 25px;
    background-color: #1a1a2e;
    color: white;
    font-size: 16px;
}

input[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #03dac6;
    color: black;
    border: none;
    border-radius: 25px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 20px;
}

input[type="submit"]:hover {
    background-color: #02c7b6;
}

.error, .success {
    font-size: 14px;
    font-weight: 500;
    margin-top: 15px;
}

.error { color: #e74c3c; }
.success { color: #27ae60; }

.toggle-link {
    margin-top: 20px;
    color: white;
}

.toggle-link a {
    color: #03dac6;
    text-decoration: none;
    font-weight: bold;
}

.toggle-link a:hover {
    text-decoration: underline;
}

    </style>
    <script>
        function toggleForms() {
            document.querySelector('.login-container').classList.toggle('active');
            document.querySelector('.signup-container').classList.toggle('active');
        }

        window.onload = function() {
            document.querySelector('.login-container').classList.add('active');
        }
    </script>
</head>
<body>

<div class="container">
    <!-- Login Container -->
    <div class="login-container active">
        <h2>Account Login</h2>
        <!-- Display login error if it exists -->
        <?php if (!empty($loginError)) : ?>
            <p class="error"><?= htmlspecialchars($loginError); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p class="toggle-link">Don't have an account? <a href="#" onclick="toggleForms()">Sign Up</a></p>
    </div>

    <!-- Signup Container -->
    <div class="signup-container">
        <h2>Customer Signup</h2>
        <?php if (!empty($successMessage)) : ?>
            <p class="success"><?= htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="signup_username" placeholder="Username" required>
            <input type="text" name="phone" placeholder="Phone Number (10 digits)" required>
            <input type="password" name="signup_password" placeholder="Password (min 6 characters)" required>
            <input type="submit" value="Sign Up">
            <!-- Display signup errors if they exist -->
            <p class="error"><?= htmlspecialchars($usernameError); ?></p>
            <p class="error"><?= htmlspecialchars($phoneError); ?></p>
            <p class="error"><?= htmlspecialchars($passwordError); ?></p>
        </form>
        <p class="toggle-link">Already have an account? <a href="#" onclick="toggleForms()">Login</a></p>
    </div>
</div>

</body>
</html>
