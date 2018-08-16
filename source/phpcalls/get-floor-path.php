<?php
//gets the layout ids based on floor
session_start();
require_once('./../config.php');

$get_floor =  $_REQUEST['floor_ID'];

$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$stmt1 = $dbh->prepare("SELECT path
						FROM floor_images
						WHERE id = :in_floor");

$stmt1->bindParam(':in_floor', $get_floor, PDO::PARAM_INT);

$stmt1->execute();

$path = $stmt1->fetch();

print json_encode($path);