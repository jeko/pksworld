<?php
$user = World_Base::$USER;

$template->templateFile = 'mapEditor.html';
$template->templateMacro = 'mapEditor';
$template->contentTitle = 'MapEditor';
$template->viewMacro = 'admin';

// MapId ermitteln
if (isset($_POST['selectMap'])) {
    // Karte auswählen
    $mapId = intval($_POST['mapList']);
}
else if (isset($_GET['mapId'])) {
	$mapId = intval($_GET['mapId']);
}
else {
	$mapId = false;
}

// Formularverarbeitung
if (isset($_POST['saveMap'])) {
	$map = new World_Map($mapId);
	
	if ($map->getId() !== 0) {
		$editor = new World_Map_Editor($mapId);
		// Mapdaten
        $editor->setDisplayName($_POST['mapName']);
        $editor->setAreaName($_POST['mapAreaName']);
        $editor->setImage($_POST['mapImageName']);
        $editor->setLayerCode($_POST['mapLayerCode']);
        $editor->setPkmnCode($_POST['mapPkmnCode']);
        // Attribute
        $flags = 0;
        if (isset($_POST['mapAttributeIndoor'])) $flags = $flags | World_Map::FLAG_INDOOR;
        if (isset($_POST['mapAttributeStoragePc'])) $flags = $flags | World_Map::FLAG_STORAGE_PC;
        if (isset($_POST['mapAttributeTrade'])) $flags = $flags | World_Map::FLAG_TRADE;
        if (isset($_POST['mapAttributeTrainerFight'])) $flags = $flags | World_Map::FLAG_TRAINER_FIGHT;
        if (isset($_POST['mapAttributeHeal'])) $flags = $flags | World_Map::FLAG_HEAL;

        $editor->setFlags($flags);
        // LayerCode parsen
        $editor->parseLayerCode();
        // PkmnCode parsen
        $editor->parsePkmnCode();
        if ($editor->saveData()) {
        	$template->successMessage = 'Karte erfolgreich gespeichert.';
        }
        else {
        	$template->failedMessage = 'Es trat ein Fehler beim Speichern der Karte auf; überprüfe die Logdateien für mehr Informationen.';
        }
	}
}

// Map laden
if ($mapId !== false) {
	$map = new World_Map($mapId);
	
	if ($map->getId() !== 0) {
		$mapData = array();
        $mapData['id'] = $map->getId();
        $mapData['name'] = $map->getDisplayName();
        $mapData['areaName'] = $map->getAreaName();
        $mapData['imageName'] = basename($map->getImagePath());
        $mapData['layerCode'] = $map->getLayerCode();
        $mapData['pkmnCode'] = $map->getPkmnCode();
        // Flags setzen
        $flags = $map->getFlags();
        $mapData['attributes'] = array();
        $mapData['attributes']['indoor'] = ($flags & World_Map::FLAG_INDOOR)? true : false ;
        $mapData['attributes']['storagePc'] = ($flags & World_Map::FLAG_STORAGE_PC)? true : false ;
        $mapData['attributes']['trade'] = ($flags & World_Map::FLAG_TRADE)? true : false ;
        $mapData['attributes']['trainerFight'] = ($flags & World_Map::FLAG_TRAINER_FIGHT)? true : false ;
        $mapData['attributes']['heal'] = ($flags & World_Map::FLAG_HEAL)? true : false ;
        
        $template->mapId = $mapId;
        $template->mapData = $mapData;
	}
}

$template->mapList = World_Map::getMapList();