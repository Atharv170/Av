<?php
$conn = new mysqli("localhost", "root", "", "fitness_arc"); 
if ($conn->connect_error) {
die("Connection failed: ".$conn->connect_error);
}
?>