<?php
/**
 * Bottomfile für Index
 * macht abschliessende Arbeiten wie die Ausgabe des Templates,
 * Daten zurückschreiben, Session abschliessen und Loggen
 */

// Template ausgeben und mögliche Fehler behandeln
$template->scriptContent = $javascriptContent;
try {
    echo $template->execute();
}
catch (Exception $e) {
	$errorMessage = 'Das Template generierte einen Fehler.';
    displayErrorPage($errorMessage, (string)$e);
}
ob_flush(); // Template senden

// Benutzerdaten speichern
if (World_Base::$USER != null) {
    World_Base::$USER->saveData();
    World_Base::$USER->unloadModule('all');
    World_Base::$SESSION->set('userData', serialize(World_Base::$USER));
}

// Sessiondaten speichern
World_Base::$SESSION->saveData();

// Fehler loggen
Error::logErrors(World_Base::$LOG, true);