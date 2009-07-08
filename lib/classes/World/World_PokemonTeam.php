<?php

  /**
   * repräsentiert ein Pokemonteam
   * basiert auf Klasse Pokemon
   * @author dominique
   *
   */

    class World_PokemonTeam extends World_Base
{
    /**
     * Enthält die UserPokemon im Team
     * @var array
     */
    protected $_pokemonTeam = array();
    protected $_teamCount = 0;
    /**
     * Maximale Anzahl Pokemon im Team
     * @var integer
     */
    const MAX_POKEMON = 6;

    function __construct()
    {
    }

    function loadData()
    {
        $ownerId = self::$USER->getId();
        if ($ownerId !== false) {
            $tables = array(TABLE_BOX . ' as box');
            $joinTables = array(TABLE_POKEMON . ' as pkmn');
            $fields = array('box.pokemon_id as id','box.slot as slot');
            $onClauses = array('pkmn.id = box.pokemon_id');
            $where = 'pkmn.owner_id=' . $ownerId . ' AND box_id=0';

            if ($query = self::$DB->selectByJoin($tables, $fields, $joinTables, $onClauses, 'INNER JOIN', $where)) {
                $this->_pokemonTeam = array_fill(0, self::MAX_POKEMON, false);
                $this->_teamCount = 0;
                foreach ($query as $row) {
                    $slot = intval($row->slot);
                    $this->_pokemonTeam[$slot] = new World_UserPokemon($this->getOwner(), $row->id);
                    $this->_teamCount++;
                }

                return true;
            }
            else {
                $this->error('Konnte Daten nicht laden: Query fehlgeschlagen.', __FILE__);
                return false;
            }
        }
    }

    function flipSlot($a, $b)
    {
        if (isset($this->_pokemonTeam[$a]) && isset($this->_pokemonTeam[$b])) {
            $fromPokemon = $this->_pokemonTeam[$a];
            $this->_pokemonTeam[$a] = $this->_pokemonTeam[$b];
            $this->_pokemonTeam[$b] = $fromPokemon;
            self::$USER->log('Slot im Team  ' . $a . ' mit Slot ' . $b . ' vertauscht.', Log::PKMN_MOVE);
        }
        else {
            $this->error('Pokemon konnte nicht verschoben werden: Ungültige Slots ' . '(A: ' . $a . ' B: ' . $b . ').', __FILE__, Error::WARN);
            return false;
        }
    }

    function getOwner()
    {
        return self::$USER;
    }

    function saveData()
    {
        foreach ($this->_pokemonTeam as $slot => $pokemon) {
            if ($pokemon instanceof World_UserPokemon) {
                $pokemon->saveData();
                $this->saveBox($slot, $pokemon);
            }
        }
        return true;
    }

    function saveBox($slot, $pokemonObj)
    {
        $fieldsValues = array(
            'pokemon_id' => $pokemonObj->getId(),
            'box_id' => 0,
            'slot' => $slot
                              );
        if (self::$DB->replace(TABLE_BOX, $fieldsValues)) {
            return true;
        }
        else {
            $this->error('Konnte Teampokemon-Position nicht speichern: Query fehlgeschlagen.', __FILE__, Error::WARN);
            return false;
        }
    }

    function getTeamCount()
    {
        return $this->_teamCount;
    }

    function hasFreeSpace()
    {
        return ($this->getTeamCount() < self::MAX_POKEMON-1);
    }

    /**
     * Fügt ein UserPokemon zum Team hinzu
     * @param $userPokemon World_UserPokemon
     * @return World_UserPokemon
     */
    function add($userPokemon)
    {
        if ($this->hasFreeSpace()) {
            if (($slot = $this->getFreeSlotNumber()) !== false) {
                $this->_pokemonTeam[$slot] = $userPokemon;
                $this->_teamCount++;
                self::$USER->log('Pokemon ' . $userPokemon->getId() . ' hinzugefügt.', Log::PKMN);
                return $userPokemon;
            }
            else {
                return false;
            }
        }
        else {
            $this->error('Konnte Pokemon nicht zum Team hinzufügen: Team voll.', __FILE__);
            return false;
        }
    }

    function getFreeSlotNumber()
    {
        if ($this->hasFreeSpace()) {
            for ($i = 0; $i < self::MAX_POKEMON; $i++) {
                if (!($this->_pokemonTeam[$i] instanceof World_UserPokemon)) {
                    return $i;
                }
            }
        }
        else {
            return false;
        }
    }

    function attackAvailable($attackName)
    {
        foreach ($this->_pokemonTeam as $pokemon) {
            if ($pokemon->attackExists($attackName)) {
                return true;
            }
        }
        return false;
    }

    function moveFromStoreBox($storeBox,$slotNumber)
    {
        $pokemon = $storeBox->getPokemonAtSlot($slotNumber);
        if ($this->add($pokemon)) {
            if ($storeBox->removeFromSlot($slotNumber)) {
                return $pokemon;
            }
            else {
                $this->remove($pokemon);
                $this->error('Konnte Pokemon nicht von Lagerbox nach Team verschieben: Entfernen von Lagerbox fehlgeschlagen.', __FILE__);
                return false;
            }
        }
        else {
            $this->error('Konnte Pokemon nicht von Lagerbox nach Team verschieben: Hinzufügen zu Team fehlgeschlagen.', __FILE__);
            return false;
        }
    }

    function moveToStoreBox($storeBox,$pokemon,$slotNumber=-1)
    {
        if ($storeBox->addPokemonAtSlot($pokemon,$slotNumber)) {
            return $pokemon;
        }
        else {
            $this->error('Konnte Pokemon nicht in Lagerbox verschieben.', __FILE__);
            return false;
        }
    }

    function removeId($pokemonId)
    {
        // prüfen ob Pokemon vorhanden
        foreach ($this->_pokemonTeam as $pokemon) {
            if ($pokemon->_id == $pokemonId) {

            }
        }
    }

    function getPokemons()
    {
        $team = array();
        foreach ($this->_pokemonTeam as $pkmn) {
            if ($pkmn instanceof World_UserPokemon) {
                $team[] = $pkmn;
            }
        }
        return $team;
    }
}

