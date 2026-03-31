<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];

$distance = $_POST['distance'];
$time = $_POST['time'];
$calories = $_POST['calories'];

$sql = "INSERT INTO workouts (user_id, exercise, value, sets, calories)
        VALUES ('$user_id', 'Running', '$distance', '$time', '$calories')";

if($conn->query($sql)){
    echo "Saved";
}else{
    echo "Error";
}
?>