<?php
$current_email = " "; // Replace with the actual value or logic
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Settings</h2>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="POST" action="settings.php">
        <label>New Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required><br><br>
        <label>New Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Update Settings</button>
    </form>
</body>
</html>