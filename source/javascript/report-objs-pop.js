//Populates the dropdown menus for selecting a survey
//Creates the map objects
//Gets record information and sends queries for furniture and area which call populates to their maps.
var report_added = false;
var cur_selected_date;
var cur_year;
var cur_month;
var cur_day;
var json_object;
var survey_info_legend = L.control();
var threshold_prompt = true;

//define objects
function Area(area_id, verts, area_name){
	this.area_id = area_id;
	this.verts = verts;
	this.area_name = area_name;
	this.occupants = 0;
	this.seats = 0;
}

function Verts(x, y, order){
	this.x = x;
	this.y = y;
	this.order = order;
}

function Furniture(fid, numSeats, x, y, degreeOffset, ftype, inArea, occupants, activities){
	this.fid = fid;
	this.numSeats = numSeats;
	this.x = x;
	this.y = y;
	this.degreeOffset = degreeOffset;
	this.ftype = ftype;
	this.inArea = inArea;
	this.occupants = occupants;
	this.activities = activities;
	totalSeats = +numSeats + +totalSeats;
	seatsUsed = +occupants + +seatsUsed;
}

function Activity(count, name){
	this.count = count;
	this.name = name;
}

$(function(){
$('#year-select').on("change", function(){
  var form_info = document.getElementById("choose_survey_form");
  var month_select = document.getElementById("month-select");
  var day_select = document.getElementById("day-select");
  var id_select = document.getElementById("survey-id-select");
  var survey_submit = document.getElementById("query_submit_button");
  month_select.style.display = "inline";
  day_select.style.display = "none";
  id_select.style.display = "none";
  survey_submit.style.display = "none";
  cur_year = form_info.elements["year-select"].value;

  //Get rid previous select options before repopulating
  var length = month_select.options.length;
  if(length > 1){
      for(i = 0; i < length; i++){
          month_select.remove(1);
      }
  }

  $.ajax({
            url: 'phpcalls/get-survey-months.php',
            type: 'get',
            data:{ 'selected_year': cur_year },
            success: function(data){
                json_object = JSON.parse(data);
                var month_select = document.getElementById('month-select');
      			var month_name;

                for(var i = 0; i < json_object.length; i++){
                    var obj = json_object[i];
        			var month = obj[0];

				    switch(month){
				      case "1":
				        month_name = "January";
				        break;

				      case "2":
				        month_name = "February";
				        break;

				      case "3":
				        month_name = "March";
				        break;

				      case "4":
				        month_name = "April";
				        break;

				      case "5":
				        month_name = "May";
				        break;

				      case "6":
				        month_name = "June";
				        break;

				      case "7":
				        month_name = "July";
				        break;

				      case "8":
				        month_name = "August";
				        break;

				      case "9":
				        month_name = "September";
				        break;

				      case "10":
				        month_name = "October";
				        break;

				      case "11":
				        month_name = "November";
				        break;

				      defualt:
				        month_name = "December";
				        break;
				    }

                    var option = document.createElement('option');
                    option.value = obj[0];
                    option.innerHTML = month_name;
                    month_select.appendChild(option);
                }
            }
       });
});

$('#month-select').on("change", function(){
  var form_info = document.getElementById("choose_survey_form");
  var day_select = document.getElementById("day-select");
  var id_select = document.getElementById("survey-id-select");
  var survey_submit = document.getElementById("query_submit_button");
  day_select.style.display = "inline";
  id_select.style.display = "none";
  survey_submit.style.display = "none";
  cur_month = form_info.elements["month-select"].value;

  //Get rid previous select options before repopulating
  var length = day_select.options.length;
  if(length > 1){
      for(i = 0; i < length; i++){
          day_select.remove(1);
      }
  }

  $.ajax({
            url: 'phpcalls/get-survey-days.php',
            type: 'get',
            data:{ 'selected_year': cur_year,
                   'selected_month': cur_month},
            success: function(data){

                //console.log("got dates");
                json_object = JSON.parse(data);
                var day_select = document.getElementById('day-select');

                for(var i = 0; i < json_object.length; i++){
                    var obj = json_object[i];

                    var option = document.createElement('option');
                    option.value = obj[0];
                    option.innerHTML = obj[0];
                    day_select.appendChild(option);
                }
            }
        });
});

$('#day-select').on("change", function(){
  var form_info = document.getElementById("choose_survey_form");
  var id_select = document.getElementById("survey-id-select");
  var survey_submit = document.getElementById("query_submit_button");
  id_select.style.display = "inline";
  survey_submit.style.display = "none";
  cur_day = form_info.elements["day-select"].value;

  cur_selected_date = cur_year + '-' + cur_month + '-' + cur_day;

  //Get rid previous select options before repopulating
  var select = document.getElementById('survey-id-select');
  var length = select.options.length;
  if(length > 1){
      for(i = 0; i < length; i++){
          select.remove(1);
      }
  }

  $.ajax({
            url: 'phpcalls/get-survey-ids.php',
            type: 'get',
            data:{ 'selected_date': cur_selected_date },
            success: function(data){
                json_object = JSON.parse(data);
				var survey_select = document.getElementById('survey-id-select');
				//var thresold_input = document.getElementById('threshold');

                for(var i = 0; i < json_object.length; i++){
                    var obj = json_object[i];
                    surv_id = obj['survey_id'];
                    lay_id = obj['layout_id'];
                    lay_name = obj['layout_name'];
					floor_num = obj['floor_num'];
					surv_date_time = obj['survey_date'];
					surv_date_time_arr = surv_date_time.split(" ");
					surv_time = surv_date_time_arr[1];
                    var option = document.createElement('option');
                    option.value = surv_id;
                    option.innerHTML = "Survey: " + surv_id + " for Layout: " + lay_name + " on floor " + floor_num + " at " + surv_time;
                    survey_select.appendChild(option);
				}
				//thresold_input.style.display = "inline";
            }
        });
	});
});

$(document).on("change", '#survey-id-select', function(){
	var survey_submit = document.getElementById("query_submit_button");
	var thresold_input = document.getElementById("threshold");
	survey_submit.style.display = "inline";
	thresold_input.style.display = "inline";
});

function printReport(){
var rFrame = document.getElementById('print_frame');
rFrame.contentWindow.print();
}

function make_map(survey_id){
  document.getElementById("query_print_button").style.display = "inline";

  	$.ajax({
	    url: 'phpcalls/get-survey-info.php',
	    type: 'get',
	    data:{ 'survey_id': survey_id},
	    success: function(data){
	        json_id = JSON.parse(data);
	        lay_id = json_id[0].layout_id;
	        lay_name = json_id[0].layout_name;
		    floor_num = json_id[0].floor_num;
		    surv_date_time = json_id[0].survey_date;
		    surv_date_time_arr = surv_date_time.split(" ");
		    surv_time = surv_date_time_arr[1];
		    surv_date = surv_date_time_arr[0];
	        var query_header = document.getElementById('query_header');
	      	query_header.style.display = "none";

	        survey_info_legend.onAdd = function (mymap){
		        var div = L.DomUtil.create('div', 'report_legend');
		        var header = document.createElement('p');
		        header.innerHTML = "Survey: " + survey_id + "</br>Layout: "
		          + lay_name + "</br>Floor: " + floor_num
		          + "</br> Time: " + surv_time + "</br> Date: " + surv_date;
		        print_header = "Survey " + survey_id + " for Layout "
		          + lay_name + " on Floor " + floor_num
		          + " at " + surv_time + " on " + surv_date;
		        div.appendChild(header);
		        return div;
			}
			survey_info_legend.addTo(mymap);
	    }
  	});
  areaMap = new Map();
  furnMap = new Map();

  //popuate furnMap and areaMap then place on map
  populateObjs(survey_id);




  mymap.fitBounds(bounds);


  //On zoomend, resize the marker icons
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

  mymap.on("browser-print-start", function(e){
    /*on print start we already have a print map and we can create new control and add it to the print map to be able to print custom information */
    survey_info_legend.addTo(e.printMap);
  });

  mymap.on("browser-print-end", function(e){
    survey_info_legend.addTo(mymap);
  });
}

//Populates Objects on the map
function populateObjs(survey_id){
	$.ajax({
		url: 'phpcalls/record-from-survey.php',
		type: 'get',
		data:{ 'survey_id': survey_id },
		success: function(data){
			jsondata = JSON.parse(data);
			queryArea(jsondata.layout);
			loadMap(parseInt(jsondata.floor));
			queryFurniture(survey_id,jsondata.layout);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});
}

//query for furniture data
function queryFurniture(survey_id, layout_id){
	$.ajax({
		url: 'phpcalls/report-furniture-layout.php',
		type: 'get',
		data:{ 'survey_id': survey_id,
				'layout_id': layout_id},
		success: function(data){
			jsondata = JSON.parse(data);
			popFurnMap(jsondata);

		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});

}

//query for area data
function queryArea(layout_id){
	$.ajax({
		url: 'phpcalls/area-from-survey.php',
		type: 'get',
		data:{ 'layout_id': layout_id },
		success: function(data){
			jsondata = JSON.parse(data);
			popAreaMap(jsondata);

		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
		}
	});
}

//populate the furnMap
function popFurnMap(jsonFurniture){
	for(key in jsonFurniture){
		//retrieve furniture
		furn = jsonFurniture[key];

		//get activities for this furniture
		activities = [];
		jsonActivities = furn.activities;
		for(iter in jsonActivities){
			curAct = jsonActivities[iter];
			actCount = curAct[0];
			actName = curAct[1];
			tempAct = new Activity(actCount, actName);
			activities.push(tempAct);
		}
		cur_furn = new Furniture(furn.furniture_id,furn.num_seats, furn.x, furn.y, furn.degree_offset, furn.furn_type, furn.in_area, furn.occupants, activities);
		//if the original x&y are not equal to x&y, this furniture is modified
		if(furn.y != furn.original_y || furn.x != furn.original_x){
			cur_furn.modified = true;
			cur_furn.original_x = furn.original_x;
			cur_furn.original_y = furn.original_y;
			modified_furn++;
		}

		furnMap.set(furn.furniture_id, cur_furn);
	}
	addSurveyedFurniture();
	addSurveyedAreas();

	var queried_info_legend = L.control({position: 'bottomright'});

	queried_info_legend.onAdd = function (mymap){
		var div = L.DomUtil.create('div', 'report_legend');
		var header = document.createElement('p');
		var floor_percent = seatsUsed/totalSeats;
		floor_percent *= 100;
		floor_percent = floor_percent.toFixed(2);

		header.innerHTML ="Seats in use: " + seatsUsed +
					"</br>Possible seats: " + totalSeats +
					"</br> Floor % Use: " + floor_percent + "%";

		div.appendChild(header);

		//This is used for the printabe iFrame
		if(!report_added){
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
			overview_header.innerHTML = "Floor Overview:";
			report_text.innerHTML ="Seats in use: " + seatsUsed +
						"</br>Possible seats: " + totalSeats +
						"</br> Floor % Use: " + floor_percent + "%";
			area_header.innerHTML = "Area Overview:";
			area_data.innerHTML = area_string;

			if(modified_furn > 0){
				var mod_furn_header = document.createElement("H3");
				report.contentDocument.body.appendChild(mod_furn_header);
				mod_furn_header.style.textDecoration = "underline";
				mod_furn_header.innerHTML = "Modified Furniture: " + modified_furn;
			}

			report_added = true;
		}

		return div;
	}

	queried_info_legend.addTo(mymap);
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
			L.control.browserPrint.mode.landscape(),
			"Portrait"
		],
		manualMode: false
	}).addTo(mymap);

	//This adds the legends to the printable map
	mymap.on("browser-print-start", function(e){
		queried_info_legend.addTo(e.printMap);
	});

	//Readds the legends to the regular map after printing
	mymap.on("browser-print-end", function(e){
		queried_info_legend.addTo(mymap);
	});
}

//populate the areaMap
function popAreaMap(jsonAreas){
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

//place floor map
function loadMap(floor_ID){
	$.ajax({
		url: 'phpcalls/get-floor-path.php',
		type: 'get',
		async: false,
		data:{ 'floor_ID': floor_ID },
		success: function(data){
			var json_object = JSON.parse(data);
			floor_path = json_object;
			image = L.imageOverlay(floor_path[0], bounds).addTo(mymap);
		}
	});
}

//iterate through furnMap adding all furniture to mymap
function addSurveyedFurniture(){
	furnMap.forEach(function(key, value, map){

		latlng = [key.y, key.x];
		ftype = parseInt(key.ftype)
		selectedIcon = getIconObj(ftype);
		degreeOffset = parseInt(key.degreeOffset);
		numSeats = key.numSeats;
		occupied = key.occupants;
		fid = key.fid;
		areaId = key.inArea;
		activities = key.activities;

		//add occupants & numseats to area totals
		curArea = areaMap.get(areaId);
		curArea.occupants += parseInt(occupied);
		curArea.seats += parseInt(numSeats);

		//if this furniture is modified, draw a line from its original latlng
		if(key.modified){
			originalLatLng = [key.original_y, key.original_x];
			pointList = [latlng, originalLatLng];

			polyline = new L.polyline(pointList, {
				color: 'red',
				weigth: 3,
				opacity: 0.5,
				smoothFactor: 1
			});

			polyline.addTo(mymap);
		}

		marker = L.marker(latlng, {
			icon: selectedIcon,
			rotationAngle: degreeOffset,
						rotationOrigin: "center",
			draggable: false,
			ftype: ftype,
			numSeats: numSeats,
			fid: fid.toString()
		}).addTo(furnitureLayer);
		//initialize the popupString for a regular piece of furniture
		popupString = "Seats occupied: " + occupied + " of " + numSeats;

		//set oppacity to a ratio of the seat use, minimum of 0.3 for visibility
		oppacity = 0.3 + occupied/numSeats;
		//default oppacity for rooms is 0.5 or 1 for rooms that are occupied
		if(numSeats === "0"){
			popupString = "Room occupants: " + occupied;
			if(occupied > 0){
				oppacity = 1;
			} else {
				oppacity = 0.5
			}
		}
		//add activities and their count to the popupString
		for(i in activities){
			popupString += "</br>"+activities[i].count +"X: " + activities[i].name;
		}

		marker.bindPopup(popupString);
		marker.setOpacity(oppacity);
		marker.addTo(mymap);
	});
}

//iterate through areaMap adding areas
function addSurveyedAreas(){
	areaMap.forEach(function(key, value, map){
		drawArea(key).addTo(mymap);
	});
}

//draw surveyed areas
function drawArea(area){
	var curVerts = [];
	var report = document.getElementById("print_frame");

	for(var i=0; i < area.verts.length; i++){
		area_verts = area.verts[i];
		curVerts.push([area_verts.x,area_verts.y]);
	}

	var form_info = document.getElementById("choose_survey_form");
	threshold = form_info.elements["threshold"].value;
	var poly = L.polygon(curVerts);
	var use_percent = area.occupants/area.seats;
	use_percent *= 100;
	use_percent = use_percent.toFixed(2);
	popupString = "<strong>"+area.area_name +"</strong></br>Average use: " + area.occupants + " of " + area.seats + " or " + use_percent + "%";
	
	//convert the varables to numbers, if not you will get unexpected results
	//https://www.w3schools.com/js/js_comparisons.asp
	threshold = Number(threshold);
	use_percent = Number(use_percent);
	if (use_percent > threshold)
	{
		popupString = popupString.fontcolor("green");
		poly.setStyle({fillColor: 'green'});
	}

	else
	{
		popupString = popupString.fontcolor("red");
		poly.setStyle({fillColor: 'red'});
	}

	poly.bindPopup(popupString);

	if(area.seats === 0){
		area_string += "<strong>" + area.area_name + "</strong></br>Room use: " + area.occupants + "</br></br>";
	}

	else if(typeof area_string == 'undefined'){
		area_string = "<strong>" + area.area_name + "</strong></br>Average use: " + area.occupants + " of " + area.seats + " or " + use_percent + "%</br></br>";
	}

	else{
		area_string += "<strong>" + area.area_name + "</strong></br>Average use: " + area.occupants + " of " + area.seats + " or " + use_percent + "%</br></br>";
	}

	return poly;
}
