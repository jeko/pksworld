<?php

/**
 * Repräsentiert ein Pokemon in der Welt
 *
 * @author dominique
 *
 */

class World_PokemonInstance extends World_Pokemon
{
	private $_experiencePoints = 0;
	private $_levelUpEp = null;
	private $_sex = null;
	private $_ability = null;
	private $_statusProblem = null;
	private $_kp = null;
	private $_level = null;
	private $_wesen = null;
	protected $_attacks = array();
	private $_dvValues = array();
	protected $_tempChanges = array();

	/**
	 * Erstellt ein Exemplar des Pokemons mit der
	 * Pokedexnummer $pokemonId
	 * @param $pokemonId integer Pokedexnummer
	 * @return void
	 */
	function __construct($pokemonId)
	{
		$this->loadData($pokemonId);
	}

	function loadData($pokemonId = false)
	{
		if ($pokemonId !== false) {
	        parent::loadData($pokemonId);
			$this->setExperiencePoints();
			$this->setSex();
			$this->setAbility();
            $this->setEvValues();
            $this->setDvValues();
			$this->setKp();
			$this->setWesen();
			$this->loadAttacks();
		}
	}
	
	function saveData()
	{
		// PokemonInstanzen werden nicht in die Datenbank geschrieben
		// Im Falle eines Kampfes wird das ganze Objekt
		// serialisiert in die Datenbank geschrieben (s. world_pokemon_fight)
	}
	
	function getAllVariables()
	{
		$vars = array();
		foreach ($this as $var => $value) {
			$vars[$var] = $value;
		}
		return $vars;
	}

    /**
     * lädt die Attacken, die dieses Pokemon auf dem angegebenen Level
     * beherrscht.
     * @see lib/classes/World/World_Pokemon#loadAttacks($saveVar)
     */
	function loadAttacks()
	{
		// Attacken laden
		$this->_attacks = array();
		for ($i=0; $i<World_PokemonAttack::MAX_ATTACKS; $i++) {
			//$this->_attacks[] = new World_PokemonAttack($i, $this->getId());
		}
	}
	
	function attack($attackId, $opponentPokemon)
	{
		
	}
	
	function setTempChange($name, $value, $duration_type='round', $end=1)
	{
		$name = strtolower($name);
		$tempChangeKey = ''; // wird nur für interne Speicherung im Objekt benutzt
		switch ($name) {
			case 'longState': // Paralyse, Vergiftung, Vereisung, etc.
				$tempChangeKey = 'longState'; // damit vorhergehende Veränderung überschrieben wird
				break;
			case 'shortState': // Verwirrung, etc.
				$tempChangeKey = 'shortState'; // damit vorhergehende Veränderung überschrieben wird
				break;
            case 'kp': // Statuswert-Veränderung
            case 'atk':
            case 'def':
            case 'spa':
            case 'spv':
            case 'mov':
            	$tempChangeKey = 'statusValue' . ucfirst($name);
            	// Prüfen ob schonmal verändert und gegenrechnen
				if (isset($this->_tempChanges[$tempChangeKey])) {
					$value = $this->_tempChanges[$name] + $value;
				}
            default:
                $tempChangeKey = 'random' . rand(0,100);
		}
		$this->_tempChanges[$tempChangeKey] = array('name' => $name, 'value' => $value, 'duration_type' => $duration_type, 'end' => $end);
		return true;
	}
	
	function removeTempChange($name = 'all')
	{
		if ($name == 'all') {
			$this->_tempChanges = array();
		}
		else {
			if (isset($this->_tempChanges[$name])) {
				unset($this->_tempChanges[$name]);
			}
		}
		return true;
	}

	/**
	 * Setzt die Erfahrungspunkte dieses Pokemons und
	 * speichert das Level intern ab.
	 * @param $points integer Erfahrungspunkte
	 * @return void
	 */
	function setExperiencePoints($points = 0)
	{
		if ($points < 0) {
			$points = 0;
		}
		else if ($points > $this->getEpOnLevel100()) {
			$points = $this->getEpOnLevel100();
		}
		$this->_experiencePoints = $points;
		$this->calcLevel();
	}
	
	/**
	 * setzt die DV-Werte dieses Pokemons auf die Werte $dvValuesAssocArray.
	 * Ist $dvValuesAssocArray nicht angegeben, werden die Werte zufällig gesetzt.
	 * @param $dvValuesAssocArray assoc_array Array in der Form array('name'=>'wert'), wobei 'name' z.B. 'atk', 'def', etc. sein kann.
	 * @return void
	 */
	function setDvValues($dvValuesAssocArray = false)
	{
		if ($dvValuesAssocArray === false || !is_array($dvValuesAssocArray)) {
			// alle Werte zufällig setzen, Range: 1-31 pro Wert
			$dvValuesAssocArray = array('atk'=>0, 'def'=>0, 'spa'=>0, 'spv'=>0, 'mov'=>0, 'kp'=>0);
			foreach ($dvValuesAssocArray as $statusName => $statusValue) {
				$dvValuesAssocArray[$statusName] = intval(rand(1,31));
			}
		}

		$this->_dvValues = $dvValuesAssocArray;
	}
	
    /**
     * setzt die EV-Werte dieses Pokemons auf die Werte $evValuesAssocArray.
     * Ist $evValuesAssocArray nicht angegeben, werden die Werte auf 0 gesetzt.
     * @param $evValuesAssocArray assoc_array Array in der Form array('name'=>'wert'), wobei 'name' z.B. 'atk', 'def', etc. sein kann.
     * @return void
     */
	function setEvValues($evValuesAssocArray = false)
	{
		if ($evValuesAssocArray === false || !is_array($evValuesAssocArray)) {
			$evValuesAssocArray = array('kp' => 0, 'atk' => 0, 'def' => 0, 'spa' => 0, 'spv' => 0, 'mov' => 0);
		}
		
		$this->_evValues = $evValuesAssocArray;
	}

	/**
	 * setzt das Geschlecht des Pokemons auf $sexToken bzw.
	 * berechnet das Geschlecht anhand der Geschlechterverteilung
	 * des Pokemons im Pokedex
	 * @param $sexToken string Geschlechtsidentifier (siehe World_Pokemon::getSexes()): m, f, oder n
	 * @return void
	 */
	function setSex($sexToken = null)
	{
		$availableSex = $this->getSexes();
		if ($sexToken == null || !in_array($sexToken, $availableSex)) {
			// Nach Prozentsatz setzen
			$percentageMale = $this->getPercentageMale();
			$randomNumber = rand(0, 1) * 100;
			if ($randomNumber > $percentageMale) {
				// weiblich
				$sexToken = $availableSex['female'];
			}
			else {
				// männlich
				$sexToken = $availableSex['male'];
			}
		}
		$this->_sex = $sexToken;
	}

	/**
	 * setzt die aktive Fähigkeit auf die Fähigkeit mit 
	 * der Nummer $abilityNumber 
	 * @param $abilityNumber integer Nummer der Fähigkeit (1 oder 2)
	 * @return void
	 */
	function setAbility($abilityNumber = 1)
	{
		$availableAbilities = $this->getAbilities();
		if ($abilityNumber > count($availableAbilities)) {
			$abilityNumber = count($availableAbilities);
		}
		$abilityNumber -= 1;

		$this->_ability = $availableAbilities[$abilityNumber];
	}

	/**
	 * setzt die verbleibenden KP dieses Pokemons
	 * @param $newKp integer restliche KP
	 * @return void
	 */
	function setKp($newKp = null)
	{
		$maxKp = $this->getMaxKp();
		if ($newKp < 0) {
			$newKp = 0;
		}
		else if ($newKp > $maxKp || $newKp == null) {
			$newKp = $maxKp;
		}
		else {
			$newKp = 0;
		}
		
		$this->_kp = $newKp;
	}

	/**
	 * setzt das Wesen des Pokemons
	 * @param $wesen string Wesen
	 * @return void
	 */
	function setWesen($wesen = 'Ernst')
	{
		$this->_wesen = $wesen;
	}

	/**
	 * gibt das Level dieses Pokemons zurück
	 * @return integer
	 */
	function getLevel()
	{
		if ($this->_level == null) {
			$this->calcLevel();
		}
		return $this->_level;
	}

	/**
	 * berechnet das Level auf Basis
	 * der gespeicherten EP (@see setExperiencePoints())
	 * und speichert es intern ab
	 * @return void
	 */
	function calcLevel()
	{
		$trainingLevels = array(1=>'fast', 2=>'medium fast', 3=>'medium fast', 4=>'medium slow', 5=>'medium slow', 6=>'slow');
		$trainingLevel = $this->getTrainingLevel();
		$ep = $this->getExperiencePoints();

		// grösstes Level mit diesen Erfahrungspunkten abrufen + EP auf nächstem Level
		$fields = array('level', '`' . $trainingLevels[$trainingLevel] . '`');
		$where = '`' . $trainingLevels[$trainingLevel] . '` >= ' . $ep;
		$addSql = 'ORDER BY level ASC LIMIT 2';
		
		if ($query = self::$DB->selectByWhere(TABLE_CONST_EP, $fields, $where, $addSql)) {
			if ($query->getNumRows() > 1) {
				// Aktuelles Level speichern
				$row = $query->current();
				$level = $row->level;
				// Ep auf nächstem Level speichern
				$row = $query->next();
				$levelUpEp = $row->$trainingLevels[$trainingLevel];
			}
			else {
				$level = 0;
                $levelUpEp = 0;
			}
		}
		else {
			$level = 0;
            $levelUpEp = 0;
			$this->error('Konnte Level nicht abrufen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
		}
		$this->_level = $level;
		$this->_levelUpEp = $levelUpEp;
	}
	
	/**
	 * gibt den DV-Basiswert dieses Pokemons zurück
	 * @param $identifier string DV-Bezeichner
	 * @return integer DV-Wert
	 */
	function getDvValue($identifier)
	{
		if (isset($this->_dvValues[$identifier])) {
			return $this->_dvValues[$identifier];
		}
		else {
			$this->error('Unbekannter DV-Wert ' . $identifier . '.', __FILE__, Error::ERR);
			return 1;
		}
	}
	
	/**
	 * gibt den EV-Value $identifier zurück
	 * @param $identifier string EV-Bezeichner
	 * @return integer EV-Wert
	 */
	function getEvValue($identifier)
	{
	    if (isset($this->_evValues[$identifier])) {
            return $this->_evValues[$identifier];
        }
        else {
            $this->error('Unbekannter EV-Wert ' . $identifier . '.', __FILE__, Error::ERR);
            return 1;
        }
	}

	/**
	 * gibt die EP dieses Pokemons zurück
	 * @return integer Erfahrungspunkte
	 */
	function getExperiencePoints()
	{
		return $this->_experiencePoints;
	}
	/**
     * gibt EP des nächsten Levels zurück
     * @return integer Erfahrungspunkte
     */
    function getLevelUpEp()
    {
        return $this->_levelUpEp;
    }

	/**
	 * gibt das Geschlechtskürzel (m/f/n) dieses Pokemon
	 * zurück
	 * @return integer Geschlechtskürzel (m/f/n)
	 */
	function getSex()
	{
		return $this->_sex;
	}

	/**
	 * gibt die aktive Fähigkeit des Pokemons zurück
	 * @return string Fähigkeit
	 */
	function getAbility()
	{
		return $this->_ability;
	}

	/**
	 * gibt das Statusproblem dieses Pokemons zurück
	 * @return string Statusproblem
	 */
	function getStatusProblem($type = 'long')
	{
		$value = '';
		switch ($type) {
			case 'long':
				$value = ucfirst($this->_tempChanges['longState']);
				break;
			case 'short':
			    $value = ucfirst($this->_tempChanges['shortState']);
			    break;
			default:
				$value = '';
				$this->error('getStatusProblem: Unbekannter Typ ' . $type . '.', __FILE__, Error::WARN);
		}
		return $value;
	}

	/**
	 * gibt die verbleibenden KP zurück
	 * @return integer KP-Punkte
	 */
	function getKp()
	{
		return $this->_kp;
	}

	/**
	 * gibt das Wesen des Pokemons zurück
	 * @return string Wesen
	 */
	function getWesen()
	{
		return $this->_wesen;
	}

	/**
	 * gibt die maximalen KP dieses Pokemons
	 * zurück (in Verbindung mit dem Level)
	 * @return integer Maximale KP
	 */
	function getMaxKp()
	{
		$level = $this->getLevel();
		$baseKp = $this->getBaseValue('kp');
		$baseDvKp = $this->getDvValue('kp');
		$baseEvKp = $this->getEvValue('kp');

		return intval((10 + $baseKp * $level) + ($baseDvKp / 100 * $level) + (0.25 * $baseEvKp / 100 * $level));
	}
	
   
   /**
     * Gibt den Wert des Basiswertes $valueIdentifier zurück.
     * Bezieht auch dv-Werte mit ein
     * @param $valueIdentifier string Bezeichner des Basiswert ('ev_atk',...)
     * @return integer Basiswert
     */
    function getBaseValue($valueIdentifier)
    {
        $parts = explode('_', $valueIdentifier);
        $value = false;
        switch ($parts[0]) {
            case 'ev':
                $value = $this->getEvValue($parts[1]);
            break;
            case 'dv':
                $value = $this->getDvValue($parts[1]);
            break;
            default:
                $value = parent::getBaseValue($parts[0]);
        }
        return $value;
    }
	
	/**
	 * berechnet einen Statuswert und gibt ihn
	 * zurück
	 * @param $identifier string Bezeichner
	 * @return integer Statuswert
	 */
	function getStatusValue($identifier)
	{
        $baseValueIdentifier = strtolower($identifier);
		$statusValueIdentifier = strtoupper($identifier);
		$level = $this->getLevel();
		$wesen = $this->getWesen();
		$wesenFactor = 1;
		
		switch ($statusValueIdentifier) {
			case 'ATK':
				switch ($wesen) {
					case 'Frech':
					case 'Hart':
					case 'Mutig':
					case 'Solo':
						$wesenFactor = 1.1;
						break;
					case 'Kühn':
					case 'Mäßig':
					case 'Scheu':
					case 'Still':
						$wesenFactor = 0.9;
						break;
					default:
						$wesenFactor = 1;
				}
				break;
			case 'DEF':
                switch ($wesen) {
                    case 'Kühn':
                    case 'Lasch':
                    case 'Locker':
                    case 'Pfiffig':
                        $wesenFactor = 1.1;
                        break;
                    case 'Hastig':
                    case 'Mild':
                    case 'Solo':
                    case 'Zart':
                        $wesenFactor = 0.9;
                        break;
                    default:
                        $wesenFactor = 1;
                }
                break;
			case 'SPA':
                switch ($wesen) {
                    case 'Hitzig':
                    case 'Mäßig':
                    case 'Mild':
                    case 'Ruhig':
                        $wesenFactor = 1.1;
                        break;
                    case 'Froh':
                    case 'Hart':
                    case 'Pfiffig':
                    case 'Sacht':
                        $wesenFactor = 0.9;
                        break;
                    default:
                        $wesenFactor = 1;
                }
                break;
			case 'SPV':
                switch ($wesen) {
                    case 'Forsch':
                    case 'Sacht':
                    case 'Still':
                    case 'Zart':
                        $wesenFactor = 1.1;
                        break;
                    case 'Frech':
                    case 'Hitzig':
                    case 'Lasch':
                    case 'Naiv':
                        $wesenFactor = 0.9;
                        break;
                    default:
                        $wesenFactor = 1;
                }
                break;
			case 'MOV':
				switch ($wesen) {
                    case 'Froh':
                    case 'Hastig':
                    case 'Naiv':
                    case 'Scheu':
                        $wesenFactor = 1.1;
                        break;
                    case 'Forsch':
                    case 'Locker':
                    case 'Mutig':
                    case 'Ruhig':
                        $wesenFactor = 0.9;
                        break;
                    default:
                        $wesenFactor = 1;
                }
                break;
			default:
		}
		
		$dvValue = $this->getDvValue($baseValueIdentifier);
		$evValue = $this->getEvValue($baseValueIdentifier);
        $statValue = $this->getBaseValue($baseValueIdentifier);
        
        // Berechnung des kumlierten Statuswertes
		$statusValue = (
		  (5 + $statValue * $level)
		  + ($dvValue / 100 * $level)
		  + ($evValue / 100 * $level * 0.25)
		) * $wesenFactor;
		
		// Einbeziehen von temporären Statuswert-Veränderungen (durch Kampf etc.)
		$identifier = ucfirst(strtolower($identifier)); // 'ATK' wird zu 'Atk', 'DEF' zu 'Def', etc.
		if (isset($this->_tempChanges['statusValue' . $identifier])) {
			$statusValue += $this->_tempChanges['statusValue' . $identifier];
		}
		return $statusValue;
	}
}

