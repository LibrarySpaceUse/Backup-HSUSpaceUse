<?php
    session_start();
	//Get's username from the login and greets the user to the homepage
	//Also add instructions for new users to help navigate the page.
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Floor Creator </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
                    <p class="nav selected"><a href="create-floor.php">Floor Creator</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
                </nav>
    </header>
    <main>
        <h2>Please upload a floor blueprint image: </h2>
        <form class="floor-uploader" action="upload.php" method="post" enctype="multipart/form-data">
            <div class="upload">
                <p>Select image to upload:</p>
                <input type="file" name="fileToUpload" id="fileToUpload">
            </div>

            <div class="upload">
                <p>Please enter the name of the floor: </p>
                <input type="text" name="floorName" id="imageFloorName" value="" required>
            </div>

            <div class="upload">
                <p>Please enter a floor number: </p>
                <input type="number" name="floorNum" id="imageFloorNum" value="1" min="1" required>
            </div>
            <input type="submit" value="Upload Image" name="submit" id="upload_submit">
        </form>
    </main>
                <?php
                }
            ?>
    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>