
//This file checks the inputs on query-range.php and when all inputs have a value populates 
//the multibox select with the proper survey data depending on inputs
$(function(){
    $('#date-select-1, #date-select-2, #in_floor_select, #in_layout_select').on("change", function(){
        document.getElementById("mapid").style.display = "none";
        document.getElementById("multi-select").style.display = "block";
        document.getElementById("submit-surveys").style.display = "block";
        report_added = false;
        areaMap.clear();
        furnMap.clear();
        
        var cur_date_start = document.getElementById("date-select-1").value;
        var cur_date_end = document.getElementById("date-select-2").value;
        var cur_layout = document.getElementById("in_layout_select");
        if(cur_date_start.value != "" && cur_date_end.value != "" && cur_layout.value != ""){
            query_layout = cur_layout.value;
            $.ajax({
                url: 'phpcalls/multibox-select.php',
                type: 'get',
                data:{ 
                    'start_date': cur_date_start,
                    'end_date': cur_date_end,
                    'layout_id' : query_layout
                },
                success: function(data){
                    //This code requires the transfer-select plugin
                    var json_object = JSON.parse(data);
                    $('#multi-select').empty();
                    var t = $('#multi-select').bootstrapTransfer(
                        {'target_id': 'multi-select-input',
                         'height': '15em',
                         'hilite_selection': true});

                    for(var i = 0; i < json_object.length; i++){
                        var obj = json_object[i];
                        var surv_id = obj['survey_id'];
                        var date = obj['survey_date'];
                        t.populate([
                            {value:surv_id, content:date}
                        ]);
                    }
                    
                }
            });
        }
    });

    //this populates the dropdown for layouts after a floor is selected from the form.
    $('#in_floor_select').on("change", function(){
        var form_info = document.getElementById("choose_period_form");
        cur_selected_floor = form_info.elements["in_floor_select"].value;
        var select = document.getElementById('in_layout_select');
        var length = select.options.length;
        if(length > 1){
            for(i = 0; i < length; i++){
                select.remove(1);
            }
        }
        $.ajax({
            url: 'phpcalls/get-layout-ids.php',
            type: 'get',
            data:{ 'floor': cur_selected_floor },
            success: function(data){

                json_object = JSON.parse(data);
                var layout_select = document.getElementById('in_layout_select');

                for(var i = 0; i < json_object.length; i++){
                    var obj = json_object[i];
                    lay_id = obj['layout_id'];
                    lay_name = obj['layout_name'];
                    lay_date = obj['date_created'];
                    var option = document.createElement('option');
                    option.value = lay_id;
                    option.innerHTML = "Layout: " + lay_name + " created " + lay_date;
                    layout_select.appendChild(option);
                }
            }
        });

    });
});