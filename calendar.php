<?php
session_start();

// ----------------------
// CONFIGURATION
// ----------------------
$apiUrl = "https://api.api-ninjas.com/v1/exercises";
$apiKey = "V+JOhr6cpZbP42fU7UVOHQ==5IlWO6uniaHxCpun";

// ----------------------
// HANDLE MONTH/YEAR NAVIGATION
// ----------------------
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// ----------------------
// HANDLE ADD/REMOVE EXERCISES FOR USER
// ----------------------
if (!isset($_SESSION['userExercises'])) {
    $_SESSION['userExercises'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add user-selected exercise for a day
    if (isset($_POST['day']) && isset($_POST['exercise'])) {
        $day = intval($_POST['day']);
        $exercise = trim($_POST['exercise']);
        $key = "$year-$month-$day";
        if (!isset($_SESSION['userExercises'][$key])) $_SESSION['userExercises'][$key] = [];
        $_SESSION['userExercises'][$key][] = $exercise;
    }

    // Remove exercise
    if (isset($_POST['remove_key']) && isset($_POST['remove_index'])) {
        $key = $_POST['remove_key'];
        $index = intval($_POST['remove_index']);
        if (isset($_SESSION['userExercises'][$key][$index])) {
            array_splice($_SESSION['userExercises'][$key], $index, 1);
            if (empty($_SESSION['userExercises'][$key])) unset($_SESSION['userExercises'][$key]);
        }
    }

    header("Location: calendar.php?month=$month&year=$year");
    exit();
}

// ----------------------
// FETCH EXERCISES FROM API
// ----------------------
function fetchExercisesFromAPI($muscle = 'biceps') {
    global $apiUrl, $apiKey;

    $url = $apiUrl . "?muscle=" . urlencode($muscle);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Api-Key: $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data) return [];
    return array_map(fn($ex) => $ex['name'], $data); // extract exercise names
}

$availableExercises = fetchExercisesFromAPI('biceps');

// ----------------------
// CALENDAR SETUP
// ----------------------
$daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$firstDay = date('w', strtotime("$year-$month-01"));
$daysInMonth = date('t', strtotime("$year-$month-01"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exercise Calendar</title>
<style>
body { font-family: Arial; display: flex; justify-content: center; padding: 20px; background: #f0f0f0; }
.calendar { background: #fff; padding: 20px; border-radius: 10px; width: 800px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.header a { text-decoration: none; font-size: 18px; padding: 5px 10px; background: #007bff; color: white; border-radius: 5px; }
table { width: 100%; border-collapse: collapse; }
th, td { width: 14.28%; border: 1px solid #ddd; vertical-align: top; height: 100px; padding: 5px; cursor: pointer; }
th { background: #007bff; color: white; }
td .day { font-weight: bold; margin-bottom: 5px; }
td .exercise { font-size: 14px; background: #d3e3fd; padding: 2px 4px; margin-bottom: 2px; border-radius: 3px; display: flex; justify-content: space-between; align-items: center; }
td.today { background: #007bff; color: white; }
#exerciseModal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
#exerciseModal form { background: white; padding: 20px; border-radius: 10px; width: 300px; }
button.deleteBtn { background: red; color: white; border: none; border-radius: 3px; cursor: pointer; padding: 0 4px; margin-left: 5px; }
select { width:100%; padding:5px; margin-bottom:10px; }
</style>
</head>
<body>

<div class="calendar">
    <div class="header">
        <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">❮ Prev</a>
        <div><strong><?= date('F Y', strtotime("$year-$month-01")) ?></strong></div>
        <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next ❯</a>
    </div>

    <table>
        <thead>
            <tr>
                <?php foreach($daysOfWeek as $day): ?>
                    <th><?= $day ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $date = 1;
        for ($i=0; $i<6; $i++) {
            echo "<tr>";
            for ($j=0; $j<7; $j++) {
                if ($i===0 && $j<$firstDay || $date>$daysInMonth) {
                    echo "<td></td>";
                } else {
                    $key = "$year-$month-$date";
                    $classes = '';
                    if ($date == date('j') && $month == date('n') && $year == date('Y')) $classes = 'today';
                    echo "<td class='$classes' onclick='openModal($date)'>";
                    echo "<div class='day'>$date</div>";
                    if (isset($_SESSION['userExercises'][$key])) {
                        foreach ($_SESSION['userExercises'][$key] as $index => $ex) {
                            echo "<div class='exercise'>";
                            echo htmlspecialchars($ex);
                            echo "<form method='POST' style='display:inline; margin:0; padding:0;'>
                                    <input type='hidden' name='remove_key' value='$key'>
                                    <input type='hidden' name='remove_index' value='$index'>
                                    <input type='hidden' name='month' value='$month'>
                                    <input type='hidden' name='year' value='$year'>
                                    <button type='submit' class='deleteBtn'>x</button>
                                  </form>";
                            echo "</div>";
                        }
                    }
                    echo "</td>";
                    $date++;
                }
            }
            echo "</tr>";
            if ($date > $daysInMonth) break;
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div id="exerciseModal">
    <form method="POST">
        <h3>Add Exercise</h3>
        <input type="hidden" name="day" id="modalDay">
        <input type="hidden" name="month" value="<?= $month ?>">
        <input type="hidden" name="year" value="<?= $year ?>">
        <select name="exercise" required>
            <option value="">Select exercise</option>
            <?php foreach ($availableExercises as $ex): ?>
                <option value="<?= htmlspecialchars($ex) ?>"><?= htmlspecialchars($ex) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding:5px 10px;">Add</button>
        <button type="button" onclick="closeModal()" style="padding:5px 10px;">Cancel</button>
    </form>
</div>

<script>
function openModal(day) {
    document.getElementById('modalDay').value = day;
    document.getElementById('exerciseModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('exerciseModal').style.display = 'none';
}
</script>

</body>
</html>
