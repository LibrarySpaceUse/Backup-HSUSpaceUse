<?php
//insert the areas in the area_in_layout table
require_once('./../config.php');

$user = $_REQUEST['user'];
$floor_num = $_REQUEST['floor_num'];
$floor_name = $_REQUEST['floor_name'];
$floor_id = $_REQUEST['floor_id'];
$date_created = date("Y-m-d h:i:sa");

//setup connection to DB
$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);
$dbh->beginTransaction();
$insert_layout_stmt = $dbh->prepare('INSERT INTO layout (layout_name, author, floor, floor_id, date_created)
                                     VALUES (:floor_name, :user, :floor_num, :floor_id, :date_created)');


$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);

$insert_layout_stmt->bindParam(':floor_name', $floor_name, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':user', $user, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':floor_num', $floor_num, PDO::PARAM_INT);
$insert_layout_stmt->bindParam(':floor_id', $floor_id, PDO::PARAM_INT);
$insert_layout_stmt->bindParam(':date_created', $date_created, PDO::PARAM_STR);

$insert_layout_stmt->execute();
$data = array('layout_id' => $dbh->lastInsertId());
$dbh->commit();


print json_encode($data);