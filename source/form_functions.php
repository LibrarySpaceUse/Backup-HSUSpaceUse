<?php
	/*These functions build HTML form elements used for the collection of data and navigation*/
	//navigation buttons
	function nav_form(){
		?>
			<nav class="hidden">
					<p class="nav"><a href="home.php">Home</a></p>
                    <p class="nav selected"><a href="data-collection.php">Data Collection</a></p>
                    <p class="nav"><a href="query-select.php">Query Report</a></p>
                    <p class="nav"><a href="editor.php">Layout Creator</a></p>
                    <p class="nav"><a href="create-floor.php">Floor Creator</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
	        </nav>
        <?php
	}

	//selects the years of survey records
	function get_year_options(){
		require_once('config.php');
		//get activities and populate activityMap
		$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

		$getDates = $dbh->prepare('SELECT DISTINCT YEAR(survey_date) as date_surveyed FROM survey_record');

		if($getDates->execute()){
			while($row = $getDates->fetch(PDO::FETCH_ASSOC)){
				?>
				<option value="<?= $row['date_surveyed'] ?>"><?= $row['date_surveyed'] ?> </option>
				<?php
			}
		}
	}
?>
