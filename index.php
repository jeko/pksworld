<?php
/**
 * Steuerungsdatei index.php
 * 
 */
//TODO Fehler unterdrücken lassen! (während Entwicklungsphase aber nützlich)
error_reporting(E_ALL);
ob_start();
$t = microtime();

require_once('constants.php'); // Konstanten
require_once('config.php'); // Konfiguration
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Site-URL generieren
require_once(FUNC_PATH . 'getParamFile.php'); // GetFile-Parameter prüfen
require_once(FUNC_PATH . 'displayErrorPage.php'); // Zeigt On-the-Fly eine Fehlerseite an
require_once(FUNC_PATH . 'getSpritePosition.php'); // Ermittelt die Position des Pkmns auf dem Sprite

include(INC_INDEX_TOP);
include(INC_PROCESS_SITE);
include(INC_INDEX_BOTTOM);

echo "<!-- Processing time: " . (microtime() - $t) . "ms -->";

// Ende
exit();