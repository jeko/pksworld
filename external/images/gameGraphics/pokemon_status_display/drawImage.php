<?php
$bild = imagecreatefrompng('grund.png');

$balken_ep = imagecreatefrompng('ep.png');
$balken_ep_sizes = getimagesize('ep.png');

// .....  KP-Balken reinkopieren!
if (!is_numeric($_GET['mkp'])) {$_GET['mkp'] = 100; $_GET['ukp'] = 0;}
if (!is_numeric($_GET['ukp'])) {$_GET['mkp'] = 100; $_GET['ukp'] = 0;}
$breite_kp = @floor(100 / $_GET['mkp'] * $_GET['ukp']);
if ($breite_kp > 0) {
  if ($breite_kp > 50) {
    $balken_kp = imagecreatefrompng('kp_gruen.png');
    $balken_kp_sizes = getimagesize('kp_gruen.png');
  } else {
    if ($breite_kp > 25) {
      $balken_kp = imagecreatefrompng('kp_orange.png');
      $balken_kp_sizes = getimagesize('kp_orange.png');
    } else {
      $balken_kp = imagecreatefrompng('kp_rot.png');
      $balken_kp_sizes = getimagesize('kp_rot.png');
    }
  }
  imagecopyresized ($bild, $balken_kp, 15, 2, 0, 0, $breite_kp, 3, $balken_kp_sizes[0], $balken_kp_sizes[1]);
}

// .....  EP-Balken reinkopieren!
if (!is_numeric($_GET['nep'])) {$_GET['nep'] = 100; $_GET['hep'] = 0;}
if (!is_numeric($_GET['hep'])) {$_GET['nep'] = 100; $_GET['hep'] = 0;}
$breite_ep = @floor(100 / $_GET['nep'] * $_GET['hep']);
if ($breite_ep > 0) {
  imagecopyresized ($bild, $balken_ep, 15, 10, 0, 0, $breite_ep, 3, $balken_ep_sizes[0], $balken_ep_sizes[1]);
}

header ("Pragma:no-cache");
header ("Cache-Control:private,no-store,no-cache,must-revalidate");
header ("Content-Type: image/png");
ImagePNG($bild);
?>

