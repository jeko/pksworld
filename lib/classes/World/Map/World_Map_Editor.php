<?php

/**
 * Lädt ein Karte zum Bearbeiten
 *
 * @author dominique
 *
 */

class World_Map_Editor extends World_Map
{
	private $_parser = null;
	private $_error = false;

	function __construct($mapId)
	{
		parent::__construct($mapId);
		$this->_parser = new World_Map_Parser();
	}

	function setDisplayName($newName)
	{
		$this->_displayName = $newName;
	}

	function setAreaName($newAreaName)
	{
		$this->_areaName = $newAreaName;
	}

	function setFlags($flags)
	{
		$this->_flags = $flags;
	}

	function setImage($imageName)
	{
		$this->_imageName = $imageName;
	}

	function setLayerCode($layerCode)
	{
		$this->_layerCode = $layerCode;
	}
	
    function setPkmnCode($pkmnrCode)
    {
        $this->_pkmnCode = $pkmnCode;
    }

	function parseLayerCode()
	{
		$this->_parser->setImage(IMG_MAPS_ABSOLUTE_BLANK . $this->_imageName);
		$this->_parser->compile($this->getLayerCode());
		if ($this->_parser->hasError() === false) {
			return true;
		}
		else {
			$this->error('Parsevorgang fehlgeschlagen.', __FILE__, Error::CRIT);
			$this->_error = true;
			return false;
		}
	}
	
	function parsePkmnCode()
	{
		return true;
	}

	function saveData()
	{
		if ($this->_error === false) {
			$this->_parser->saveImage(IMG_MAPS_ABSOLUTE . basename($this->getImagePath()));
			// Map-Daten schreiben
			$fieldsValues = array(
		 		'map_id' => $this->getId(),
		 		'name' => $this->getDisplayName(),
		 		'areaname' => $this->getAreaName(),
		 		'image' => basename($this->getImagePath()),
			    'flags' => $this->getFlags()
			);
			if (self::$DB->replace(TABLE_CONST_MAP, $fieldsValues)) {
				// Codes schreiben
				$fieldsValues = array(
		 				'map_id' => $this->getId(),
		 				'code' => $this->getLayerCode()
				);
				if (self::$DB->replace(TABLE_CONST_MAP_CODE, $fieldsValues)) {
					// LayerObjekte aus Datenbank löschen
					if (self::$DB->deleteByWhere(TABLE_CONST_MAP_OBJECT, 'map_id=' . $this->getId())) {
						// LayerObjekte schreiben
						$layerObjects = $this->_parser->getLayerObjects();
						foreach ($layerObjects as $lO) {
							$fieldsValues = array(
		 						    'map_id' => $this->getId(),
	 						        'name' => $lO->name,
	 						        'href' => $lO->href,
	 						        'text' => $lO->text,
	 						        'top' => $lO->y,
	 						        '`left`' => $lO->x,
	 						        'width' => $lO->w,
	 						        'height' => $lO->h,
								    'conditions' => $lO->getConditionsForQuery()
							);
							if (self::$DB->insert(TABLE_CONST_MAP_OBJECT, $fieldsValues) === false) {
								$this->error('Konnte LayerObjekt ' . $lO->name . ' nicht schreiben: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
							}
						}
						return true;
					}
					else {
						$this->error('Konnte LayerObjekte nicht löschen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
						return false;
					}
				}
				else {
					$this->error('Konnte MapCodes nicht schreiben: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
					return false;
				}
			}
			else {
				$this->error('Konnte MapDaten nicht schreiben: Query fehlgeschlagen', __FILE__, Error::DATABASE);
				return false;
			}
		}
		else {
			$this->error('Speichern der Karte aufgrund von Fehlern unterbunden.', __FILE__, Error::DATABASE);
			return false;
		}
	}
}

