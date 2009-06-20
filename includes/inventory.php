<?php
$user = World_Base::$USER;

$inventoryObject = $user->getInventory();
$userItems = array();

// Vorhandene Itemgruppen ermitteln
$itemGroups = $inventoryObject->getItemGroups();
$itemGroup = 1;
foreach ($itemGroups as $key => $iG) {
	$itemGroups[$key] += array('link' => buildSiteUrl('inventory', '', 'itemGroup=' . $iG['id']));
}

// Gewählte Itemgruppe überprüfen und verwenden
if (isset($_GET['itemGroup'])) {
	if (isset($itemGroups[$_GET['itemGroup']])) {
		$itemGroup = intval($_GET['itemGroup']);
	}
}

// Items auslesen
foreach($inventoryObject->getItemStacks() as $itemStack) {
	if($itemStack->getItemGroup() == $itemGroup) {
		$userItems[] = array(
			'name'	=>	$itemStack->getName(),
			'quantity'	=>	$itemStack->getQuantity(),
		    'spriteCoords' => getSpritePosition(IMG_ITEMS_NORMAL_SPRITE, $itemStack->getItem()->getId())
		);
	}
}

// Template aufbauen
$template->templateFile = 'inventory.html';
$template->templateMacro = 'inventory';
$template->contentTitle = 'Beutel';
$template->items = $userItems;
$template->itemGroups = $itemGroups;
$template->selectedItemGroup = $itemGroup;
$template->sprite = IMG_ITEMS_NORMAL_SPRITE;