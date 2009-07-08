<?php

/**
 * Repräsentiert eine Attacke
 *
 * @author dominique
 *
 */

class World_PokemonAttack extends World_Attack
{
	private $_restAp = 0;
	private $_position = 0;

	const MAX_ATTACKS = 4;

	function __construct($position = false, $pokemonId = 0)
	{
		if ($position !== false) {
			$this->_position = $position;
			$this->loadData($pokemonId);
		}
	}

	function loadData($pokemonId = false)
	{
		if ($pokemonId !== false) {
			$fields = array('attack_id', 'rest_ap');
			$where = 'pokemon_id=' . $pokemonId . ' AND position=' . $this->_position;
			if ($query = self::$DB->selectByWhere(TABLE_ATTACK, $fields, $where)) {
				if ($query->getNumRows() > 0) {
					$row = $query->current();
					$this->_id = $row->attack_id;
					$this->_restAp = $row->rest_ap;
					parent::loadData($this->_id);
				}
			}
		}
	}

	function saveData()
	{

	}

	function getRestAp()
	{
		return $this->_restAp;
	}

	function getMaxAp()
	{

	}
}

