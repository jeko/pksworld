<?php
/**
 * gibt dem Benutzer ein Item das auf einer Karte definiert wurde
 */

$user = World_Base::$USER;
$map = $user->getModule('map');
$itemId = $_GET['id'];

// Item überprüfen
if ($map->giveItemExists($itemId)) {
    $inventory = $user->getModule('inventory');
    $item = new World_Item($itemId);
    $inventory->addItem($item, 1);
}