<?php
    session_start();
	//Get's username from the login and greets the user to the homepage
	//Also add instructions for new users to help navigate the page.
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Collect Data </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="./javascript/get_layouts.js"></script>
    <link rel="stylesheet" href="normalize.css" type="text/css" >
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >
</head>
<body>
    <header>
        <img class="logo" src="images/hsu-wm.svg">
        <h1>SpaceUse</h1>

    <?php
        if (array_key_exists("username", $_SESSION)){
            ?>
            <h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
            <?php
        }
    ?>


    <?php
        if (!array_key_exists("username", $_SESSION)){
            ?>
            <p class="invalid-login"> Please first <a href="index.php">login</a> before accessing the app</p>
            <?php
        }
        else{
            ?>
            <nav>
                <p class="nav"><a href="home.php">Home</a></p>
                <p class="nav"><a href="data-collection.php">Data Collection</a></p>
                <p class="nav"><a href="query-select.php">Query Report</a></p>
                <p class="nav"><a href="editor.php">Layout Creator</a></p>
                <p class="nav"><a href="create-floor.php">Floor Creator</a></p>
                <p class="nav"><a href="logout.php">Logout</a></p>
            </nav>
    </header>
    <main>
      <h2>Success <?= $_SESSION["username"]?>, your floor has been recorded and saved to the database</h2>


    </main>
            <?php
        }
    ?>

    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>