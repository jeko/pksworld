<?php

/* Kleine Helferfunktion um Arrayelemente nach lowercase zu transformieren
 */
function lower(&$string) { $string = strtolower(basename($string)); }

// Arrays mit include-Files generieren
// damit sie nicht bei jedem Aufruf von getParamFile neu erstellt werden müssen
$_includeFiles = array_merge(glob(INC_PATH . '*/*.php'), glob(INC_PATH . '*.php'));
$_includeFilesLowered = $_includeFiles;
array_walk($_includeFilesLowered, 'lower'); 

/**
 * gibt den Dateinamen einer Include-Datei zurück die über den GET-Param $getParam
 * referenziert wird
 * Funktion ist case INsensitive, also $getParam = 'Hi' und $getParam = 'hi'
 * werden gleich behandelt.
 * @param $getParam string GET-Parameter
 * @return string Dateiname oder false wenn die Datei nicht gefunden werden konnte
 */
function getParamFile($getParam)
{
    global $_includeFiles, $_includeFilesLowered;
    
    $getParamFileLowered = strtolower($getParam) . '.php';

	if (($key = array_search($getParamFileLowered, $_includeFilesLowered)) !== false) {
		return $_includeFiles[$key];
	}
	else {
		return false;
	}
}

