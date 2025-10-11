<?php
session_start();
include_once __DIR__ . '/../config.php';
include_once 'functions.php';

if (isset($_SESSION['email'])) {
    header('Location: dashboard.php');
    exit;
}

if (is_post_request()) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!isset($_POST['email'], $_POST['password']) || empty($_POST['email']) || empty($_POST['password'])) {
        $_SESSION['message'] = 'Please complete the login form.';
        header('Location: login.php');
        exit;
    }


    $check_data = $pdo->prepare("SELECT id, email, password_hash from users WHERE email = ?");
    $check_data->execute([$email]);
    $user = $check_data->fetch(PDO::FETCH_ASSOC);

    if ($user["email"] != $email) {
        $_SESSION["message"] = "Email not registered in database";
        header("Location: login.php");
        exit;
    }
    if($user && password_verify($password, $user["password_hash"])) {
        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $user["id"];
        header("Location: dashboard.php");
        exit;     
    } else {
        $_SESSION["message"] = "Password not correct.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CoinTrack</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../favicon.ico">

</head>

<body class="auth-body">
    <div class="auth-container">
        <img src="../favicon.ico" alt="CoinTrack Logo" class="auth-logo">
        <h1>Welcome Back</h1>
        <form method="post" action="">
            <label for="email">Email</label>
            <input type="email" placeholder="Enter Email" name="email" id="email_login" required>

            <label for="password">Password</label>
            <input type="password" placeholder="Enter Password" id="password_login" name="password" required>

            <button type="submit" class="auth-btn">Login</button>

            <?php
                if (isset($_SESSION['message'])) {
                    echo '<p class="auth-message">'.$_SESSION['message'].'</p>';
                    unset($_SESSION['message']);
                };
            ?>

            <p class="auth-link">Don't have an account? <a href="../index.php">Register</a></p>
        </form>
    </div>
</body>


</html>