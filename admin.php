<?php
session_start();
require_once 'db.php'; // Assumes $conn is your MySQLi connection

// 🧩 Only allow admins through session check (optional)

// 🔍 Detect which name columns exist dynamically
$columns = [];
$result = mysqli_query($conn, "SHOW COLUMNS FROM admins");
while ($col = mysqli_fetch_assoc($result)) {
    $columns[] = $col['Field'];
}

// Choose the correct query based on your table structure
if (in_array('firstname', $columns) && in_array('lastname', $columns)) {
    $userQuery = "SELECT id, firstname, lastname, username, email FROM admins";
} elseif (in_array('first_name', $columns) && in_array('last_name', $columns)) {
    $userQuery = "SELECT id, first_name, last_name, username, email FROM admins";
} elseif (in_array('name', $columns)) {
    $userQuery = "SELECT id, name, username, email FROM admins";
} else {
    // fallback (minimal columns)
    $userQuery = "SELECT id, username, email FROM admins";
}

$userResult = mysqli_query($conn, $userQuery);

// Fetch all contact messages (example)
$contactQuery = "SELECT * FROM contacts"; // or your table name
$contactResult = mysqli_query($conn, $contactQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - FitCast</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #74ebd5 0%, #9face6 100%);
            margin: 0;
            padding: 0;
            color: #333;
        }

        nav {
            background: #2d3e50;
            padding: 14px 32px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.11);
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin-right: 24px;
            font-weight: 500;
            letter-spacing: 1px;
            transition: color 0.18s;
        }
        nav a:hover {
            color: #ffe680;
        }

        .container {
            max-width: 950px;
            margin: 48px auto;
            background: #fff;
            padding: 38px 34px 28px 34px;
            border-radius: 11px;
            box-shadow: 0 7px 22px rgba(42, 90, 217, 0.13);
        }

        h1, h2 {
            color: #2d3e50;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-bottom: 18px;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            border: none;
            padding: 12px 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f0f5ff;
        }

        @media (max-width: 820px) {
            .container { padding: 18px 6px; }
            nav { flex-direction: column; align-items: flex-start; }
            nav a { margin: 9px 0; }
            table, th, td { font-size: 15px; }
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="dashboard.php">User</a> |
        <a href="admin.php">Admin</a> |
        <a href="settings.php">Settings</a> |
        <a href="logout.php">Logout</a>
    </nav>
    <hr>
    <div class="container">
        <h2>Registered Users</h2>
        <table border="1">
            <tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th></tr>
            <?php while ($row = mysqli_fetch_assoc($userResult)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td>
                    <?php
                    if (isset($row['firstname']) && isset($row['lastname'])) {
                        echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
                    } elseif (isset($row['first_name']) && isset($row['last_name'])) {
                        echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                    } elseif (isset($row['name'])) {
                        echo htmlspecialchars($row['name']);
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($row['username'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
            </tr>
            <?php } ?>
        </table>

        <h2>Contact Messages</h2>
        <table border="1">
            <tr><th>Name</th><th>Email</th><th>Message</th></tr>
            <?php while ($row = mysqli_fetch_assoc($contactResult)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['message'] ?? '—') ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
