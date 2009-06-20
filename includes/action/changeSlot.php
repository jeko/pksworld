<?php
/**
 * Ändert die Position eines Pokemons im Team/Slot
 * Die GET-Parameter übermitteln die Slotnummern an 
 * neuer Position
 */

$user = World_Base::$USER;

if (isset($_GET['box'])) {
	$boxNr = intval($_GET['box']);
	// Box=0 bedeutet Team, daher Slotanordnung auf Teampokemon anwenden
	if ($boxNr == 0) {
		$pokemonTeam = $user->getModule('pokemonTeam');
		// Box existiert, GET-Parameter parsen
		parse_str($_GET['data']);
		// -> Array: $pokemonTeamListing
		$getParam = $pokemonTeamListing;
		$changedSlots = array();
		// Durch alle Slot gehen und prüfen ob sich Position verändert
		for ($i = 0; $i < World_PokemonTeam::MAX_POKEMON; $i++) {
			$newSlot = $getParam[$i];

			if ($newSlot != $i) {
				// Slots sind verschieden
				// Pokemons an den Slots tauschen
				if (!isset($changedSlots[$i])) {
					$pokemonTeam->flipSlot($i, $newSlot);
					$changedSlots[$newSlot] = $i;
				}
			}
		}
	}
	else {
		// Bei Lagerboxen prüfen, ob Map überhaupt über einen Lagerboxzugang verfügt
		$map = $user->getModule('map');
		if ($map->getFlags() & World_Map::FLAG_STORAGE_PC) {
			$boxes = $user->getModule('storeBox')->storeBoxes;
			if (isset($boxes[$boxNr]) && $boxes[$boxNr] instanceof World_StoreBox) {
				// Box existiert, GET-Parameter parsen)
				parse_str($_GET['data']);
				// -> Array: $pokemonTeamListing
				$getParam = $pokemonTeamListing;
				$box = $boxes[$boxNr];
				$changedSlots = array();
				// Durch alle Slot gehen und prüfen ob sich Position verändert
				for ($i = 0; $i < World_StoreBox::MAX_SLOTS; $i++) {
					$newSlot = $getParam[$i];
					if ($newSlot !== $i && !in_array($newSlot, $changedSlots)) {
						// Slots sind verschieden und noch nicht vertauscht worden
						// Pokemons an den zu vertauschenden Slots vertauschen
						// Slots freimachen
						$box->changeSlots($newSlot, $i);
						$changedSlots[] = $newSlot;
						$changedSlots[] = $i;
					}
				}
			}
		}
	}
}