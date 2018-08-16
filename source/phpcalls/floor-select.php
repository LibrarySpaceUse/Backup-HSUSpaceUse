<?php
	//gets layout_id's from  layout for a floor
	session_start();
	require_once('./../config.php');

	$floor_ID =  $_REQUEST['floor_ID'];

	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$stmt1 = $dbh->prepare("SELECT floor_num 
							FROM floor_images 
							where id = :floor");

	$stmt1->bindParam(':floor', $floor_ID, PDO::PARAM_INT);

	$stmt1->execute();

	$floor_num = $stmt1->fetch();

	//echo "<script>console.log( 'Debug Objects: " . $floor_num[0] . "' );</script>";

	$stmt2 = $dbh->prepare("SELECT layout_id, layout_name 
							FROM layout 
							where floor = :floor");

	/*statment for after layout is selected*/
	$stmt2->bindParam(':floor', $floor_num[0], PDO::PARAM_INT);

	$stmt2->execute();

	$floor_result = $stmt2->fetchAll();

	print json_encode($floor_result);