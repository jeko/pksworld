<?php

/**
 * heilt alle Teampokemon des Benutzers wenn auf der Karte ein
 * Heilspot verfügbar ist
 */

$user = World_Base::$USER;

if ($user->getMap()->getFlags() & World_Map::FLAG_HEAL) {
	$team = $user->getTeamPokemon();
	// Jedes Pokemon heilen
	foreach ($team as $teamPokemon) {
		$teamPokemon->removeTempChange(); // Alle Statusveränderungen entfernen
		$teamPokemon->setKp(); // KP wieder auffüllen
	}

	$user->getModule('messages')->push('Dein Team wurde vollständig geheilt.');
}