<?php
// synchro wwff from csv file to mysql db
// non-working - just as template for re-work
//
// define db:
// reference
// status
// name
// dxcc
// latitude
// logntitude
// qsoCount
// lastAct
//
//

$mysqli = new mysqli("localhost", "user", "pass", "db_name");

$csvFilePath = "import-template.csv";
$file = fopen($csvFilePath, "r");
while (($row = fgetcsv($file)) !== FALSE) {
    $stmt = $mysqli->prepare("INSERT INTO map_points (userName, firstName, lastName) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $row[1], $row[2], $row[3]);
    $stmt->execute();
}
