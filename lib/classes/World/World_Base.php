<?php

/**
 * Repräsentiert eine Basisklasse die
 * allen anderen World Klassen zugrunde liegen
 * sollte, da sie einen einheitlichen Zugriff
 * auf die Datenbank und das Error-
 * handling bietet.
 *
 * @author dominique
 *
 */

abstract class World_Base extends Error
{
    /**
     * Zeigt auf das DatabaseInterface
     * @var DatabaseInterface
     */
    static $DB = null;
    /**
     * Zeigt auf den Weltlog
     * @var Log_Writer
     */
    static $LOG = null;
    /**
     * Zeigt auf die Session
     * @var World_Session
     */
    static $SESSION = null;
    /**
     * Zeigt auf den eingeloggten User
     * @var World_User
     */
    static $USER = null;
    
        /**
     * Liest Daten von der Datenbank und initialisiert damit
     * Objekt.
     * Muss von den abgeleiteten Klassen invididuell
     * implementiert werden.
     * @return bool
     */
    abstract function loadData();
    /**
     * Speichert die Daten des Objekts in der Datenbank ab.
     * Muss von den abgeleiteten Klassen invididuell
     * implementiert werden.
     * @return bool
     */
    abstract function saveData();
    
    function error($msg, $file=false, $errorType=self::INFO)
    {
    	if (self::$USER instanceof World_User) {
    		$msg .= ' (' . $file . ')';
    		self::$USER->log($msg, $errorType);
    	}
    	else {
    	   parent::error($msg, $file, $errorType);
    	}
    }
}

