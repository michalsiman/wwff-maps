<?php

// --------------------------------------------------------------------
// gpx file download generator
//
// required parameters:
//
// program = program code (like "OKFF" or "DLFF" ...)
//
// type = all - all area
//        awitha - only activated area
//        awouta - only NON-activated area
//
// --------------------------------------------------------------------

require '../settings/db_credentials.php';
$mysqli = new mysqli($host, $user, $pass, $db, $port);   // mysql db credentials
$mysqli->set_charset($charset);

$program = htmlspecialchars($_GET['program']);
$type = htmlspecialchars($_GET['type']);

header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: application/gpx+xml");
header("Content-Disposition: attachment; filename=\"".$program."-".$type."-".gmdate("YmdHi").".gpx\"");

echo '<?xml version="1.0" encoding="windows-1250" standalone="no" ?>'.PHP_EOL;
echo '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="GPS Data Team ( http://www.gps-data-team.com )">'.PHP_EOL;

if ($type=="all") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>''";
if ($type=="awitha") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>'' AND qsoCount>0";
if ($type=="awouta") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>'' AND qsoCount=0";
$result = $mysqli->query($query);
//$counter = mysqli_num_rows($result);
while($row = $result->fetch_assoc()) {
    echo '<wpt lon="'.$row["longitude"].'" lat="'.$row["latitude"].'"><name>'.$row["reference"].' '.$row["name"].' ('.$row["qsoCount"].'/'.$row["lastAct"].')</name></wpt>'.PHP_EOL;
  }

echo '</gpx>'.PHP_EOL;

$mysqli->close();
