<?php
//Gets the days of the survey for a single survey report based on the year and month already chosen
session_start();
require_once('./../config.php');

$get_year = $_REQUEST['selected_year'];
$get_month = $_REQUEST['selected_month'];

$day_start = $get_year."-".$get_month."-01 00::00::00";
$day_end = $get_year."-".$get_month."-31 23::59::59";

$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$stmt1 = $dbh->prepare("SELECT DISTINCT DAY(survey_date) as year FROM survey_record WHERE (survey_date BETWEEN :date_start AND :date_end)");

$stmt1->bindParam(':date_start', $day_start, PDO::PARAM_STR);
$stmt1->bindParam(':date_end', $day_end, PDO::PARAM_STR);

$stmt1->execute();

$day_result = $stmt1->fetchAll();

print json_encode($day_result);
