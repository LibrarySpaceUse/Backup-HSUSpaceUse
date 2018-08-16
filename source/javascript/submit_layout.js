/*This function is called when the layout is submitted and it populates the database with the appropriate furniture objects*/
function submitLayout(username, layout_name, floor, furnMap, areaMap){
    
    $(".loading").addClass("loadingapply");
    $("#load-image").addClass("imagerotate");
    $.ajax({
        url: 'phpcalls/create-layout.php',
        type: 'get',
        data:{
            'username': username,
            'layout_name': layout_name,
            'floor': floor
        },
        success: function(data){
            /*Get that new layout ID for insertion statements*/
            var json_object = JSON.parse(data);
            layout_id = json_object.layout_id;
			
			//prep furnMap to insert
            var objmap = mapToObj(furnMap);
            var json_string = JSON.stringify(objmap);
			
            $.ajax({
				url: 'phpcalls/insert-furniture.php',
				type: 'post',
				data:{
					'layout_id': layout_id,
					'to_json': json_string
				},
				success: function(data){
					var used_json = data;
					if(data > 0){
						/*now insert areas to area_in_layout*/
						var aobjmap = mapToObj(areaMap);
						//Remove Poly from obj
						for(var z in aobjmap){
							var cur_obj = aobjmap[z];
							delete cur_obj.polyArea;
						}
			            var ajson_string = JSON.stringify(aobjmap);
						$.ajax({
							url: 'phpcalls/insert-area-in-layout.php',
							type: 'post',
							data:{
								'layout_id': layout_id,
								'to_json': ajson_string
							},
							success: function(data){
								var used_json = data;
								if(data > 0){
									window.location.href = 'layout-success.php';
								} else {
									alert("Something went wrong.");
									console.log(data);
								}
							}
						});
					} else {
						alert("Something went wrong.");
						console.log(data);
					}
				}
			});
        }, 
        error: function(xhr, status, error) {
		  console.log(xhr.responseText);
		}
    });
}

//takes a furnMap and returns it as an object array
function mapToObj(inputMap) {
    var obj = {};
    inputMap.forEach(function(value, key){
        obj[key] = value
    });

    return obj;
}

