<?php
	//In this file, the core structure of editing a layout is implemented.
	session_start();
	require_once('get_floor_svg.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> Layout Editor </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
  	 integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
  	 crossorigin=""/>
   <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
  	 integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
  	 crossorigin=""></script>
   <script src="./javascript/icons.js"></script>
   <script src="./javascript/editor_helper.js"></script>
   <script src="./javascript/submit_layout.js"></script>
   <script src="./javascript/helpers.js"></script>
   <script src="./javascript/add-areas.js"></script>
   <script src="./javascript/leaflet.rotatedMarker.js"></script>
   <script type="text/javascript">
    $(function() {
        $("#nav_toggle").click(function(){
            $("nav").toggleClass("hidden");
            $("header").toggleClass("hidden");
            $("main").toggleClass("to-top");
            $("footer").toggleClass("hidden");
            $(".hide_nav").toggleClass("nav_open");
        })
    });
    </script>
</head>
<body>
    <button class="hide_nav" id="nav_toggle"><p id="hide_nav_plus">&plus;</p></button>
    <header class="hidden">
        <img class="logo" src="images/hsu-wm.svg">
        <h1>SpaceUse</h1>


    <?php
        if (array_key_exists("username", $_SESSION)){
            ?>
            <h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
            <?php
        }
    ?>
    </header>
    <?php
        if (!array_key_exists("username", $_SESSION)){
            ?>
            <p class="invalid-login"> Please first <a href="index.php">login</a> before accessing the app</p>
            <?php
        }
        else{
             ?>
            <nav class="hidden">
                <p class="nav"><a href="home.php">Home</a></p>
                <p class="nav"><a href="data-collection.php">Data Collection</a></p>
                <p class="nav"><a href="query-select.php">Query Report</a></p>
				<p class="nav selected"><a href="editor.php">Layout Creator</a></p>
				<p class="nav"><a href="create-floor.php">Floor Creator</a></p>
                <p class="nav"><a href="logout.php">Logout</a></p>
            </nav>
            <main class="to-top">
                <form class="layout-selector" id="lay-select">
                    <fieldset>
                        <label style="padding-right: 97px; color: white; text-decoration:bold;">
                          Select a floor:</label>
                        <!-- Choose the floor to work from-->
                        <select name="floor-select" id="floor_dropdown">
                            <option value="default">Choose a Floor</option>
							<?php
                    	    	get_floor_svg();
                        	?>
                        </select>
						<button type="button" id="submit_floor" style="display: none;">Select</button>

						<!--select a piece of furniture to place -->
            			<div class="furn_editor_select">
							<label style="padding-right: 10px; color: white; text-decoration:bold;">
                   				Select a piece of furniture:</label>

						    <select name="furniture-select" id="furn_icons">
    						</select>
            			</div>

            <div id="editor_buttons">
  						<button type="button" id="getAreas" style="display: none;">Generate Areas</button>
  						<button type="button" id="insertLayout" style="display: none;">Submit Layout</button>
            </div>
						<div class="loading">
							<img src="images/loadwheel.svg" id="load-image">
						</div>
					</fieldset>
                </form>
				<!--Create div for the popup -->
				<div id="popupHolder"><div id="popup"></div></div>
                <div id="mapid"></div>
                    <?php
                }
            ?>
			<!-- Modal -->
			<div id="roomPopup">
				<!-- Modal content-->
				<div class="modal-content">
					<!--<div class="modal-header">
						<h4 class="modal-title">Room Information</h4>
					</div>-->
					<div>
						<label>Room Name: </label>
						<input id="roomName" name="roomName" type="text">
						<label>Room ID: </label>
						<input id="roomId" name="roomId" type="text" placeholder="Example: LIB 101">
						<input id="roomSubmit" type="submit" value="Submit">
					</div>
				</div>
			</div>
                <footer class="footd hidden">
                    <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
                </footer>
            </main>
    <script>
		//create map
    	var mymap = L.map('mapid', {crs: L.CRS.Simple, minZoom: 0, maxZoom: 4});
    	var furnitureLayer = L.layerGroup().addTo(mymap);
		var areaLayer = L.layerGroup().addTo(mymap);
		var drawnItems = new L.FeatureGroup();
    	var bounds = [[0,0], [360,550]];
		mymap.fitBounds(bounds);

		//setup global variables
		var selected_marker;
		var selected_furn;
		var mapPopulated = false;
		var floor_selection = -1;
		var form_info = document.getElementById("floor_dropdown");
		var floor_image = "local";
		var polyArea;
		var furn;
		var ftype;
		var coord;
		var lat;
		var lng;

		//Create the boundries for placing furniture
		var latMax = 359.75;
		var latMin = -0.5;
		var longMax = 508.18;
		var longMin = 42.18;

		//container for furniture objects
		var furnMap = new Map();
		var mapKey = 0;

		//create a container for areas
		var areaMap = new Map();

		//floor image placed from dropdown selection
		var image;

		//Varables to hold the room info
		var roomName = "";
		var roomId = "";
		
		$(document).ready(function() {
			$("#roomPopup").dialog({
				autoOpen: false,
				title: "Room Info"
			});
		});

		$("#roomSubmit").click(function(e){

			roomName = document.getElementById("roomName").value;
			roomId = document.getElementById("roomId").value;
			document.getElementById("roomName").value = "";
			document.getElementById("roomId").value = "";
			$('#roomPopup').dialog('close');

			createFurnObj();
			roomName = "";
			roomId = "";
		});

		//define our furniture object here
		function Furniture(id,ftype, latlng, fname, roomName, roomId){
			this.id = id;
			this.fname = fname;
			this.marker;
			this.degreeOffset = 0;
			this.x = latlng.lng;
			this.y = latlng.lat;
			this.ftype = ftype;
			this.roomName = roomName;
			this.roomId = roomId;
		}

		var selectFloor = document.getElementById("submit_floor");
		var floor_dropdown = document.getElementById("floor_dropdown");
		floor_dropdown.onchange = function(){
			selectFloor.style.display = "inline";
		}

		selectFloor.onclick = function(){
			document.getElementById("getAreas").style.display = "block";
			document.getElementById("insertLayout").style.display = "block";
			if(floor_selection > 0 && form_info.value != floor_selection){
			if (confirm("You are about to change the floor image and delete all of the furniture added to the map, are you sure you want to continue?")) {
				//Remove the current markers and areas
				mymap.removeLayer(furnitureLayer);
				mymap.removeLayer(areaLayer);
				furnitureLayer = L.layerGroup().addTo(mymap);
				areaLayer = L.layerGroup().addTo(mymap);
				//Clear furnmap and areamap, so user can start new
				furnMap = new Map();
				areaMap = new Map();
				mapPopulated = false;
				addMapPic();
				getAreas.innerHTML = "Generate Areas";
				//insertLayout.style.display = "none";
			}
			else {
				form_info.options[floor_selection].selected = true;
			}
			}

			else if(floor_selection == -1){
				addMapPic();
			}
		}

		function addMapPic(){
			//remove old floor image and place newly selected floor image
			if( mymap.hasLayer(image)){
				mymap.removeLayer(image);
			}

			//floor_selection is the number of the floor(ex. Floor 2 is the second floor so floor selection is 2)
			floor_selection = form_info.value;
			$.ajax({
				url: 'phpcalls/get-floor-path.php',
				type: 'get',
				async: false,
				data:{ 'floor_ID': floor_selection },
				success: function(data){
					var json_object = JSON.parse(data);
					floor_path = json_object;
					image = L.imageOverlay(floor_path[0], bounds).addTo(mymap);
				}
			});
		}

		//get areas and place over map
		var getAreas = document.getElementById("getAreas");
		getAreas.onclick = function(){
			//get areas for this floor
      		//check if the areaMap has been populated already
      		//create areas if the map is empty
			if(!mapPopulated){
				
				createAreas(floor_selection);
        		getAreas.innerHTML = "Remove Areas";
  				mapPopulated = true;
       
			}

			else{
				mymap.removeLayer(areaLayer);
				areaLayer = L.layerGroup().addTo(mymap);
				mapPopulated = false;
				getAreas.innerHTML = "Generate Areas";
				//insertLayout.style.display = "none";
			}
		}

		//Make sure all pieces of furniture are in areas before inserting a new layout.
		var insertLayout = document.getElementById("insertLayout");
		insertLayout.onclick = function(){
			var layoutReady = true;
			var outOfBoundsLatLng = [];
			var roomCheck;
			//calculate the area each piece of furniture is in.
			furnMap.forEach(function(value, key, map){
				//get the x,y for each piece of furniture
				y = value.y;
				x = value.x;
				roomCheck = value.ftype;
				area_id="TBD";

				areaMap.forEach(function(jtem, key, mapObj){
					//check if x,y are in a polygon
					if(isMarkerInsidePolygon(y,x, jtem.polyArea)){
						area_id = jtem.area_id;
					}
				});

				if(area_id !== "TBD"){
					value.inArea = area_id;
				}
        		else {
					if(roomCheck != 20){
						layoutReady = false;
						outOfBoundsLatLng = [y,x];
					}
				}
			});


			//check if the layout is ready to insert
			if(layoutReady){
				var author = "<?= $_SESSION["username"]?>";
				var name;
				var person = prompt("Please enter a name for the layout:", author);
				if (person == null || person == "") {
				alert("User has canceled the layout submit.");
				}
				else{
					var layout_name = person;
					submitLayout(author, layout_name, floor_selection, furnMap, areaMap);
         		}
			}
        	else{
  				//layout not ready, alert the user and pan to last marker out of bounds.
  				alert("Not all of your furniture is in an area, fix this before re-submitting!");
  				mymap.panTo(outOfBoundsLatLng);
			}
		}

		//place a draggable marker onClick!
		function onMapClick(e) {
		coord = e.latlng;
		lat = coord.lat;
		lng = coord.lng;

		/*if(lat > latMax || lat < latMin || lng > longMax || lng < longMin){
			alert("Please place the furniture inside the map");
		}*/

			//get the furniture select element
			furn = document.getElementById("lay-select").elements.namedItem("furniture-select");
			//get the type id from the value
			ftype = furn.value;
			//convert the string furniture type into an int to send to getIconObj(int ftype)
			ftype = parseInt(ftype);

			//Prompt user for Room Info
			if(ftype == 20){
				$('#roomPopup').dialog('open');
			}

			else{
				createFurnObj();
			}
    
		}

		//bind onMapClick function
		mymap.on('click', onMapClick);

		//On zoomend, resize the marker icons
		mymap.on('zoomend', function() {
			var markerSize;
			//resize the markers depending on zoomlevel so they appear to scale
			//zoom is limited to 0-4
			switch(mymap.getZoom()){
				case 0: markerSize= 5; break;
				case 1: markerSize= 10; break;
				case 2: markerSize= 20; break;
				case 3: markerSize= 40; break;
				case 4: markerSize= 80; break;
			}
			//alert(mymap.getZoom)());
			var newzoom = '' + (markerSize) +'px';
			var newLargeZoom = '' + (markerSize*2) +'px';
			//marker = L.marker(e.latlng, {icon: couchFour }).addTo(furnitureLayer).bindPopup("I am a Computer Station.");
			$('#mapid .furnitureIcon').css({'width':newzoom,'height':newzoom});
			$('#mapid .furnitureLargeIcon').css({'width':newLargeZoom,'height':newLargeZoom});
		});

		function markerClick(e){
			//when a marker is clicked, it should be rotatable, and delete able
			selected_marker = this;
			selected_furn = furnMap.get(selected_marker.options.fid);
			//make sure the nameDiv is created and attached to popup
			if(document.getElementById("nameDiv") == null){
				var nameDiv = document.createElement("div");
				nameDiv.id = "nameDiv";
				document.getElementById("popup").appendChild(nameDiv);
			}
			//set the nameDiv to the name of the current furniture
			var nameDiv = document.getElementById("nameDiv");
			nameDiv.innerHTML = "<strong>Type: </strong>"+selected_furn.fname+"</br></br>";

			if(document.getElementById("deleteButtonDiv") == null) {
				//create a div to hold delete marker button
				var deleteButtonDiv = document.createElement("div");
				deleteButtonDiv.id = "deleteButtonDiv";
				//attach deleteButton div to popup
				document.getElementById("popup").appendChild(deleteButtonDiv);
				//create delete button
				var deleteMarkerButton = document.createElement("BUTTON");
				deleteMarkerButton.id = "deleteMarkerButton";
				deleteMarkerButton.innerHTML = "Delete";
				deleteMarkerButton.onclick = deleteHelper;
				//deleteMarkerButton.className = "deleteButton";
				//add the button to the div
				document.getElementById("deleteButtonDiv").appendChild(deleteMarkerButton);
			}

			//check if the rotateDiv has been made
			if(document.getElementById("rotateDiv") == null){
				//create a div to hold rotateButton
				var rotateDiv = document.createElement("div");
				rotateDiv.id = "rotateDiv";
				//attach the rotatebutton div to the popup
				document.getElementById("popup").appendChild(rotateDiv);
				rotateHelper("rotateDiv");
			}
		}

		function createFurnObj(){
			//get the index of the selected item
			var findex = furn.selectedIndex;
			//get the options
			var furnOption = furn.options;
			//get the inner text of the selected furniture item to save the name.
			var fname = furnOption[findex].text;


			var selectedIcon = getIconObj(ftype);

			var latlng = coord;

			//create the furniture object and store in map
			var newFurn = new Furniture(mapKey, ftype, latlng, fname, roomName, roomId);
			console.log(newFurn);

			furnMap.set(mapKey, newFurn);
			if(document.getElementById("popup") == null){
					popupDiv = document.createElement("DIV");
					popupDiv.id = "popup";
					document.getElementById("popupHolder").appendChild(popupDiv);
			}

			var popup = document.getElementById("popup");
			var popupDim =
			{
				'minWidth': '200',
				'minHeight': '2000px',
			};//This is the dimensions for the popup

			marker = L.marker(coord, {
					fid: mapKey++,
					icon: selectedIcon,
					rotationAngle: 0,
					draggable: true
			}).addTo(furnitureLayer).bindPopup(popup,popupDim);
			//give it an onclick function
			marker.on('click', markerClick);

			//define drag events
			marker.on('drag', function(e) {
				console.log('marker drag event');
			});
			marker.on('dragstart', function(e) {
				console.log('marker dragstart event');
				mymap.off('click', onMapClick);
			});
			marker.on('dragend', function(e) {
				//update latlng for insert string
				var changedPos = e.target.getLatLng();
				var lat=changedPos.lat;
				var lng=changedPos.lng;

				selected_marker = this;
				selected_furn = furnMap.get(selected_marker.options.fid);
				selected_furn.x = lng;
				selected_furn.y = lat;

				//output to console to check values
				console.log('marker dragend event');

				setTimeout(function() {
					mymap.on('click', onMapClick);
				}, 10);
			});
		}
    </script>
</body>
</html>
