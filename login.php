<?php
session_start();
include 'header.php';
require_once 'db.php'; // should contain $conn = mysqli_connect(...);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {

        // -------------------
        // 1. Check Admin Table
        // -------------------
        $sqlAdmin = "SELECT id, username, first_name, last_name, email, password, role 
                     FROM admins WHERE username = ?";
        $stmtAdmin = mysqli_prepare($conn, $sqlAdmin);
        mysqli_stmt_bind_param($stmtAdmin, "s", $username);
        mysqli_stmt_execute($stmtAdmin);
        $resultAdmin = mysqli_stmt_get_result($stmtAdmin);

        if ($admin = mysqli_fetch_assoc($resultAdmin)) {
            // Verify password (hashed or plain text for testing)
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                $_SESSION['admins'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'first_name' => $admin['first_name'],
                    'last_name' => $admin['last_name'],
                    'email' => $admin['email'],
                    'role' => $admin['role']
                ];
                header("Location: admin.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        }
        mysqli_stmt_close($stmtAdmin);

        // -------------------
        // 2. Check Users Table
        // -------------------
        $sqlUser = "SELECT id, username, email, password FROM users WHERE username = ?";
        $stmtUser = mysqli_prepare($conn, $sqlUser);
        mysqli_stmt_bind_param($stmtUser, "s", $username);
        mysqli_stmt_execute($stmtUser);
        $resultUser = mysqli_stmt_get_result($stmtUser);

        if ($user = mysqli_fetch_assoc($resultUser)) {
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['users'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            if (!isset($error)) {
                $error = "User not found.";
            }
        }
        mysqli_stmt_close($stmtUser);

    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<style>
body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    background-color: #f5f5f5;
    font-family: Arial, sans-serif;
}
.loginForm {
    background: #fff;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    width: 320px;
}
input {
    width: 90%;
    padding: 8px;
    margin: 8px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}
input[type="submit"] {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}
input[type="submit"]:hover {
    background-color: #0056b3;
}
</style>

<div class="loginForm">
<form method="post">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="submit" name="login" value="Login">
    <p>Don't have an account? <a href="register.php">Sign up</a></p>
</form>
</div>

<?php include 'footer.php'; ?>
