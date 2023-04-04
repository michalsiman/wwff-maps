<?php

//ini_set('display_errors', 1);
ini_set('user_agent', 'My-Application/2.5');
ini_set('memory_limit', '1024M'); // or you could use 1G

$dxcc = strtoupper(htmlspecialchars($_POST['dxcc']));
$area = htmlspecialchars($_POST['area']);

$datovy_soubor_cesta = dirname(__FILE__) ."/data_files/wwff_directory.csv";

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



// stazeni aktualniho .csv souboru z wwff.co stranek (generuje se tam vzdy jedno denne)
download_file("https://wwff.co/wwff-data/wwff_directory.csv", $datovy_soubor_cesta);



?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script type="text/javascript" src="https://api.mapy.cz/loader.js"></script>
    <script type="text/javascript">Loader.load();</script>
</head>
<body id="advanced-markers">
<div class="text-center mb-4"> 

<h3>WWFF maps generator</h3>

<form action="" method="post" class="form-example">

    <strong>Please choose:</strong>
    DXCC:     
    <select name="dxcc">
        <option value="">... choose ...</option>    
<?php 
$csv = csv_to_array($datovy_soubor_cesta);
foreach ($csv as $arr) if ($arr["dxcc"]!="") $list_of_dxcc[]=$arr["dxcc"];
$list_of_dxcc = array_unique($list_of_dxcc);
foreach ($list_of_dxcc as $oneline) {
    echo '<option value="'.$oneline.'" ';
    if ($dxcc==$oneline) echo ' selected';
    echo '>'.$oneline.'</option>';
}
?>
    </select>
    Area:     
    <select name="area">
        <option value="">... choose ...</option>
        <option value="all" <?php if ($area=="all") echo " selected"; ?>>all</option>
        <option value="awitha" <?php if ($area=="awitha") echo " selected"; ?>>with activation only</option>
        <option value="awouta" <?php if ($area=="awouta") echo " selected"; ?>>without activation only</option>
    </select>
    File format:     
    <select name="file">
        <option value="gpx">gpx</option>
    </select>
    <input type=submit value="Generate">

</form>
<br />
<?php

if ($dxcc!="" and $area!="") {

    $pocet=0;

    if ($area=="awouta") {

        $file_name = $dxcc."FF-without-activation-only.gpx";

        $folder_name = "data_files/";

        $gpx_soubor_cesta = dirname(__FILE__) . "/" .$folder_name . $file_name;

        $csv = csv_to_array($datovy_soubor_cesta);

        $fp = fopen( $gpx_soubor_cesta, 'w');
        fwrite($fp, '<?xml version="1.0" encoding="windows-1250" standalone="no" ?>'.PHP_EOL);
        fwrite($fp, '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="GPS Data Team ( http://www.gps-data-team.com )">'.PHP_EOL);

        foreach ($csv as $arr) {
            
            if ($arr["dxcc"]==$dxcc and $arr["status"]=="active" and $arr["qsoCount"]<1) {

                fwrite($fp, '<wpt lon="'.$arr["longitude"].'" lat="'.$arr["latitude"].'"><name>'.$arr["reference"].' '.$arr["name"].' (0/none)</name></wpt>'.PHP_EOL);

                $pocet++;
            }

        }

        fwrite($fp, '</gpx>'.PHP_EOL);

        fclose($fp);
        
        echo '<a href="'.$folder_name.$file_name.'">'.$file_name.'</a><br />';

        echo "<br />Total ".$pocet." areas without activation only.<br />";

    }

    if ($area=="all") {

        $file_name = $dxcc."FF-all-area.gpx";

        $folder_name = "data_files/";

        $gpx_soubor_cesta = dirname(__FILE__) . "/" .$folder_name . $file_name;

        $csv = csv_to_array($datovy_soubor_cesta);

        $fp = fopen( $gpx_soubor_cesta, 'w');
        fwrite($fp, '<?xml version="1.0" encoding="windows-1250" standalone="no" ?>'.PHP_EOL);
        fwrite($fp, '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="GPS Data Team ( http://www.gps-data-team.com )">'.PHP_EOL);

        foreach ($csv as $arr) {
            
            if ($arr["dxcc"]==$dxcc and $arr["status"]=="active") {

                fwrite($fp, '<wpt lon="'.$arr["longitude"].'" lat="'.$arr["latitude"].'"><name>'.$arr["reference"].' '.$arr["name"].' ('.$arr["qsoCount"].'/'.$arr["lastAct"].')</name></wpt>'.PHP_EOL);

                $pocet++;
            }

        }

        fwrite($fp, '</gpx>'.PHP_EOL);

        fclose($fp);
        
        echo '<a href="'.$folder_name.$file_name.'">'.$file_name.'</a><br />';

        echo "<br />Total ".$pocet." areas together.<br />";

    }

    if ($area=="awitha") {

        $file_name = $dxcc."FF-with-activation-only.gpx";

        $folder_name = "data_files/";

        $gpx_soubor_cesta = dirname(__FILE__) . "/" .$folder_name . $file_name;

        $csv = csv_to_array($datovy_soubor_cesta);

        $fp = fopen( $gpx_soubor_cesta, 'w');
        fwrite($fp, '<?xml version="1.0" encoding="windows-1250" standalone="no" ?>'.PHP_EOL);
        fwrite($fp, '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="GPS Data Team ( http://www.gps-data-team.com )">'.PHP_EOL);

        foreach ($csv as $arr) {
            
            if ($arr["dxcc"]==$dxcc and $arr["status"]=="active" and $arr["qsoCount"]>0) {

                fwrite($fp, '<wpt lon="'.$arr["longitude"].'" lat="'.$arr["latitude"].'"><name>'.$arr["reference"].' '.$arr["name"].' ('.$arr["qsoCount"].'/'.$arr["lastAct"].')</name></wpt>'.PHP_EOL);

                $pocet++;
            }

        }

        fwrite($fp, '</gpx>'.PHP_EOL);

        fclose($fp);
        
        echo '<a href="'.$folder_name.$file_name.'">'.$file_name.'</a><br />';
        
        echo "<br /><p>Total ".$pocet." areas with activation only.</p><br />";

    }

//echo "<br /><p>(ALERT! for download .gpx file please choose SAVE LINK AS from context menu)</p>";
    

}

?>
</div>
<?php
echo "<div id=\"m\" style=\"height:760px\"></div>";
?>

<script>
    var obrazek = "https://api.mapy.cz/img/api/marker/drop-red.png";

    var m = new SMap(JAK.gel("m"));
    m.addControl(new SMap.Control.Sync()); /* Aby mapa reagovala na změnu velikosti průhledu */
    m.addDefaultLayer(SMap.DEF_BASE).enable(); /* Turistický podklad */
    var mouse = new SMap.Control.Mouse(SMap.MOUSE_PAN | SMap.MOUSE_WHEEL | SMap.MOUSE_ZOOM); /* Ovládání myší */
    m.addControl(mouse); 

    var data = {
    <?php
    $csv = csv_to_array($datovy_soubor_cesta);
    foreach ($csv as $arr) {
        if($arr["dxcc"]==$dxcc and $arr["status"]=="active" and $arr["latitude"]!="" and $arr["longitude"]!="") {
            echo '      "'.$arr["reference"].' '.htmlspecialchars($arr["name"]).' ('.$arr["qsoCount"].'/'.$arr["lastAct"].')": "'.$arr["latitude"].'N,'.$arr["longitude"].'E",'.PHP_EOL;
        }
    }
    ?>
    };
    var znacky = [];
    var souradnice = [];

    for (var name in data) { /* Vyrobit značky */
        var c = SMap.Coords.fromWGS84(data[name]); /* Souřadnice značky, z textového formátu souřadnic */
        
        var options = {
            url:obrazek,
            title:name,
            anchor: {left:10, bottom: 1}  /* Ukotvení značky za bod uprostřed dole */
        }
        
        var znacka = new SMap.Marker(c, null, options);
        souradnice.push(c);
        znacky.push(znacka);
    }

    var vrstva = new SMap.Layer.Marker();     /* Vrstva se značkami */
    m.addLayer(vrstva);                          /* Přidat ji do mapy */
    vrstva.enable();                         /* A povolit */
    for (var i=0;i<znacky.length;i++) {
        vrstva.addMarker(znacky[i]);
    }

    var cz = m.computeCenterZoom(souradnice); /* Spočítat pozici mapy tak, aby značky byly vidět */
    m.setCenterZoom(cz[0], cz[1]);        
</script>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
<?php





