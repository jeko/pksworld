<?php
/**
 * prüft ob das Argument $getParam in der Konfiguration
 * unter $config['GetParamFile'] aufgeführt ist und liefert den entsprechenden
 * Dateinamen zurück. Ist $subSection angegeben,
 * so wird in einem Unterfeld gesucht ($config['GetParamFile'][$subSection]
 * Funktion ist case INsensitive, also $getParam = 'Hi' und $getParam = 'hi'
 * werden gleich behandelt.
 * @param $getParam string GET-Parameter
 * @param $subSection string (optional) Unterfeld von $config['GetParamFile']
 * @return string Dateiname
 */
function getParamFile($getParam, $subSection = false)
{
	global $config;
	if ($subSection === false) {
		$validGetParams = $config['GetParamFile'];
	}
	else {
		$validGetParams = $config['GetParamFile'][$subSection];
	}
	$validGetParams = array_change_key_case($validGetParams, CASE_LOWER);
	$getParamLowered = strtolower($getParam); // auch lowercase (für Vergleich)
	if (isset($validGetParams[$getParamLowered])) {
		return $validGetParams[$getParamLowered];
	}
	else {
		return false;
	}
}