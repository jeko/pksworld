<?php
/**
 * Ermittelt die Position des Pokemons mit der
 * Pokedexnummer $id auf einem CSS-Sprite. Gibt 
 * ein array('x', 'y') zurück.
 * @param $id integer
 * @return assoc_array
 */
function getSpritePosition($spriteImg, $fieldNumber)
{	
	$position = array('x'=>0, 'y'=>0);
	// Settings auslesen zur Bestimmung der Patterngrösse
	$settings = parse_ini_file(SPRITE_CONF_FILE, true);
	foreach ($settings as $dirBase => $section) {
		$basePos= strpos($spriteImg, IMG_PATH . $dirBase);
        if ($basePos !== false && $basePos === 0) {
			$directories = explode(',', $section['directories']);
			$patternSize = $section['pattern_size'];
		}
	}
	// Position berechnen
	if (isset($patternSize)) {
	    $breaks = floor($fieldNumber / SPRITE_BREAK_AFTER);
	    $pos = ($fieldNumber % SPRITE_BREAK_AFTER);
	    if ($pos == 0) $pos = SPRITE_BREAK_AFTER;
	    
	    $position['x'] = $breaks * $patternSize;
	    $position['y'] = $pos * $patternSize;
	}
	return $position;
}