<?php
/**
 * Verarbeitet GET-Parameter für Aktionen wie Heilen/Mapwechsel etc.
 */

/**
 * @var string Das einzubindende File (Standard: Karte)
 */
$includeFile = INC_MAP;
$user = World_Base::$USER;

// Prüfen ob User eingeloggt
if ($user == null) {
	// User nicht eingeloggt
	$includeFile = INC_LOGIN; // Standard-File: login
	if (isset($_GET[SITE_PARAM])) {
		// Bestimmen des include-Files anhand des GET-Parameters SITE_PARAM
		$param = $_GET[SITE_PARAM];
		$paramFile = getParamFile($param, 'notLoggedIn');
		// Prüfung, ob der Site-Parameter zulässig ist
		if ($paramFile !== false) {
			$includeFile = $paramFile;
		}
	}
}
else {    
	// Aktion einbinden
	if (isset($_GET[ACTION_PARAM])) {
		$param = $_GET[ACTION_PARAM];
		$paramFile = getParamFile($param, 'action');
		if ($paramFile !== false) { // Einbinden + Prüfen ob Einbinden erfolgreich
			if ((include $paramFile) === false) {
				$errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
				$longErrorMessage = 'Datei Include-Datei ' . $paramFile . ' konnte nicht nicht gefunden werden (' . __FILE__ . ').';
				displayErrorPage($errorMessage, $longErrorMessage);
			}
		}
	}
	
    // Nachrichten einbinden
    if ((include INC_MESSAGES) === false) {
        $errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
        $longErrorMessage = 'Datei Include-Datei ' . INC_MESSAGES . ' konnte nicht nicht gefunden werden (' . __FILE__ . ').';
        displayErrorPage($errorMessage, $longErrorMessage);
    }
	
	// User eingeloggt
	if ($user->isInFight()) {
		// Benutzer im Kampf
		$includeFile = INC_FIGHT;
	}
	else {
		// Seitenboxen einbinden    // Einbinden + Prüfen ob Einbinden erfolgreich
		if ((include INC_SIDE_BOXES) === false) {
			$errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
			$longErrorMessage = 'Datei Include-Datei ' . INC_SIDE_BOXES . ' konnte nicht nicht gefunden werden (' . __FILE__ . ').';
			displayErrorPage($errorMessage, $longErrorMessage);
		}

		// Hauptinhalt ermitteln
		if (isset($_GET[SITE_PARAM])) {
			// Bestimmen des include-Files anhand des GET-Parameters SITE_PARAM
			$param = $_GET[SITE_PARAM];
			$paramFile = getParamFile($param);
			// Prüfung, ob der Site-Parameter zulässig ist
			if ($paramFile !== false) {
				$includeFile = $paramFile;
			}
		}
		else {
			$includeFile = INC_MAP;
		}
	}
}

// Einbinden + Prüfen ob Einbinden erfolgreich
if ((include $includeFile) === false) {
	$errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
	$longErrorMessage = 'Datei Include-Datei ' . $includeFile . ' konnte nicht nicht gefunden werden (' . __FILE__ . ').';
	displayErrorPage($errorMessage, $longErrorMessage);
}

//　Prüfen ob Request über Ajax läuft und template umschalten
if (isset($_GET['ajax'])) {
    $template->setTemplate('ajax_index.html');
    if ($user == null) {
    	$template->viewMacro = 'ajaxNotLoggedIn';
    }
    else {
    	$template->viewMacro = 'ajaxStandard';
    }
}