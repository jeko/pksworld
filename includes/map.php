<?php
$user = World_Base::$USER;

/** Map darstellen **/
$map = $user->getMap();

if (!($map instanceof World_Map)) {
	$user->error('Konnte Map nicht laden.', __FILE__, Error::CRIT);
	$map = new World_Map(1);
}

// Layerobjekte mit Bedingungen prüfen
$layerObjects = $map->getLayerObjects();

// Template aufbauen
$template->templateFile = 'map.html';
$template->templateMacro = 'map';
$template->contentTitle = $map->getAreaName();
$template->mapName = $map->getDisplayName();
$template->mapAreaName = $map->getAreaName();
$template->mapImagePath = $map->getImagePath();
$template->mapId = $map->getId();
$template->mapWidth = $map->getWidth();
$template->mapHeight = $map->getHeight();
$template->layerObjects = $layerObjects;

// Anschlussbilder vorladen
$accessList = $map->getAccessList();
$javascriptContent[] = 'var preloadMaps = new Array(';
foreach ($accessList as $mapId) {
	$preloadMap = new World_Map($mapId);
	$javascriptContent[] = '"' . $preloadMap->getImagePath() . '",';
}
$javascriptContent[] = 'false); preloadImages(preloadMaps, false);
changeContentTitle("worldContentTitle", "' . $map->getAreaName() . '");';

// Tooltips anzeigen
$javascriptContent[] = '$$("#mapLayer area").each(function(input) { new Tooltip(input); });';
