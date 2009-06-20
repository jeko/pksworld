<?php

/**
 * Repräsentiert eine Lagerbox
 * @author dominique
 *
 */

class World_StoreBox extends World_Base
{
	/**
	 * Beinhaltet die Slots als 2-dim$key=>ensionale Matrix
	 * @var array
	 */
	protected $_slots = null;
	protected $_owner = null;
	protected $_boxId = null;
	protected $_displayName = '';
	protected $_image = '';
	/**
	 * Maximale Anzahl Slots
	 * @var integer
	 */
	const MAX_SLOTS = 20;

	function __construct($owner, $boxId)
	{
		$this->_owner = $owner;
		$this->loadData($boxId);
	}

	function loadData($boxId = false)
	{
		if ($boxId !== false) {
			$this->_boxId = $boxId;
			$table = TABLE_CONST_BOX . ' AS cbox';
			$fields = array('cbox.name as box_name','cbox.image as box_image');
			$where = 'cbox.id = ' . $boxId;

			if (self::$DB->selectByWhere($table, $fields, $where)) {
				if (self::$DB->getNumRows() > 0) {
					$row = self::$DB->getRow();
					// Box-informationen speichern
					$this->_image = $row['box_image'];
					$this->_displayName = $row['box_name'];

                    // Pokemon der Box abfragen
					$tables = array(TABLE_BOX . ' AS box', TABLE_POKEMON . ' AS pkmn');
					$fields = array('box.slot','box.pokemon_id');
					$where = 'pkmn.owner_id = ' . $this->getOwner()->getId()
					. ' AND box.box_id = ' . $boxId
					. ' AND pkmn.id = box.pokemon_id'
					;
					$addSql = 'ORDER BY box.slot ASC';
						
					if (self::$DB->selectByWhere($tables, $fields, $where, $addSql)) {
						$this->_slots = array_fill(0, self::MAX_SLOTS, false);
						while ($row = self::$DB->getRow()) {
							$this->_slots[intval($row['slot'])] = $row['pokemon_id'];
							self::$DB->next();
						}
						// Daten laden
						foreach ($this->_slots as $slot=>$pkmnId) {
							if ($pkmnId !== false) {
								$this->_slots[$slot] = new World_UserPokemon($this->getOwner(), $pkmnId);
							}
						}
						return true;
					}
					else {
						$this->error('Konnte Boxpokemon nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
						return false;
					}
				}
			}
			else {
				$this->error('Konnte Boxinformationen nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
				return false;
			}
		}
	}
	
    function changeSlot($from, $to)
    {
        if (isset($this->_slots[$from]) && isset($this->_slots[$to])) {
            $fromPokemon = $this->_slots[$from];
            $this->_slots[$from] = $this->_slots[$to];
            $this->_slots[$to] = $fromPokemon;
            self::$USER->log('Pokemon in Box ' . $boxNr . ' von Slot ' . $i . ' nach Slot ' . $newSlot . ' verschoben.', Log::PKMN_MOVE);
        }
        else {
            $this->error('Pokemon konnte nicht verschoben werden: Ungültige Slots.', __FILE__, Error::WARN);
        	return false;
        }
    }

	function getOwner()
	{
		return $this->_owner;
	}

	function saveData()
	{
		for ($i = 0; $i < self::MAX_SLOTS; $i++) {
			if ($this->_slots[$i] instanceof World_UserPokemon) {
				$pokemonObj = $this->_slots[$i];
				$this->saveBox($i, $pokemonObj);
			}
		}
	}

	function saveBox($slot, World_UserPokemon $pokemonObj)
	{
		$fieldsValues = array(
        'pokemon_id' => $pokemonObj->getId(),
        'box_id' => $this->getBoxId(),
        'slot' => $slot
		);
		if (self::$DB->replace(TABLE_BOX, $fieldsValues)) {
			return true;
		}
		else {
			$this->error('Konnte Box-Position nicht speichern: Query fehlgeschlagen.', __FILE__, Error::WARN);
			return false;
		}
	}

	function getBoxId()
	{
		return $this->_boxId;
	}

	function getBoxName()
	{
		return $this->_displayName;
	}

	function getBoxImage()
	{
		return $this->_image;
	}

	/**
	 * Fügt ein Pokemon an die Position $slotnumber in die Box ein.
	 * $slotnumber muss im Bereich 0 - World_Storebox::MAX_SLOTS-1 liegen.
	 * Ist $slotnumber nicht angegeben oder kleiner als 0 oder ist
	 * die Position $slotnumber schon besetzt, so wird die nächste freie
	 * Position gewählt das Pokemon dort eingesetzt.
	 * Ist $slotnumber grösser als World_Storebox::MAX_SLOTS-1, so wird das
	 * Pokemon an der Position World_Storebox::MAX_SLOTS-1 eingefügt.
	 * Ist kein Platz mehr in der Box vorhanden wird false zurückgegeben.
	 * @param $pokemon World_UserPokemon
	 * @param $slotnumber integer  Einfügeposition in der Box (0)
	 * @return integer
	 */
	function addPokemonAtSlot(World_UserPokemon $pokemon, $slotnumber=0)
	{
		// überprüfe Slotnummer und freien Platz
		if ($this->getFreeSpace() > 0) {
			if (!$this->isValidSlotNumber($slotnumber)) {
				$slotnumber = $this->getFreeSlotNumber();
			}
			$this->_slots[$slotnumber] = $pokemon;
			self::$USER->log('Pokemon ' . $pokemon->getDisplayName() . ' zu Box ' . $this->getBoxId() . ' an Slot ' . $slotnumber . ' hinzugefügt.', Log::PKMN);
			return $slotnumber;
		}
		else {
			$this->error('Pokemon zu Slot hinzufügen fehlgeschlagen: Keine freien Slots mehr.', __FILE__);
			return false;
		}
	}

	function getFreeSlotNumber()
	{
		for ($i = 0; $i < self::MAX_SLOTS; $i++) {
			if (!($this->_slots[$i] instanceof World_UserPokemon)) {
				return $i;
			}
		}
		return false;
	}

	function getFreeSpace()
	{
		return self::MAX_SLOTS - $this->getUsedCount();
	}

	function isValidSlotNumber($number)
	{
		return ($number > 0 && $number < self::MAX_SLOTS);
	}

	function slotIsFree($slotnumber)
	{
		if (isset($this->_slots[$slotnumber])) {
			if ($this->_slots[$slotnumber] instanceof World_UserPokemon) {
				return false;
			}
		}
		return true;
	}

	function getUsedCount()
	{
		$used = 0;
		for ($i = 0; $i < self::MAX_SLOTS; $i++) {
			if ($this->_slots[$i] instanceof World_UserPokemon) {
				$used++;
			}
		}
		return $used;
	}

	/**
	 * Entfernt das Pokemon von Position $slotnumber aus der Box und gibt
	 * das World_Pokemon::World_Trainerpokemon-Objekt zurück.
	 * Ist $slotnumber nicht angegeben oder kleiner als 0 oder ist die Position
	 * oder ist $slotnumber grösser als World_Storebox::MAX_SLOTS
	 * oder ist $slotnumber nicht besetzt, so wird false zurückgegeben.
	 * @param $slotnumber  Position des zu entfernenden Slots (0)
	 * @return mixed
	 */
	function removePokemonFromSlot($slotnumber=0)
	{
		if ($this->isValidSlotNumber($slotnumber)) {
			if ($this->slotIsFree($slotnumber) === false) {
				$pokemon = $this->getPokemonAtSlot($slotnumber);
				unset($this->_slots[$slotnumber]);
				return $pokemon;
			}
			else {
				$this->error('Konnte Pokemon nicht von Slot entfernen: Slot ist nicht belegt.', __FILE__);
				return false;
			}
		}
		else {
			$this->error('Konnte Pokemon nicht von Slot entfernen: Slotnummer ist ungültig.', __FILE__);
			return false;
		}
	}

	/**
	 * Gibt das Pokemon an Position $slotnumber der Box zurück oder
	 * false, wenn die Position nicht belegt ist.
	 * @param $slotnumber
	 * @return World_UserPokemon
	 */
	function getPokemonAtSlot($slotnumber=0)
	{
		if ($this->slotIsFree($slotnumber) === false) {
			return $this->_slots[$slotnumber];
		}
		else {
			return false;
		}
	}
}

//