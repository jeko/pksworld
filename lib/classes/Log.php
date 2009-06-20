<?php

/**
 * Interface für Log-Klassen, das v.a. Konstanten für 
 * Log-Klassen zur Verfügung stellt.
 * Konstanten für Fehlerlevel sind dem Zend Framework
 * entnommen, die RFC-3164 befolgen.
 *
 * @author dominique
 *
 */

abstract class Log extends Error implements Log_Constants
{
    /**
     * Ort des Logfiles
     * @var string
     */
    protected $_file = '';
    /**
     * Handle auf das geöffnete Logfile
     * @var resource
     */
    protected $_fileHandle = null;
    /**
     * Speichert die Lognachrichten
     * @var array
     */
    protected $_entries = array();
    
    public $logFormat = self::DEFAULT_LOG_FORMAT;
    public $dateFormat = self::DEFAULT_DATE_FORMAT;
    public $entrySeparator = self::DEFAULT_ENTRY_SEPARATOR;
    
    /**
     * öffnet das File $file;
     * @param string $file
     * @return bool
     */
    abstract function open($file);
    
    /**
     * Schliesst das Logfile
     * @return bool
     */
    function close()
    {
        $erg = @fclose($this->_fileHandle);
        $this->_fileHandle = null;
        return $erg;
    }
}