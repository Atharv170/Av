<?php
session_start();
include 'db.php';

$email = $_POST['email'];
$pass = md5($_POST['password']);

$res = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$pass'");

if($res && $res->num_rows > 0){
    $user = $res->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];

    header("Location: dashboard.php");
    exit();
}else{
    echo "<script>
    alert('Invalid Login');
    window.location.href='login.html';
    </script>";
}
?>