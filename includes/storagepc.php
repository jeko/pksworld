<?php
$user = World_Base::$USER;
$storeBox = $user->getStoreBox();
$spriteImg = IMG_BOX_SMALL . IMG_SPRITE_FILENAME;
$spriteImgBig = IMG_BOX_BIG . IMG_SPRITE_FILENAME;
$boxCount = 2;
$boxes = array();
$storeBoxes = array();

$template->templateFile = 'storagepc.html';
$template->contentTitle = 'Lagerbox';

// Boxen auslesen und templatefähig aufbauen
foreach($storeBox->storeBoxes as $key => $value)
{
	$storeBoxes[$key] = array(
         'nr' => $key,
         'name' => $value->getBoxName(),
         'spritePos' => getSpritePosition($spriteImg, $key+1)
	);
}

// Anhand von Get-Parametern bestimmen
// ob alle Boxen dargestellt werden müssen
// oder nur eine.
if (isset($_GET['box']) && isset($_GET['boxIndex'])) {
    $storageBoxNr = intval($_GET['box']);
    $boxIndex = intval($_GET['boxIndex']);
	$boxCount = $boxIndex+1;
	$template->templateMacro = 'singleBox';
}
else {
	$boxIndex = 0;
	$storageBoxNr = 0;
    $template->templateMacro = 'displayPc';
}

// Gewählte Boxen auslesen
for (; $boxIndex < $boxCount; $boxIndex++) {
	
    $currentBoxNr = $storageBoxNr;
    
	// Pokemon auslesen
	$boxPokemon = array();
	for($i = 0; $i < World_StoreBox::MAX_SLOTS; $i++)
	{
		if (!$storeBox->storeBoxes[$currentBoxNr]->slotIsFree($i)) {
			$pokemon = $storeBox->storeBoxes[$boxNr]->getPokemonAtSlot($i);

			$boxPokemon[$i] = array(
		         'displayName' => $pokemon->getDisplayName(),
		         'spritePos' => getSpritePosition($spriteImg, $pokemon->getPokedexNumber()),
		         'maxKp' => $pokemon->getMaxKp(),
		         'restKp' => $pokemon->getKp(),
		         'level' => $pokemon->getLevel(),
		         'ep' => $pokemon->getExperiencePoints(),
		         'levelUpEp' => $pokemon->getLevelUpEp()
			);
		}
		else {
			$boxPokemon[$i] = false;
			continue;
		}
	}

	// Speichern
	$box = array();

	$box['boxPokemon'] = $boxPokemon;
	$box['currentBoxNr'] = $currentBoxNr;
	$box['box'] = $storeBoxes[$currentBoxNr];
	$box['index'] = $boxIndex;
	$boxes[] = $box;
	$javascriptContent[] = 'initializeStoragePc(storagePcContainerIdBase + ' . $boxIndex . ');';
}

$template->bigSprite = $spriteImgBig;
$template->sprite = $spriteImg;
$template->maxBoxes = count($storeBoxes);
$template->storeBoxes = $storeBoxes;
$template->boxes = $boxes;
$template->box = $boxes[0]; // für singlebox-View


$javascriptContent[] = '
// Team öffnen und Lagerbox initialisieren
loadSideBoxContent("' . buildSiteUrl('team', '', 'noSortable=true') . '", initializeStoragePcPokemonTeam);
';