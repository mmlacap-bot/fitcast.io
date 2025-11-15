<?php
// weather.php
// Weather + Weekly Forecast + Exercise Recommendation
// Get your free API key at https://www.weatherapi.com/

$apiKey = '0c5684e4f5f2410786c115223251011'; // Replace with your own

function fetch_json($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false || $code !== 200) return null;
    return json_decode($resp, true);
}

// Input
$city = trim((string)filter_input(INPUT_GET, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$lat = trim((string)filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$lon = trim((string)filter_input(INPUT_GET, 'lon', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

$weather = null;
$forecast = null;
$error = null;

if ($apiKey === 'YOUR_WEATHERAPI_KEY_HERE') {
    $error = 'API key not set.';
} elseif ($city !== '') {
    $q = urlencode($city);
    $url = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$q}&days=7&aqi=no&alerts=no";
    $weather = fetch_json($url);
    if ($weather === null || isset($weather['error'])) $error = 'Unable to fetch weather.';
} elseif ($lat !== '' && $lon !== '') {
    $coords = "{$lat},{$lon}";
    $url = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$coords}&days=7&aqi=no&alerts=no";
    $weather = fetch_json($url);
    if ($weather === null || isset($weather['error'])) $error = 'Unable to fetch weather.';
}

// Optional: Original current-condition function (still usable if needed)
function getExerciseAdvice($temp, $humidity, $condition) {
    if (stripos($condition, 'rain') !== false) {
        return ['⚠️ Not ideal for outdoor workouts — try indoor training or yoga.', 'bad'];
    } elseif ($temp >= 30) {
        return ['🥵 Too hot for intense workouts — stay hydrated or go for light stretching.', 'bad'];
    } elseif ($temp < 20) {
        return ['💨 Cool weather — perfect for jogging or cycling!', 'good'];
    } elseif ($humidity > 80) {
        return ['💧 High humidity — prefer gym workouts or light walking.', 'medium'];
    } else {
        return ['💪 Great conditions for running or outdoor exercise!', 'good'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Weather & Fitness Forecast</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; margin:0; padding:2rem; background:#f5f7fb; color:#111; }
    .card { max-width:850px; margin:0 auto; background:#fff; padding:1.25rem; border-radius:10px; box-shadow:0 6px 18px rgba(20,30,50,0.08); }
    form { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem }
    input[type="text"], input[type="number"] { padding:.5rem .6rem; border:1px solid #d6dbe6; border-radius:6px; min-width:150px }
    button { padding:.55rem .9rem; border:0; background:#2563eb; color:#fff; border-radius:6px; cursor:pointer }
    .meta { display:flex; gap:1rem; align-items:center; margin-top:.5rem; color:#555 }
    .temp { font-size:2.25rem; font-weight:600 }
    img.icon { vertical-align:middle; width:72px; height:72px }
    .error { color:#b91c1c; background:#fee2e2; padding:.5rem; border-radius:6px }
    .small { font-size:0.9rem; color:#444 }
    .actions { margin-left:auto; display:flex; gap:.5rem; align-items:center }
    .advice { margin-top:1rem; padding:0.75rem 1rem; border-radius:8px; font-weight:500; }
    .good { background:#dcfce7; color:#166534; }
    .medium { background:#fef9c3; color:#854d0e; }
    .bad { background:#fee2e2; color:#991b1b; }
    .forecast { margin-top:1.5rem; display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:.75rem; }
    .day { background:#f9fafb; border-radius:8px; padding:.75rem; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
    .day img { width:48px; height:48px; }
    @media (max-width:560px){ form { flex-direction:column } .actions { margin-left:0 } }
</style>
</head>
<body>
<div class="card">
    <h1>🌦️ Weather & Fitness Forecast</h1>
    <form method="get" action="">
        <input type="text" name="city" placeholder="City name (e.g. Manila)" value="<?php echo htmlspecialchars($city); ?>">
        <input type="number" step="any" name="lat" placeholder="Latitude" value="<?php echo htmlspecialchars($lat); ?>">
        <input type="number" step="any" name="lon" placeholder="Longitude" value="<?php echo htmlspecialchars($lon); ?>">
        <div class="actions">
            <button type="submit">Get Weather</button>
            <button type="button" onclick="getLocation()">Use My Location</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($weather): 
        $name = htmlspecialchars($weather['location']['name'] ?? '');
        $country = htmlspecialchars($weather['location']['country'] ?? '');
        $current = $weather['current'];
        $desc = htmlspecialchars($current['condition']['text']);
        $tempC = round($current['temp_c'], 1);
        $tempF = round($current['temp_f'], 1);
        $feels = round($current['feelslike_c'], 1);
        $hum = htmlspecialchars($current['humidity']);
        $wind = htmlspecialchars($current['wind_kph']);
        $iconUrl = 'https:' . ($current['condition']['icon'] ?? '');
        $forecast = $weather['forecast']['forecastday'] ?? [];

        // --- New weekly-based advice logic ---
        $advice = '';
        $class = '';
        if (!empty($forecast)) {
            $totalTemp = 0;
            $totalHumidity = 0;
            $count = 0;
            $rainyDays = 0;

            foreach ($forecast as $day) {
                $avgTemp = $day['day']['avgtemp_c'] ?? 0;
                $avgHum = $day['day']['avghumidity'] ?? 0;
                $cond = strtolower($day['day']['condition']['text'] ?? '');

                $totalTemp += $avgTemp;
                $totalHumidity += $avgHum;
                $count++;

                if (str_contains($cond, 'rain')) {
                    $rainyDays++;
                }
            }

            $avgTemp = $count ? $totalTemp / $count : $tempC;
            $avgHum = $count ? $totalHumidity / $count : $hum;
            $rainChance = ($rainyDays / max(1, $count)) * 100;

            if ($rainChance > 40) {
                [$advice, $class] = ['🌧️ Many rainy days this week — plan indoor workouts!', 'bad'];
            } elseif ($avgTemp >= 30) {
                [$advice, $class] = ['🥵 Mostly hot this week — avoid outdoor workouts at midday.', 'medium'];
            } elseif ($avgTemp < 20) {
                [$advice, $class] = ['💨 Cool week — great for jogging or cycling!', 'good'];
            } elseif ($avgHum > 80) {
                [$advice, $class] = ['💧 Humid week — go for lighter activities or gym workouts.', 'medium'];
            } else {
                [$advice, $class] = ['💪 Great weather all week for outdoor exercise!', 'good'];
            }
        }
    ?>
        <div style="display:flex;align-items:center;gap:1rem">
            <?php if ($iconUrl): ?><img class="icon" src="<?php echo $iconUrl; ?>" alt="<?php echo $desc; ?>"><?php endif; ?>
            <div>
                <div class="temp"><?php echo $tempC . '°C / ' . $tempF . '°F'; ?> <span class="small">— <?php echo $desc; ?></span></div>
                <div class="small"><?php echo "{$name}, {$country}"; ?></div>
            </div>
        </div>
        <div class="meta">
            <div>Feels like: <strong><?php echo $feels; ?>°C</strong></div>
            <div>Humidity: <strong><?php echo $hum; ?>%</strong></div>
            <div>Wind: <strong><?php echo $wind; ?> kph</strong></div>
        </div>
        <div class="advice <?php echo $class; ?>"><?php echo $advice; ?></div>

        <?php if ($forecast): ?>
        <h2 style="margin-top:1.5rem;">📅 7-Day Forecast</h2>
        <div class="forecast">
            <?php foreach ($forecast as $day): 
                $date = htmlspecialchars($day['date']);
                $icon = 'https:' . ($day['day']['condition']['icon'] ?? '');
                $cond = htmlspecialchars($day['day']['condition']['text']);
                $max = round($day['day']['maxtemp_c'], 1);
                $min = round($day['day']['mintemp_c'], 1);
            ?>
            <div class="day">
                <div class="small"><?php echo date('D', strtotime($date)); ?></div>
                <img src="<?php echo $icon; ?>" alt="<?php echo $cond; ?>">
                <div><?php echo $max; ?>° / <?php echo $min; ?>°C</div>
                <div class="small"><?php echo $cond; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="small">Enter a city or coordinates, then click “Get Weather”.</p>
    <?php endif; ?>
</div>

<script>
function getLocation() {
    if (!navigator.geolocation) return alert('Geolocation not supported.');
    navigator.geolocation.getCurrentPosition(pos=>{
        document.querySelector('input[name="lat"]').value = pos.coords.latitude.toFixed(6);
        document.querySelector('input[name="lon"]').value = pos.coords.longitude.toFixed(6);
    }, err=>{
        alert('Unable to get location: ' + (err.message || err.code));
    }, { timeout: 8000 });
}
</script>
</body>
</html>
