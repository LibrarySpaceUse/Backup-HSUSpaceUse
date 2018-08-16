<?php
	//query the peak data for all areas

	require_once('./../config.php');

	//get array of survey's
	$survey_ids = json_decode($_GET['survey_ids'], true);
	$layout_id = $_REQUEST['layout_id'];
	$peakdata = array();

	//setup connection to DB
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	//get areas from layout id
	$area_stmt = $dbh->prepare('
		SELECT area_id
		FROM area_in_layout
		WHERE layout_id = :layout_id
		');

	$area_stmt->bindParam(':layout_id', $layout_id, PDO::PARAM_INT);
	$area_stmt->execute();

	$areas = $area_stmt->fetchAll();

	foreach($areas as $row){
		//current area id obtained to query peak info for survey's
		$area_id = $row['area_id'];
		$highest_pop = 0;
		$highest_survey_id = 0;

		//look at each survey and calculate the sum of all unmodified furniture in that area for the survey
		foreach($survey_ids as $key => $value){

			$survey_id = $value["id"];
			$survey_sum = 0;

			//get all furntiture for the layout and area
			$area_furn_stmt = $dbh->prepare('
				SELECT furniture_id, number_of_seats
				FROM furniture, furniture_type
				WHERE furniture_type = furniture_type_id
				AND layout_id = :layout_id
				AND in_area = :area_id');

			$area_furn_stmt->bindParam(':layout_id', $layout_id, PDO::PARAM_INT);
			$area_furn_stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);

			$area_furn_stmt->execute();

			$area_furn = $area_furn_stmt->fetchAll();

			//iterate through all furniture in an area and calculate unmodified furniture in that area for that survey
			foreach($area_furn as $row){
				$fid = $row['furniture_id'];
				$number_of_seats = $row['number_of_seats'];
				//check if the furniture you are looking at is a room
				if($number_of_seats == 0){
					$room_info_stmt = $dbh->prepare('
						SELECT total_occupants
						FROM surveyed_room
						WHERE survey_id = :survey_id
						AND furniture_id = :furniture_id');

					$room_info_stmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);
					$room_info_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);

					$room_info_stmt->execute();

					$survey_sum = (int)$room_info_stmt->fetchColumn();

				} else {
					//query to check if a furniture in a survey is modified
					$modified_furn_check_stmt = $dbh->prepare('
						SELECT in_area
						FROM modified_furniture
						WHERE furniture_id = :furniture_id
						AND survey_id = :survey_id');

					$modified_furn_check_stmt->bindParam(':furniture_id', $fid, PDO::PARAM_INT);
					$modified_furn_check_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);

					$modified_furn_check_stmt->execute();

					$mod_area = $modified_furn_check_stmt->fetchColumn();

					//if modified furniture rests in the same area, or the furniture isn't modified, continue to compute the total peak sum of that area.
					if($mod_area == $area_id || $mod_area == FALSE){
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

						$tempsum = (int)$occupied_furn_stmt->fetchColumn();
						$survey_sum += $tempsum;
					}
				}
			}

			//now compute data for modified furniture for the same specific area and survey
			$modified_furn_stmt = $dbh->prepare('
				SELECT furniture_id
				FROM modified_furniture
				WHERE in_area = :area_id
				AND survey_id = :survey_id');

			$modified_furn_stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
			$modified_furn_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);

			$modified_furn_stmt->execute();

			$modified_furn = $modified_furn_stmt->fetchAll();

			//go through all modified furniture in the area and add their totals to the sum
			foreach ($modified_furn as $row) {
				$mfid = $row['furniture_id'];

				$occupied_mod_furn_stmt = $dbh->prepare(
					'SELECT count(*) occupied_seats
					FROM seat
					WHERE furniture_id = :furniture_id
					AND occupied = 1
					AND survey_id = :survey_id
					GROUP BY seat.furniture_id');

				$occupied_mod_furn_stmt->bindParam(':furniture_id', $mfid, PDO::PARAM_INT);
				$occupied_mod_furn_stmt->bindParam(':survey_id', $survey_id, PDO::PARAM_INT);

				$occupied_mod_furn_stmt->execute();

				$tempsum = (int)$occupied_mod_furn_stmt->fetchColumn();
				$survey_sum += $tempsum;
			}

			if($survey_sum > $highest_pop){
				$highest_pop = $survey_sum;
				$highest_survey_id = $survey_id;
			}
		}

		$peak_date = 'Null';

		//get date information for the peak data
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

		//store peak area data
		$area_peak_item = array(
			'area_id' => $area_id,
			'peak' => $highest_pop,
			'peak_survey' => $highest_survey_id,
			'peak_date' => $peak_date
		);
		array_push($peakdata, $area_peak_item);
	}

	//return data as an encoded json string
	print json_encode($peakdata);