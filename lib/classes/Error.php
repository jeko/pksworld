<?php

/**
 * Error-Handling
 *
 * @author dominique
 *
 */

class Error implements Error_Constants {
	/**
	 * Fehlernachrichten
	 * @var string
	 */
    static private $_errors = array();
    /**
     * speichert ob Objekt einen
     * Fehler erzeugt hat.
     * @var bool
     */
    private $_hasError = false;
    /**
     * speichert die letzte Fehlernachricht
     * @var string
     */
    private $_errorMessage = '';
    
 
    /**
     * gibt true zurück wenn Fehler
     * vorhanden sind und setzt
     * das hasError-Flag zurück.
     * @return bool
     */
    function hasError()
    {
	   $hasE = $this->_hasError;
	   // hasError zurücksetzen
	   $this->_hasError = false;
	   return $hasE;
    }
    /**
     * Fehler hinzufügen
     * @param string $msg
     * @return void
     */
    function error($msg, $file=false, $errorType=self::INFO)
    {
    	if ($msg != '') {
    		// Prüfen ob ein Dateiname mitgegeben wurde und an Nachricht anfügen
            if ($file !== false) {
            	// Documentroot entfernen
            	if (strpos($file, $_SERVER['DOCUMENT_ROOT']) !== false) {
            		$file = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
            	}
            	// Message prüfen
            	if (is_string($msg)) {
            	   $msg .= ' (' . $file . ')';
            	}
            	else {
            		$msg = 'FATAL: Fehlermeldung setzen ist fehlgeschlagen: Nachricht ist kein String. ( ' . $file . ')';
            		$errorType = Error::CRIT;
            	}
            }
            // Nachricht abspeichern
            self::$_errors[$errorType][] = $msg;
            $this->_errorMessage = $msg;
            // hasError-Flag setzen
            $this->_hasError = true;
    	}
    }
    /**
     * gibt die letzte Fehlermeldung zurück
     * falls vorhanden
     * @return string
     */
    function getLastErrorMessage() {
        if ($this->hasError()) {
        	return $this->_errorMessage;
        }
        else {
        	return false;
        }
    }
    /**
     * löscht alle Fehlermeldungen
     * @return void
     */
    static function clearErrors()
    {
    	self::$_errors = array();
    }
    
    /**
     * gibt alle Fehler zurück
     * @return array
     */
    static function getErrors($type)
    {
    	if (isset(self::$_errors[$type])) {
    		return self::$_errors[$type];
    	}
    	else {
    		return false;
    	}
    }
    /**
     * schreibt alle Fehler in den Log $logWriterObj und
     * löscht den Fehlerspeicher wenn für $clear true
     * gesetzt wurde.
     * @param Log_Writer $logWriterObj
     * @param bool $clear
     * @return void
     */
    static function logErrors($logWriterObj, $clear=false)
    {
    	foreach (self::$_errors as $errType => $errArray) {
    		$logWriterObj->writeArray($errArray, $errType);
    	}
    	if ($clear) {
    		self::$_errors = array();
    	}
    }
}

