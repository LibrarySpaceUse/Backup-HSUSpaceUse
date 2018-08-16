$(function(){
    $('#floor_dropdown').on("change", function(){
        $.ajax({
            url: 'phpcalls/get_furn_id.php',
            type: 'get',
            data:{},
            success: function(data){

                //console.log("got dates");
                json_object = JSON.parse(data);
                var furn_dropdown = document.getElementById('furn_icons');

                for(var i = 0; i < json_object.length; i++){
                    var obj = json_object[i];

                    var option = document.createElement('option');
                    option.value = obj[0];
                    option.innerHTML = obj[1];
                    furn_dropdown.appendChild(option);
                }
            }
        });

    });
});