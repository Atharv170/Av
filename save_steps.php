<?php
session_start();
include 'db.php';

$user_id=$_SESSION['user_id'];

$steps=$_POST['steps'];
$calories=$_POST['calories'];
$date=date("Y-m-d");

$conn->query("INSERT INTO steps(user_id,steps,calories,date)
VALUES('$user_id','$steps','$calories','$date')");
?>