<?php

$user = World_Base::$USER;
$pokemonObjects = $user->getTeamPokemon();
$pokemonTeam = array();

if (!is_array($pokemonObjects)) {
	$pokemonTeam = array(6);
	$user->error('Fehler beim Laden des Pokemonteams.', __FILE__, Error::WARN);
}
else {
	$spriteImg = IMG_POKEMON_SMALL_NORMAL_SPRITE;
	for ($i = 0; $i < World_PokemonTeam::MAX_POKEMON; $i++) {
		if (isset($pokemonObjects[$i]) && $pokemonObjects[$i] instanceof World_UserPokemon) {
			$spritePosition = getSpritePosition($spriteImg, $pokemonObjects[$i]->getPokedexNumber());
			$pkO = $pokemonObjects[$i];
			$pokemonTeam[$i] = array(
			 'displayName' => $pkO->getDisplayName(),
			 'spriteX' => $spritePosition['x'],
			 'spriteY' => $spritePosition['y'],
			 'maxKp' => $pkO->getMaxKp(),
			 'restKp' => $pkO->getKp(),
			 'level' => $pkO->getLevel(),
			 'ep' => $pkO->getExperiencePoints(),
			 'levelUpEp' => $pkO->getLevelUpEp()
			);
		}
		else {
			$pokemonTeam[$i] = false;
		}
	}
}

// Template aufbauen
$template->templateFile = 'team.html';
$template->templateMacro = 'list';
$template->contentTitle = 'Pokemonteam';
$template->sprite = $spriteImg;
$template->pokemonTeam = $pokemonTeam;

// Liste sortierbar machen (wenn GET-Parameter noSort nicht gesetzt)
if (!isset($_GET['noSortable'])) {
	$javascriptContent[] = '
	Sortable.create($("pokemonTeamListing"), {only: "teamPokemon", constraint: 0});
    ';
}