<?php

// --------------------------------------------------------------------
// gpx file download generator
//
// required parameters:
//
// dxcc = country code (like "OK" or "DL" ...)
//
// type = 1 - all
//        2 - only activated area
//        3 - only non-activated area
//
// --------------------------------------------------------------------

$search = htmlspecialchars($_GET['call']);

//header('Content-type: image/jpeg');
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: image/jpeg");
header("Content-Disposition: attachment; filename=\"diplom-vffa-2022-$search.jpg\"");

$img = imagecreatefromjpeg("img.jpg");

  $txt = $search;
  $fontFile = realpath("font.ttf");
  $fontSize = 100;
  $fontColor = imagecolorallocate($img, 255, 255, 255);
  $black = imagecolorallocate($img, 0, 0, 0);
  $angle = 0;

 
  $whereX = 950; //vodorovna osa
  $whereY = 1050; // svisla osa

  imagettftext($img, $fontSize, $angle, $whereX, $whereY, $black, $fontFile, $txt);
  imagejpeg($img);
  imagedestroy($img);




?>