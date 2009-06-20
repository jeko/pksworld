<?php

/**
 * Repräsentiert eine Karte
 *
 * @author dominique
 *
 */

class World_Map extends World_Base
{
	/**
	 * Id der Map in der Datenbank
	 * @var integer
	 */
	protected $_id = 0;
	/**
	 * Anzeigename der Map
	 * @var string
	 */
	protected $_displayName = '';
	/**
	 * Name der Region
	 * @var string
	 */
	protected $_areaName = '';
	/**
	 * Pfad zum Bild der Map
	 * @var string
	 */
	protected $_imageName = '';
	/**
	 * Code für den Objektlayer über der Map
	 * @var string
	 */
	protected $_layerCode = null;

	protected $_pkmnCode = null;

	protected $_layerObjects = null;
	/**
	 * HTML-Code für den Objektlayer
	 * @var string
	 */
	protected $_layerCodeHtml = '';
	/**
	 * Kartenattribute
	 * @var assoc_array
	 */
	protected $_flags = 0;
	/**
	 * Array von MapIds zu Zugang zu dieser Map haben
	 * @var unknown_type
	 */
	protected $_accessMaps = array();
	/**
	 * Wenn keine MapId angegeben wird, diese Map laden
	 * @var integer
	 */
	const DEFAULT_MAP = 1;
	// Flags
	const FLAG_INDOOR = 1;
	const FLAG_STORAGE_PC = 2;
	const FLAG_TRADE = 4;
	const FLAG_TRAINER_FIGHT = 8;
	const FLAG_HEAL = 16;

	function __construct($mapId = false)
	{
		if ($mapId !== false) {
			$this->loadData($mapId);
		}
	}

	function loadData($mapId = false)
	{
		if ($mapId === false) {
			$mapId = self::$USER->getPos();
		}

		// Kartendaten auslesen
		$tables = TABLE_CONST_MAP . ' AS m';

		$jointables = array(
		TABLE_CONST_MAP_ACCESS . ' AS m_acc',
		TABLE_CONST_MAP_CODE . ' AS m_cod'
		);

		$onclauses = array(
        'm_acc.map_id_from = m.map_id',
        'm_cod.map_id = m.map_id'
        );
         
        $fields = array(
		'm.name AS name',
		'm.areaname AS areaname',
		'm.image AS image',
        'm.flags AS flags'		
        );

        $where = 'm.map_id=' . $mapId;

        if (self::$DB->selectByJoin($tables, $fields, $jointables, $onclauses, 'INNER JOIN', $where)) {
        	$row = self::$DB->getRow();
        	$this->_displayName = $row['name'];
        	$this->_areaName = $row['areaname'];
        	$this->_id = $mapId;
        	$this->_flags = $row['flags'];
        	$this->_imageName = $row['image'];
        	return true;
        }
        else {
        	$this->error('Konnte Kartendaten nicht laden: Query fehlgeschlagen.', Error::DATABASE, __FILE__);
        	return false;
        }
	}
	/**
	 * liest die LayerObjekte der Karte $mapId von der
	 * Datenbank in den internen Speicher
	 * Gibt den Erfolg zurück.
	 * @param $mapId integer
	 * @return bool
	 */
	function readLayerObjects($mapId = null)
	{
		if ($mapId == null) {
			$mapId = $this->getId();
		}
		$this->_layerObjects = array();
		// Layer-Objekte der Karte abfragen
		$fields = array('name', 'href', 'text', 'top', '`left`', 'width', 'height', 'conditions');
		if (self::$DB->selectByWhere(TABLE_CONST_MAP_OBJECT, $fields, 'map_id=' . $mapId)) {
			if (self::$DB->getNumRows() > 0) {
				while (($row = self::$DB->getRow()) !== false) {
					$layerObject = new World_Map_LayerObject($row['name'], $row['left'], $row['top'], $row['width'], $row['height'], $row['text'], $row['href']);
					// Bedingungen auslesen
					if (strpos($row['conditions'], '=') !== false) {
						$conditionStrings = explode(',', $row['conditions']);
						foreach ($conditionStrings as $conditionString) {
							$condition = explode('=', $conditionString);
							if (count($condition) == 2) {
								$layerObject->addCondition($condition[0], $condition[1]);
							}
						}
					}
					$this->_layerObjects[] = $layerObject;
					self::$DB->next();
				}
			}
			$this->processLayerObjectConditions();
			return true;
		}
		else {
			$this->error('Konnte LayerObjekte nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return false;
		}
	}
	function processLayerObjectConditions()
	{
		$layerObjects = $this->getLayerObjects();
		foreach ($layerObjects as $key => $layerObj) {
			if ($layerObj->hasConditions()) {
				// Feld hat Bedingungen definiert
				$layerObjConditions = $layerObj->getConditions();
				foreach ($layerObjConditions as $layerObjCond => $condValue) {
					// Erfüllung der Bedingungen für diesen Benutzer prüfen
					switch ($layerObjCond) {
						case 'attack':
							if (self::$USER->attackIsAvailable($condValue) === false) {
								unset($layerObjects[$key]);
							}
							break;
						case 'item':
							if (self::$USER->hasItem($condValue) === false) {
								unset($layerObjects[$key]);
							}
							break;
					}
				}
			}
		}
		$this->_layerObjects = $layerObjects;
	}
	/**
	 * lädt Layercode und Pkmn-Block-Code
	 * @return unknown_type
	 */
	function loadMapCode()
	{
		$table = TABLE_CONST_MAP_CODE;
		$fields = array('code', 'pkmn_block');
		$where = 'map_id=' . $this->getId();
		if (self::$DB->selectByWhere($table, $fields, $where)) {
			if (self::$DB->getNumRows() > 0) {
				$row = self::$DB->getRow();
				$this->_layerCode = $row['code'];
				$this->_pkmnCode = $row['pkmn_block'];
				return true;
			}
		}
		else {
			$this->error('Kann Mapcode nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
		}
		$this->_layerCode = '';
		$this->_pkmnCode = '';
		return false;
	}

	/**
	 * gibt die LayerObjects zurück
	 * @return assoc_array
	 */
	function getLayerObjects()
	{
		if ($this->_layerObjects === null) {
			$this->readLayerObjects($this->getId());
		}
		return $this->_layerObjects;
	}

	/**
	 * liefert die Breite des Kartenbilds in Pixeln
	 * @return integer
	 */
	function getWidth()
	{
		if (file_exists($this->getAbsoluteImagePath())) {
			$dimensions = @getimagesize($this->getAbsoluteImagePath());
			if ($dimensions) {
				return $dimensions[0];
			}
			else {
				$this->error('Konnte Kartendimensionen nicht ermitteln.', __FILE__, Error::ERR);
				return false;
			}
		}
	}
	/**
	 * liefert die Höhe des Kartenbilds in Pixeln
	 * @return integer
	 */
	function getHeight()
	{
		if (file_exists($this->getAbsoluteImagePath())) {
			$dimensions = @getimagesize($this->getAbsoluteImagePath());
			if ($dimensions) {
				return $dimensions[1];
			}
			else {
				$this->error('Konnte Kartendimensionen nicht ermitteln.', __FILE__, Error::ERR);
				return false;
			}
		}
	}

	function saveData()
	{
	}

	/**
	 * gibt die ID der Map zurück.
	 * @return integer
	 */
	function getId()
	{
		return $this->_id;
	}
	function getDayTime()
	{
		// TODO mit world_settings verknüpfen
		$hours = intval(date('h'));
		if ($hours > 18 & $hours < 6) {
			$dayTime = 'night';
		}
		else {
			$dayTime = 'day';
		}
		return $dayTime;
	}
	/**
	 * gibt die Kartenflags zurück
	 * @return integer
	 */
	function getFlags()
	{
		return $this->_flags;
	}
	/**
	 * gibt den Anzeigename der Karte zurück
	 * @return string
	 */
	function getDisplayName()
	{
		return $this->_displayName;
	}
	/**
	 * gibt den Gebietsnamen zurück
	 * @return string
	 */
	function getAreaName()
	{
		return $this->_areaName;
	}
	/**
	 * gibt den Bildpfad der Karte zurück,
	 * relativ zur Domain
	 * @return string
	 */
	function getImagePath()
	{
		return IMG_MAPS . $this->_imageName;
	}
	/**
	 * gibt den absoluten Bildpfad der Karte zurück
	 * @return string
	 */
	function getAbsoluteImagePath()
	{
		return IMG_MAPS_ABSOLUTE . $this->_imageName;
	}
	/**
	 * gibt ein Array von Kartenids zurück,
	 * zu welchen diese Karte Zugang hat
	 * @return array
	 */
	function getAccessList()
	{
		$accessList = array();
        if (self::$DB->selectByWhere(TABLE_CONST_MAP_ACCESS, 'map_id_to', 'map_id_from=' . $this->getId())) {
        	while ($row = self::$DB->getRow()) {
        		$accessList[] = $row['map_id_to'];
        		self::$DB->next();
        	}
        }
        return $accessList;
	}
	/**
	 * gibt das fertige HTML für diese
	 * Karte zurück
	 * @return string
	 */
	function getParsedHtml()
	{
		return $this->_layerCodeHtml;
	}

	/**
	 * gibt zurück, ob Map $sourceMap Zugang zu dieser
	 * Map hat.
	 * @param $sourceMap World_Map
	 * @return bool
	 */
	function hasAccess($sourceMap)
	{
		if (in_array($sourceMap->getId(), $this->_accessMaps)) {
			return true;
		}
		else {
			return false;
		}
	}

	function canAccess($targetMapId)
	{
		if (self::$DB->selectByWhere(TABLE_CONST_MAP_ACCESS, '1', 'map_id_from=' . $this->getId() . ' AND map_id_to=' . $targetMapId)) {
			if (self::$DB->getNumRows() > 0) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			$this->error('Kann Zugang nicht prüfen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return false;
		}
	}

	/**
	 * gibt den Layercode zurück
	 * @return string
	 */
	function getLayerCode()
	{
		if ($this->_layerCode === null) {
			$this->loadMapCode();
		}
		return $this->_layerCode;
	}

	function getPkmnCode()
	{
		if ($this->_pkmnCode === null) {
			$this->loadMapCode();
		}
		return $this->_pkmnCode;
	}

	/**
	 * ermittelt die ID der Karte mit dem Namen $name
	 * @param $name
	 * @return integer
	 */
	static function getIdFromName($name)
	{
		if (self::$DB->selectByWhere(TABLE_CONST_MAP, 'map_id', 'name="' . $name . '"')) {
			$row = self::$DB->getRow();
			if ($row) {
				return $row['map_id'];
			}
			else {
				return false;
			}
		}
	}

	/**
	 * gibt eine Liste aller Karten zurück
	 * @return array Array in der Form array( array('id'=>#, 'name'=>""), ...)
	 */
	static function getMapList()
	{
		$mapList = array();
		if (self::$DB->select(TABLE_CONST_MAP, array('map_id', 'name'), 'ORDER BY name ASC')) {
			while ($row = self::$DB->getRow()) {
				$mapList[] = array('id' => $row['map_id'], 'name' => $row['name']);
				self::$DB->next();
			}
			return $mapList;
		}
		else {
			$this->error('Konnte Kartenliste nicht abrufen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return $mapList;
		}
	}
}

