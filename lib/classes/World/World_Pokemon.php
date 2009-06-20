<?php

/**
 * Repräsentiert ein Pokemon
 *
 * @author dominique
 *
 */

class World_Pokemon extends World_Base
{
	/**
	 * Pokedexnummer im Nationaldex
	 * @var integer
	 */
	protected $_pokedexNumber = 0;
	/**
	 * Name des Pokemons im Pokedex
	 * @var string
	 */
	protected $_pokedexName = '';
	/**
	 * Verfügbare Attacken für dieses Pokemon
	 * @var array von World_Attack-Objekten
	 */
	protected $_attacks = null;

	private $types = null;
	private $percentageMale = null;
	private $size = null;
	private $weight = null;
	private $species = null;
	private $pokesturmPlaces = null;
	private $abilities = null;
	private $pokedexDescription = null;
	private $zuchtbar = null;
	private $zuchtSteps = null;
	private $trainingLevel = null;
	private $epOnLevel100 = null;
	private $happiness = null;
	protected $_baseValues = null;

	/**
	 * Höchstzahl an verfügbaren Attacken
	 * @var integer
	 */
	const MAX_ATTACKS = 4;

    /**
     * lädt die Daten des Pokemons $pokedexNumber
     * @param $pokedexNumber integer Pokedex-Nr.
     * @return void
     */
	function __construct($pokedexNumber)
	{
		// Read data from database
		$this->loadData($pokedexNumber);
	}
	
	function loadData($pokedexNumber = false)
	{
		if ($pokedexNumber) {
			$fields = array('name');
			if (self::$DB->selectById(TABLE_CONST_POKEMON, $fields, $pokedexNumber)) {
				$row = self::$DB->getRow();
				$this->_pokedexNumber = $pokedexNumber;
				$this->_pokedexName = $row['name'];
				return  true;
			}
			else {
				$this->error('Konnte Pokemon Daten nicht laden: Query fehlgeschlagen.', __FILE__);
				return false;
			}
		}
	}

	function saveData()
	{
		// darf nicht implementiert werden, da Tabelle konstant.
		$this->error('Ungültiger Aufruf von saveData: Tabelle darf nicht verändert werden.', __FILE__);
	}

	/**
	 * lädt ein Datenfeld $field Tabelle mit konstanten
	 * Pokemondaten (world_const_pokemon)
	 * und schreibt den Inhalt in die Variable $saveVar
	 * @param $field string Name des Felds in der Datenbank
	 * @param $saveVar mixed Referenz auf Variable
	 * @return void
	 */
	function loadField($field, &$saveVar)
	{
		if ($saveVar == null) {
			switch ($field) {
				case 'attacks':
					$this->loadAttacks($saveVar);
					break;
				case 'types':
					break;
				case 'pokesturmPlaces':
					break;
				case 'abilities':
					break;
				case 'zuchtGroups':
					break;
				case 'baseValues':
					$this->loadBaseValues($saveVar);
					break;
				default:
					// normales Datenbankfeld
					if (self::$DB->selectById(TABLE_CONST_POKEMON, $field, $this->getPokedexNumber())) {
						if (self::$DB->getNumRows() > 0) {
							$row = self::$DB->getRow();
							$saveVar = $row[$field];
						}
						else {
							$this->error('Konnte Feld ' . $field . ' nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
						}
					}
			}
		}
	}

	/**
	 * lädt die Attacken, die dieses Pokemon
	 * lernen kann und speichert das Array aus
	 * World_Attack-Objekten in die Variable
	 * $saveVar ab.
	 * @param $saveVar mixed Referenz auf Variable
	 * @return void
	 */
	function loadAttacks(&$saveVar)
	{
		$fields = array(
        'attack_id'
        );

        if (self::$DB->selectByWhere(TABLE_CONST_ATTACK_LEARN, $fields, 'pokedex_nr=' . $this->getPokedexNumber())) {
        	if (self::$DB->getNumRows() > 0) {
        		$saveVar = array();
        		while ($row = self::$DB->getRow())
        		$saveVar[] = $row['attack_id'];
        	}
        	// Attacken laden
        	foreach ($saveVar as $key=>$aId) {
        		$saveVar[$key] = new World_Attack($aId);
        	}
        }
        else {
        	$this->error('Konnte BaseValues nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
        }
	}

	/**
	 * lädt die Zuchtgruppen dieses Pokemons
	 * und speichert die Namen als Array in die
	 * Variable $saveVar
	 * @param $saveVar mixed Referenz auf Variable
	 * @return void
	 */
	function loadZuchtGroups(&$saveVar)
	{
		$fields = array(
        'zuchtgruppe1',
	    'zuchtgruppe2'
	    );

	    if (self::$DB->selectById(TABLE_CONST_POKEMON, $fields, $this->getPokedexNumber())) {
	    	if (self::$DB->getNumRows() > 0) {
	    		$saveVar = array();
	    		$row = self::$DB->getRow();
	    		foreach ($row as $fieldValue) {
	    			$saveVar[] = $fieldValue;
	    		}
	    	}
	    	else {
	    		$this->error('Konnte BaseValues nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
	    	}
	    }
	}

	/**
	 * lädt die Typen dieses Pokemons
	 * und speichert sie als Array in die 
	 * Variable $saveVar.
	 * @param $saveVar mixed Referenz auf Variable
	 * @return void
	 */
	function loadTypes(&$saveVar)
	{
		$fields = array(
        'typ1',
        'typ2'
        );

        if (self::$DB->selectById(TABLE_CONST_POKEMON, $fields, $this->getPokedexNumber())) {
        	if (self::$DB->getNumRows() > 0) {
        		$saveVar = array();
        		$row = self::$DB->getRow();
        		foreach ($row as $fieldValue) {
        			$saveVar[] = $fieldValue;
        		}
        	}
        	else {
        		$this->error('Konnte Types nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
        	}
        }
	}

	/**
	 * lädt die Basiswerte, die der
	 * Berechnung der Statuswerte zu Grunde liegen,
	 * und speichert die in die Variable $saveVar
	 * ab.
	 * @param $saveVar mixed Referenz auf Variable
	 * @return void
	 */
	function loadBaseValues(&$saveVar)
	{
		$fields = array(
        'base_ep as ep',
        'base_kp as kp',
        'base_atk as atk',
        'base_def as def',
        'base_spa as spa',
        'base_spv as spv',
        'base_mov as mov',
        'base_ev_kp as ev_kp',
		'base_ev_atk as ev_atk',
        'base_ev_def as ev_def',
        'base_ev_spa as ev_spa',
        'base_ev_mov as ev_mov'
        );

        if (self::$DB->selectById(TABLE_CONST_POKEMON, $fields, $this->getPokedexNumber())) {
        	if (self::$DB->getNumRows() > 0) {
        		$saveVar = array();
        		$row = self::$DB->getRow();
        		foreach ($row as $fieldName => $fieldValue) {
        			$saveVar[$fieldName] = $fieldValue;
        		}
        	}
        	else {
        		$this->error('Konnte BaseValues nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
        	}
        }
	}

	function loadAbilities()
	{

	}

	function loadPokesturmPlaces()
	{

	}

	/**
	 * gibt den (Pokedex-)Namen des Pokemons zurück
	 * @return string Pokedexname
	 */
	function getPokedexName()
	{
		return $this->_pokedexName;
	}

	/**
	 * gibt die Pokedex-Nr. des Pokemons zurück
	 * @return integer Pokedexnummer
	 */
	function getPokedexNumber()
	{
		return $this->_pokedexNumber;
	}

	/**
	 * gibt alle erlernbaren Attacken als
	 * Array von World_Attack-Objekten zurück.
	 * @return array World_Attack Attacken
	 */
	function getAttacks()
	{
		$this->loadField('attacks', $this->_attacks);
		return $this->_attacks;
	}

	/**
	 * gibt den Typ $typeNum des Pokemons zurück
	 * @param $typeNum integer Nummer des Typs, also 1 für Typ 1 und 2 für Typ 2
	 * @return string Pokemontyp
	 */
	function getType($typeNum)
	{
		$this->loadField('types', $this->_type);
		return $this->_type[$typeNum-1];
	}

	/**
	 * gibt den prozentualen Anteil männlicher
	 * Exemplare dieses Pokemons zurück
	 * @return integer Prozent männlich
	 */
	function getPercentageMale()
	{
		$this->loadField('prozent_maennlich', $this->_percentageMale);
		return $this->_percentageMale;
	}

	/**
	 * gibt die Grösse des Pokemons zurück
	 * @return integer Grösse
	 */
	function getSize()
	{
		$this->loadField('groesse', $this->_size);
		return $this->_size;
	}

	/**
	 * gibt das Gewicht des Pokemons zurück
	 * @return integer Gewicht
	 */
	function getWeight()
	{
		$this->loadField('weight', $this->_weight);
		return $this->_weight;
	}

	/**
	 * gibt die Spezies des Pokemons zurück
	 * @return string Spezies
	 */
	function getSpecies()
	{
		$this->loadField('spezies', $this->_species);
		return $this->_species;
	}
	
	/**
	 * gibt die verfügbaren Geschlechter zurück
	 * @return assoc_array Assoziatives Array, dessen Werte die datenbankkompatiblen Geschlechtercodes enthalten
	 */
	function getSexes()
	{
		return array('male'=>'m', 'neutral'=>'n', 'female'=>'f');
	}

	/**
	 * gibt die Fundorte dieses Pokemons in Pokesturm
	 * zurück.
	 * @return array Fundorte
	 */
	function getPokesturmPlaces()
	{
		$this->loadField('pokesturmPlaces', $this->_pokesturmPlaces);
		return $this->_pokesturmPlaces;
	}

	/**
	 * gibt die Pokedexbeschreibung zu diesem
	 * Pokemon zurück
	 * @return string
	 */
	function getPokedexDescription()
	{
		$this->loadField('pokedex_beschreibung', $this->_pokedexDescription);
		return $this->_pokedexDescription;
	}

	/**
	 * gibt alle Fähigkeiten zurück, die dieses
	 * Pokemon haben kann.
	 * @return array Fähigkeiten
	 */
	function getAbilities()
	{
		$this->loadField('abilities', $this->_abilities);
		return $this->_abilities;
	}

	/**
	 * gibt den Namen der Zuchtgruppe $groupNum zurück
	 * @param $groupNum integer Nummer der Gruppe, also 1 für Zuchtgruppe 1 bzw. 2 für Zuchtgruppe 2
	 * @return string Zuchtgruppe
	 */
	function getZuchtGroup($groupNum)
	{
		$this->loadField('zuchtGroups', $this->_zuchtGroup);
		return $this->_zuchtGroup[$groupNum-1];
	}

	/**
	 * gibt die benötigten Schritte bis zum Ei-Schlüpfen
	 * (sogenannte Zuchtschritte) zurück.
	 * @return integer Zuchtschritte
	 */
	function getZuchtSteps()
	{
		$this->loadField('zucht_schritte', $this->_zuchtSteps);
		return $this->_zuchtSteps;
	}

	/**
	 * gibt zurück, ob das Pokemon zuchtbar ist oder nicht
	 * @return bool true wenn zuchtbar
	 */
	function isZuchtbar()
	{
		$this->loadField('zuchtbar', $this->_zuchtbar);
		return $this->_zuchtbar;
	}

	/**
	 * gibt die Trainingsschwierigkeit des Pokemons zurück
	 * @return integer Traininglevel
	 */
	function getTrainingLevel()
	{
		$this->loadField('training_schwierigkeit', $this->_trainingLevel);
		return $this->_trainingLevel;
	}

	/**
	 * gibt die benötigten EP für Level 100 zurück
	 * @return integer Erfahrungspunkte auf Lvl. 100
	 */
	function getEpOnLevel100()
	{
		$this->loadField('ep_lvl100', $this->_epOnLevel100);
		return $this->_epOnLevel100;
	}

	/**
	 * Gibt die Höhe der Grund-Freundschaft des Pokemons
	 * zurück.
	 * @return integer Freundschaft
	 */
	function getHappiness()
	{
		$this->loadField('grund_freundschaft', $this->_happiness);
		return $this->_happiness;
	}

	/**
	 * Gibt den Wert des Basiswertes $valueIdentifier zurück.
	 * @param $valueIdentifier string Bezeichner des Basiswert ('ev_atk',...)
	 * @return integer Basiswert
	 */
	function getBaseValue($valueIdentifier)
	{
		$this->loadField('baseValues', $this->_baseValues);
		if (isset($this->_baseValues[$valueIdentifier])) {
			return $this->_baseValues[$valueIdentifier];
		}
		else {
			return false;
		}
	}
}

