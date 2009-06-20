<?php
/**
 * Führt einen Kartenwechsel aus
 */

$user = World_Base::$USER;

// Mapwechsel prüfen
if (isset($_GET['map'])) {
	$targetMapId = intval($_GET['map']);
    if ($targetMapId !== false) {
       $user->changeMap($targetMapId);
    }
}