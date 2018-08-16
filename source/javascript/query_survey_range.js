
//define variables used in building map
var areaMap = new Map();
var furnMap = new Map();
var mymap;
var areaLayer;
var furnitureLayer;
var bounds;
var survey_id_array;
var avgFloorPop = 0;
var floorMaxSeats = 0;
var total_floor_vistors = 0;
var queried_info_legend;
var survey_info_legend;
var threshold;

//define objects
function Area(area_id, verts, area_name){
	this.area_id = area_id;
	this.verts = verts;
	this.area_name = area_name;
	this.numSeats = 0;
	this.avgPopArea = 0;
	this.avgRatio = 0;
	this.totalSeatsUsed = 0;
	this.peak = 0;
	this.peakSurvey = 0;
	this.peakDate = 0;
}

function Verts(x, y, order){
	this.x = x;
	this.y = y;
	this.order = order;
}

function Furniture(fid, numSeats, x, y, degree_offset, ftype, inArea, avgUseRatio, avgOccupancy, sumOccupants, modified_count, mod_array, activities){
	this.fid = fid;
	this.numSeats = numSeats;
	this.x = x;
	this.y = y;
	this.degree_offset = degree_offset;
	this.ftype = ftype;
	this.inArea = inArea;
	this.avgUseRatio = avgUseRatio;
	this.avgOccupancy = avgOccupancy;
	this.sumOccupants = sumOccupants;
	this.modified_count = modified_count;
	this.mod_array = mod_array;
	this.activities = activities;
}

function Activity(count, name){
	this.count = count;
	this.name = name;
}

//Call this function to build the map when the user clicks "submit survey" button
$(function(){
	$('#submit-surveys').click(function(){
		threshold = Number(document.getElementById("threshold").value);


		if(mymap != null){
			mymap.remove();
			mymap = null;
			areaLayer = null;
			furnitureLayer = null;
		}

		document.getElementById("mapid").style.display = "block";
		document.getElementById("query_print_button").style.display = "inline";
		document.getElementById("multi-select").style.display = "none";
		document.getElementById("submit-surveys").style.display = "none";
		document.getElementById("map_container").innerHTML = "<div id='mapid'></div>";

		mymap = L.map('mapid', {crs: L.CRS.Simple});
		areaLayer = L.layerGroup().addTo(mymap);
		furnitureLayer = L.layerGroup().addTo(mymap);
		bounds = [[0,0], [360,550]];

		mymap.fitBounds(bounds);

		mymap.on('zoomend', function() {
            var markerSize;
            var markerAnchor;
            //resize the markers depending on zoomlevel so they appear to scale
            //zoom is limited to 0-4
            switch(mymap.getZoom()){
                case 0: markerSize= 5; markerAnchor= -2.5; break;
                case 1: markerSize= 10; markerAnchor = -5; break;
                case 2: markerSize= 20; markerAnchor = -10; break;
                case 3: markerSize= 40; markerAnchor = -20; break;
                case 4: markerSize= 80; markerAnchor = -40; break;
            }
            var newzoom = '' + (markerSize) +'px';
            var newanchor = '' + (markerAnchor) +'px';
            var newLargeZoom = '' + (markerSize*2) +'px';
            var newLargeAnchor = '' + (markerAnchor*2) +'px';
            $('#mapid .furnitureIcon').css({'width':newzoom,'height':newzoom, 'margin-left':newanchor, 'margin-top':newanchor});
            $('#mapid .furnitureLargeIcon').css({'width':newLargeZoom,'height':newLargeZoom, 'margin-left':newLargeAnchor, 'margin-top':newLargeAnchor});
        });

		survey_id_array = [];
		var i = 0;
		var cur_layout = document.getElementById("in_layout_select");
		var cur_floor = document.getElementById("in_floor_select");
		var layout_name = cur_layout.options[cur_layout.selectedIndex].text;
		var snip = /(.*Layout:\s+)(.*)(\s+created.*)/;
		var snip_name = layout_name.replace(snip, "$2");

		//this code snippet grabs elements selected in the multibox and puts survey id's into an array for later use
		$('#multi-select-input').children('option').each(function(){
			var survey_obj = new Object();
			survey_obj.id = this.value;
			survey_id_array[i] = survey_obj;
			i++;
		});

		survey_info_legend = L.control();
		survey_info_legend.onAdd = function (mymap){
			var div = L.DomUtil.create('div', 'report_legend');
			var header = document.createElement('p');
			var date1 = document.getElementById('date-select-1');
			var date2 = document.getElementById('date-select-2');

			header.innerHTML = "Survey Range: " + date1.value + " through " + date2.value
						+ "</br>Number of Surveys: " + survey_id_array.length + "</br>Layout: "
						+ snip_name + "</br>Floor: " + cur_floor.value;
			print_header = "Survey range report for Layout "
						+ cur_layout.value + " on Floor " + cur_floor.value
						+ "</br>" + date1.value + " - " + date2.value;

			div.appendChild(header);

			return div;
		}

		survey_info_legend.addTo(mymap);
		var json_string = JSON.stringify(survey_id_array);
		loadMap(parseInt(cur_floor.value));
		queryAreas(cur_layout.value, json_string);
		
	});
});

//Queries all of the area's associate with the Layout to populate the Area Map with.
function queryAreas(layout_id, survey_id_json){
	$.ajax({
		url: 'phpcalls/area-from-survey.php',
		type: 'get',
		data:{ 'layout_id': layout_id },
		success: function(data){
			jsondata = JSON.parse(data);
			popAreaMap(jsondata);
			queryFurnitureInfo(survey_id_json, layout_id);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});
}

//Queries all the furniture data based on the survey id's and the layout.
//It consolidates furniture data for all survey's for each object and returns that data within a JSON string.
function queryFurnitureInfo(survey_id_json, layout_id){
	$.ajax({
		url: 'phpcalls/report-multisurvey-furniture.php',
		type: 'get',
		data:{ 'survey_ids': survey_id_json,
				'layout_id': layout_id},
		success: function(data){
			jsondata = JSON.parse(data);
			popFurnMap(jsondata);
			addFurniture();
			calculateAreaPeaks(survey_id_json, layout_id);
			calculateAreaData();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});
}

//accepts the area data from a json string and builds the areas on the map with the proper data
function popAreaMap(jsonAreas){
	areaMap = new Map();
	for(key in jsonAreas){
		cur_area = jsonAreas[key];
		verts = [];
		for(i in cur_area.area_vertices){
			cur_verts = cur_area.area_vertices[i];

			newVert = new Verts(cur_verts.v_x , cur_verts.v_y, cur_verts.load_order);
			verts.push(newVert);
		}

		newArea = new Area(cur_area.area_id, verts, cur_area.area_name);
		areaMap.set(cur_area.area_id, newArea);
	}
}

//Popualtes the Furniture map with appropriately consolidated furniture data.
function popFurnMap(jsonFurn){
	furnMap = new Map();
	for(key in jsonFurn){
		cur_furn = jsonFurn[key];
		if(cur_furn.in_area != null){
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, cur_furn.x, cur_furn.y, cur_furn.degree_offset, cur_furn.furn_type, cur_furn.in_area, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.mod_array, cur_furn.activities);
		}
		else{
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, cur_furn.x, cur_furn.y, cur_furn.degree_offset, cur_furn.furn_type, null, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.mod_array, cur_furn.activities);
		}
		furnMap.set(cur_furn.furniture_id, newFurniture);
	}
}

//Helper calls the draw function for each area.
function addSurveyedAreas(){
	areaMap.forEach(function(key, value, map){
		drawArea(key).addTo(mymap);
	});
}

function activityExists(act_obj, array){
	for(i in array){
		if(act_obj.name === array[i].name){
			return true;
		}
	}
	return false;
}

//this function add's the furniture markers to the map and builds the marker popups associated with each piece of furniture
function addFurniture(){
	var survey_id_json = JSON.stringify(survey_id_array);
	furnMap.forEach(function(key, value, map){
		//establish values for each piece of furniture
		latlng = [key.y, key.x];
		ftype = parseInt(key.ftype)
		selectedIcon = getIconObj(ftype);
		degreeOffset = parseInt(key.degreeOffset);
		numSeats = key.numSeats;
		avgOccupied = key.avgOccupancy;
		sumOccupant = key.sumOccupants;
		avgUse = key.avgUseRatio;
		fid = key.fid;
		areaId = key.inArea;
		mod_array = key.mod_array;
		activities = key.activities;

		//if this furniture is modified, add modified data to the furniture and then, draw a line from its original latlng
		//get array of modified coordinates
		for(j in mod_array){
			coor_element = mod_array[j];
			new_latlng = [coor_element[1], coor_element[0]];
			pointList = [latlng, new_latlng];

			polyline = new L.polyline(pointList, {
				color: 'red',
				weigth: 3,
				opacity: 0.5,
				smoothFactor: 1
			});

			polyline.addTo(mymap);

			mod_marker = L.marker(new_latlng, {
				icon: selectedIcon,
				rotationAngle: degreeOffset,
							rotationOrigin: "center",
				draggable: false,
				ftype: ftype,
				numSeats: numSeats,
				fid: fid.toString()
			}).addTo(furnitureLayer);

			mod_popString = "<strong>Modified Furniture: </br>Occupied Seats: </strong>" + coor_element[3] + "/" + coor_element[4];
			mod_marker.bindPopup(mod_popString);
			mod_marker.setOpacity(.5);
			mod_marker.addTo(mymap);
		}

		//initilize furniture marker
		marker = L.marker(latlng, {
			icon: selectedIcon,
			rotationAngle: degreeOffset,
						rotationOrigin: "center",
			draggable: false,
			ftype: ftype,
			numSeats: numSeats,
			fid: fid.toString()
		}).addTo(furnitureLayer);

		//if numSeats is 0 we know that the furniture marker is a room marker
		//therefor we must query the peak data for that room to properly populate the popup string
		if(numSeats === "0"){
			room_occ = avgOccupied;
			room_sum = sumOccupant;

			$.ajax({
				url: 'phpcalls/get-room-peaks.php',
				type: 'get',
				async: false,
				data:{ 'survey_ids': survey_id_json,
						'furniture_id': fid},
				success: function(data){
					jsondata = JSON.parse(data);
					popupString = "<strong>Room Average Occupancy: </strong>" 
						+ avgOccupied 
						+ "</br><strong>Total Occupants over time: </strong>" 
						+ sumOccupant
						+ "</br><strong>Peak Pop: </strong>"
						+ jsondata['room_peak']
						+ "</br><strong>Peak Date: </strong>"
						+ jsondata['room_peak_date']
						+ "</br><strong>Peak Survey ID: </strong>"
						+ jsondata['room_peak_survey'];
					
					//default oppacity for rooms is 0.5 or 1 for rooms that are occupied
					if(room_occ > 0){
						oppacity = 1;
					} else {
						oppacity = 0.5
					}
					
					var act_array = [];

					for(i in activities){
						var cur_act = activities[i];
						var act_object = new Activity(parseInt(cur_act[0]), cur_act[1]);
						if(act_array.length == 0){
							act_array.push(act_object);
						}
						else{
							if(activityExists(act_object, act_array) == false){
								act_array.push(act_object);
							}
							else{
								for(j in act_array){
									if(act_object.name == act_array[j].name){
										act_array[j].count = parseInt(act_object.count) + parseInt(act_array[j].count);
									}
								}
							}
						}
					}

					for(k in act_array){
						var cur_object = act_array[k];
						popupString += "</br>"+ cur_object.count +"X: " + cur_object.name;
					}

					marker.bindPopup(popupString);
					marker.setOpacity(oppacity);
					marker.addTo(mymap);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log("Status: " + textStatus);
					console.log("Error: " + errorThrown);
				}
			});
		}
		else{
			
			//initialize the popupString for a regular piece of furniture
			popupString = "<strong>Average Occupancy: </strong>" + avgOccupied + " of " + numSeats + "</br><strong>Total Occupants: </strong>" + sumOccupant + "</br><strong>Average Use: </strong>" + Math.round((avgUse * 100) * 100)/100 +"%";

			//set oppacity to a ratio of the seat use, minimum of 0.3 for visibility
			oppacity = 0.3 + avgOccupied;

			//add activities and their count to the popupString

			var act_array = [];

			for(i in activities){
				var cur_act = activities[i];
				var act_object = new Activity(parseInt(cur_act[0]), cur_act[1]);
				if(act_array.length == 0){
					act_array.push(act_object);
				}
				else{
					if(activityExists(act_object, act_array) == false){
						act_array.push(act_object);
					}
					else{
						for(j in act_array){
							if(act_object.name == act_array[j].name){
								act_array[j].count = parseInt(act_object.count) + parseInt(act_array[j].count);
							}
						}
					}
				}
			}

			for(k in act_array){
				var cur_object = act_array[k];
				popupString += "</br>"+ cur_object.count +"X: " + cur_object.name;
			}
			

			marker.bindPopup(popupString);
			marker.setOpacity(oppacity);
			marker.addTo(mymap);
		}
	});
}

//This function calculates the data for the area based on the furniture map
//it consolidates the furniture data based upon which area the furniture is located within
function calculateAreaData(){
	var iterateAreaMap = areaMap.values();
	floorMaxSeats = 0;
	total_floor_vistors = 0;
	avgFloorPop = 0;

	for(var i of areaMap){
		var num_surveys = survey_id_array.length;
		var cur_area = iterateAreaMap.next().value;
		var area_furn_count = 0;
		var area_ratio_sum = 0;
		var max_seats = 0;
		var total_seats_used = 0;
		var iterateFurnMap = furnMap.values();

		for(var j of furnMap){
			var cur_furn = iterateFurnMap.next().value;
			if(cur_furn != undefined){
				//check if the furniture is in the current area we are collecting data on
				if(cur_furn.inArea == cur_area.area_id){
					area_furn_count++;
					max_seats += parseInt(cur_furn.numSeats);
					area_ratio_sum += parseFloat(cur_furn.avgUseRatio);
					total_seats_used += parseInt(cur_furn.sumOccupants);
				}
				//check if a modified furniture exists for that furniture that is also within the current area
				for(var k = 0; k < cur_furn.mod_array.length; k++){
					var mod_furn = cur_furn.mod_array[k];
					if(mod_furn[2] == cur_area.area_id){
						var mod_occ = mod_furn[3];
						var mod_seats = mod_furn[4];
						total_seats_used += parseInt(mod_occ);
						area_ratio_sum += parseFloat(mod_occ/mod_seats);
						area_furn_count++;
					}
				}
			}
		}
		//calculate area data based on now consolidated furniture data
		cur_area.numSeats = max_seats;
		floorMaxSeats += cur_area.numSeats;
		cur_area.avgPopArea = total_seats_used/num_surveys;
		avgFloorPop += cur_area.avgPopArea;
		cur_area.avgRatio = area_ratio_sum/area_furn_count;
		cur_area.totalSeatsUsed = total_seats_used;
		total_floor_vistors += cur_area.totalSeatsUsed;
	}
}

//This function calculate the peak use data for each area
function calculateAreaPeaks(survey_id_json, layout_id){
	$.ajax({
		url: 'phpcalls/get-area-peaks.php',
		type: 'get',
		data:{ 'survey_ids': survey_id_json,
				'layout_id': layout_id},
		success: function(data){
			jsonpeakdata = JSON.parse(data);
			var iterateAreaMap = areaMap.values();
			for(var i of areaMap){
				var cur_area = iterateAreaMap.next().value;
				for(key in jsonpeakdata){
					var cur_peak_data = jsonpeakdata[key];
					if(cur_area.area_id == cur_peak_data['area_id']){
						cur_area.peak = cur_peak_data['peak'];
						cur_area.peakSurvey = cur_peak_data['peak_survey'];
						cur_area.peakDate = cur_peak_data['peak_date'];
					}
				}
			}
			//call addsurveyed areas to draw the areas onto the map now that all data for area's has been collected.
			addSurveyedAreas();
			//check to see if a report exists for text report printout
			//if not create one!
			if(!report_added){
				createPrintReport();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});
}

//this function draws the areas and their popups onto the map
//it also builds the area string for the text print out report
function drawArea(area){
	var curVerts = [];
	for(var i=0; i < area.verts.length; i++){
		area_verts = area.verts[i];
		curVerts.push([area_verts.x,area_verts.y]);
	}

	var poly = L.polygon(curVerts);
	popupString = "<strong>"+area.area_name +"</strong></br>Number of Seats: "
		+ area.numSeats +"</br>Average Area Population: " + Math.round((area.avgPopArea) * 100)/100
		+"</br>Percentage Use: "
		+ Math.round(((area.avgPopArea/area.numSeats) * 100) * 100)/100
		+ "%</br>Ratio of use over Period: "
		+ Math.round((area.avgRatio * 100) * 100)/100 
		+ "%</br>Peak Population: "
		+ area.peak
		+ "</br>Peak Date: "
		+ area.peakDate
		+ "</br>Peak Survey ID: "
		+ area.peakSurvey;

	poly.bindPopup(popupString);

	if(area.numSeats != 0){
		area_string += popupString + "</br></br>";
	}
	else{
		area_string += "<strong>" + area.area_name 
					+"</strong></br>Average Room Population: " 
					+ area.avgPopArea
					+ "</br>Peak Room Occupants: " + area.peak 
					+ "</br>Peak Date: " + area.peakDate 
					+ "</br>Peak Survey ID: " + area.peakSurvey
					+ "</br></br>";
	}

	//convert the varables to numbers, if not you will get unexpected results
	//https://www.w3schools.com/js/js_comparisons.asp
	var area_use = Number((area.avgPopArea/area.numSeats)*100);

	if(area_use < threshold){
		poly.setStyle({fillColor:"red"});
	}
	else{
		poly.setStyle({fillColor:"green"});
	}

	return poly;
}

//this function builds the text print out report
function createPrintReport(){
	//This is used for the printable iFrame
		var report = document.getElementById("print_frame");
		var report_header = document.createElement("H2");
		var overview_header = document.createElement("H3");
		var report_text = document.createElement("P");
		var area_header = document.createElement("H3");
		var area_data = document.createElement("P");

		report_header.style.textAlign = "center";
		overview_header.style.textDecoration = "underline";
		area_header.style.textDecoration = "underline";

		report.contentDocument.body.appendChild(report_header);
		report.contentDocument.body.appendChild(overview_header);
		report.contentDocument.body.appendChild(report_text);
		report.contentDocument.body.appendChild(area_header);
		report.contentDocument.body.appendChild(area_data);

		report_header.innerHTML = print_header;
		overview_header.innerHTML = "Survey Range Overview:";
		report_text.innerHTML = "Number of Surveys: " + survey_id_array.length
														+ "</br>Possible seats: " + floorMaxSeats
														+ "</br>Total floor vistors: " + total_floor_vistors
														+ "</br>Average floor population: " + Math.round(avgFloorPop);
		area_header.innerHTML = "Area Overview:";
		area_data.innerHTML = area_string;

		report_added = true;
		queried_info_legend = L.control({position: 'bottomright'});

		queried_info_legend.onAdd = function (mymap){
			var div = L.DomUtil.create('div', 'report_legend');
			var header = document.createElement('p');

			header.innerHTML ="Possible seats: " + floorMaxSeats +
						"</br> Total floor vistors: " + total_floor_vistors +
						"</br> Average floor population: " + Math.round(avgFloorPop);

			div.appendChild(header);

			return div;
		}

		queried_info_legend.addTo(mymap);

		addPrintMap();

}

//Adds the print map button to the map
function addPrintMap(){
	L.control.browserPrint({
		title: 'Library Query Report',
		documentTitle: 'Library Query Report',
		printLayer: L.tileLayer('http://tile.stamen.com/toner/{z}/{x}/{y}.png', {
			attribution: 'HSU Library App Team',
			minZoom: 1,
			maxZoom: 16,
			ext: 'png'
		}),
		closePopupsOnPrint: false,
		printModes: [
			L.control.browserPrint.mode.landscape()
		],
		manualMode: false
	}).addTo(mymap);

	//This adds the legends to the printable map
	mymap.on("browser-print-start", function(e){
		queried_info_legend.addTo(e.printMap);
		survey_info_legend.addTo(e.printMap);
	});

	//Readds the legends to the regular map after printing
	mymap.on("browser-print-end", function(e){
		queried_info_legend.addTo(mymap);
		survey_info_legend.addTo(mymap);
	});
}
