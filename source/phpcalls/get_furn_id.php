<?php	
    session_start();
    require_once('./../config.php');
    //get furniture types to populate dropdown for placing on map
    $dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

    $fTypeSelectStmt = $dbh->prepare("SELECT *
                                      FROM furniture_type");

    $fTypeSelectStmt->execute();

    $furn_icons = $fTypeSelectStmt->fetchAll();

    print json_encode($furn_icons);
