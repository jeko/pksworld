<?php
$user = World_Base::$USER;
if (isset($_GET['nr'])) {
	$num= intval($_GET['nr']);
	$pokemon = new World_PokemonInstance($num);
	if ($user->addPokemon($pokemon) !== false) {
		$user->getModule('messages')->push('Pokemon hinzugefügt (' . $num . ')');
	}
	else {
		$user->getModule('messages')->push('Pokemon hinzufügen fehlgeschlagen.');
	}
}