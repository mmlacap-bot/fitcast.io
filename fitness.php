<?php
require_once 'db.php';

// === CREATE TABLES IF NOT EXIST ===
mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    height_cm FLOAT,
    weight_kg FLOAT,
    age INT,
    gender VARCHAR(10)
)");

mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    date DATE,
    duration_minutes INT,
    calories_burned FLOAT,
    notes TEXT
)");

mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_id INT,
    name VARCHAR(100),
    sets INT,
    reps INT,
    weight FLOAT
)");

// === API SECTION ===
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $resource = $_GET['api'];
    $method = $_SERVER['REQUEST_METHOD'];

    function sanitize($conn, $data) {
        return mysqli_real_escape_string($conn, $data);
    }

    // --- USERS ---
    if ($resource === 'users') {
        if ($method === 'GET') {
            $res = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name,email,height_cm,weight_kg,age,gender) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssddis",
                $input['name'], $input['email'], $input['height_cm'], $input['weight_kg'], $input['age'], $input['gender']);
            mysqli_stmt_execute($stmt);
            echo json_encode(["success"=>true, "message"=>"Welcome, {$input['name']}! User added successfully."]);
        }
    }

    // --- WORKOUTS ---
    if ($resource === 'workouts') {
        if ($method === 'GET') {
            $res = mysqli_query($conn, "
                SELECT w.*, u.name AS user_name FROM workouts w
                LEFT JOIN users u ON w.user_id=u.id
                ORDER BY w.date DESC
            ");
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $stmt = mysqli_prepare($conn, "INSERT INTO workouts (user_id,name,date,duration_minutes,calories_burned,notes) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "issdds",
                $input['user_id'], $input['name'], $input['date'],
                $input['duration_minutes'], $input['calories_burned'], $input['notes']);
            mysqli_stmt_execute($stmt);
            echo json_encode(["success"=>true, "message"=>"Workout '{$input['name']}' added!"]);
        }
    }

    // --- EXERCISES ---
    if ($resource === 'exercises') {
        if ($method === 'GET') {
            $res = mysqli_query($conn, "
                SELECT e.*, w.name AS workout_name FROM exercises e
                LEFT JOIN workouts w ON e.workout_id=w.id
                ORDER BY e.id DESC
            ");
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $stmt = mysqli_prepare($conn, "INSERT INTO exercises (workout_id,name,sets,reps,weight) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "isiii",
                $input['workout_id'], $input['name'], $input['sets'], $input['reps'], $input['weight']);
            mysqli_stmt_execute($stmt);
            echo json_encode(["success"=>true, "message"=>"Exercise '{$input['name']}' added to workout!"]);
        } elseif ($method === 'DELETE') {
            parse_str(file_get_contents("php://input"), $params);
            $id = intval($params['id'] ?? 0);
            if ($id > 0) {
                $stmt = mysqli_prepare($conn, "DELETE FROM exercises WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                echo json_encode(["success"=>true]);
            } else {
                echo json_encode(["error"=>"Missing ID"]);
            }
        }
    }

    // --- EXTERNAL EXERCISES API ---
    if ($resource === 'external_exercises') {
        $apiUrl = "https://api.api-ninjas.com/v1/exercises?muscle=biceps";
        $apiKey = "V+JOhr6cpZbP42fU7UVOHQ==5IlWO6uniaHxCpun";
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["X-Api-Key: $apiKey"]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        echo $response;
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🏋️ Fitness Dashboard</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f4f6f8; }
    h1 { color: #007bff; }
    .container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 5px; }
    button { margin-top: 10px; padding: 10px 15px; border: none; background: #007bff; color: white; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0056b3; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #007bff; color: white; }
</style>
</head>
<body>
<h1>🏋️ Fitness Tracker Dashboard</h1>
<div class="container">

    <!-- USERS -->
    <div class="card">
        <h2>Add User</h2>
        <label>Name</label><input id="userName">
        <label>Email</label><input id="userEmail" type="email">
        <label>Height (cm)</label><input id="userHeight" type="number">
        <label>Weight (kg)</label><input id="userWeight" type="number">
        <label>Age</label><input id="userAge" type="number">
        <label>Gender</label>
        <select id="userGender"><option>Male</option><option>Female</option></select>
        <button onclick="addUser()">Add User</button>
        <h3>All Users</h3>
        <table id="userTable"><thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead><tbody></tbody></table>
    </div>

    <!-- WORKOUTS -->
    <div class="card">
        <h2>Add Workout</h2>
        <label>User</label><select id="workoutUser"></select>
        <label>Name</label><input id="workoutName">
        <label>Date</label><input id="workoutDate" type="date">
        <label>Duration (min)</label><input id="workoutDuration" type="number">
        <label>Calories Burned</label><input id="workoutCalories" type="number">
        <label>Notes</label><textarea id="workoutNotes"></textarea>
        <button onclick="addWorkout()">Add Workout</button>
        <h3>All Workouts</h3>
        <table id="workoutTable"><thead><tr><th>ID</th><th>Name</th><th>User</th><th>Date</th></tr></thead><tbody></tbody></table>
    </div>

    <!-- EXERCISES -->
    <div class="card">
        <h2>Add Exercise</h2>
        <label>Workout</label><select id="exerciseWorkout"></select>
        <label>Name</label><input id="exerciseName">
        <label>Sets</label><input id="exerciseSets" type="number">
        <label>Reps</label><input id="exerciseReps" type="number">
        <label>Weight (kg)</label><input id="exerciseWeight" type="number">
        <button onclick="addExercise()">Add Exercise</button>
        <h3>All Exercises</h3>
        <table id="exerciseTable"><thead><tr><th>ID</th><th>Name</th><th>Workout</th><th>Sets</th><th>Reps</th><th>Action</th></tr></thead><tbody></tbody></table>
    </div>

</div>

<script>
const API = 'fitness.php?api=';

// Helper
async function api(resource, options = {}) {
    const res = await fetch(API + resource, {
        headers: {'Content-Type': 'application/json'}, ...options
    });
    return res.json();
}

// USERS
async function loadUsers() {
    const users = await api('users');
    document.querySelector('#userTable tbody').innerHTML = users.map(u =>
        `<tr><td>${u.id}</td><td>${u.name}</td><td>${u.email}</td></tr>`).join('');
    document.getElementById('workoutUser').innerHTML = users.map(u =>
        `<option value="${u.id}">${u.name}</option>`).join('');
}

// Add user
async function addUser() {
    const data = {
        name: userName.value, email: userEmail.value, height_cm: +userHeight.value,
        weight_kg: +userWeight.value, age: +userAge.value, gender: userGender.value
    };
    const res = await api('users', { method: 'POST', body: JSON.stringify(data) });
    alert(res.message || "User added!");
    loadUsers();
}

// WORKOUTS
async function loadWorkouts() {
    const workouts = await api('workouts');
    document.querySelector('#workoutTable tbody').innerHTML = workouts.map(w =>
        `<tr><td>${w.id}</td><td>${w.name}</td><td>${w.user_name}</td><td>${w.date}</td></tr>`).join('');
    document.getElementById('exerciseWorkout').innerHTML = workouts.map(w =>
        `<option value="${w.id}">${w.name} (${w.user_name})</option>`).join('');
}

async function addWorkout() {
    const data = {
        user_id: +workoutUser.value, name: workoutName.value, date: workoutDate.value,
        duration_minutes: +workoutDuration.value, calories_burned: +workoutCalories.value, notes: workoutNotes.value
    };
    const res = await api('workouts', { method: 'POST', body: JSON.stringify(data) });
    alert(res.message || "Workout added!");
    loadWorkouts();
}

// EXERCISES
async function loadExercises() {
    const exercises = await api('exercises');
    document.querySelector('#exerciseTable tbody').innerHTML = exercises.map(e =>
        `<tr><td>${e.id}</td><td>${e.name}</td><td>${e.workout_name}</td><td>${e.sets}</td><td>${e.reps}</td>
         <td><button onclick="removeExercise(${e.id})">❌</button></td></tr>`).join('');
}

async function addExercise() {
    const data = {
        workout_id: +exerciseWorkout.value, name: exerciseName.value,
        sets: +exerciseSets.value, reps: +exerciseReps.value, weight: +exerciseWeight.value
    };
    const res = await api('exercises', { method: 'POST', body: JSON.stringify(data) });
    alert(res.message || "Exercise added!");
    loadExercises();
}

async function removeExercise(id) {
    await fetch(API + 'exercises&id=' + id, { method: 'DELETE' });
    loadExercises();
}

// INIT
loadUsers();
loadWorkouts();
loadExercises();
</script>
</body>
</html>
