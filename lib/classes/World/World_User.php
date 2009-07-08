<?php
    /**
     * Repräsentiert einen Benutzer
     *
     * @author dominique
     *
     */
     

class World_User extends World_Base
{
    protected $_id           = 0;
    protected $_userName     = '';
    protected $_loaded       = false;
    protected $_money        = '';
    protected $_pos          = 0;
    protected $_lastHealSpot = 0;
    
    static $table            = TABLE_USER;
    public $loadedModules    = array();
    private $_definedModules = array();

    function __construct($id=false)
    {
        if ($id !== false) {
            $this->loadData($id);
        }
    }

    function __get($varName)
    {
        // schauen ob in modules vorhanden
        if (key_exists($varName, $this->_definedModules)) {
        return $this->getModule($varName);
        }
        else {
            $this->error('Unbekannte Variable: ' . $varName, __FILE__, Error::WARN);
        }
    }
    /**
     * registriert ein Modul
     * @param $moduleIdentifier string
     * @param $moduleClassName string
     * @return void
     */
    function registerModule($moduleIdentifier, $moduleClassName)
    {
        $this->_definedModules[$moduleIdentifier] = $moduleClassName;
    }
    /**
     * Gibt ein Modul zurück. Das Modul wird
     * nachgeladen falls es noch nicht geladen
     * wurde.
     * @param $identifier
     * @return unknown_type
     */
    function getModule($identifier)
    {
        if (!isset($this->loadedModules[$identifier])) {
            if (isset($this->_definedModules[$identifier])) {
                $className = $this->_definedModules[$identifier];
                $this->loadedModules[$identifier] = new World_Module($identifier, $className);
                $this->loadedModules[$identifier]->loadModule();
            }
            else {
                $this->error('Unbekanntes Modul ' . $identifier . ': Laden fehlgeschlagen.', __FILE__, Error::WARN);
                return false;
            }
        }
        return $this->loadedModules[$identifier]->getModule();
    }
    /**
     * gibt zurück ob ein Modul geladen wurde
     * @param $identifier string
     * @return bool
     */
    function moduleIsLoaded($identifier)
    {
        return isset($this->loadedModules[$identifier]);
    }
    /**
     * speichert ein Modul in die Datenbank und in den Cache ab,
     * falls es geladen wurde.
     * @param $identifier string
     * @return bool
     */
    function saveModule($identifier)
    {
        if ($this->moduleIsLoaded($identifier)) {
            $module = $this->getModule($identifier);
            if ($module instanceof World_Base) {
                $module->saveData();
                return true;
            }
            else {
                $this->error('Modulspeicherung fehlgeschlagen: Falscher Datentyp (muss von World_Base abgeleitet sein).', __FILE__, Error::CRIT);
                return false;
            }
        }
        return true;
    }
    /**
     * entlädt alle Module
     * @return void
     */
    function unloadModule($identifier = 'all')
    {
        if ($identifier == 'all') {
            $this->loadedModules = array();
        }
        else {
            if (isset($this->loadedModules[$identifier])) {
                unset($this->loadModules[$identifier]);
            }
        }
    }

    function reloadModule($identifier = 'all')
    {
        if ($identifier == 'all') {
            foreach ($this->loadedModules as $id => $module) {
                $module->loadModule(true);
            }
        }
        else {
            $module = $this->getModule($identifier);
            $this->loadedModules[$identifier]->loadModule(true);
        }
    }

    function getId()
    {
        return $this->_id;
    }

    function getDisplayName()
    {
        return $this->_userName;
    }

    function getTeamPokemon()
    {
        return $this->getPokemonTeam()->getPokemons();
    }


    function saveData()
    {
        // Module speichern
        foreach ($this->loadedModules as $module) {
            $module->saveData();
        }

        // User-Daten speichern
        $fieldsValues = array();
        $fieldsValues['money'] = $this->getMoney();
        $fieldsValues['map_id'] = $this->getPos();
        $fieldsValues['last_heal_spot'] = $this->getLastHealSpot();
        if (self::$DB->updateById(self::$table, $fieldsValues, $this->getId())) {
            return true;
        }
        else {
            $this->error('Konnte Daten nicht speichern: Query fehlgeschlagen.', __FILE__);
            return false;
        }
    }

    function loadData($id = false)
    {
        if ($id !== false) {
            // Daten aus Datenbank laden
            $fields = array('name', 'money', 'map_id', 'last_heal_spot');
            if ($query = self::$DB->selectById(TABLE_USER,$fields,$id)) {
                $row = $query->current();
                $this->_id = $id;
                $this->_money = $row->money;
                $this->_pos = $row->map_id;
                $this->_lastHealSpot = $row->last_heal_spot;
                $this->_userName = $row->name;
                $this->_loaded = true;
                return true;
            }
            else {
                $this->_loaded = false;
                $this->error('Konnte Daten nicht laden: Query fehlgeschlagen.', __FILE__);
                return false;
            }
            return $this;
        }
    }

    /**
     * gibt zurück, ob der Benutzer geladen wurde
     * @return bool
     */
    function isLoaded()
    {
        return $this->_loaded;
    }

    function changeMap($targetMapId, $force = false)
    {
        // Zugang überprüfen
        if ($force === true) {
            $access = true;
        }
        else {
            $map = $this->getMap();
            if ($map->canAccess($targetMapId)) {
                $access = true;
            }else {
                $access = false;
            }
        }
        // User-Position verändern
        if ($access === true) {
            $this->_pos = $targetMapId;
            $this->reloadModule('map');

            // Heilspot abspeichern
            if ($this->getModule('map')->getFlags() & World_Map::FLAG_HEAL) {
                $this->_lastHealSpot = $this->getModule('map')->getId();
            }
			
            $this->log('Karte gewechselt, von ' . $this->map->getId() . ' nach ' . $targetMapId . '.', Log::MAP);
            return true;
        }
        else {
            $this->error('Kann Userposition (' . $this->getPos() . ' -> ' . $targetMapId . ') nicht verändern: Kein gültiger Zugang zum Ziel.', __FILE__, Error::ERR );
            return false;
        }
    }
    
    /**
     * Überprüft ob der Benutzer in einem Kampf ist.
     * @return bool true wenn Benutzer im Kampf
     */
    function isInFight()
    {
        $where = 'user_id=' . $this->getId();
        $success = self::$DB->selectByWhere(TABLE_FIGHT, '1', $where);
        if ($success) {
            if ($success->getNumRows() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            $this->error('Konnte Kampfinformation nicht abfragen: Query fehlgeschlagen.', __FILE__, Error::WARN);
        }
    }

    /**
     * fügt $amount zum Geldbestand hinzu
     * bzw. zieht $amount ab (bei negativem Wert)
     * Gibt den neuen Bestand zurück.
     * @param $amount
     * @return integer
     */
    function giveMoney($amount)
    {
        $this->_money += $amount;
        if ($amount > 0) {
            $this->log('Kontostand um ' . $amount . 'PD erhöht.', Log::MONEY_GAIN);
        }
        else {
            $this->log('Kontostand um ' . $amount . 'PD verkleinert.', Log::MONEY_LOSS);
        }
        return $this->_money;
    }
    /**
     * Gibt den aktuellen Geldbestand zurück
     * @return integer
     */
    function getMoney()
    {
        return $this->_money;
    }
    /**
     * gibt die ID der letzten Karte mit Heilspot zurück
     * @return integer
     */
    function getLastHealSpot()
    {
        return $this->_lastHealSpot;
    }
    /**
     * gibt die ID der Karte zurück,
     * auf der sich der User gerade
     * befindet
     * @return integer
     */
    function getPos()
    {
        return $this->_pos;
    }
    /**
     * fügt die Anzahl $quantity vom Gegenstand
     * $item zum Inventar des Users hinzu.
     * @param $item World_Item
     * @param $quantity integer
     * @return bool
     */
    function giveItem($item, $quantity=1)
    {
        if ($this->getModule('inventory')->addItem($item, $quantity)) {
            $this->log('Item #'.$item->getId().' '.$quantity.'mal hinzugefügt.', Log::ITEM_GAIN);
            return true;
        }
        else {
            return false;
        }
    }

    function removeItem($item, $quantity=1)
    {
        if ($this->inventory->removeItem($item, $quantity)) {
            $this->log('Item #'.$item->getId().' '.$quantity.'mal entfernt.', Log::ITEM_LOSS);
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * kauft $quantity Anzahl $item Gegenstände. Wenn special_price
     * nicht angegeben wird, wird der Standardpreis des Items genommen.
     * Ist das Vermögen zu klein, wird false zurückgegeben.
     * @param $item World_Item
     * @param $quantity integer
     * @param $special_price integer
     * @return bool
     */
    function buyItem($item, $quantity=1, $special_price=false)
    {
        // Totalbetrag berechnen
        if ($special_price === false) {
            $price = $item->getPrice();
        }
        else {
            $price = $special_price;
        }
        $sum = $price * $quantity;
        // Check ob genügend Geld vorhanden
        if ($this->getMoney() >= $sum) {
            // Betrag vom Konto abziehen und Items gutschreiben
            $this->giveMoney(-1 * $sum);
            $this->giveItem($item, $quantity);
            $this->log($quantity . 'mal Item #' . $item->getId() . ' für ' . $price . 'PD/Stück gekauft.', Log::ITEM_BUY);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * verkauft $quantity Anzahl $item Gegenstände.
     * Sind zu wenige Gegestände vorhanden wird false zurückgegeben.
     * @param $item World_Item
     * @param $quantity integer
     * @return bool
     */
    function sellItem($item, $quantity=1)
    {
        // Check ob Item vorhanden
        $itemStack = $this->inventory->getItemStackByItemId($item->getId());
        if ($itemStack !== false && $itemStack->getQuantity() >= $quantity) {
            // Items aus Inventar entfernen und Betrag gutschreiben
            $this->removeItem($item, $quantity);
            $this->giveMoney($item->getPrice() * $quantity);
            $this->log($quantity . 'mal Item #' . $item->getId() . ' für ' . $item->getPrice() . 'PD/Stück verkauft.', Log::ITEM_SELL);
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * Fängt ein wildes Pokemon ein und versorgt
     * es ins Team bzw. in die Lagerbox
     * @param $pokemon World_Pokemon
     * @return bool
     */
    function catchPokemon($pokemon)
    {
        if ($this->hasPokemonSpace()) {
            // Werte des Pokemon anpassen
            $userpkmn = new World_UserPokemon($this);
            //TODO fertig machen
        }
        else {
            $this->error('Es ist kein Platz für ein neues Pokemon mehr vorhanden.', __FILE__, Error::NOTICE);
            return false;
        }
    }
    /**
     * Überprüft ob noch Platz für neue Pokemon
     * da ist (Team und Lagerbox)
     * @return bool true wenn Platz vorhanden
     */
    function hasPokemonSpace()
    {
        return ($this->pokemonTeam->hasFreeSpace() || $this->storeBox->hasFreeSpace());
    }

    function attackIsAvailable($attackName)
    {
        $this->getModule('pokemonTeam');
        return $this->pokemonTeam->attackAvailable($attackName);
    }

    function hasItem($itemName)
    {
        return $this->inventory->itemExists($itemName);
    }

    function log($msg, $type)
    {
        if ($msg != '') {
            self::$LOG->write($this->getId() . ' ' . $msg, $type);
        }
    }
    /**
     *
     * @param $pokemonObj World_Pokemon
     * @return unknown_type
     */
    function addPokemon(World_PokemonInstance $pokemonObj)
    {
        $userPokemon = new World_UserPokemon($this);
        $userPokemon->createFromPokemon($pokemonObj);
        if ($this->getPokemonTeam()->hasFreeSpace()) {
            return $this->getPokemonTeam()->add($userPokemon);
        }
        else if ($this->getStoreBox()->hasFreeSpace()) {
            return    $this->getStoreBox()->addPokemon($userPokemon);
        }
        else {
            $this->error('Kann Pokemon nicht hinzufügen: Team und Lagerboxen voll.', __FILE__, Error::WARN);
            return false;
        }
    }
}
