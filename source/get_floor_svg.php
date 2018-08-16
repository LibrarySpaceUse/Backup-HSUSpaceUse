<?php
	function get_floor_svg(){
        require_once('config.php');
		//get activities and populate activityMap
		$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

		$getFloor = $dbh->prepare('SELECT id, name FROM floor_images');

		if($getFloor->execute()){
			while($row = $getFloor->fetch(PDO::FETCH_ASSOC)){
				?>
				<option value="<?= $row['id']?>"><?= $row['name']?> </option>
				<?php
			}
		}
	}
?>
