<?php
/**
 * Verarbeitet GET-Parameter für Aktionen wie Heilen/Mapwechsel etc.
 */

/**
 * @var string Das einzubindende File (Standard: Karte)
 */
$includeFile = 'map.php';
$user = World_Base::$USER;

// Prüfen ob User eingeloggt
if (!($user instanceof World_User)) {
	// User nicht eingeloggt
	$includeFile = 'login.php'; // Standard-File: login
	if (isset($_GET[SITE_PARAM])) {
		// Bestimmen des include-Files anhand des GET-Parameters SITE_PARAM
		$param = $_GET[SITE_PARAM];
		$paramFile = getParamFile($param);
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
		$paramFile = getParamFile($param);
		if ($paramFile !== false) { // Einbinden + Prüfen ob Einbinden erfolgreich
			if ((include $paramFile) === false) {
				$errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
				$longErrorMessage = 'Datei Include-Datei ' . $paramFile . ' konnte nicht nicht gefunden werden (' . __FILE__ . ').';
				displayErrorPage($errorMessage, $longErrorMessage);
			}
		}
	}
	
    // Nachrichten einbinden
    if ((include 'messages.php') === false) {
        $errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
        $longErrorMessage = 'Datei Include-Datei messages.php konnte nicht nicht gefunden werden (' . __FILE__ . ').';
        displayErrorPage($errorMessage, $longErrorMessage);
    }
	
	// User eingeloggt
	if ($user->isInFight()) {
		// Benutzer im Kampf
		$includeFile = 'fight.php';
	}
	else {
		// Seitenboxen einbinden    // Einbinden + Prüfen ob Einbinden erfolgreich
		if ((include 'sideBoxes.php') === false) {
			$errorMessage = 'Öffnen der Include-Datei fehlgeschlagen';
			$longErrorMessage = 'Datei Include-Datei sideBoxes.php konnte nicht nicht gefunden werden (' . __FILE__ . ').';
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
			$includeFile = 'map.php';
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
