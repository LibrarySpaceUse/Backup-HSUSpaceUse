<?php
//gets the layout ids based on floor
session_start();
require_once('./../config.php');

$get_floor =  $_REQUEST['floor'];

$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$stmt1 = $dbh->prepare("SELECT layout_id, layout_name, date_created 
						FROM layout
						WHERE floor = :in_floor");

$stmt1->bindParam(':in_floor', $get_floor, PDO::PARAM_INT);

$stmt1->execute();

$layout_result = $stmt1->fetchAll();

print json_encode($layout_result);
