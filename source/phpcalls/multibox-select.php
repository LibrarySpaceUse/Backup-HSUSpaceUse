<?php
//This file queries all survey's related to startdate, enddate, and layout id 
//Returns an array of id's and dates for the user to select
	require_once('./../config.php');

	$start_date = $_REQUEST['start_date'];
	$end_date = $_REQUEST['end_date'];
	$lay_id = $_REQUEST['layout_id'];
	//get activities and populate activityMap
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$getSurveys = $dbh->prepare('
		SELECT survey_id, survey_date
		FROM survey_record
		JOIN layout ON survey_record.layout_id = layout.layout_id
		WHERE survey_date > :start_date
		AND survey_date < :end_date
		AND layout.layout_id = :layout');

	$getSurveys->bindParam(':start_date', $start_date, PDO::PARAM_STR);
	$getSurveys->bindParam(':end_date', $end_date, PDO::PARAM_STR);
	$getSurveys->bindParam(':layout', $lay_id, PDO::PARAM_INT);

    $getSurveys->execute();
	
	$get_surveys_result = $getSurveys->fetchAll();

	$data = array();

	foreach ($get_surveys_result as $row) {
		$survey_id = $row['survey_id'];
		$survey_date = $row['survey_date'];
		$survey_day = date('l', strtotime($survey_date));
		$combi_date = $survey_date.' '.$survey_day;

		$data_item = array(
			'survey_id' => $survey_id,
			'survey_date' => $combi_date
		);
		array_push($data, $data_item);
	}



	print json_encode($data);