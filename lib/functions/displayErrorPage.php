<?php
/**
 * Gibt eine Fehlerseite aus (anzuwenden bei schwerwiegenden
 * Fehlern wie Templatefehler o.ä.)
 * @param $errorMessage Anzuzeigende Fehlermeldung (für Admins)
 * @return void
 */
function displayErrorPage($errorMessage, $longErrorMessage = false)
{
	global $template;
	if ($longErrorMessage == false) {
		$longErrorMessage = $errorMessage;
	}
	// Loggen
	World_Base::$LOG->write((string)$longErrorMessage . ' (' .  __FILE__ . ')', Error::FATAL);
	// Meldung ausgeben
	$templateErrorMessage = '[' . date('Y-m-d H:i:s') . '] ' . $errorMessage 
    . ' (der Fehler wurde in die <a href="' . buildSiteUrl('logViewer') . '"'
    . ' title="Log Viewer öffnen">Log-Datei</a> geschrieben).';
	// Versuch Error-Template zu laden
	try {
		$template->templateFile = 'error.html';
		$template->templateMacro = 'error';
		$template->contentTitle = 'Oops...';
		$template->errorMessage = $templateErrorMessage;
		echo $template->execute();
	}
	catch (Exception $e) {
		// Fehlgeschlagen, Ausgabe als String
		World_Base::$LOG->write((string)$e . ' (' .  __FILE__ . ')', Error::FATAL);
		$templateErrorMessage .= '<br />Das Fehlertemplate konnte nicht geladen werden.';
		echo '<p>' . $templateErrorMessage . '</p>';
	}
}