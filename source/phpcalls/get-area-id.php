<?php	
    session_start();
    require_once('./../config.php');
    //get furniture types to populate dropdown for placing on map
    $dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

    $stmt = $dbh->prepare("SELECT MAX(area_id)
                           FROM area");

    $stmt->execute();

    $areaId = $stmt->fetch();

    print json_encode($areaId);