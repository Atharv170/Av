<?php
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM workouts WHERE id='$id'");

header("Location: dashboard.php");
?>