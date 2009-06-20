<?php

/**
 * Lesende Klasse für Logs
 *
 * @author dominique
 *
 */

class Log_Reader extends Log
{
	private $_logLines = array();
	
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
     * Öffnet das Logfile $file read-only.
     * @param string $file
     * @return bool
     */
    function open($file)
    {
        if (file_exists($file)) {
        	$lines = @file($file);
            if ($lines !== false) {
            	$sortedLines = array();
            	$regex = '@\[([2-9][0-9][0-9][0-9]-[01][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9])\]'
            	   . ' ([0-9]+) ([0-9]+ )?(.*)@';
                // fileHandle speichern
                foreach ($lines as $line) {
                	if (preg_match($regex, $line, $match)) {
                		$sortedLines[] = array('time' => $match[1], 'level' => $match[2], 'user' => $match[3], 'message' => $match[4]);
                	}
                }
                $this->_logLines = $sortedLines;
            }
            else {
                $this->error('Could not open '.$file.'.', __FILE__);
                return false;
            }
        }
        else {
            $this->error('File '.$file.' does not exist.', __FILE__);
            return false;
        }
    }
	
	/**
	 * Gibt den kompletten Inhalt des Logfiles zurück
	 * @return bool
	 */
	function getAll()
	{
		return $this->_logLines;
	}
	
	/**
	 * Gibt Einträge vom Zeitpunkt $startTime bis 
	 * $endTime zurück. Ist $endtime nicht angegeben wird
	 * der Parameter ignoriert; es werden alle 
	 * Einträge ab $startTime zurückgegeben.
	 * @param $startTime
	 * @param $endTime
	 * @return bool
	 */
	function getByTime($startTime,$endTime=0)
	{
		
	}
	
	/**
	 * Gibt Einträge, die die Bedingungen in
	 * $conditions erfüllen zurück.
	 * $conditions ist ein assoziatives Array von
	 * Bedingungen.\n Folgende Schlüssel sind möglich:\n
	 * - 'starttime' Liefert Einträge ab diesem Zeitpunkt\n
	 * - 'endtime' Liefert Einträge bis zu diesem Zeitpunkt\n
	 * - 'level' Liefert Einträge mit diesem Level\n
	 * - 'startlevel' Liefert Einträge mit diesem oder höherem Level\n
	 * - 'endlevel' Liefert Einträge mit diesem oder tieferem Level\n
	 * - 'beginmessage' Liefert Einträge, deren Text mit diesem Text beginnt\n
	 * - 'endmessage' Liefert Einträge, deren Test mit diesem Text endet\n
	 * Beispiel: Um alle kritischen Fehler zwischen 18:00 und 20:00 Uhr zu erhalten,
	 * ist das Array $conditions wie folgt aufgebaut:\n
	 * $conditions = array('starttime'=>'18:00','endtime'=>'20:00','level'=>Log_Constants::CRIT);\n
	 * Die Erkennung von Datum und Zeit in 'starttime' und 'endtime' erfolgt über
	 * die PHP-Funktion strtotime().
	 * @param assoc_array $conditions
	 * @return bool
	 */
	function getByConditions($conditions)
	{
		
	}
	
	
}

