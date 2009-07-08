<?php

  /**
   * Repräsentiert einen Kampf zwischen zwei Kontrahenten
   *
   * @author dominique
   *
   */

class World_Fight extends World_Base
{
    protected $_id = 0;
    protected $_image = '';
    protected $_started = false;

    function __constuct()
    {

    }

    function setTrainerFight($challengeId)
    {

    }

    function setWildFight($pkmnBlock)
    {
        $map = $this->getMap();
        $dayTime = $map->getDayTime();
        $mapId = $map->getId();
        $fields = array(
            'chance',
            'pokedex_nr'
                        );
        $where = 'block="' . mysql_real_escape_string($pkmnBlock) . '"'
            . ' AND map_id=' . $mapId
            . ' AND (daytime="' . $dayTime . '"'
            . ' OR daytime="both")'
            ;

        if ($query = self::$DB->selectByWhere(TABLE_CONST_MAP_PKMN, $fields, $where)) {
            if ($query->getNumRows() > 0) {
                // Gegnerisches Pokemon ermitteln
                $pokemonId = false;
            }
            else {
                return false;
            }
        }
        else {
            $this->error('Konnte Pokemonblöcke nicht abrufen: Query fehlgeschlagen.', __FILE__, Error::WARN);
            return false;
        }
    }

    function started()
    {
        return $this->_started;
    }

    function end()
    {
        // TODO Challenge-Eintrag löschen
        // TODO Fight-Eintrag löschen
        // TODO PD zuweisen
    }

    function loadData($user_id = false) {
        // TODO Daten aus Fight-Tabelle laden
    }

    function saveData()
    {
        // TODO Daten in Fight-Tabelle schreiben
        // TODO wild: Daten in pokemon_fight schreiben
    }

    function getBattleBg()
    {
        // TODO Kampfhintergrund liefern
    }

    function getOpponentPokemon()
    {
        // TODO Trainer: Teampokemon des Gegners liefern
        // TODO Wild: Pokemondaten unserialisieren
    }

    function getUserPokemon()
    {
        // TODO Prüfen ob gewähltes Pokemon tot
        // TODO Ja: Nächst verfügbares liefern
        // TODO Nein: Gewähltes Pokemon liefern
    }

    function isTrainerFight()
    {
        // TODO Prüfen ob Trainerkampf
    }
}

