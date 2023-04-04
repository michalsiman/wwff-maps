<?php
// synchro wwff from csv file to mysql db

$mysqli = new mysqli("localhost", "root", "", "csv-to-mysql");

$csvFilePath = "import-template.csv";
$file = fopen($csvFilePath, "r");
while (($row = fgetcsv($file)) !== FALSE) {
    $stmt = $mysqli->prepare("INSERT INTO tbl_users (userName, firstName, lastName) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $row[1], $row[2], $row[3]);
    $stmt->execute();
}
