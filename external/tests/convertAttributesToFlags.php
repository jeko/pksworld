<?php

// Auf false setzen nachdem Script seinen Zweck erfüllt hat.
// Andernfalls kann es zu erheblichem Datenverlust führen,
// wenn die Datenbank nicht mehr synchron läuft

$active = false;

if ($active === false) die("Script sollte nicht mehr ausgeführt werden (veraltet).");

/** START **/
// konvertiert Kartenattribute in die Flagform und schreibt das Flag in die DB

ob_start();
error_reporting(E_ALL);
session_start();
set_time_limit(0);

require_once('./../../constants.php'); // Konstanten
require_once(FUNC_PATH . 'autoload.php'); // Autoloader für Klassen
require_once(FUNC_PATH . 'buildSiteUrl.php'); // Autoloader für Klassen
require_once(WORLD . 'config.php'); // Konfiguration
World_Base::$DB = new DatabaseInterface($config['mySql']['host'], $config['mySql']['user'], $config['mySql']['pass'], $config['mySql']['db']);

// Maps auslesen
$mapList = World_Map::getMapList();

foreach ($mapList as $mapData) {
    $map = new World_Map($mapData['id']);
    
    if ($map->getId() !== 0) {
        $mapId = $map->getId();
        echo $map->getDisplayName() . ' ';
        $fields = array(
        'attr_indoor',
        'attr_storage_pc',
        'attr_trade',
        'attr_trainer_fight',
        'attr_heal'
        );
        $where = 'map_id=' . $mapId;
        
        if (World_Base::$DB->selectByWhere(TABLE_CONST_MAP_ATTRIBUTE, $fields, $where)) {
        	$row = World_Base::$DB->getRow();
        	$flags = 0;
            if ($row['attr_indoor'] == 1) $flags = $flags | World_Map::FLAG_INDOOR;
            if ($row['attr_storage_pc'] == 1) $flags = $flags | World_Map::FLAG_STORAGE_PC;
            if ($row['attr_trade'] == 1)    $flags = $flags | World_Map::FLAG_TRADE;
            if ($row['attr_trainer_fight'] == 1)   $flags = $flags | World_Map::FLAG_TRAINER_FIGHT;
            if ($row['attr_heal'] == 1)   $flags = $flags | World_Map::FLAG_HEAL;
            if (World_Base::$DB->updateByWhere(TABLE_CONST_MAP, array('flags' => $flags), $where)) {
            	echo " OK";
            }
            else {
            	echo "Writing Failed";
            }
        }
        else {
        	echo "Selecting Failed";
        }
        echo "<br />";
    }
}