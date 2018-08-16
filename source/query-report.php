<?php
	//The main page for any single queries that the user will grab from the DB.
	session_start();
    require_once('form_functions.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Query Report </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
    integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
    crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
    integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
    crossorigin=""></script>
		<script src="./javascript/report-objs-pop.js"></script>
		<script src="./javascript/leaflet.rotatedMarker.js"></script>
		<script src="./javascript/icons.js"></script>
		<script src="./javascript/leaflet.browser.print.min.js"></script>
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >

		<?php
		if (array_key_exists("username", $_SESSION)){
				?>
				<h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
				<?php
		}
		?>

</head>
<script type="text/javascript">

</script>
<body>
    <header>
        <img class="logo" src="images/hsu-wm.svg">
        <h1>SpaceUse</h1>
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
                    <p class="nav selected"><a href="query-select.php">Query Report</a></p>
                    <p class="nav"><a href="editor.php">Layout Creator</a></p>
                    <p class="nav"><a href="create-floor.php">Floor Creator</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
                </nav>
    </header>
    <main>

        <form class="report-selector" id="choose_survey_form">
            <fieldset>
				<div id="query_header_container">
					<h2 id="query_header"><?= $_SESSION["username"]?> what shall we query today? </h2>
				</div>
                <select name="year" id="year-select">
                    <option value="0">Choose a Year</option>
                    <?php
                    	get_year_options();
                    ?>
                </select>
				<select name="month" id="month-select" style="display:none">
                    <option value="0">Choose a Month</option>
                </select>
				<select name="day" id="day-select" style="display:none">
                    <option value="0">Choose a Day</option>
                </select>

                <select name="survey_id" id="survey-id-select" style="display:none">
                    <option id="chosen_survey" value="0">Choose a Survey</option>
                </select>
                <input type="number" id="threshold" name="threshold" value="<? echo isset($_GET['threshold']) ? $_GET['threshold'] : '10' ?>" 
                    style="display: none;" min="1" max="100"/>
                <input type="submit" name="submit-query" style="display:none" id="query_submit_button"/>
				<input type="button" id="query_print_button" value="Print Report" style="display:none" onclick="printReport()"/>
            </fieldset>
        </form>
                <?php
            }
        ?>

		<div id="mapid"></div>
		<iframe id="print_frame">
			<html>
				<body>
				</body>
			</html>
		</iframe>
		<?php

		if (array_key_exists("survey_id", $_GET)){
			?>
			<script>
				//create maps and grab survey_id
				var survey_id = <?= $_GET["survey_id"] ?>;
				//used to keep track of all the seats on the floor and how many are being used
                var totalSeats = 0;
                var seatsUsed = 0;
                var print_header;
                var area_string;
                var modified_furn = 0;
                var threshold;

				//make map
				var mymap = L.map('mapid', {crs: L.CRS.Simple});
				var areaLayer = L.layerGroup().addTo(mymap);
				var furnitureLayer = L.layerGroup().addTo(mymap);
				var bounds = [[0,0], [360,550]];

				make_map(survey_id);

		</script>
		<?php
		}
    ?>
    </main>
    <footer>
			<p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>
