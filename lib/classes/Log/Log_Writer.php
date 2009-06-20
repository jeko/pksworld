<?php

/**
 * Schreibende Klasse für Logs
 *
 * @author dominique
 *
 */

class Log_Writer extends Log
{    
    /**
     * öffnet das File $file wenn angegeben
     * @param $file
     * @return void
     */
    function __construct($file='')
    {
        if ($file!='') {
            $this->open($file);
        }
    }
	
    /**
     * Öffnet das Logfile $file append-only.
     * @param string $file
     * @return bool
     */
    function open($file)
    {
        $fh = @fopen($file,'a');
        if ($fh !== false) {
        	if (is_writeable($file)) {
	        	// zuerst geöffnetes File schliessen
	        	if ($this->_fileHandle != null) {
	                   $this->close();
	          	}
	           	// fileHandle speichern
	           	$this->_fileHandle = $fh;
	           	$this->_file = $file;
	        }
            else {
                $this->error('File '.$file.' is not writeable.', __FILE__);
                return false;
            }
        }
        else {
          	$this->error('Could not open '.$file.' append-only (mode=a).', __FILE__);
           	return false;
        }
    }
    
    function log($message, $type)
    {
        
    }
    
    
    function write($msg,$messageLevel = self::INFO)
    {
    	if (@fwrite($this->_fileHandle, $this->formatLine($msg,$messageLevel) . $this->entrySeparator)) {
            return true;
    	}
    	else {
            $this->error('Konnte nicht in Datei schreiben.', __FILE__);
            return false;
    	}
    }
    
    function writeArray($lines,$messageLevel = self::INFO)
    {
    	foreach ($lines as $line) {
    		$this->write($line,$messageLevel);
    	}
    	return true;
    }
    
    /**
     * liefert den Eintrag als schreibbare
     * Zeile nach dem Format in $this->logFormat
     * @return string
     */
    function formatLine($message,$type)
    {
        // in Log-format bringen und Eintragstrenner entfernen   
        $line = $this->logFormat;
        $line = str_replace(
                   array('%timestamp', '%type', '%message', $this->entrySeparator),
                   array(
                       date($this->dateFormat),
                       $type,
                       $message,
                       ''),
                   $line);     
        return $line;
    }
}

