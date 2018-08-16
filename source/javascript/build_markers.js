//This function is called when the layout is set to properly build all of the furniture objects within the map
function build_markers(layout_id){
	$.ajax({
        url: 'phpcalls/get-furn.php',
        type: 'post',
        data:{ 'layout_id': layout_id },
        success: function(data){
            var json_object = JSON.parse(data);
            for(var i in json_object){
            	var keystring = json_object[i];
				var furn_id;
				//get the value of the furniture id
            	for(var i in keystring){
					furn_id = i;
				}

                //intilize variables from furniture id
            	var num_seats = parseInt(keystring[furn_id].num_seats);
                var newFurniture = new Furniture( furn_id, num_seats);
            	var x = keystring[furn_id].x;
            	var y = keystring[furn_id].y;
            	var degree_offset = keystring[furn_id].degree_offset;
            	var furniture_type = keystring[furn_id].furn_type;
            	var default_seat_type = keystring[furn_id].default_seat_type;
            	var latlng = [y,x];
                newFurniture.degreeOffset = degree_offset;
				area_id="TBD";
				newFurniture.y = y;
				newFurniture.x = x;

				//parse the furniture type to an int, then get the right icon for it.
				var type = parseInt(furniture_type);
				var selectedIcon = getIconObj(type);

                //place a marker for each furniture item
                marker = L.marker(latlng, {
                    icon: selectedIcon,
                    rotationAngle: degree_offset,
				            	rotationOrigin: "center",
                    draggable: false,
                    ftype: furniture_type,
                    numSeats: num_seats,
                    fid: furn_id.toString()
                }).addTo(furnitureLayer).bindPopup(popup, popupDim);

                //make marker clickable
                marker.on('click', markerClick);
				marker.setOpacity(.3);
					
				//update marker coords when a user stops dragging the marker, set to furniture object to indicate modified
				marker.on("dragend", function(e){
					selected_furn.modified = true;
					latlng =  e.target.getLatLng();

                    selected_furn.latlng = latlng;
                    y = latlng.lat;
                    x = latlng.lng;
                    area_id="TBD";
                    selected_furn.y = y;
                    selected_furn.x = x;
                    areaMap.forEach(function(jtem, key, mapObj){
                        
                        if(isMarkerInsidePolygon(y,x, jtem.polyArea)){
                            area_id = jtem.area_id;
                        }
                    });
                    if(area_id !== "TBD"){
                        selected_furn.in_area = area_id;
                    }
				});
				furnMap.set(furn_id.toString(), newFurniture);
            }
        }
    });

}