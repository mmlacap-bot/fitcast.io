<?php
session_start();

// ----------------------
// LOGOUT HANDLER
// ----------------------
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Optional login check
if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php");
    // exit();
}

// ----------------------
// AUTO-DETECT USER LOCATION (via IP-API)
// ----------------------
$geoResponse = @file_get_contents("http://ip-api.com/json/");
$geoData = $geoResponse ? json_decode($geoResponse, true) : null;
$city = $geoData && isset($geoData['city']) ? $geoData['city'] : "Manila";

// ----------------------
// API NINJAS SETTINGS
// ----------------------
$apiKey = "V+JOhr6cpZbP42fU7UVOHQ==5IlWO6uniaHxCpun"; // Your API key
$apiUrl = "https://api.api-ninjas.com/v1/weather?city=" . urlencode($city);

// Fetch API Ninjas data
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Api-Key: $apiKey"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Important for XAMPP/localhost
$apiResponse = curl_exec($ch);
curl_close($ch);

if ($apiResponse) {
    $weather = json_decode($apiResponse, true);

    if (isset($weather['temp'])) {
        $temperature = $weather['temp'];
        $feels_like = $weather['feels_like'];
        $humidity = $weather['humidity'];
        $wind_speed = $weather['wind_speed'];
        $cloud_pct = $weather['cloud_pct'];

        // Determine condition based on cloud percentage
        if ($cloud_pct > 70) {
            $condition = "Cloudy";
        } elseif ($cloud_pct > 40) {
            $condition = "Partly Cloudy";
        } else {
            $condition = "Clear";
        }

        $hour = date("G");
        $is_day = ($hour >= 6 && $hour < 18) ? 1 : 0;
        $weather_date = date("Y-m-d");
    } else {
        $temperature = null;
        $condition = "Unavailable";
        $weather_date = date("Y-m-d");
        $is_day = 1;
    }
} else {
    $temperature = null;
    $condition = "Unavailable";
    $weather_date = date("Y-m-d");
    $is_day = 1;
}

// ----------------------
// SAMPLE FITNESS DATA
// ----------------------
$fitness_date = date("Y-m-d");
$steps = 8420;
$calories = 372;
$workout = "Push-ups & Bicep curls";

// ----------------------
// DETERMINE SAFETY STATUS
// ----------------------
$safetyMessage = "";
if ($temperature !== null) {
    $lowerCondition = strtolower($condition);

    if ($temperature >= 18 && $temperature <= 30 && !preg_match('/(rain|storm|snow|thunder)/i', $lowerCondition)) {
        $safetyMessage = "☀️ Great weather for outdoor exercise!";
    } elseif ($temperature > 30) {
        $safetyMessage = "🥵 It's quite hot today — stay hydrated or work out indoors.";
    } elseif (preg_match('/(rain|storm|thunder)/i', $lowerCondition)) {
        $safetyMessage = "🌧️ Rainy or stormy weather — best to stay indoors.";
    } elseif ($temperature < 18) {
        $safetyMessage = "❄️ It's a bit cold — consider an indoor workout.";
    } else {
        $safetyMessage = "⚠️ Weather might be unpredictable — exercise caution.";
    }
} else {
    $safetyMessage = "❌ Unable to fetch live weather data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | Fitness & Weather Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #74ebd5, #9face6);
            margin: 0;
            padding: 0;
        }
        .navbar {
            background: #2d3e50;
            color: #fff;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .navbar a {
            color: #fff;
            margin-right: 20px;
            text-decoration: none;
            transition: color 0.3s;
        }
        .navbar a:hover { color: #e74c3c; }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .section { margin-bottom: 30px; }
        h2 { color: #2d3e50; }
        .card {
            background: #e9ecef;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .logout-btn {
            float: right;
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #c0392b; }
        .notification {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 16px;
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="fitness.php">Fitness Tracker</a>
        <a href="weather.php">Weather Tracker</a>
        <a href="calendar.php">Calendar</a>
        <a href="profile.php">Profile</a>
        <a href="contactus.php">Contact Us</a>
        <a href="settings.php">Settings</a>
        <form method="post" style="display:inline;">
            <button class="logout-btn" type="submit" name="logout">Logout</button>
        </form>
    </div>

    <div class="container">
        <h1>Dashboard</h1>

        <!-- WEATHER SAFETY NOTIFICATION -->
        <div class="notification">
            <?php echo htmlspecialchars($safetyMessage); ?>
        </div>

        <div class="section">
            <h2>Latest Fitness Data</h2>
            <div class="card">
                <strong>Date:</strong> <?= htmlspecialchars($fitness_date) ?><br>
                <strong>Steps:</strong> <?= htmlspecialchars($steps) ?><br>
                <strong>Calories Burned:</strong> <?= htmlspecialchars($calories) ?><br>
                <strong>Workout:</strong> <?= htmlspecialchars($workout) ?>
            </div>
            <a href="fitness.php">View All Fitness Data</a>
        </div>

        <div class="section">
            <h2>Latest Weather Data</h2>
            <div class="card">
                <strong>Date:</strong> <?= htmlspecialchars($weather_date) ?><br>
                <strong>City:</strong> <?= htmlspecialchars($city) ?><br>
                <strong>Temperature:</strong> <?= htmlspecialchars($temperature) ?>°C<br>
                <strong>Feels Like:</strong> <?= htmlspecialchars($feels_like ?? 'N/A') ?>°C<br>
                <strong>Humidity:</strong> <?= htmlspecialchars($humidity ?? 'N/A') ?>%<br>
                <strong>Wind Speed:</strong> <?= htmlspecialchars($wind_speed ?? 'N/A') ?> m/s<br>
                <strong>Condition:</strong> <?= htmlspecialchars($condition) ?><br>
                <strong>Time of Day:</strong> <?= $is_day ? "Daytime ☀️" : "Night 🌙" ?>
            </div>
            <a href="weather.php">View All Weather Data</a>
        </div>
    </div>
</body>
</html>
