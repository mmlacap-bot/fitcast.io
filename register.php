<?php
session_start();
include 'header.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $name = $username !== '' ? $username : trim($first . ' ' . $last);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            echo "<div class='error-message'>Email already registered!</div>";
        } else {
            // Insert user
            $sql = "INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $first, $last, $username, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                echo "<div class='success-message'>Registered successfully! <a href='login.php'>Login</a></div>";
            } else {
                echo "<div class='error-message'>Error inserting record: " . mysqli_error($conn) . "</div>";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check_stmt);
    } else {
        echo "<div class='error-message'>Please fill in all required fields correctly.</div>";
    }
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        color: #333;
    }

    .container {
        background-color: #fff;
        padding: 40px 50px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        width: 400px;
        text-align: center;
        animation: fadeIn 0.8s ease-in-out;
    }

    .container h2 {
        margin-bottom: 20px;
        color: #333;
        font-weight: 700;
    }

    form input {
        width: 90%;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: 0.3s;
    }

    form input:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
    }

    button {
        width: 100%;
        padding: 12px;
        background-color: #007bff;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-top: 10px;
    }

    button:hover {
        background-color: #0056b3;
    }

    p {
        margin-top: 15px;
        font-size: 14px;
    }

    p a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }

    p a:hover {
        text-decoration: underline;
    }

    .error-message, .success-message {
        margin: 15px auto;
        width: 80%;
        padding: 10px;
        border-radius: 8px;
        font-weight: 500;
        text-align: center;
    }

    .error-message {
        background: #ffe5e5;
        color: #c00000;
    }

    .success-message {
        background: #e5ffe8;
        color: #007a2d;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container">
    <h2>Register</h2>
    <form id="registrationForm" method="post">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Submit</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<script src="script.js"></script>
<?php include 'footer.php'; ?>
