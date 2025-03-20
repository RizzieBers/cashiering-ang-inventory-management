<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "protein_database";

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

// Login process
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password']) && !isset($_POST['signup_username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Directly check for admin credentials
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: bar.php"); // Redirect to bar.php for admin
        exit;
    }

    // Proceed with regular user login
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            header("Location: home.html"); // Redirect to home.html for regular users
            exit;
        } else {
            // Set error message for incorrect password
            $loginError = "Invalid username or password.";
        }
    } else {
        // Set error message for invalid username
        $loginError = "Invalid username or password.";
    }
    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Log in / Sign up</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(90deg, #FF7E39, #E92B8A);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            width: 80%;
            max-width: 1000px;
            justify-content: space-between;
            align-items: center; /* Center-aligns items vertically */
            gap: 20px; /* Adds space between video and form container */
        }

        .promo-section {
            flex: 1; /* Adjusts size relative to .form-container */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .promo-video {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 10px;
        }

        .form-container {
            flex: 1; /* Adjusts size relative to .promo-section */
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }


        h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 25px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 25px;
            background-color: #f7f7f7;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #ff7e5f;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #e96443;
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
            color: #666;
        }

        .toggle-link a {
            color: #ff7e5f;
            text-decoration: none;
            font-weight: bold;
        }

        .toggle-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function showForm(formType) {
            const loginContainer = document.querySelector('.login-container');
            const signupContainer = document.querySelector('.signup-container');

            if (formType === 'login') {
                loginContainer.style.display = 'block';
                signupContainer.style.display = 'none';
            } else {
                loginContainer.style.display = 'none';
                signupContainer.style.display = 'block';
            }
        }

        window.onload = function() {
            showForm('login'); // Show login form by default
        }
    </script>

    
</head>
<body>

<div class="container">
    <!-- Promo Video Section -->
    <div class="promo-section">
        <video class="promo-video" autoplay loop muted>
            <source src="uploads/promo.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Combined Login and Signup Container -->
        <div class="login-container">
            <h2>Account Login</h2>
            <?php if (!empty($loginError)) : ?>
                <p class="error"><?= htmlspecialchars($loginError); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
            <p class="toggle-link">Don't have an account? <a href="#" onclick="showForm('signup')">Sign Up</a></p>
        </div>

        <div class="signup-container" style="display: none;">
            <h2>Customer Signup</h2>
            <?php if (!empty($successMessage)) : ?>
                <p class="success"><?= htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="signup_username" placeholder="Username" required>
                <input type="text" name="phone" placeholder="Phone Number (10 digits)" required>
                <input type="password" name="signup_password" placeholder="Password (min 6 characters)" required>
                <input type="submit" value="Sign Up">
                <p class="error"><?= htmlspecialchars($usernameError); ?></p>
                <p class="error"><?= htmlspecialchars($phoneError); ?></p>
                <p class="error"><?= htmlspecialchars($passwordError); ?></p>
            </form>
            <p class="toggle-link">Already have an account? <a href="#" onclick="showForm('login')">Login</a></p>
        </div>
    </div>
</div>

</body>
</html>

