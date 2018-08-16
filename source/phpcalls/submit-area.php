<?php
//insert the areas in the area_in_layout table
require_once('./../config.php');

$areaName = $_REQUEST['areaName'];
//$areaId = $_REQUEST['areaId'];


//setup connection to DB
$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);
$dbh->beginTransaction();

$stmt1 = $dbh->prepare('INSERT INTO area (name, facilities_id) 
                        VALUES (:areaName, "TEMP 101")');

$stmt1->bindParam(':areaName', $areaName, PDO::PARAM_STR);

$stmt1->execute();
$dbh->commit();


