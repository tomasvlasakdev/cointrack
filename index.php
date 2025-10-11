<?php
session_start();
include_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CoinTrack</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="icon" href="favicon.ico">
</head>

<body class="auth-body">
    <div class="auth-container">
        <img src="favicon.ico" alt="CoinTrack Logo" class="auth-logo">
        <h1>Create Account</h1>
        <form action="./php/register.php" method="post">
            <label for="username">Username</label>
            <input type="text" placeholder="Enter Username" name="username" id="username" required>

            <label for="email">Email</label>
            <input type="email" placeholder="Enter Email" name="email" id="email" required>

            <label for="password">Password</label>
            <input type="password" placeholder="Enter Password" id="password" name="password" required>

            <label for="password2">Repeat Password</label>
            <input type="password" placeholder="Repeat Password" id="password2" name="password2" required>

            <button type="submit" class="auth-btn">Register</button>
            <button type="reset" class="auth-btn secondary">Reset</button>

            <?php
                if (isset($_SESSION['message'])) {
                    echo '<p class="auth-message">'.$_SESSION['message'].'</p>';
                    unset($_SESSION['message']);
                };
            ?>

            <p class="auth-link">Already have an account? <a href="./php/login.php">Login</a></p>
        </form>
    </div>
</body>


</html>