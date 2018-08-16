<?php
	//The main page for any queries that the user will grab from the DB.
	//Needs more queries such as activities, whiteboard use.
	//  Load the state of the library during that survey to give us not only area_use, but furniture location
    session_start();
    require_once('get_floor_svg.php');
    require_once('form_functions.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Query Report </title>
    <meta charset="utf-8" />

    <!-- jquery CDN-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
    integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
    crossorigin=""/>

    <!-- scripts here are for jquery ui-->
    <script src="./javascript/transfer-select-js/jquery.js"></script>
    <script src="./javascript/jquery-ui-js/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="styles/jquery-ui.min.css" type="text/css">

    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
    integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
    crossorigin=""></script>
		<script src="./javascript/report-objs-pop.js"></script>
		<script src="./javascript/leaflet.rotatedMarker.js"></script>
		<script src="./javascript/leaflet.browser.print.min.js"></script>

    <!--scripts here are for the transfer select plugin-->

    <script src="./javascript/transfer-select-js/bootstrap-transfer.js"></script>
		<script src="./javascript/icons.js"></script>
    <script src="./javascript/populate_multibox.js"></script>
    <script src="./javascript/query_survey_range.js"></script>
    <link href="styles/bootstrap-transfer.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >


    <!--script for enabling the bootstrapTransfer plugin-->
    <script type="text/javascript">
		//Needed for printing text report feature
		var area_string = "";
		var print_header;
        var report_added = false;

        $(function() {
            $("#date-select-1").datepicker();
            $("#date-select-2").datepicker();
        });

		function printReport(){
			var rFrame = document.getElementById('print_frame');
			rFrame.contentWindow.print();
		}
    </script>
</head>

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
                if (array_key_exists("username", $_SESSION)){
                    ?>
                    <h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
                    <?php
                }
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
        <form class="report-selector" id="choose_period_form">
            <fieldset>
                <p class="p-inline"> Start Date: 
                    <input type="text" name="date1" id="date-select-1">
                </p>

                <p class="p-inline"> End Date: 
                    <input type="text" name="date2" id="date-select-2">
                </p>

                <p class="p-inline"> Floor: 
                    <select name="in-floor" id="in_floor_select">
                        <option value="">Choose Floor</option>
                        <?php
                    	    get_floor_svg();
                        ?>
                    </select>
                </p>
                <p class="p-inline"> Layout: 
                    <select name="in-layout" id="in_layout_select">
                        <option value="">Choose a Corresponding Layout</option>
                    </select>
                </p>

                <p class="p-inline"> Area Use Threshold: 
                    <input type="number" id="threshold" name="threshold" value="10" 
                        style="display: inline;" min="1" max="100"/>
                </p>

                <input type="button" id="query_print_button" value="Print Report" style="display:none" onclick="printReport()"/>
                
			</fieldset>
        </form>


                <?php
            }
        ?>
        <div id="multi-select"></div>
        <button class="nav_button" id="submit-surveys">Submit Surveys</button>
        <div id="map_container" style='width: 100%; height: 92.5%;'>
            <div id="mapid" ></div>
        </div>

		<iframe id="print_frame">
			<html>
				<body>
				</body>
			</html>
		</iframe>
		<?php
    ?>
    </main>
    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>
