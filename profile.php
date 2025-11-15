<?php
session_start();
require_once 'db.php';

// Redirect to login if user is not logged in
$user_id = $_SESSION['user_id'];

// Fetch user details safely using MySQLi
$sql = "SELECT first_name, last_name, username, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    $user = null; // fallback if statement preparation fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | FitCast</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7ff;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #004aad;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #0066ff;
        }

        .main-content {
            flex: 1;
            background: white;
            padding: 40px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-header img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin-right: 20px;
            border: 3px solid #004aad;
        }

        .profile-header h1 {
            margin: 0;
            color: #004aad;
        }

        .profile-info {
            background: #f0f5ff;
            padding: 20px;
            border-radius: 10px;
        }

        .profile-info p {
            font-size: 16px;
            margin: 10px 0;
        }

        .profile-info strong {
            color: #004aad;
        }

        .profile-actions {
            margin-top: 25px;
        }

        .profile-actions a {
            text-decoration: none;
            background: #004aad;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-right: 10px;
            transition: 0.3s;
        }

        .profile-actions a:hover {
            background: #0066ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>FitCast</h2>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="weather.php">🌤 Weather</a>
            <a href="profile.php">👤 Profile</a>
            <a href="logout.php">🚪 Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if ($user): ?>
                <div class="profile-header">
                    <img src="images/default-avatar.png" alt="Profile Picture">
                    <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                </div>

                <div class="profile-info">
                    <h2>Personal Information</h2>
                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php">✏️ Edit Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            <?php else: ?>
                <h2>User not found</h2>
                <p>There was an error retrieving your profile information.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
