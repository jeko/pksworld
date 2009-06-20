<?php
/**
 * stellt eine Url mit einem Site-Parameter (SITE_PARAM)
 * zusammen
 * @param $site der Wert des Site-Parameters
 * @param $action (optional) Aktion
 * @param $additionalParameters (optional) weitere Parameter
 * @return string URL
 */
function buildSiteUrl($site, $action='', $additionalParameters='')
{	
    $url = WORLD_DIRECTORY . '?' . SITE_PARAM . '=' . urlencode(strtolower($site));
    // Aktion hinzufügen
    if ($action != '') {
    	$url .= '&' . ACTION_PARAM . '=' . urlencode(strtolower($action));
    }
    // zusätzliche Parameter hinzufügen
    if ($additionalParameters != '') {
    	// nach Parametern aufsplitten
    	if (strpos($additionalParameters, '&amp;') !== false) {
    		$params = explode('&amp;', $additionalParameters);
    	}
    	else {
    		$params = explode('&', $additionalParameters);
    	}
    	// Wert für Wert kodieren
    	foreach ($params as $key=>$param) {
    		$keyVal = explode('=', $param);
    		$params[$key] = urlencode($keyVal[0]) . '=' . urlencode($keyVal[1]);
    	}
    	// Parameter wieder zusammenfügen
    	$additionalParams = implode('&', $params);
    	$url .= '&' . $additionalParams;
    }
    
    return $url;
}