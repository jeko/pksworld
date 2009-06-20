<?php

error_reporting(E_ALL);
session_start();
ob_start();

if (isset($_GET['nr'])) {
$pokemonNumber = intval($_GET['nr']);
}
else {
	die("Missing Pokemon number (url: ?nr=#)");
}

ob_start();
error_reporting(E_ALL);
set_time_limit(0);

require_once('../../constants.php'); // Konstanten
require_once('../../config.php'); // Konfiguration
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Site-URL generieren
require_once(FUNC_PATH . 'getParamFile.php'); // GetFile-Parameter prüfen
require_once(FUNC_PATH . 'displayErrorPage.php'); // Zeigt On-the-Fly eine Fehlerseite an
require_once(FUNC_PATH . 'getSpritePosition.php'); // Ermittelt die Position des Pkmns auf dem Sprite
World_Base::$DB = new DatabaseInterface($config['mySql']['host'], $config['mySql']['user'], $config['mySql']['pass'], $config['mySql']['db']);

include(INC_INDEX_TOP);
$user = World_Base::$USER;
if ($user !== null) {
	$pokemon = new World_PokemonInstance($pokemonNumber);
	if ($user->addPokemon($pokemon) !== false) {
		echo "Pokemon hinzugefügt.";
	}
	else {
		echo "Ein Fehler trat auf.";
	}

	$user->saveData();
	
    Error::logErrors(World_Base::$LOG, true);
}
else {
	die("Failed creating user.");
}