<?php

/**
 * Session-Handling
 * Sessionmanagement
 * @author dominique
 *
 */

class World_Session extends World_Base {
    private $_sid = 0;
    private $_key = array();
    private $_secondaryKey = array();
    private $_data = array();
    private $_changed = array();

    const EXPIRES_AFTER = 3600;
    const KEY_SEPARATOR = '-';
    const KEY_PARTS = 4;

    function __construct()
    {
    }
    /**
     * startet eine neue Session oder
     * greift eine bestehende auf
     * @param $sid integer
     * @param $key integer
     * @return void
     */
    function start($sid = false, $key = false)
    {
        $sid = intval($sid);
        $key = $this->uncompressKey($key);
        // Session überprüfen
        if ($sid !== false && $this->checkSession($sid, $key)) {
            $this->_sid = $sid;
            $this->loadData();
        }
        else {
            // neue Session starten
            $this->_sid = rand();
            $this->_key = $this->generateKey();
            $this->_secondaryKey = $this->generateKey();
            $this->_data = array();
            $this->_changed = array();
            $this->saveData();
        }
    }

    /**
     * prüft die Session gegen den Datenbank bestand 
     * auf Gültigkeit, sie abgelaufen ist und ob der Key
     * stimmt. Sollte der Primarykey nicht stimmen,
     * wird gegen den Secondarykey geprüft und ein Wechsel
     * der Keys vollzogen.
     * @param $sid integer
     * @param $key integer
     * @return bool
     */
    function checkSession($sid, $key)
    {
        $table = TABLE_SESSION;
        $expiring = time() - self::EXPIRES_AFTER;
        $whereBase = 'sid=' . $sid . ' AND time > ' . $expiring;
        if ($this->validateKey($key)) {
            // Primärschlüssel prüfen
            $where = '';
            foreach ($key as $num => $val) {
                $where .= ' AND primary_key_' . $num . '=' . $val;
            }
            $where = $whereBase . $where;
            if (self::$DB->selectByWhere($table, '1', $where)) {
                if (self::$DB->getNumRows() > 0) {
                    return true;
                }
                else {
                    // Primärschlüssel stimmt nicht, daher
                    // Sekundärschlüssel prüfen
                    $where = '';
                    foreach ($key as $num => $val) {
                        $where .= ' AND secondary_key_' . $num . '=' . $val;
                    }
                    $where = $whereBase . $where;
                    if (self::$DB->selectByWhere($table, '1', $where)) {
                        if (self::$DB->getNumRows() > 0) {
                            // Schlüsselwechsel vollziehen
                            $this->setNewSecondaryKey();
                            return true;
                        }
                        else {
                            // Keiner der beiden Schlüssel stimmt
                            return false;
                        }
                    }
                    else {
                        $this->error('Fehler beim Prüfen der Session (Sekundärschlüssel).', __FILE__, Error::DATABASE);
                        return false;
                    }
                }
            }
            else {
                $this->error('Fehler beim Prüfen der Session (Primärschlüssel).', __FILE__, Error::DATABASE);
                return false;
            }
        }
        else {
            return false;
        }
    }
    /**
     * lädt Sessiondaten (non-PHPdoc)
     * @see lib/classes/World/World_Base#loadData()
     */
    function loadData($sid = false)
    {
        $where = 'sid=' . $this->getSid();
        // Keys laden
        $table = TABLE_SESSION;
        $fields = array();
        $fields = array_merge($fields, $this->getKeyQueryFields('primary_key_'));
        $fields = array_merge($fields, $this->getKeyQueryFields('secondary_key_'));

        if (self::$DB->selectByWhere($table, $fields, $where)) {
            $row = self::$DB->getRow();
            $this->_key = array();
            $this->_secondaryKey = array();
            for ($i = 0; $i < self::KEY_PARTS; $i++) {
                $this->_key[$i] = $row['primary_key_' . $i];
                $this->_secondaryKey[$i] = $row['secondary_key_' . $i];
            }

            // Sessionvariablen laden
            $table = TABLE_SESSION_DATA;
            $fields = array('name');
            if (self::$DB->selectByWhere($table, $fields, $where)) {
                $this->_data = array();
                while ($row = self::$DB->getRow()) {
                    $this->set($row['name'], -1);
                    self::$DB->next();
                }
                return true;
            }
            else {
                $this->error('Kann Sessionvariablen nicht auslesen.', __FILE__, Error::DATABASE);
                return false;
            }
        }
        else {
            $this->error('Kann Sessionkeys nicht auslesen.', __FILE__, Error::DATABASE);
            return false;
        }
    }

    /**
     * lädt den Wert der Sessionvariable
     * $name
     * @param $name string
     * @return mixed
     */
    function load($name)
    {
        $table = TABLE_SESSION_DATA;
        $fields = array('value');
        $where = 'sid=' . $this->getSid() . ' AND name="' . $name . '"';
        if (self::$DB->selectByWhere($table, $fields, $where)) {
            if (self::$DB->getNumRows() > 0) {
                $row = self::$DB->getRow();
                $this->_data[$name] = $row['value'];
                return true;
            }
        }
        else {
            $this->error('Kann Wert von ' . $name . ' nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
        }
        return false;
    }

    function saveData()
    {
        $this->saveSession();
        $this->saveSessionVars();
    }
    /**
     * speichert die Session-Daten wie Keys und Zugriffszeit
     * @return bool
     */
    function saveSession()
    {
        // Session schreiben
        $sessionTable = TABLE_SESSION;
        $fieldsValues = array('sid' => $this->getSid(), 'time' => time());
        // Primärschlüssel hinzufügen
        $fieldsValues = $fieldsValues + $this->getKeyQueryFields('primary_key_', $this->_key);
        // Sekundärschlüssel hinzufügen
        $fieldsValues = $fieldsValues + $this->getKeyQueryFields('secondary_key_', $this->_secondaryKey);
        // In die Datenbank schreiben
        if (self::$DB->replace($sessionTable, $fieldsValues)) {
            return true;
        }
        else {
            $this->error('Kann Sessiondaten nicht schreiben.', __FILE__, Error::DATABASE);
        }
    }
    function saveSessionVars()
    {
        // Sessionvariablen schreiben
        $sessionVarTable = TABLE_SESSION_DATA;
        foreach ($this->_data as $name => $value) {
            if (isset($this->_changed[$name])) {
                self::$DB->replace($sessionVarTable, array('sid' => $this->getSid(), 'name' => $name, 'value' => $value));
            }
        }
    }
    /**
     * löscht die Session und alle damit verbundenen
     * Session-Daten
     * @return bool
     */
    function destroy()
    {
        $tables = array(TABLE_SESSION, TABLE_SESSION_DATA);
        $sid = $this->getSid();
        $where = TABLE_SESSION . '.sid=' . $sid . ' OR ' . TABLE_SESSION_DATA . '.sid=' . $sid;
        if (self::$DB->deleteByWhereCrossTable($tables, $tables, $where)) {
            return true;
        }
        else {
            $this->error('Kann Session nicht zerstören: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
            return false;
        }
    }

    /**
     * ersetzt Primärschlüssel durch Sekundärschlüssel
     * und setzt einen neuen Sekundärschlüssel
     * @return void
     */
    function flipKeys()
    {
        $key_ = $this->_key;
        $this->_key = array_values($this->_secondaryKey);
        $this->_secondaryKey = $key_;
    }
    
    function setNewSecondaryKey()
    {
        $this->_secondaryKey = $this->generateKey();
        $this->saveSession();
    }

    /**
     * generiert einen neuen Key und gibt ihn
     * zurück
     * @return array
     */
    function generateKey()
    {
        $key = array();
        // Key generieren
        for ($i = 0; $i < self::KEY_PARTS; $i++) {
            $key[] = rand();
        }
        return $key;
    }
    /**
     * Teilt einen String-Key in seine Bestandteile auf
     * @param $key string
     * @return array
     */
    function uncompressKey($key)
    {
        // Key dekomprimieren
        if (is_string($key)) {
            $keyUncompressed = explode(self::KEY_SEPARATOR, $key);
            return $keyUncompressed;
        }
        else {
            return $key;
        }
    }
    /**
     * validiert einen Key
     * @param $key
     * @return unknown_type
     */
    function validateKey($key)
    {
        // Prüfen auf richtige Länge und Format
        if (is_array($key)) {
            for ($i = 0; isset($key[$i]) && $i < self::KEY_PARTS; $i++) {
                if (!is_numeric($key[$i])) {
                    break;
                }
            }
            //
            if ($i == self::KEY_PARTS) {
                return true;
            }
        }
        // Key scheint korrupt
        return false;
    }
    /**
     * Erzeugt Arrays zur Nutzung in diversen
     * Key-Abfragen; ist ein $key
     * mitgegeben, so werden die Felder mit "=$key[$i]" erzeugt.
     * @param $fieldPrefix string
     * @param $key array
     * @return array
     */
    function getKeyQueryFields($fieldPrefix, $key = false)
    {
        $fields = array();
        for ($i = 0; $i < self::KEY_PARTS; $i++) {
            if ($key !== false) {
                $fields[$fieldPrefix . $i] = (isset($key[$i]))?$key[$i]:0;
            }
            else {
                $fields[$i] = $fieldPrefix . $i;
            }
        }
        return $fields;
    }
    /**
     * gibt den Schlüssel dieser
     * Session zurück
     * @return array
     */
    function getKey()
    {
        return $this->_key;
    }
    /**
     * gibt den Key als String zurück.
     * Teile getrennt durch Trennzeichen
     * @return string
     */
    function getKeyAsString()
    {
        return implode(self::KEY_SEPARATOR, $this->_key);
    }
    /**
     * gibt die Sessionid zurück
     * @return string
     */
    function getSid()
    {
        return $this->_sid;
    }
    /**
     * gibt den Wert der Sessionvariable
     * $name zurück.
     * @param $name string
     * @return mixed
     */
    function get($name)
    {
        if (isset($this->_data[$name])) {
            if ($this->_data[$name] == -1) {
                $this->load($name);
            }
            return $this->_data[$name];
        }
        else {
            return false;
        }
    }

    function __get($name)
    {
        return $this->get($name);
    }
    /**
     * setzt eine Sessionvariable $name mit
     * dem Wert $value
     * @param $name string
     * @param $value string
     * @return void
     */
    function set($name, $value)
    {
        $this->_data[$name] = $value;
        if ($value != -1) {
            $this->_changed[$name] = true;
        }
    }
    
    function __set($name, $value)
    {
        $this->set($name, $value);
    }
    
    function exists($name)
    {
        if (isset($this->_data[$name])) {
            return true;
        }
        else {
            return false;
        }
    }
}
