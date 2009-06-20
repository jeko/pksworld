<?php

// Auf false setzen nachdem Script seinen Zweck erfüllt hat.
// Andernfalls kann es zu erheblichem Datenverlust führen,
// wenn die Datenbank nicht mehr synchron läuft

$active = true;

if ($active === false) die("Script sollte nicht mehr ausgeführt werden (veraltet).");

/** START **/
// Parsed einmal alle Maps neu (geeignet um die gebrannten Kartenbilder zu erstellen)

ob_start();
error_reporting(E_ALL);
session_start();
set_time_limit(0);

require_once('./../../constants.php'); // Konstanten
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Autoloader für Klassen
require_once(WORLD . 'config.php'); // Konfiguration
World_Base::$DB = new DatabaseInterface($config['mySql']['host'], $config['mySql']['user'], $config['mySql']['pass'], $config['mySql']['db']);

$mapList = World_Map::getMapList();

// Formularverarbeitung
foreach ($mapList as $mapData) {
	$map = new World_Map($mapData['id']);
	
	if ($map->getId() !== 0) {
		echo $map->getDisplayName() . ' ';

        $editor = new World_Map_Editor($mapData['id']);
        // LayerCode parsen
        $editor->parseLayerCode();
        // PkmnCode parsen
        $editor->parsePkmnCode();
        if ($editor->saveData()) {
            echo "OK<br />";
        }
        else {
            echo "Fehler<br />";
        }
		
        ob_flush();
	}
}