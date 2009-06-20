<?php
$user = World_Base::$USER;

$template->templateFile = 'teleport.html';
$template->templateMacro = 'teleport';
$template->contentTitle = 'Teleport';
$template->mapList = World_Map::getMapList();

/** Benutzer teleportieren **/
if (isset($_POST['changeMap'])) {
	$targetMapId = $_POST['changeMap'];
    if ($targetMapId !== false) {
       $user->changeMap($targetMapId, true);
       $template->successMessage = 'Benutzer erfolgreich auf Karte # ' . $targetMapId . ' teleportiert';
    }
    else {
    	$template->failedMessage = 'Karte # ' . $targetMapId  . ' wurde nicht gefunden. Teleport fehlgeschlagen.';
    }
}