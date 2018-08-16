<?php
	//query the peak data for all rooms
	require_once('./../config.php');

	//get array of survey's
	$survey_ids = json_decode($_GET['survey_ids'], true);
	$furniture_id = $_REQUEST['furniture_id'];

	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$highest_pop = 0;
	$highest_survey_id = 0;

	foreach($survey_ids as $key => $value){

		$survey_id = $value["id"];

		$room_select_stmt = $dbh->prepare('
			SELECT total_occupants
			FROM surveyed_room
			WHERE survey_id = :survey_id
			AND furniture_id = :furniture_id');

		$room_select_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);
		$room_select_stmt->bindParam(':furniture_id', $furniture_id, PDO::PARAM_INT);

		$room_select_stmt->execute();

		$pop = (int)$room_select_stmt->fetchColumn();

		if($pop > $highest_pop){
			$highest_pop = $pop;
			$highest_survey_id = $survey_id;
		}
	}

	$peak_date = 'Null';

	if($highest_survey_id > 0){
		$peak_date_stmt = $dbh->prepare('
		SELECT survey_date
		FROM survey_record
		WHERE survey_id = :survey_id');

		$peak_date_stmt->bindParam(':survey_id', $highest_survey_id, PDO::PARAM_INT);
		$peak_date_stmt->execute();
		$peak_date = $peak_date_stmt->fetchColumn();
		$peak_date = date('l jS \of F Y h:i:s A', strtotime($peak_date));
	}

	$peak_room = array(
		'room_peak' => $highest_pop,
		'room_peak_survey' => $highest_survey_id,
		'room_peak_date' => $peak_date
	);

	print json_encode($peak_room);