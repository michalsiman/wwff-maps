<?php
//----------------------------------------------------------------------------------
//ini_set('display_errors', 1);
$mysqli = new mysqli("localhost", "wwff", "FLORAifauna:2020", "wwff_maps");   // <<< mysql db credentials
$area = htmlspecialchars($_POST['area']);
if($area=="") $area="all";
$program = strtoupper(htmlspecialchars($_POST['program']));
//----------------------------------------------------------------------------------
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script type="text/javascript" src="https://api.mapy.cz/loader.js"></script>
    <script type="text/javascript">Loader.lang = "en"; Loader.load(); </script>
    <link href="styles/style.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body id="advanced-markers">
<div class="text-center mb-4">
<form action="" method="post" class="autoSubmit">
    <strong>WWFF maps</strong>&nbsp;       
    <select name="program" class="autoSubmit">
        <option value="">... select ...</option>
    <?php
    //---------------------------------------------------------------------------------
    $query = "SELECT program, count(program) AS cnt FROM `wwff_area` WHERE status='active' GROUP BY program ORDER BY program";
    $result = $mysqli->query($query);
    while($row = $result->fetch_assoc()) {
        echo "<option value='".$row['program']."'";
        if ($row['program']==$program) echo " selected";
        echo ">".$row['program'];
        echo " (".$row['cnt'].")";
        echo "</option>".PHP_EOL;
      }
    //---------------------------------------------------------------------------------
    ?>
    </select>&nbsp;
    <select name="area" class="autoSubmit">
        <option value="all" <?php if ($area=="all") echo " selected"; ?>>all</option>
        <option value="awitha" <?php if ($area=="awitha") echo " selected"; ?>>with QSO</option>
        <option value="awouta" <?php if ($area=="awouta") echo " selected"; ?>>without QSO</option>
    </select>&nbsp;
<?php
//---------------------------------------------------------------------------------
if ($area=="all") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>''";
if ($area=="awitha") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>'' AND qsoCount>0";
if ($area=="awouta") $query = "SELECT * FROM wwff_area WHERE status='active' AND program='".$program."' AND latitude<>'' AND longitude<>'' AND qsoCount=0";
$result = $mysqli->query($query);
$counter = mysqli_num_rows($result);
if ($counter>0) echo " (".mysqli_num_rows($result)." areas - <a href='gpx_generator.php?program=".$program."&type=".$area."'>GPX file</a>) ";
if ($counter==0) echo " no records";
//---------------------------------------------------------------------------------
?>
</form>
</div>
<?php
//----------------------------------------------------------------------------------
if ($program!="") {
//----------------------------------------------------------------------------------
?>
<div id="m" style="height:100%"></div>
<script>
    var obrazek = "https://api.mapy.cz/img/api/marker/drop-red.png";
    
    var m = new SMap(JAK.gel("m"));

    m.addControl(new SMap.Control.Sync()); /* Aby mapa reagovala na změnu velikosti průhledu */
    m.addDefaultLayer(SMap.DEF_BASE).enable(); /* Turistický podklad */
    var mouse = new SMap.Control.Mouse(SMap.MOUSE_PAN | SMap.MOUSE_WHEEL | SMap.MOUSE_ZOOM); /* Ovládání myší */
    m.addControl(mouse);
    m.addDefaultControls();
    var sync = new SMap.Control.Sync({bottomSpace:0});
    var c = new SMap.Control.Mouse(SMap.MOUSE_PAN);
    m.addControl(sync);

    var data = {
    <?php
    //----------------------------------------------------------------------------------
    while($row = $result->fetch_assoc()) {
        if ($row["qsoCount"]==0) $row["qsoCount"]="none";
        if ($row["lastAct"]=="1980-01-01") $row["lastAct"]="none";
        echo '      "'.$row["reference"].' '.htmlspecialchars($row["name"]).' ('.$row["qsoCount"].'/'.$row["lastAct"].')": "'.$row["latitude"].'N,'.$row["longitude"].'E",'.PHP_EOL;
      }
    //----------------------------------------------------------------------------------
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
<?php
//----------------------------------------------------------------------------------
} else {
echo "<center>";
echo "<h3>Welcome on WWFF maps</h3>";
echo "";
echo "<p>Select the WWFF program at the top of this page, then you will see the WWFF references for that area (if any) - the map works with yesterday's data from WWFF database.</p>";
echo "<p>When you hover your mouse over a WWFF reference, you will see the reference number, name, total number of QSOs and the date of last activation.</p>";
echo "<p>The map displays data from the WWFF database - the national administrator of the area is responsible for the accuracy of the data. Some areas do not have coordinates entered, or have coordinates entered incorrectly, and may be displayed in a different position or not at all.</p>";
echo "";
echo "<img src='img/example_wwff_map.png'><br />";
echo "<p>";
echo "WWFF active areas in db: ".mysqli_num_rows($mysqli->query("SELECT * FROM wwff_area WHERE status='active'"))."<br />";
echo "WWFF active areas with QSO: ".mysqli_num_rows($mysqli->query("SELECT * FROM wwff_area WHERE status='active' AND qsoCount>0"))."<br />";
echo "WWFF active areas without QSO: ".mysqli_num_rows($mysqli->query("SELECT * FROM wwff_area WHERE status='active' AND qsoCount=0"))."<br />";
echo "</p>";
echo "</center>";
}
//----------------------------------------------------------------------------------
?>
<script>
$('.autoSubmit, .autoSubmit select, .autoSubmit input, .autoSubmit textarea').change(function () {
    const el = $(this);
    let form;

    if (el.is('form')) { form = el; }
    else { form = el.closest('form'); }

    form.submit();
});
</script>
</body>
</html>
<?php
//----------------------------------------------------------------------------------
$mysqli->close();
//----------------------------------------------------------------------------------
