<?php
//get's the months of potential survey's based on year selected
session_start();
require_once('./../config.php');

$get_year = $_REQUEST['selected_year'];

$year_start = $get_year."-01-01 00::00::00";
$year_end = $get_year."-12-31 23::59::59";

$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$stmt1 = $dbh->prepare("SELECT DISTINCT MONTH(survey_date) as year FROM survey_record WHERE (survey_date BETWEEN :date_start AND :date_end)");

$stmt1->bindParam(':date_start', $year_start, PDO::PARAM_STR);
$stmt1->bindParam(':date_end', $year_end, PDO::PARAM_STR);

$stmt1->execute();

$month_result = $stmt1->fetchAll();

print json_encode($month_result);
