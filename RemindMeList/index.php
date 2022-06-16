<?php
session_start();
require_once("helpers/db_connection.php");

// Send the user to the login screen if they are not logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['flash_message'] = "You need to be logged in";
    header("location: login.php");
    exit;
}
$id = $_SESSION['id'];

// Add a new object to the list
if (isset($_POST['type'])) {
    // Check if the user provided a title name that is more than spaces
    if (trim($_POST['objectToAdd'] == "")) {
        $_SESSION['flash_message'] = "Fill in the title that you want to add";
        header("location: index.php");
        exit;
    } else {
        $toAdd = trim($_POST['objectToAdd']);
    }
    $objectType = $_POST['type'];
    
    // Save information, since its optional there is no need to redirect if empty
    $information = (isset($_POST['information'])) ?  $_POST['information'] : $information = "";

    // Add object to the corresponding DB based on the object type
    switch($objectType) {
        case "books":
            $insert = $pdo->prepare("INSERT INTO books (userid, title, information) VALUES (:id, :title, :information)");
            $insert->execute(['id' => $id, 'title' => $toAdd, 'information' => $information]);
            break;
        case "series":
            $insert = $pdo->prepare("INSERT INTO series (userid, title, information) VALUES (:id, :title, :information)");
            $insert->execute(['id' => $id, 'title' => $toAdd, 'information' => $information]);
            break;
        case "movies":
            $insert = $pdo->prepare("INSERT INTO movies (userid, title, information) VALUES (:id, :title, :information)");
            $insert->execute(['id' => $id, 'title' => $toAdd, 'information' => $information]);
            break;
        default:
            $_SESSION['flash_message'] = "Something went wrong, try again";
            header("location: index.php");
            exit;
    }

    // Let the user know the addition was succesfull
    $_SESSION['flash_message'] = "Succesfully added";
    header("location: index.php");
    exit;
}


// Collect the current entries for the user from the DB's
$books_query = $pdo->prepare("SELECT * FROM books WHERE userid=:id");
$books_query->execute(['id' => $id]);
$books = $books_query->fetchAll();

$series_query = $pdo->prepare("SELECT * FROM series WHERE userid=:id");
$series_query->execute(['id' => $id]);
$series = $series_query->fetchAll();

$movies_query = $pdo->prepare("SELECT * FROM movies WHERE userid=:id");
$movies_query->execute(['id' => $id]);
$movies = $movies_query->fetchAll();

// Code to remove objects from the DB
if (isset($_POST['removeBook'])) {
    $remove = $_POST['removeBook'];
    // Create a titles array from the previous query to check if the title exists in the DB before attempting to remove
    $titles = [];
    foreach ($books as $book) {
        array_push($titles, $book['title']);
    }
    if (!in_array($remove, $titles)) {
        $_SESSION['flash_message'] = "Something went wrong, try again";
        header("location: index.php");
        exit;
    } 
    $remove_query = $pdo->prepare("DELETE FROM books WHERE userid=:id AND title=:title");
    $remove_query->execute(['id' => $id, 'title' => $remove]);
    $_SESSION['flash_message'] = "Succesfully removed book";
    header("location: index.php");
    exit;  
} elseif (isset($_POST['removeSerie'])) {
    $remove = $_POST['removeSerie'];
    $titles = [];
    foreach ($series as $serie) {
        array_push($titles, $serie['title']);
    }
    if (!in_array($remove, $titles)) {
        $_SESSION['flash_message'] = "Something went wrong, try again";
        header("location: index.php");
        exit;
    } 
    $remove_query = $pdo->prepare("DELETE FROM series WHERE userid=:id AND title=:title");
    $remove_query->execute(['id' => $id, 'title' => $remove]);
    $_SESSION['flash_message'] = "Succesfully removed serie";
    header("location: index.php");
    exit;  
} elseif (isset($_POST['removeMovie'])) {
    $remove = $_POST['removeMovie'];
    $titles = [];
    foreach ($movies as $movie) {
        array_push($titles, $movie['title']);
    }
    if (!in_array($remove, $titles)) {
        $_SESSION['flash_message'] = "Something went wrong, try again";
        header("location: index.php");
        exit;
    } 
    $remove_query = $pdo->prepare("DELETE FROM movies WHERE userid=:id AND title=:title");
    $remove_query->execute(['id' => $id, 'title' => $remove]);
    $_SESSION['flash_message'] = "Succesfully removed movie";
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
    <title>&#128195; Remind Me List</title>
</head>
<body>
    <?php 
    include("helpers/navbar.php");
    include("helpers/flash.php");
    ?>
    <script src="helpers/script.js"></script>
    <?php if (isset($err)) {echo $err;}?>
    <div class="flexbox">
        <div class="container">
            <h4 class="listHeaders">&#128218; Books to read:</h4>
            <ul>
                <?php if ($books_query->rowCount() > 0) { foreach ($books as $book):?>
                <li>
                    <div class="flexboxSmall">
                        <div class="flexLeft">
                            <h4><?= htmlspecialchars($book['title']) ?> </h4>
                        </div>
                        <div class="flexRight">
                            <form method="POST" accept="index.php">
                                <button class="emoticon" type="submit" name="removeBook" value="<?= htmlspecialchars($book['title']) ?>">
                                    &#9989;
                                </button>
                            </form>
                        </div>
                    </div>
                    <i><?= htmlspecialchars($book['information']) ?></i>

                </li>
                <?php endforeach;}?>
                
            </ul>
            <form method="POST" action="index.php">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Add a book" required name="objectToAdd" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit" name="type" value="books">Add book</button>
                    </div>
                </div>
                <div>
                    <input type="text" class="form-control" placeholder="Optional: Reason for adding" name="information">
                </div>
            </form>
        </div>
        <div class="container">
            <h4 class="listHeaders">&#127871; Series to binge:</h4>
            <ul>                
                <?php if ($series_query->rowCount() > 0) { foreach ($series as $serie):?>
                <li>
                    <div class="flexboxSmall">
                        <div class="flexLeft">
                            <h4><?= htmlspecialchars($serie['title']) ?> </h4>
                        </div>
                        <div class="flexRight">
                            <form method="POST" accept="index.php">
                                <button class="emoticon" type="submit" name="removeSerie" value="<?= htmlspecialchars($serie['title']) ?>">
                                    &#9989;
                                </button>
                            </form>
                        </div>
                    </div> 
                    <i><?= htmlspecialchars($serie['information']) ?></i>                    
                </li>
                <?php endforeach;}?>
            </ul>
            <form method="POST" action="index.php">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Add a serie" required name="objectToAdd" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit" name="type" value="series">Add serie</button>
                    </div>
                </div>
                <div>
                    <input type="text" class="form-control" placeholder="Optional: Reason for adding" name="information">
                </div>
            </form>
        </div>
        <div class="container">
            <h4 class="listHeaders">&#127916; Movies to watch:</h4>
            <ul>                   
            <?php if ($movies_query->rowCount() > 0) { foreach ($movies as $movie):?>
                <li>
                    <div class="flexboxSmall">
                        <div class="flexLeft">
                            <h4><?= htmlspecialchars($movie['title']) ?> </h4>
                        </div>
                        <div class="flexRight">
                            <form method="POST" accept="index.php">
                                <button class="emoticon" type="submit" name="removeMovie" value="<?= htmlspecialchars($movie['title']) ?>">
                                    &#9989;
                                </button>
                            </form>
                        </div>
                    </div> 
                    <i><?= htmlspecialchars($movie['information']) ?></i>
                </li>
            <?php endforeach;}?>
            </ul>
            <form method="POST" action="index.php">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Add a movie" required name="objectToAdd" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit" name="type" value="movies">Add movie</button>
                    </div>
                </div>
                <div>
                    <input type="text" class="form-control" placeholder="Optional: Reason for adding" name="information">
                </div>
            </form>
        </div>
    </div>
    <?php include("helpers/footer.php"); ?>
</body>
</html>