<?php
$user = World_Base::$USER;

/** Bietet eine Oberfläche zur Optimierung einzelner Komponenten wie
 * - Datenbank (Tabellenoptimierung)
 * - Neugenerierung von optimierten JS- und CSS-Dateien
 */

$template->templateFile = 'optimisation.html';
$template->contentTitle = 'Optimierung';
$template->viewMacro = 'admin';

if (isset($_POST['showList'])) {
	// Optimierung gewählt, Elementliste ermitteln
	switch ($_POST['selectAction']) {
		case 'database':
			$template->optimisationName = 'Datenbank';
			$template->optimisationIdentifier = 'database';
			$template->elementIdentifier = 'Tabellen';
			// Datenbanktabellen ermitteln
			$elements = World_Base::$DB->getTables(TABLE_PREFIX);
			break;
		case 'stylesheets':
			$template->optimisationName = 'CSS';
			$template->optimisationIdentifier = 'stylesheets';
			$template->elementIdentifier = 'Stylesheets';
			// Dateien ermitteln
			$pattern = CSS_STYLESHEETS_ABSOLUTE . '*.css';
			$elements = glob($pattern);
			break;
		case 'scripts':
			$template->optimisationName = 'JS';
			$template->optimisationIdentifier = 'scripts';
			$template->elementIdentifier = 'Scripts';
			// Dateien ermitteln
			$pattern = JS_SCRIPTS_ABSOLUTE . '*.js';
			$elements = glob($pattern);
			break;
		case 'sprites':
			$template->optimisationName = 'CSS-Sprites';
			$template->optimisationIdentifier = 'sprites';
			$template->elementIdentifier = 'Bilderverzeichnisse';
			// Sprite-Config auslesen
			$spriteConf = parse_ini_file(SPRITE_CONF_FILE, true);
			$elements = array();
			foreach ($spriteConf as $dirBase => $section) {
				$patternSize = $section['pattern_size'];
				$type = $section['type'];
				$imageType = $section['image_type'];
				$directories = explode(',', $section['directories']);
				foreach ($directories as $dir) {
					$dir = $dirBase . $dir;
					$elements[] = array(
            		  'name' => $dir,
            		  'value' => $dir,
            		  'checked' => false,
            		  'data' => $patternSize . ',' . $type . ',' . $imageType
					);
				}
			}
			break;
		case 'sessions':
			$template->optimisationName = 'Sessionmanagement';
			$template->optimisationIdentifier = 'sessions';
			$template->elementIdentifier = 'Sessions';
			$template->optimisationDescription = 'Abgelaufene Sessions löschen.';
			// Sessiontabellen anzeigen
			$elements = array();
			$elements[] = array(
			 'name' => 'Sessiondaten (' . TABLE_SESSION . ', ' . TABLE_SESSION_DATA . ')',
			 'value' => 'session_data',
			 'checked' => true,
			 'data' => false
			);
			break;
		default:
			$template->optimisationName = 'Nichts';
			$template->elementIdentifier = 'Nichts';
			$template->elements = false;
	}
	// Elemente zusammenführen
	$list = array();
	if ($elements !== false && is_array($elements)) {
		foreach ($elements as $element) {
			$el = array('name' => '', 'checked' => true, 'value' => '', 'data' => '');
			if (is_array($element)) {
				$el = $element;
			}
			else {
				$el['name'] = $element;
				$el['value'] = $element;
			}
			$list[] = $el;
		}
	}
	$template->list = $list;
	$template->templateMacro = 'list';
}
else if (isset($_POST['startOptimisation'])) {
	// Optimierung mit gewählten Elementen durchführen
	$result = '';
	switch ($_POST['optimisation']) {
		case 'database':
			$result .= 'Starte Tabellenoptimierung...' . "\n";
			for ($i=0; $i < $_POST['totalElements']; $i++) {
				if (!isset($_POST['element_' . $i])) continue;
				$result .= 'Tabelle ' . $_POST['element_' . $i];
				if (World_Base::$DB->optimizeTable(array($_POST['element_' . $i]))) {
					$result .=  ' optimiert.';
				}
				else {
					$result .= ' konnte nicht optimiert werden (Fehler, s. Log).';
				}
				$result .= "\n";
			}
			$template->elementIdentifier = 'Tabellen';
			$template->elementCount = $i + 1;
			break;
		case 'stylesheets': // Dateien nur zusammenführen
			$mainFileContent = '';
			$mainFileName = CSS_FILE_ABSOLUTE;
			$result .= 'Starte Dateioptimierung...' . "\n";

			// Dateien auslesen und anhängen
			for ($i=0; $i < $_POST['totalElements']; $i++) {
				if (!isset($_POST['element_' . $i])) continue;
				$file = $_POST['element_' . $i];
				$directory = dirname($file);
				$result .= 'Datei ' . basename($file);
				$mainFileContent .= file_get_contents($file);
				$result .= " angehängt.\n";
			}

			// Datei schreiben
			$result .= "\n" . 'Schreibe Hauptdatei ' . $mainFileName . '...';

			if (@file_put_contents($mainFileName, $mainFileContent) !== false) {
				$result .= ' Schreiben erfolgreich.' . "\n" . 'Bitte nicht vergessen, die Konstanten in constants.php nach dem Testen zu aktualisieren!';
			}
			else {
				$result .= ' Schreiben fehlgeschlagen.';
			}
			$template->elementIdentifier = 'Dateien';
			$template->elementCount = $i + 1;
			break;
		case 'scripts': // Dateien minifizieren und zusammenführen
			$mainFileContent = '';
			$mainFileName = JS_FILE_ABSOLUTE;

			$result .= 'Starte Dateioptimierung...' . "\n";
			for ($i=0; $i < $_POST['totalElements']; $i++) {
				if (!isset($_POST['element_' . $i])) continue;
				$file = $_POST['element_' . $i];
				$directory = dirname($file);
				$result .= 'Datei ' . basename($file);
				$fileContent = file_get_contents($file);
				if ($fileContent !== false) {
					$result .= ' ausgelesen. Optimiere...';
					$minified = JSMin::minify($fileContent);
					$mainFileContent .= $minified . "\n";
					$result .= ' optimiert und angehängt. Speichern...';
					if (@file_put_contents($directory . '/minified/' . basename($file), $minified) !== false) {
						$result .= ' gespeichert.';
					}
					else {
						$result .= ' Speichern fehlgeschlagen.';
					}
				}
				else {
					$result .= ' Auslesen fehlgeschlagen.';
				}
				$result .= "\n";
			}
			$template->elementIdentifier = 'Dateien';
			$template->elementCount = $i + 1;

			$result .= "\n" . 'Schreibe Hauptdatei ' . $mainFileName . '...';

			if (@file_put_contents($mainFileName, $mainFileContent) !== false) {
				$result .= ' Schreiben erfolgreich.' . "\n" . 'Bitte nicht vergessen, die Konstanten in constants.php nach dem Testen zu aktualisieren!';
			}
			else {
				$result .= ' Schreiben fehlgeschlagen.';
			}
			break;
		case 'sprites':
			$result .= 'Starte Spriteerstellung...' . "\n";
			for ($e=0; $e < $_POST['totalElements']; $e++) {
				if (!isset($_POST['element_' . $e])) continue;
				// Eingabeverzeichnisse
				$folder = $_POST['element_' . $e];
				$inputDir = IMG_PATH_ABSOLUTE . $folder;
				$saveTo = $inputDir . IMG_SPRITE_FILENAME;
				$data = explode(',', $_POST['element_data_' . $e]);
				$inputW = $data[0];
				$inputH = $inputW;
				$type = $data[1];
				$inputFormat = $data[2]; // alias imageType

				$result .= "Bearbeite Ordner $folder...";

				// Höheste PokemonId abfragen
				switch ($type) {
					case 'pokemon':
						$erg = World_Base::$DB->select(TABLE_CONST_POKEMON, array('MAX(id) AS highest'));
						break;
					case 'item':
						$erg = World_Base::$DB->select(TABLE_CONST_ITEM, array('MAX(id) AS highest'));
						break;
					case 'box':
                        $erg = World_Base::$DB->select(TABLE_CONST_BOX, array('MAX(id) AS highest'));
                        break;
				}
				if (!$erg) {
					$result .= 'Konnte MAX(ID) nicht abrufen.';
					continue;
				}

				$row = $erg->current();
				$highestId = $row->highest;
				$breakAfter = SPRITE_BREAK_AFTER;
				$maxSteps = ceil($highestId / $breakAfter);
				$step = 0;
				$ypos = 0;
				$xpos = 0;

				$spriteW = $maxSteps * $inputW;
				$spriteH = $breakAfter * $inputH;
				$spriteImg = imagecreate($spriteW, $spriteH);
				// Transparenter Hintergrund
				$bg = imagecolorallocatealpha($spriteImg, 255, 255, 255, 127);
				imagefill($spriteImg, 0, 0 , $bg);

				$result .= "Erstelle Sprite $inputW x $inputH aus $highestId Elementen...";

				// Durch alle Elemente gehen
				for ($i = 1; $i < $highestId; $i++) {
					$positionInBreak = $i % $breakAfter;
					$breakNumber = floor($i / $breakAfter);

					$xpos = $breakNumber * $inputW;
					$ypos = $positionInBreak * $inputH;
					$correctionX = 0;
					$correctionY = 0;

					// Bild ermitteln
					switch ($type) {
						case 'pokemon':
							$element = new World_Pokemon($i);
							$exists = ($element->getPokedexNumber() != 0);
							$file = $inputDir . $element->getPokedexName();
							break;
						case 'item':
							$element = new World_Item($i);
							$exists = ($element->getId() != 0);
							$file = $inputDir . $element->getDisplayName();
							break;
						case 'box':
                            $exists = true;
                            $file = $inputDir . 'Box' . $i;
                            break;
						default:
							// echo "Unbekannter Typ.\n";
							continue;

					}

					// echo " Teilbild ermittelt. ID $i:";
					if ($exists !== false) {
						// Pokemon existiert, daher Bild auslesen und einfügen
						// echo " Suche Bild...";
						switch (strtolower($inputFormat)) {
							case 'gif':
								$file .= '.gif';
								$elementImage = @imagecreatefromgif($file);
								break;
							case 'jpg':
							case 'jpeg':
								$file .= '.jpg';
								$elementImage = @imagecreatefromjpeg($file);
								break;
							case 'png':
								$file .= '.png';
								$elementImage = @imagecreatefrompng($file);
								break;
						}
					}
					// Wenn kein Bild vorhanden, Platzhalter einsetzen
					if (!$elementImage || $elementImage == null) {
						// echo ' Bild nicht gefunden, verwende Platzhalter.';
					}
					else {
						// Dimensionen ermitteln
						$pic = @getimagesize($file);
						if ($pic[0] < $inputW) {
							$correctionX = ($inputW - $pic[0]) / 2;
						}
						if ($pic[1] < $inputH) {
							$correctionY = ($inputH - $pic[1]) / 2;
						}
						// echo ' Bild gefunden.';

						// Bild kopieren
						@imagecopy($spriteImg, $elementImage,
						$xpos + $correctionX, $ypos + $correctionY,
						0, 0, $pic[0], $pic[1]);
						@imagedestroy($elementImage);
					}
					// echo "\n";
				}

				@imagealphablending($spriteImg, false);
				@imagesavealpha($spriteImg, true);

				// Bild ausgeben
				if (@imagepng($spriteImg, $saveTo, 9)) {
					$result .= " Sprite gespeichert unter\n\t$saveTo.\n";
				}
				else {
					$result .= " Sprite speichern nach\n\t$saveTo\nfehlgeschlagen.\n";
				}
				@imagedestroy($spriteImg);
			}
			$template->elementIdentifier = 'Grafiken';
			$template->elementCount = $e + 1;
			break;
			case 'sessions':
				$result = "Lösche abgelaufene Sessiondaten...\n";
				$template->elementIdentifier = 'Session';
				if (isset($_POST['element_0'])) {
					// Sessiondaten löschen
					$tables = array(TABLE_SESSION, TABLE_SESSION_DATA);
					$expiringTime = time() - SESSION_EXPIRING_TIME;
					$where = 'world_session.time < ' . $expiringTime . ' AND world_session.sid=world_session_data.sid';
					if (World_Base::$DB->deleteByWhereCrossTable($tables, $tables, $where)) {
						$result .= "Sessionvariablen gelöscht...\n";
						$where = 'time < ' . $expiringTime;
						if (World_Base::$DB->deleteByWhere(TABLE_SESSION, $where)) {
							$result .= "Sessions gelöscht.\n";
							$template->elementCount = World_Base::$DB->getAffectedRows();
						}
					}
					else {
						$result .= "Query fehlgeschlagen!\n";
						$template->elementCount = 0;
					}
				}
				break;
			default:
				break;
	}

	$template->result = $result;
	$template->templateMacro = 'result';
}
else {
	// Default-Oberfläche anzeigen
	$template->templateMacro = 'overview';
}