<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$exercise = $_POST['exercise'];
$value = $_POST['value'];
$sets = $_POST['sets'];
$date = date("Y-m-d");

// CALORIE LOGIC
$calories = 0;

if($exercise == "Push Ups"){
    $calories = $value * $sets * 0.5;
}
elseif($exercise == "Squats"){
    $calories = $value * $sets * 0.7;
}
elseif($exercise == "Running"){
    $calories = $value * 60;
}
elseif($exercise == "Cycling"){
    $calories = $value * 50;
}
elseif($exercise == "Yoga"){
    $calories = $sets * 30;
}
elseif($exercise == "Jump Rope"){
    $calories = $value * $sets * 0.2;
}

// INSERT QUERY
$sql = "INSERT INTO workouts (user_id, exercise, value, sets, calories)
        VALUES ('$user_id', '$exercise', '$value', '$sets', '$calories')";

// 🔥 THIS LINE WAS MISSING
$conn->query($sql);

header("Location: dashboard.php");
?>