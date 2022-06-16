<?php
session_start();

// Load database connection 
require("helpers/db_connection.php");

if (isset($_SESSION['id'])) {
    $_SESSION["flash_message"] = "User already logged in. Log out first if you want to register a new user.";
    header("location: index.php");
    exit;
}

// Process registration attempt through post method
if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $_SESSION['username_field'] = $username;
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];

    // Reject the registration if the passwords don't match or if no username or password is passed in
    if ($password != $passwordRepeat) {
        $_SESSION["flash_message"] = "Passwords have to match";
        header("location: register.php");
        exit;
    } else if ($username == "") {
        $_SESSION["flash_message"] = "Please fill in a username";
        header("location: register.php");
        exit;
    } else if ($password == "") {
        $_SESSION["flash_message"] = "Please fill in a password";
        header("location: register.php");
        exit;
    }

    // Query the database to see if a user already exists with the same username
    $users_query = $pdo->prepare("SELECT * FROM users WHERE username=:username");
    $users_query->execute(['username' => $username]);
    $user_query = $users_query->fetch();
    
    // Reject the registration if the username is already taken, ottherwise register the user
    if ($users_query->rowCount() > 0) {
        $_SESSION["flash_message"] = "Username already taken";
        header("location: register.php");
        exit;
    } else { 
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $registration = $pdo->prepare("INSERT INTO users (username, pass_hash) VALUES (:username, :password_hash)");
        $registration->execute(['username' => $username, 'password_hash' => $password_hash]);
        $_SESSION["flash_message"] = "Successfully registered";
        header("location: login.php");
        exit;
    }
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
    <title>&#128221; Register</title>
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
        <h2>Register to start using the app</h2>
    </div>
    <div class="login">
        <form action="register.php" method="POST">
            <div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" id="username" required="required" placeholder="Username" autofocus value=<?= (isset($_SESSION['username_field']))? htmlspecialchars($_SESSION['username_field']): "" ;?>>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Password" name="password" required="required" id="password">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Repeat password" name="passwordRepeat" required="required" id="passwordRepeat">
                </div>
                <button type="submit" class="btn btn-outline-secondary">Register</button>
            </div>
        </form>
    </div>
    <?php include("helpers/footer.php"); ?>   
</body>
</html>