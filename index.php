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
require_once(FUNC_PATH . 'getParamFile.php'); // Include-File abhängig vom GET-Parameter ermitteln
require_once(FUNC_PATH . 'displayErrorPage.php'); // Zeigt On-the-Fly eine Fehlerseite an
require_once(FUNC_PATH . 'getSpritePosition.php'); // Ermittelt die Position des Pkmns auf dem Sprite

// Includepath setzen damit Dateien direkt ohne Pfadangabe eingebunden werden können
$includePaths = implode(PATH_SEPARATOR, array(INC_PATH, ADMIN_INC_PATH, SITE_INC_PATH, ACTION_INC_PATH));
set_include_path($includePaths);

include('index_top.php');
include('processSite.php');
include('index_bottom.php');

echo "<!-- Processing time: " . (microtime() - $t) . "ms -->";

// Ende
exit();
