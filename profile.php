<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, profile_picture, background_color FROM users WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$username = $user['username'] ?? 'User';
$profile_picture = $user['profile_picture'] ?? 'default.jpg';
$background_color = $user['background_color'] ?? '#ffffff';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #1a1a2e;
            color: white;
            padding-top: 80px; /* Adjust based on the header height */
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #4a0072, #d500f9);
            color: white;
            padding: 20px 30px;
            border-radius: 10px 10px 0 0;
            z-index: 1000;
        }

        .header-left {
            font-size: 24px;
            font-weight: bold;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 18px;
        }

        .user-dropdown {
            position: relative;
            cursor: pointer;
        }

        .user-dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #29293d;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            width: 200px;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px;
            text-decoration: none;
            color: white;
        }

        .dropdown-menu a:hover {
            background: #4a0072;
        }

        .profile-container {
            text-align: center;
            padding: 20px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffcc00;
        }

        h2 {
            font-size: 24px;
            color: #ffcc00;
            font-weight: bold;
        }

        .form-container {
            background: #29293d;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            margin: 20px auto;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.2);
        }

        .form-container label {
            display: block;
            margin-top: 10px;
            font-size: 16px;
        }

        .form-container input[type="file"],
        .form-container input[type="color"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background: #03dac6;
            color: black;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        button:hover {
            background: #02c2ad;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">Welcome, <?php echo htmlspecialchars($username); ?>!</div>
    <div class="header-right">
        <div class="user-dropdown">
            <span>👤 <?php echo htmlspecialchars($username); ?> ▼</span>
            <div class="dropdown-menu">
                <a href="profile.php">👤 Profile</a>
                <a href="lang.php">🛍 View Products</a> 
                <a href="pastorders.php">📦 Past Orders</a>
                <a href="vouchers.php">🎟 Vouchers</a>
                <a href="help.php">❓ Help Center</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>
        <div class="cart-icon" onclick="toggleCart()">
            🛒 Cart: <span id="cartCount">0</span> items
        </div>
    </div>
</div>

<div class="profile-container">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
    
    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="form-container">
        <label>Change Profile Picture:</label><br>
        <input type="file" name="profile_picture"><br>
        <label>Change Password:</label><br>
        <input type="password" name="new_password"><br>

        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>
