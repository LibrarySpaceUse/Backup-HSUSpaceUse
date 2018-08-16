<?php
//insert the areas in the area_in_layout table
//echo "<script>console.log( 'Debug Objects: " . $vert . "' );</script>";
require_once('./../config.php');

$vert = json_decode($_POST['verts'], true);
$layout = $_REQUEST['layout_id'];
$tempId = -100;
$load_order = 0;

//setup connection to DB
$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);

foreach($vert as $key => $value){
	//get basic furniture data
	
	$x = $value['x'];
    $y = $value['y'];
	$areaName = $value['areaName'];
    $areaId = $value['areaId'];
    
    if($tempId != $areaId){
        $tempId = $areaId;
        $load_order = 0;
    }

    else{
        $load_order++;
    }
	
	$dbh->beginTransaction();
	$insert_vert_stmt = $dbh->prepare("INSERT INTO area_vertices (area_id, v_y, v_x, load_order)
                                       VALUES (:areaId, :y , :x , :order)");
	$insert_vert_stmt->bindParam(':x', $x, PDO::PARAM_INT);
	$insert_vert_stmt->bindParam(':y', $y, PDO::PARAM_INT);
	$insert_vert_stmt->bindParam(':areaId', $areaId, PDO::PARAM_INT);
	$insert_vert_stmt->bindParam(':order', $load_order, PDO::PARAM_INT);

    $insert_vert_stmt->execute();

    $dbh->commit();
    
    $dbh->beginTransaction();
    $insert_area_in_layout = $dbh->prepare("INSERT INTO area_in_layout (area_id, layout_id)
                                            VALUES (:areaId, :layout)");
    
    $insert_area_in_layout->bindParam(':areaId', $areaId, PDO::PARAM_INT);
    $insert_area_in_layout->bindParam(':layout', $layout, PDO::PARAM_INT);    

    $insert_area_in_layout->execute();

	$dbh->commit();

}