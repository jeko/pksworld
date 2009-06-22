<?php

$user = World_Base::$USER;
$battle = new World_Fight;

// Neuen Kampf starten
if ($battle->started() === false) {
	if (isset($_GET['field'])) {
		// Wildkampf starten
		if ($battle->setWildFight($_GET['field']) === false) {
			$battle = false;
		}
	}
	else if (isset($_GET['opponentId'])) {
		// Trainerkampf starten
		if ($battle->setTrainerFight($_GET['opponentId']) === false) {
			$battle = false;
		}
	}
}

// Kampf anzeigen
if ($battle !== false) {
    // Daten zusammentragen und Template aufbauen
    
    /** User **/
	$userData = array();
    $userData['userName'] = '';
	$userData['trainerImg'] = '';
    $userData['pokemon'] = array();
    $userData['pokemon']['name'] = '';
    $userData['pokemon']['pokedexName'] = '';
    $userData['pokemon']['img'] = '';
    $userData['pokemon']['statusImg'] = '';
    $userData['pokemon']['restKp'] = '';
    $userData['pokemonTeam'] = array();
    $pokemonObjects = $user->getTeamPokemon();
    for ($i = 0; $i < World_PokemonTeam::MAX_POKEMON; $i++) {
        if (isset($pokemonObjects[$i]) && $pokemonObjects[$i] instanceof World_UserPokemon) {
            $userData['pokemonTeam'][$i] = array('displayName' => $pokemonObjects[$i]->getDisplayName());
        }
        else {
            $userData['pokemonTeam'][$i] = null;
        }
    }
    
    /** Opponent **/
	$opponentData = array();
    $opponentData['userName'] = '';
    $opponentData['trainerImg'] = '';
    $opponentData['pokemon'] = array();
    $opponentData['pokemon']['name'] = '';
    $opponentData['pokemon']['pokedexName'] = '';
    $opponentData['pokemon']['img'] = '';
    $opponentData['pokemon']['statusImg'] = '';
    $opponentData['pokemon']['restKp'] = '';

	/** Battleground **/
    $bgData = array();
	$bgData['img'] = '';
	
    $template->templateFile = 'fight.html';
    $template->templateMacro = 'fight';
    $template->fight->user = $userData;
    $template->fight->opponent = $opponentData;
    $template->fight->battleground = $bgData;
}
else {
	include('map.php');
}
