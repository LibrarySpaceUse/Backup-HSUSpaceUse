<?php
//inserts a new layout into the layout record
require_once('./../config.php');
$username = $_REQUEST['username'];
$layout_name = $_REQUEST['layout_name'];
$floor = $_REQUEST['floor'];
$date_created = date("Y-m-d h:i:sa");

$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);
$dbh->beginTransaction();
$insert_layout_stmt = $dbh->prepare('
	INSERT INTO layout (layout_name, author, floor, date_created)
    VALUES (:layout_name, :username, :floor, :date_created)');


$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);

$insert_layout_stmt->bindParam(':layout_name', $layout_name, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':username', $username, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':floor', $floor, PDO::PARAM_INT);
$insert_layout_stmt->bindParam(':date_created', $date_created, PDO::PARAM_STR);

$insert_layout_stmt->execute();
$data = array('layout_id' => $dbh->lastInsertId());
$dbh->commit();

print json_encode($data);