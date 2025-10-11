<?php
session_start();
include_once __DIR__ . '/../config.php';
include_once 'functions.php';


if(is_post_request()) {

    if(!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password2']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['password2'])) {
        $_SESSION['message'] = 'Please complete the registration form!';
        header("Location: /PCv/cointrack_2/index.php");
        exit;
    }

    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    $password_hash = password_hash($_POST["password"], PASSWORD_BCRYPT); 

    if($_POST['password'] !== $_POST['password2']) { // if passwords are not identical
        $_SESSION['message'] .= "Hesla se neshodují!";
        header("Location: /PCv/cointrack_2/index.php");
        }


    $check_email = $pdo->prepare("SELECT email from users WHERE email = ?");
    $check_email->execute([$email]);

    if($check_email->rowCount() > 0) { // if the email is a duplicate, exit
    $_SESSION["message"] = "This email is already used, try another one.";
    header("Location: /PCv/cointrack_2/index.php");
    exit;
    }   else { // else, insert data
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) values (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);

        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $pdo->lastInsertId(); // important for functionality, forgot to write this 
        header("Location: dashboard.php");
        
    }
    
}


?>