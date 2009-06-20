<?php
/**
 * Headerdatei für Index
 */

// Logging aktivieren
if (LOG_ACTIVE === true) {
	World_Base::$LOG = new Log_Writer(LOG_FILE);
}

// Datenbankverbindung herstellen
World_Base::$DB = new DatabaseInterface($config['mySql']['host'], $config['mySql']['user'], $config['mySql']['pass'], $config['mySql']['db']);
unset($config['mySql']['pass']); // Vermeidung einer versehentlichen Ausgabe des PWs

// Session starten
World_Base::$SESSION = new World_Session();
if (isset($_COOKIE['worldSessionId'])) {
	World_Base::$SESSION->start($_COOKIE['worldSessionId'], $_COOKIE['worldSessionKey']);

	// zufällig neuen Primär-Schlüssel generieren
	if (rand(1,10) > 8) {
		World_Base::$SESSION->flipKeys();
	}
}
else {
	World_Base::$SESSION->start();
}
setcookie('worldSessionId', World_Base::$SESSION->getSid(), time() + SESSION_EXPIRING_TIME, '/');
setcookie('worldSessionKey', World_Base::$SESSION->getKeyAsString(), time() + SESSION_EXPIRING_TIME, '/');

// Benutzerdaten laden
$user = null;
$userId = World_Base::$SESSION->get('userId');
if ($userId !== false) {
	// User ist eingeloggt
	$userData = World_Base::$SESSION->get('userData');
	if ($userData !== false) {
		$dataObject = unserialize($userData);
		// validieren
		if ($dataObject instanceof World_User) {
			$user = $dataObject;
		}
		else {
			$user = new World_User($userId);
		}
	}
	else {
		$user = new World_User($userId);
	}
	
	// Prüfung ob Daten geladen
	if ($user->isLoaded() === false) {
		$user = null;
	}
	else {
		// Module registrieren
		$user->registerModule('pokemonTeam', 'World_PokemonTeam');
        $user->registerModule('storeBox', 'World_StoreBoxContainer');
        $user->registerModule('map', 'World_Map');
        $user->registerModule('fight', 'World_Fight');
        $user->registerModule('inventory', 'World_Inventory');
        $user->registerModule('messages', 'World_Messages');
        $user->registerModule('settings', 'World_Settings');
	}
}
World_Base::$USER = $user;

// Templateinstanz erzeugen
$template = new PHPTAL();
$template->setTemplateRepository(array(TEMPLATE_PATH, ADMIN_TEMPLATE_PATH, SITE_TEMPLATE_PATH));
$template->setTemplate('index.html');
$template->viewMacro = 'world'; // Standard, ansonsten auch 'admin' für Adminoberflächen
$template->WORLD = WORLD_DIRECTORY;
$template->SESSION = World_Base::$SESSION;

$javascriptContent = array();