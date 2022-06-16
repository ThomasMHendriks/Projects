<?php
session_start();

// Load database connection 
require_once("helpers/db_connection.php");

if (isset($_SESSION['id'])) {
    $_SESSION["flash_message"] = "User already logged in. Log out first if you want to switch users.";
    header("location: index.php");
    exit;
}

// Process login attempt through post method
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $_SESSION['username_field'] = $username;
    $password = $_POST['password'];

    // Check if the user left either space blank
    if ($username == "" || $password == "") {
        $_SESSION["flash_message"] = "Please fill in a username and password";
        header("location: login.php");
        exit;
    }

    // Query login info for login check
    $login_query = $pdo->prepare("SELECT * FROM users WHERE username=:username");
    $login_query->execute(['username' => $username]);
    $login = $login_query->fetch();

    // Login fails if the username is not in the DB or the password doesn't match
    if ($login_query->rowCount() == 0) {
        $_SESSION["flash_message"] = "Invalid Username";
        header("location: login.php");
        exit;
    } elseif (!password_verify($password, $login['pass_hash'])) {
        $_SESSION["flash_message"] = "Invalid password";
        header("location: login.php");
        exit;
    }

    // Login succesfull
    $_SESSION['id'] = $login['id'];
    $_SESSION["flash_message"] = "Login Succesfull";
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles.css">
    <title>&#128274; Login</title>
</head>
<body>
    <?php 
    include("helpers/navbar.php");
    include("helpers/flash.php");
    ?>
    <script src="helpers/script.js"></script>

    <?php if (isset($err)) {echo $err;}?>
    <div class="welcome">
        <h2>Welcome to the RemindMeList app</h2>
        <h2>Log in to start using the app</h2>
    </div>
    
    <div class="login">
        <form action="login.php" method="POST">
            <div>
                <div class="mb-3">                    
                    <input type="text" class="form-control" name="username" id="username" required="required" placeholder="Username" autofocus value=<?= (isset($_SESSION['username_field']))? htmlspecialchars($_SESSION['username_field']): "" ;?>>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Password" name="password" required="required" id="password">
                </div>
                <button type="submit" class="btn btn-outline-secondary">Login</button>
            </div>
        </form>
    </div>
    <?php include("helpers/footer.php"); ?>
</body>
</html>