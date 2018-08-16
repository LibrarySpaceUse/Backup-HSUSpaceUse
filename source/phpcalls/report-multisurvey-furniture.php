<?php
	//query multiple survey id's and return the information
	require_once('./../config.php');

	//get array of survey's
	$survey_ids = json_decode($_GET['survey_ids'], true);
	$layout_id = $_REQUEST['layout_id'];
	$data = array();

	//setup connection to DB
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	//get all furntiture for the layout
	$all_furn_stmt = $dbh->prepare('
		SELECT furniture_id, x_location, y_location, degree_offset, furniture_type, number_of_seats, in_area
		FROM furniture, furniture_type
		WHERE furniture_type = furniture_type_id
		AND layout_id = :layout_id');
										
	$all_furn_stmt->bindParam(':layout_id', $layout_id, PDO::PARAM_INT);

	$all_furn_stmt->execute();

	$all_furn = $all_furn_stmt->fetchAll();

	//iterate all the furniture
	foreach($all_furn as $row){
		//establish current furniture we are examining
		$fid = $row['furniture_id'];
		$numSeats = $row['number_of_seats'];
		$inArea = $row['in_area'];
		$x_location = $row['x_location'];
		$y_location = $row['y_location'];
		$degree_offset = $row['degree_offset'];
		$furn_type = $row['furniture_type'];
		$ratio = 0;
		$sum_occupants = 0;
		$mod_count = 0;
		$mod_array = array();
		$activities = array();
		$num_surveys = count($survey_ids);

		foreach($survey_ids as $key => $value){
		//now look at this one piece of furniture for each survey selected
			$survey_id = $value["id"];
			$tempOcc = 0;
			$tempRatio = 0;
			if($numSeats === "0"){
				//get room occupants
				$roomOccupantStmt = $dbh->prepare("
					SELECT total_occupants 
					FROM surveyed_room
					WHERE furniture_id = :furniture_id
					AND survey_id = :survey_id");
				$roomOccupantStmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);										
				$roomOccupantStmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);
				
				$roomOccupantStmt->execute();
				//place the total occupants in the room in the variable for the furniture
				$tempOcc = (int)$roomOccupantStmt->fetchColumn();
				
			} else {
				//Since it's numSeats isn't 0, it isn't a room. Get seat information.
				//get count of occupied seats in furniture
				$occupied_furn_stmt = $dbh->prepare(
					'SELECT count(*) occupied_seats
					FROM seat
					WHERE furniture_id = :furniture_id
					AND occupied = 1
					AND survey_id = :survey_id
					GROUP BY seat.furniture_id');
												
				$occupied_furn_stmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);
				$occupied_furn_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);
				
				$occupied_furn_stmt->execute();
				
				$tempOcc = (int)$occupied_furn_stmt->fetchColumn();
				$tempRatio = $tempOcc/$numSeats;
				
				//if the column returns a number, and it is greater than 0, overwrite occupants.

			}

			$activity_stmt = $dbh->prepare(
				'SELECT COUNT(activity_description), activity_description 
				FROM activity, seat_has_activity, seat
				WHERE furniture_id = :furniture_id
				AND seat.seat_id = seat_has_activity.seat_id
				AND seat_has_activity.activity_id = activity.activity_id
				AND survey_id = :survey_id
				GROUP BY activity_description');
												
			$activity_stmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);
			$activity_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);
			
			$activity_stmt->execute();
			
			$activitiesQuery = $activity_stmt->fetchAll();

			foreach($activitiesQuery as $row){
				array_push($activities, array($row['COUNT(activity_description)'],$row['activity_description']));
			}

			//get modified furniture where it exists
			$mod_furn_stmt = $dbh->prepare(
				'SELECT *
				FROM modified_furniture
				WHERE furniture_id = :furniture_id
				AND survey_id = :survey_id');
												
			$mod_furn_stmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);
			$mod_furn_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);
			
			$mod_furn_stmt->execute();
			
			$mod_furn = $mod_furn_stmt->fetch(PDO::FETCH_BOTH);
			
			if($mod_furn_stmt->rowCount() > 0){
				$mod_count++;
				$mod_occupancy = $tempOcc;
				$mod_seats = $numSeats;
				$new_x = $mod_furn['new_x'];
				$new_y = $mod_furn['new_y'];
				$new_area = $mod_furn['in_area'];
				array_push($mod_array, array($new_x, $new_y, $new_area, $mod_occupancy, $mod_seats));
			} else {
				if($tempOcc > 0){
					$ratio += $tempRatio;
					$sum_occupants += $tempOcc;
				}
			}
		}

		$average_use_ratio = $ratio/$num_surveys;
		$average_occupancy = $sum_occupants/$num_surveys;

		$array_item = array( 
			'furniture_id' => $fid,
			'num_seats' => $numSeats,
			'x' => $x_location,
			'y' => $y_location,
			'degree_offset' => $degree_offset,
			'furn_type' => $furn_type,
			'in_area' => $inArea,
			'avg_use_ratio' => $average_use_ratio,
			'sum_occupants' => $sum_occupants,
			'avg_occupancy' => $average_occupancy,
			'modified_count' => $mod_count,
			'mod_array' => $mod_array,
			'activities' => $activities
		);
		array_push($data, $array_item);
	}
	print json_encode($data);
