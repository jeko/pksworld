<?php

/**
 * Repräsentiert einen Parser für den Layercode einer Map
 *
 * @author dominique
 *
 */

class World_Map_Parser extends World_Base
{
	/**
	 * @var array Array von benutzbaren Tags
	 */
	private $_tags = array();
	public $layerCode = '';
    private $_layerObjects = array();
    private $_imagePath = '';
    private $_imageObj = false;
    private $_error = false;

	function __construct()
	{
		$this->loadData(null);
	}
	
	/**
	 * setzt das zu bearbeitende Bild
	 * @param $imagePath Pfad zum Bild
	 * @return unknown_type
	 */
	function setImage($imagePath)
	{
		if (file_exists($imagePath)) {
			$this->_imagePath = $imagePath;
			$this->_imageObj = @imagecreatefrompng($imagePath);
			if ($this->_imageObj !== false) {
				return true;
			}
			else {
				$this->error('Kann Bild ' . $imagePath . ' nicht öffnen.', __FILE__, Error::WARN);
				return false;
			}
		}
		else {
			$this->error('Kann Bild nicht setzen: ' . $imagePath . ' existiert nicht.', __FILE__, Error::WARN);
			return false;
		}
	}
	
	function saveData()
	{
		
	}

	function loadData($id = false)
	{
		if (self::$DB->select(TABLE_CONST_MAP_TAG, 'name')) {
			$tags = array();
			while ($row = self::$DB->getRow()) {
				$tags[] = $row['name'];
				self::$DB->next();
			}
			foreach ($tags as $tag) {
                $this->_tags[$tag] = new World_Map_ParserTag($tag);
			}
		}
		else {
			$this->error('Kann Parser-Tags nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			$this->_error = true;
			return false;
		}
	}

	function readAttributesFromLine($codeLine)
	{
		$attributes = array();
		// Atribute suchen (in der Form 'attribut="wert"')
		$attributeRegex = '@([a-z_]+)="([^"]*)"@';
		if (preg_match_all($attributeRegex, $codeLine, $attributeMatch)) {
			$attributeNames = $attributeMatch[1];
			$attributeValues = $attributeMatch[2];
			// Zu Array zusammenstellen
			foreach ($attributeNames as $key => $name) {
				$attributes[$name] = $attributeValues[$key];
			}
		}
		return $attributes;
	}

	/**
	 * überprüft ob das Array $atr (array('attribut1','attribut2',...))
	 * alle erforderlichen Attribute für den Tag $tagName enthält
	 * @param $atr assoc_array
	 * @param $tagName string
	 * @return bool
	 */
	function hasAllAttributes($attributes, $tagName) {
		if (isset($this->_tags[$tagName])) {
			$neededAttributes = $this->_tags[$tagName]->getAttributes();
			$optionalAttributes = $this->_tags[$tagName]->getOptionalAttributes();
			
			// optionale Attribute ignorieren
			foreach ($optionalAttributes as $optionalAttribute) {
				if (($key = array_search($optionalAttribute, $attributes)) !== false) {
					unset($attributes[$key]);
				}
			}
			
            $difference = array_diff($neededAttributes, $attributes);
	        
			if (count($difference) == 0) {
				return true;
			}
			else {
				$this->error('Falsche Attribute für ' . $tagName . '.', __FILE__, Error::WARN);
				return false;
			}
		}
		else {
			$this->error('Tag ' . $tagName . ' ist nicht definiert.', __FILE__, Error::WARN);
			return false;
		}
	}

	/**
	 * konvertiert Code in ein LayerObjekt
	 * @param $codeLine string
	 * @return string
	 */
	function compileLine($codeLine)
	{
		$layerObject = false;
		// feststellen, ob Zeile ein Tag enthält
		// '<', '>' und sonstige Zeichen ausserhalb des Tags entfernen
		$lineRegex = '@.*<([a-z]+) .*>.*@i';
		if (preg_match($lineRegex, $codeLine, $lineMatch)) {
			$tagName = $lineMatch[1];
			$attributes = $this->readAttributesFromLine($codeLine);

			// Attribute überprüfen
			if ($this->hasAllAttributes(array_keys($attributes), $tagName)) {
				switch ($tagName) {
					case 'pfeil':
                        $graphic = GAME_GRAPHICS_ABSOLUTE_PATH . 'pfeil_' . $attributes['richtung'] . '.png';
                        if (file_exists($graphic)) {
                            $dimensions = getimagesize($graphic);
                            $layerObject = new World_Map_LayerObject('pfeil_' . $attributes['richtung'], $attributes['left'], $attributes['top'], $dimensions[0], $dimensions[1], $attributes['hover'], buildSiteUrl('map', 'changemap', 'map=' . World_Map::getIdFromName($attributes['to'])));
                            $this->drawOnImage($graphic, $attributes['left'], $attributes['top']);
                        }                   
                        break;
					case 'olink':
						$layerObject = new World_Map_LayerObject('olink', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('map', 'changemap', 'map=' . World_Map::getIdFromName($attributes['to'])));
						break;
				    case 'umove':
                        // $layerObject = new World_Map_LayerObject('umove', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], '?site=attack&id=' . urlencode($attributes['move']));
                        // break;
					case 'hover':
                        $layerObject = new World_Map_LayerObject('hover', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover']);
						break;
                    case 'plink':
                        $layerObject = new World_Map_LayerObject('plink', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], '...', buildSiteUrl('fight', '', 'field=' . $attributes['field']));
                        break;
                    case 'plinka':
                        $layerObject = new World_Map_LayerObject('plinka', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], '...', buildSiteUrl('fight', '', 'field=' . $attributes['field']));
                        break;
                    case 'pokebox':
                        $layerObject = new World_Map_LayerObject('pokebox', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('storagePc'));
                        break;
                    case 'heal':
                        $layerObject = new World_Map_LayerObject('heal', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('map', 'heal'));
                        break;
                    case 'shop':
                        $layerObject = new World_Map_LayerObject('shop', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('shop', '', 'id=' . $attributes['shopid']));
                        break;
                    case 'pokeshop':
                        $layerObject = new World_Map_LayerObject('pokeshop', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('pokeshop', '', 'id=' . $attributes['shopid']));
                        break;
                    case 'tkampf':
                        $layerObject = new World_Map_LayerObject('tkampf', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('trainerFight'));
                        break;
                    case 'tausch':
                        $layerObject = new World_Map_LayerObject('trade', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('trade'));
                        break;
                    case 'giveitem':
                        $layerObject = new World_Map_LayerObject('giveitem', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('map', 'giveItem', 'id=' . $attributes['itemid']), $attributes['itemdid']);
                        break;
                    case 'img':
                    	$graphic = GAME_GRAPHICS_ABSOLUTE_PATH . $attributes['src'];
                    	if (file_exists($attributes['src'])) {
                    		$dimensions = getimagesize($attributes['src']);
                            $layerObject = new World_Map_LayerObject('img', $attributes['left'], $attributes['top'], $dimensions[0], $dimensions[1], $attributes['hover']);
                            $this->drawOnImage($attributes['src'], $attributes['left'], $attributes['top']);
                    	}
                        break;
                    case 'npc':
                        $layerObject = new World_Map_LayerObject('npc', $attributes['left'], $attributes['top'], $attributes['width'], $attributes['height'], $attributes['hover'], buildSiteUrl('npc', 'id=' . $attributes['id']));
                        break;
					default:
						$layerObject = false;
				}
				
				if ($layerObject !== false) {
					// optionale Attribute verarbeiten
					$optionalAttributes = $this->_tags[$tagName]->getOptionalAttributes();
					foreach ($optionalAttributes as $optAttr) {
						if (isset($attributes[$optAttr])) {
							$attributeValue = $attributes[$optAttr];
							switch ($optAttr) {
								case 'condition_attack':
									$layerObject->addCondition('attack', $attributeValue);
									break;
								case 'condition_item':
									$layerObject->addCondition('item', $attributeValue);
									break;								
							}
						}
					}
				}
			}
		}
		return $layerObject;
	}
	
	function drawOnImage($imagePath, $x, $y)
	{
		if ($this->_imageObj) {
			if (file_exists($imagePath)) {
				$img = @imagecreatefrompng($imagePath);
				$dimensions = getimagesize($imagePath);
				$imgW = $dimensions[0];
				$imgH = $dimensions[1];
				if ($img) {
					if (@imagecopy($this->_imageObj, $img, $x, $y, 0, 0, $imgW, $imgH)) {
						return true;
					}
					else {
						$this->error('Konnte LayerObjekt-Bild nicht kopieren.', __FILE__, Error::WARN);
						$this->_error = true;
						return false;
					}
				}
				else {
					$this->error('Konnte LayerObjekt-Bild nicht öffnen.', __FILE__, Error::WARN);
					$this->_error = true;
					return false;
				}
			}
			else {
				$this->error('LayerObjekt-Bild scheint nicht zu existieren.', __FILE__, Error::WARN);
				$this->_error = true;
				return false;
			}
		}
		else {
			$this->error('Kartenbild ist nicht geöffnet.', __FILE__, Error::FATAL);
			$this->_error = true;
			return false;
		}
	}
	
	function hasError()
	{
		return $this->_error;
	}
	
	function saveImage($destination)
	{
		if (@ImagePNG($this->_imageObj, $destination)) {
			return true;
		}
		else {
			$this->error('Konnte Kartenbild nicht speichern.', __FILE__, Error::ERR);
			$this->_error = true;
			return false;
		}
	}

	/**
	 * konvertiert LayerCode zu LayerObjekten
	 * und erstellt die Map entsprechend
	 * @param $layerCode string
	 * @return string
	 */
	function compile($layerCode)
	{
		$layerObjects = array();
		// \r\n, \r zu \n
		$layerCode = str_replace(array("\r\n", "\r"), "\n", $layerCode);
		// Nach Zeilen aufsplitten und Zeilenweise parsen
		$codeLines = explode("\n", $layerCode);
		foreach ($codeLines as $line) {
			if (($layerObj = $this->compileLine($line)) !== false) {
				$layerObjects[] = $layerObj;
			}
		}

		$this->_layerObjects = $layerObjects;
		return true;
	}
	
	function getLayerObjects()
	{
		return $this->_layerObjects;
	}
}

