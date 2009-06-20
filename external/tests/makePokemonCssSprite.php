<?php
// Auf false setzen nachdem Script seinen Zweck erfüllt hat.
// Andernfalls kann es zu erheblichem Datenverlust führen,
// wenn die Datenbank nicht mehr synchron läuft
ob_start();

$active = false;

if ($active === false) die("Script sollte nicht mehr ausgeführt werden (veraltet).");

/** START **/
// konvertiert Kartenattribute in die Flagform und schreibt das Flag in die DB

ob_start();
error_reporting(E_ALL);
session_start();
set_time_limit(0);

require_once('./../../constants.php'); // Konstanten
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Autoloader für Klassen
require_once(WORLD . 'config.php'); // Konfiguration
World_Base::$DB = new DatabaseInterface($config['mySql']['host'], $config['mySql']['user'], $config['mySql']['pass'], $config['mySql']['db']);
if (!isset($_GET['path'])) {
	die('Bitte Pfad angeben (relativ zum Bilderverzeichnis)');
}
// Eingabeverzeichnisse
$inputDir = IMG_PATH_ABSOLUTE . $_GET['path'];
$saveTo = $inputDir . 'sprite.png';
$inputFormat = 'png';
$inputW = (isset($_GET['side']))?intval($_GET['side']):80;
$inputH = $inputW;

// Höheste PokemonId abfragen
if (!World_Base::$DB->select(TABLE_CONST_POKEMON, array('MAX(id) AS highest', 'COUNT(id) AS total'))) {
    die('Konnte MAX(ID) nicht abrufen.');
}

$row = World_Base::$DB->getRow();
$highestId = $row['highest'];
$totalParts = $row['total'];
$breakAfter = SPRITE_BREAK_AFTER;
$maxSteps = ceil($highestId / $breakAfter);
$step = 0;
$ypos = 0;
$xpos = 0;

$spriteW = $maxSteps * $inputW;
$spriteH = $breakAfter * $inputH;
$spriteImg = imagecreate($spriteW, $spriteH);
// Transparenter Hintergrund
$bg = imagecolorallocatealpha($spriteImg, 255, 255, 255, 127);
imagefill($spriteImg, 0, 0 , $bg);

echo "
    Hi.\n\nErstelle Sprite $inputW x $inputH aus $totalParts Elemente.
    Die höchste ID ist $highestId\n.";

// Durch alle Pokemon gehen
for ($i = 1; $i < $highestId; $i++) {
	$positionInBreak = $i % $breakAfter;
	$breakNumber = floor($i / $breakAfter);
	
	$xpos = $breakNumber * $inputW;
	$ypos = $positionInBreak * $inputH;
	$pokemon = new World_Pokemon($i);
    $pokemonImage = null;
    $correctionX = 0;
    $correctionY = 0;

    echo " ID $i:";
	if ($pokemon->getPokedexNumber() != 0) {
		// Pokemon existiert, daher Bild auslesen und einfügen
		$name = $pokemon->getPokedexName();
		$file = $inputDir . $name;
		echo " $name, suche Bild...";
		switch (strtolower($inputFormat)) {
			case 'gif':
				$file .= '.gif';
				$pokemonImage = imagecreatefromgif($file);
				break;
			case 'jpg':
			case 'jpeg':
                $file .= '.jpg';
                $pokemonImage = imagecreatefromjpeg($file);
				break;
			case 'png':
                $file .= '.png';
                $pokemonImage = imagecreatefrompng($file);
				break;
		}
	}
	// Wenn kein Bild vorhanden, Platzhalter einsetzen
	if (!$pokemonImage || $pokemonImage == null) {
		echo ' Bild nicht gefunden, verwende Platzhalter.';
	}
	else {
		// Dimensionen ermitteln
		$pic = getimagesize($file);
		if ($pic[0] < $inputW) {
			$correctionX = ($inputW - $pic[0]) / 2;
		}
		if ($pic[1] < $inputH) {
			$correctionY = ($inputH - $pic[1]) / 2;
		}
		echo ' Bild gefunden.';
		
	    // Bild kopieren
	    imagecopy($spriteImg, $pokemonImage,
	       $xpos + $correctionX, $ypos + $correctionY,
	       0, 0, $pic[0], $pic[1]);
	}
	echo "\n";
}
	
ob_clean();
header("Content-Type: image/png");

imagealphablending($spriteImg, false);
imagesavealpha($spriteImg, true);

// Bild ausgeben
imagepng($spriteImg, $saveTo, 9);
imagepng($spriteImg);
imagedestroy($spriteImg);