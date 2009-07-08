<?php

/**
 * Repräsentiert einen Behälter für mehrere Boxen
 * @author dominique
 *
 */

class World_StoreBoxContainer extends World_Base
{
	public $storeBoxes = array();
	protected $owner = null;
	static $table = 'world_box';

	function __construct()
	{
	}

	function saveData()
	{
		foreach ($this->storeBoxes as $storeBox) {
			$storeBox->saveData();
		}
		return true;
	}

	function loadData()
	{
		if ($query = self::$DB->select(TABLE_CONST_BOX, 'id')) {
			$this->storeBoxes = array();
			foreach ($query as $row) {
				$this->storeBoxes[] = new World_StoreBox($this->getOwner(), $row->id);
			}

			return true;
		}
		else {
			$this->error('Konnte Daten nicht laden: Query fehlgeschlagen.', __FILE__);
			return false;
		}
	}

	function addPokemon(World_UserPokemon $pokemon)
	{
		if ($this->hasFreeSpace()) {
			$box = $this->getFreeBox();
			$slot = $box->getFreeSlotNumber();
			$box->addPokemonAtSlot($pokemon, $slot);
		}
	}

	function getOwner()
	{
		return self::$USER;
	}

	function getFreeSpace()
	{
		$freespace = 0;
		foreach ($this->storeBoxes as $storeBox) {
			$freespace .= $storeBox->getFreeSpace();
		}
		return $freespace;
	}

	function hasFreeSpace()
	{
		return ($this->getFreeSpace() > 0);
	}

	function getFreeBox()
	{
		foreach ($this->storeBoxes as $storeBox) {
			if ($storeBox->getFreeSpace() > 0) {
				return $storeBox;
			}
		}
		return false;
	}

	function getBoxCount()
	{
		return count($this->storeBoxes);
	}

	function getSpace()
	{
		return $this->getBoxCount * World_StoreBox::MAX_SLOTS;
	}
}