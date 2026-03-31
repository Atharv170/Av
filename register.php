<?php
include 'db.php';

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Check empty
if($name == "" || $email == "" || $password == ""){
    echo "<script>
    alert('All fields are required!');
    window.location.href='register.html';
    </script>";
    exit();
}

// Encrypt password
$pass = md5($password);

// Check duplicate email
$check = $conn->query("SELECT * FROM users WHERE email='$email'");

if($check->num_rows > 0){
    echo "<script>
    alert('Email already registered!');
    window.location.href='register.html';
    </script>";
    exit();
}

// Insert
$result = $conn->query("INSERT INTO users(name,email,password)
VALUES('$name','$email','$pass')");

if($result){
    echo "<script>
    alert('Registration Successful!');
    window.location.href='login.html';
    </script>";
}else{
    echo "Error: " . $conn->error;
}
?>