
//places activities in an activityMap
$(window).on("load", function(){
	$.ajax({
        url: 'phpcalls/get-activities.php',
        type: 'get',
        data:{},
        success: function(data){

            var json_object = JSON.parse(data);
            for(var i = 0; i < json_object.length; i++){
            	var obj = json_object[i];
            	if(obj['wb_activity'] == 0)
            	{
            		var activity_id = obj['activity_id'];
                	var description = obj['activity_description'];
                	activityMap.set(activity_id, description);
                }
                
                else
                {
                	var activity_id = obj['activity_id'];
                	var description = obj['activity_description'];
                	wb_activityMap.set(activity_id, description);
                }
                
                
            }
        }
    });
});