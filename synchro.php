<?php
// synchro wwff from csv file to mysql db

//ini_set('display_errors', 1);
ini_set('user_agent', 'My-Application/2.5');
ini_set('memory_limit', '1024M'); // or you could use 1G

function download_file($url, $path) {

    $newfilename = $path;
    $file = fopen ($url, "rb");
    if ($file) {
      $newfile = fopen ($newfilename, "wb");
  
      if ($newfile)
      while(!feof($file)) {
        fwrite($newfile, fread($file, 1024 * 8 ), 1024 * 8 );
      }
    }
  
    if ($file) {
      fclose($file);
    }
    if ($newfile) {
      fclose($newfile);
    }
}

function csv_to_array($file_name) {
    $data =  $header = array();
    $i = 0;
    $file = fopen($file_name, 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        if( $i==0 ) {
            $header = $line;
        } else {
            $data[] = $line;        
        }
        $i++;
    }
    fclose($file);
    foreach ($data as $key => $_value) {
        $new_item = array();
        foreach ($_value as $key => $value) {
            $new_item[ $header[$key] ] =$value;
        }
        $_data[] = $new_item;
    }
    return $_data;
}

// locale storage of file with wwff directory
$datovy_soubor_cesta = dirname(__FILE__) ."/data_files/wwff_directory.csv";

// download actual .csv file from wwff.co
download_file("https://wwff.co/wwff-data/wwff_directory.csv", $datovy_soubor_cesta);

// fill array from local .csv file
$csv = csv_to_array($datovy_soubor_cesta);

$mysqli = new mysqli("localhost", "wwff", "FLORAifauna:2020", "wwff_maps");

$query = "truncate wwff_area";
$result = $mysqli->query($query);
echo $result;

$pocet = 0;

foreach ($csv as $arr) {
    if($arr['lastAct']=="") $arr['lastAct']="1980-01-01";
    $query = "INSERT INTO wwff_area (reference, status, name, program, dxcc, latitude, longitude, qsoCount, lastAct)".
             " VALUES ('".$arr['reference']."','".$arr['status']."','".htmlspecialchars($arr['name'])."','".$arr['program']."','".$arr['dxcc']."','".$arr['latitude']."','".$arr['longitude']."',".intval($arr['qsoCount']).",'".$arr['lastAct']."');";
    $result = $mysqli->query($query);
    $pocet++;
}

echo "$pocet";

$mysqli->close();