<?php

/**
 * repräsentiert ein Userpokemon
 * basiert auf Klasse Pokemon
 * @author dominique
 *
 */

class World_UserPokemon extends World_PokemonInstance
{
	/**
	 * Id des Pokemons in der Datenbnak
	 * @var integer
	 */
	protected $_id = 0;
	/**
	 * Anzeigename/Spitzname
	 * @var string
	 */
	protected $_displayName = '';
	/**
	 * Fangort des Pokemons
	 * @var string
	 */
	protected $_catchLocation = '';
	/**
	 * Benutzerid des Fängers
	 * @var integer
	 */
	protected $_catcherId = 0;
	/**
	 * Datenbanktabelle
	 * @var string
	 */
	static $table = 'world_pokemon';
	/**
	 * Referenz auf den Besitzer
	 * @var World_User
	 */
	public $owner = null;

	protected $_attacks = array();

	/**
	 * erstellt ein neues UserPokemon.
	 * Wird $pokemon_id angegeben, werden
	 * gleich die Daten des Pokemons mit der
	 * id $pokemon_id geladen.
	 * @param World_User $ownerobj
	 * @param integer $pokemon_id
	 * @return void
	 */
	function __construct($ownerobj,$pokemon_id=false)
	{
		$this->owner = $ownerobj;
		if ($pokemon_id !== false) {
			$this->loadData($pokemon_id);
		}
	}

	/**
	 * erstellt ein neues UserPokemon anhand
	 * eines anderen UserPokemons und schreibt
	 * es diesem Benutzer zu.
	 * @param $userPokemonObj World_UserPokemon
	 * @return bool
	 */
	function createFromUserPokemon($userPokemonObj)
	{
		$this->_pokedexNumber = $userPokemonObj->getPokedexNumber();
		parent::loadData($this->_pokedexNumber);
		$this->setDisplayName($userPokemonObj->getDisplayName());
		$this->setExperiencePoints($userPokemonObj->getExperiencePoints());
			
		if ($userPokemonObj->getCatchLocation()=='' || $userPokemonObj->getCatcherId() == 0) {
			$this->setCatcherId($this->getOwnerId());
			$this->setCatchLocation($this->getOwner()->location->_id);
		}
		$fieldsValues = array(
    	   'catcher_id' => $this->getCatcherId(),
    	   'catch_location' => $this->getCatchLocation(),
    	   'owner_id' => $this->getOwnerId(),
    	   'pokedex_nr' => $this->getPokedexNumber());
		// in die Datenbank speichern
		if (self::$DB->insert(self::$table,$fieldsValues)) {
			$this->_id = self::$DB->lastInsertId();
			return $this->saveData();
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param $pokemonObj World_Pokemon
	 * @return unknown_type
	 */
	function createFromPokemon(World_PokemonInstance $pokemonObj)
	{
		$pokemonVars = $pokemonObj->getAllVariables();
		foreach ($pokemonVars as $var => $value) {
			$this->$var = $value;
		}
		$this->_catcherId = $this->getOwnerId();
		$this->_catchLocation = $this->getOwner()->getMap()->getId();
		$this->setDisplayName('');
		return $this;
	}

	function saveData()
	{
		if ($this->getId() == 0) {
			// Datensatz für neues Pokemon vorbereiten
			$fieldsValues = array(
			 'owner_id' => $this->getOwnerId(),
			 'pokedex_nr' => $this->getPokedexNumber()
			);
			if (self::$DB->insert(TABLE_POKEMON, $fieldsValues)) {
				$this->_id = self::$DB->lastInsertId();
			}
			else {
		        $this->error('Neues Pokemon speichern fehlgeschlagen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
				return false;
			}
		}

		$fieldsValues = array(
		    'id' => $this->getId(),
			'name' => $this->getDisplayName(),
			// 'faehigkeit' => $this->getActiveAbility,
			// 'geschlecht' => $this->getSex(),
			'freundschaft' => $this->getHappiness(),
			'experience_points' => $this->getExperiencePoints(),
	        'ev_kp' => $this->getBaseValue('ev_kp'),
	        'ev_atk' => $this->getBaseValue('ev_atk'),
	        'ev_def' => $this->getBaseValue('ev_def'),
	        'ev_spa' => $this->getBaseValue('ev_spa'),
	        'ev_spv' => $this->getBaseValue('ev_spv'),
	        'ev_mov' => $this->getBaseValue('ev_mov'),
			'kp' => $this->getKp()
		);

		if (self::$DB->updateById(TABLE_POKEMON,$fieldsValues,$this->getId())) {
			return true;
		}
		else {
			$this->error('Konnte Pokemon nicht speichern: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return false;
		}
	}

	function loadData($pokemon_id = false)
	{
		if ($pokemon_id !== false) {
			$fields = array(
			 'pokedex_nr', 'name', 'experience_points',
			 'kp', 'dv_kp', 'dv_atk', 'dv_def', 'dv_spa', 'dv_spv', 'dv_mov',
			 'ev_kp', 'ev_atk', 'ev_def', 'ev_spa', 'ev_spv', 'ev_mov'
			);
            
			if ($query = self::$DB->selectByWhere(TABLE_POKEMON, $fields, TABLE_POKEMON . '.id=' . $pokemon_id)) {
				$row = $query->current();
				$name = $row->name;
                $experiencePoints = $row->experience_points;
                $kp = $row->kp;
				$pokedexNumber = $row->pokedex_nr;
				$dvValues = array(
                    'kp' => $row->dv_kp,
                    'atk' => $row->dv_atk,
                    'def' => $row->dv_def,
                    'spa' => $row->dv_spa,
                    'spv' => $row->dv_spv,
                    'mov' => $row->dv_mov
				);
				$evValues = array(
                    'kp' => $row->ev_kp,
                    'atk' => $row->ev_atk,
                    'def' => $row->ev_def,
                    'spa' => $row->ev_spa,
                    'spv' => $row->ev_spv,
                    'mov' => $row->ev_mov
                );

				// Pokedex-Daten laden
				parent::loadData($pokedexNumber);

				// Userspezifische Daten
				$this->_id = $pokemon_id;
				$this->setDisplayName($name);
				$this->setExperiencePoints($experiencePoints);
                $this->setEvValues($evValues);
                $this->setDvValues($dvValues);
				$this->setKp($kp);
				$this->loadAttacks();
				$this->loadTempChanges();
				
				return true;
			}
			else {
				$this->error('Konnte Daten nicht laden: Query fehlgeschlagen.', __FILE__);
				return false;
			}
		}
	}

	function loadTempChanges()
	{
		$table = TABLE_POKEMON_TEMP_CHANGES;
		$fields = array('name', 'value', 'duration_type', 'end');
		$where = 'pokemon_id=' . $this->getId();
		if ($query = self::$DB->selectByWhere($table, $fields, $where)) {
			foreach ($query as $change) {
				$this->setTempChange($change->name, $change->value, $change->duration_type, $change->end);
			}
		}
	}

	function loadAttacks()
	{
		// Attacken laden
		$this->_attacks = array();
		for ($i=0; $i < World_PokemonAttack::MAX_ATTACKS; $i++) {
			$this->_attacks[] = new World_PokemonAttack($i, $this->getId());
		}
	}

	function attackExists($attackName)
	{
		foreach ($this->_attacks as $attack) {
			if ($attack->getName() == $attackName) {
				return true;
			}
		}

		return false;
	}

	function getCatchLocation()
	{
		return $this->_catchLocation;
	}

	function getCatcherId()
	{
		return $this->_catcherId;
	}

	function getOwnerId()
	{
		return $this->getOwner()->getId();
	}

	function getOwner()
	{
		return $this->owner;
	}

	function getId()
	{
		return $this->_id;
	}

	function getDisplayName()
	{
		return $this->_displayName;
	}

	function setDisplayName($name)
	{
		if ($name == '') {
			$this->_displayName = $this->getPokedexName();
		}
		else {
			$this->_displayName = $name;
		}
	}
}

