<?php
/**
 * Steuerungsdatei index.php
 * 
 */
//TODO Fehler unterdrücken lassen! (während Entwicklungsphase aber nützlich)
error_reporting(E_ALL);
ob_start();
$processingStart = microtime();

require_once('constants.php'); // Konstanten
require_once('config.php'); // Konfiguration
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Site-URL generieren
require_once(FUNC_PATH . 'getParamFile.php'); // GetFile-Parameter prüfen
require_once(FUNC_PATH . 'displayErrorPage.php'); // Zeigt On-the-Fly eine Fehlerseite an
require_once(FUNC_PATH . 'getSpritePosition.php'); // Ermittelt die Position des Pkmns auf dem Sprite

$t1 = microtime () - $processingStart;
echo "Files loaded: $t1 ms<br />";
include(INC_INDEX_TOP);
$t2 = microtime () - $processingStart;
echo "Data loaded: $t2 ms (" . ($t2 - $t1) . " ms)<br />";
include(INC_PROCESS_SITE);
$t3 = microtime () - $processingStart;
echo "Site processed: $t3 ms (" . ($t3 - $t2) . " ms)<br />";
include(INC_INDEX_BOTTOM);
$t4 = microtime () - $processingStart;
echo "Data saved, template parsed: $t4 ms (" . ($t4 - $t3) . " ms)<br />";

$processingTime = microtime() - $processingStart;
echo "Aufbauzeit: $processingTime ms<br />";
// Ende
exit();