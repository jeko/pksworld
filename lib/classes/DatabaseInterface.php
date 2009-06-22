<?php

/**
 * Stellt ein Database-Interface zur Verfügung
 *
 * @author dominique
 *
 */

class DatabaseInterface extends Error
{
    /**
     * Zeichensatz für die DB-Verbindung
     * @var string
     */
    public $charset = 'utf8';
    /**
     * Lokalisierung der Verbindung
     * @var string
     */
    public $locale = 'de_DE';
    /**
     * die Resourcenid der Verbindung
     * @var resource
     */
	protected $_connectionId = null;
	/**
	 * Name der Datenbank
	 * @var string
	 */
	protected $_databaseName = '';
	/**
	 * die Resourcenid des letzten Querys
	 * @var resource
	 */
	protected $_queryId = null;
	/**
	 * der SQL-String des letzten Querys
	 * @var string
	 */
	protected $_querySql = '';
    /**
     * Array mit allen Datensätzen
     * des letzten Querys
     * @var array
     */
    protected $_queryRows = array();
    /**
     * Speichert, ob der letzte Query
     * fehlschlug oder nicht
     * @var bool
     */
    protected $_queryFailed = false;
    /**
     * Anzahl der Datensätze aus dem
     * letzten Query
     * @var integer
     */
    protected $_queryNumRows = -1;
    /**
     * Zeiger der auf einen Datensatz
     * im Array $_queryRows zeigt.
     * @var integer
     */
    protected $_queryRowPointer = -1;
    
    function __construct($host=false, $username=false, $password=false, $database=false)
    {
    	if ($host !== false) {
    		return $this->connect($host, $username, $password, $database);
    	}
    }
	
	/**
	 * stellt eine Verbindung zur Datenbank her und gibt die 
	 * Resourcen-Kennung der Verbindung zurück.
     * Bei Fehlern wird FALSE zurückgegeben.
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @return resource
	 */
	function connect($host,$username,$password,$database)
	{
		$this->_connectionId = @mysql_connect($host,$username,$password);
        if ($this->_connectionId !== false) {
            // verbunden, DB wählen
            if (@mysql_select_db($database,$this->_connectionId) === false) {
                $this->error('Could not select database '.$db.'. Disconnecting from database.', __FILE__, Error::DATABASE);
                $this->disconnect();
                return false;
            }
            else {
                // zeichensatz setzen
                if (@mysql_query("SET names '$this->charset'",$this->_connectionId)===false) {
                    $this->error('Could not set charset '.$this->charset.' for this connection.', __FILE__, Error::DATABASE);
                }
                // locale setzen
                if (@mysql_query("SET lc_time_names = '$this->locale'",$this->_connectionId)===false) {
                    $this->error('Could not set locale information '.$this->locale.' for this connection.', __FILE__, Error::DATABASE);
                }
                $this->_databaseName = $database;
            }
        }
        else {
            // verbinden fehlgeschlagen
            die('Could not connect to '.$host.' as '.$username);
            return false;
        }
        return $this->_connectionId;
	}
	/**
	 * Schliesst die Verbindung zur Datenbank 
	 * und gibt den Erfolg zurück.
	 * @return bool
	 */
	function close()
	{
		return $this->disconnect();
	}
	/**
	 * Schliesst die Verbindung und gib den
	 * Erfolg zurück.
	 * @return bool
	 */
	function disconnect()
	{
		$this->_databaseName = '';
		return @mysql_close($this->_connectionId);
	}
	
	/**
	 * Ruft Felder $fields von Tabelle $table
	 * @param $tables
	 * @param $fields
	 * @param $additionalSql
	 * @return unknown_type
	 */
	function select($tables, $fields, $additionalSql='')
	{
		$sql = 'SELECT'
               . ' ' . $this->toCsvString($fields)
               . ' FROM ' . $this->toCsvString($tables);
        return $this->query($sql);
	}
	
	/**
	 * Ruft die Felder $fields von Tabelle $table mit der 
	 * id $id ab. Bei Fehlern wird false zurückgegeben,
	 * andernfalls true.
     * @param string $table
	 * @param array $fields
	 * @param integer $id
	 * @param $additionalSql string ''
	 * @return bool
	 */
    function selectById($table,$fields,$id,$additionalSql='')
    {
    	$sql = 'SELECT'
    	       . ' ' . $this->toCsvString($fields)
    	       . ' FROM ' . $table
    	       . ' WHERE id=' . $this->getMaskedValue($id)
               . ' ' . $additionalSql;
        return $this->query($sql);        
    }
    /**
     * Fragt die Werte der in $fields definierten Felder ab, wobei
     * ein Join vom Typ $joinType (INNER, LEFT oder OUTER JOIN) über
     * die Tabellen $joinTables gemacht wird. Die ON-Bedingung wird mit
     * $onClause angegeben. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param string $table
     * @param array $fields
     * @param array $joinTables
     * @param string $onClause
     * @param string $joinType
     * @param string $whereClause
     * @param string $additionalSql
     * @return bool
     */
    function selectByJoin($tables,$fields,$joinTables,$onClauses,$joinType='INNER JOIN',$whereClause='1 > 0',$additionalSql='')
    {
    	// JoinClause zusammenstellen
    	$joinClause = '';
    	foreach ($joinTables as $key=>$joinTable) {
    		$joinClause .= $joinType . ' ' . $joinTable . ' ON ' . $onClauses[$key] . ' ';
    	}
    	
    	$sql = 'SELECT'
    	       . ' ' . $this->toCsvString($fields)
    	       . ' FROM  ' . $this->toCsvString($tables)
    	       . ' ' . $joinClause
    	       . ' WHERE '
    	       . $whereClause
    	       . ' ' . $additionalSql;
        return $this->query($sql);    	       
    }
    /**
     * Fragt Tabellenfelder $fields in den Tabellen $tables mit der SQL-Where-Klausel
     * $whereClause ab. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * Beispiel
     *  selectByWhere('users', array(
     *                                          'name',
     *                                          'register_date'),
     *                                          'age < 5 AND hair_color = 'blue'
     *                                          )
     *  Dieser Aufruf würde den Namen und das Registrierungsdatum aller
     *  Benutzer zurückgeben, die jünger als 5 Jahre alt sind und eine
     *  blaue Haarfarbe haben.
     * Bei Fehlern wird FALSE zurückgegeben.
     * @param array $tables
     * @param array $fields
     * @param string $whereClause
     * @param $additionalSql string ''
     * @return bool
     */
    function selectByWhere($tables,$fields,$whereClause,$additionalSql='')
    {
    	$sql = 'SELECT'
               . ' ' . $this->toCsvString($fields)
               . ' FROM ' . $this->toCsvString($tables)
               . ' WHERE ' . $whereClause
               . ' ' . $additionalSql;
        return $this->query($sql);
    }
    /**
     * fügt die Werte des Arrays $fieldsValues in die Felder
     * der Schlüssel des Arrays $fieldsValues ein in die Tabelle
     * $table. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param string $table
     * @param assoc_array $fieldsValues
     * @return bool
     */
    function insert($table,$fieldsValues,$additionalSql='')
    {
    	$sql = 'INSERT INTO'
    	       . ' ' . $table
    	       . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' ' . $additionalSql;
        return $this->query($sql);
    }
    /**
     * ändert die Felder des Arrays $fieldsValues auf die Werte
     * des Arrays $fieldsValues ein und zwar in den Datensatz
     * mit der Id $id in die Tabelle $table. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param string $table
     * @param assoc_array $fieldsValues
     * @param integer $id
     * @return bool
     */
    function updateById($table,$fieldsValues,$id,$additionalSql='')
    {
        $sql = 'UPDATE'
               . ' ' . $table
               . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' WHERE id=' . $id
               . ' ' . $additionalSql
               . ' LIMIT 1';
        return $this->query($sql);
    }
    /**
     * Ändert die Werte der Felder in $fieldsValues (Arrayschlüssel)
     * auf die Werte in $fieldsValues (Arraywerte) in allen Tabellen
     * $tables auf die die Bedingungen in $whereClause zutreffen.
     * Bei Fehlern wird false zurückgegeben, andernfalls true.
     * @param array $tables
     * @param assoc_array $fieldsValues
     * @param string $whereClause
     * @param string $additionalSql
     * @return bool
     */
    function updateByWhere($tables,$fieldsValues,$whereClause,$additionalSql='')
    {
        $sql = 'UPDATE'
               . ' ' . $this->toCsvString($tables)
               . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' WHERE '. $whereClause
               . ' ' . $additionalSql;
        return $this->query($sql);
    }
    /**
     * Fügt einen neuen Datensatz ein bzw.
     * ändert einen bestehenden sollte der Wert
     * des Primary Key Feldes schon belegt sein.
     * @param $table
     * @param $fieldsValues
     * @param $additionalSql
     * @return bool
     */
    function replace($table, $fieldsValues, $additionalSql='')
    {
        $sql = 'REPLACE'
               . ' ' . $table
               . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' ' . $additionalSql;
        return $this->query($sql);
    }
    /**
     * Fügt einen neuen Datensatz ein bzw.
     * ändert einen bestehenden sollte der Wert
     * des Primary Key Feldes schon belegt sein.
     * @param string $table
     * @param assoc_array $fieldsValues
     * @param integer $id
     * @param string $additionalSql
     * @return bool
     */
    function replaceById($table,$fieldsValues,$id,$additionalSql='')
    {
        $sql = 'REPLACE'
               . ' ' . $table
               . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' WHERE id=' . $this->getMaskedValue($id)
               . ' ' . $additionalSql
               . ' LIMIT 1';
        return $this->query($sql);
    }
    /**
     * Fügt hinzu bzw. ändert Datensätze, die
     * den Bedingungen der $whereClause entsprechen.
     * @param array $tables
     * @param assoc_array $fieldsValues
     * @param string $whereClause
     * @param string $additionalSql
     * @return bool
     */
    function replaceByWhere($tables,$fieldsValues,$whereClause,$additionalSql='')
    {
        $sql = 'REPLACE'
               . ' ' . $this->toCsvString($tables)
               . ' SET ' . $this->assocArrayToCsvKeyEqualValue($fieldsValues)
               . ' WHERE ' . $whereClause
               . ' ' . $additionalSql
               . ' LIMIT 1';
        return $this->query($sql);
    }
    /**
     * löscht den Eintrag mit der Id $id aus der Tabelle $table.
     * Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param string $table
     * @param integer $id
     * @return bool
     */
    function deleteById($table,$id)
    {
        $sql = 'DELETE FROM'
               . ' ' . $table
               . ' WHERE id=' . $this->getMaskedValue($id)
               . ' LIMIT 1';
        return $this->query($sql);
    }
    /**
     * löscht alle Einträge in den Tabellen
     * $tables, auf die die Bedingungen in
     * $whereClause zutreffen. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param $tables
     * @param $whereClause
     * @return bool
     */
    function deleteByWhere($tables,$whereClause)
    {
    	$sql = 'DELETE FROM'
    	       . ' ' . $this->toCsvString($tables)
    	       . ' WHERE ' . $whereClause;
    	return $this->query($sql);
    }
    
    function deleteByWhereCrossTable($tables, $fromTables, $whereClause)
    {
        $sql = 'DELETE '
               . ' ' . $this->toCsvString($tables) 
               . ' FROM ' . $this->toCsvString($fromTables)
               . ' WHERE ' . $whereClause;
        return $this->query($sql);
    }
    /**
     * führt einen Datenbankquery aus. Bei Fehlern wird false zurückgegeben,
     * andernfalls true.
     * @param string $sql
     * @return bool
     */
    private function query($sql)
    {
    	// Daten des letzten Querys zurücksetzen
    	$this->resetQueryData();
    	// Query ausführen
    	$erg = @mysql_query($sql);
    	if ($erg !== false) {
    		$this->_queryFailed = false;
    		$this->_queryId = $erg;
    		$this->_queryNumRows = @mysql_num_rows($erg);
    		$this->_queryAffectedRows = @mysql_affected_rows($erg);
    		$this->_queryRowPointer = 0;
    		$this->_querySql = $sql;
    		$this->readAllRows();
    		return true;
    	}
    	else {
    		$this->_queryFailed = true;
    		$this->error('Datenbankquery fehlgeschlagen: ' . mysql_error() . ' (' . $sql . ')', __FILE__, Error::DATABASE);
    		return false;
    	}
    }
    /**
     * Löscht die Daten des letzten Querys.
     * @return void
     */
    function resetQueryData()
    {
    	$this->_queryFailed = false;
    	$this->_queryId = null;
    	$this->_queryRows = array();
    	$this->_queryNumRows = 0;
    	$this->_queryRowPointer = 0;
    	$this->_querySql = '';
    }
    /**
     * liest alle Datensätze ein und speichert sie
     * intern ab.
     * @return bool
     */
    function readAllRows()
    {
    	if ($this->_queryFailed) {
    		return false;
    	}
    	else {
    		$this->_queryRows = array();
    		while (($row = @mysql_fetch_assoc($this->_queryId)) !== false) {
    			$this->_queryRows[] = $row;
    		}
            return true;
    	}
    }
    /**
     * liefert den aktuellen Datensatz, auf den
     * der Datensatzzeiger zeigt.
     * @return assoc_array
     */
    function getRow()
    {
   		if (isset($this->_queryRows[$this->_queryRowPointer])) {
   			return $this->_queryRows[$this->_queryRowPointer];
   		}
   		else {
   			return false;
   		}
    }
    /**
     * Bewegt den Datensatzzeiger um $steps
     * nach vorne bzw. nach hinten.
     * @param integer $steps
     * @return bool
     */
    function moveRowPointer($steps)
    {
    	// Bewegung auf Gültigkeit testen
    	$provRowPointer = $this->_queryRowPointer + $steps;
        $this->_queryRowPointer += $steps;
    	if ($provRowPointer > 0 && $provRowPointer < $this->getNumRows()) {
    		// $this->error('Datensatzzeiger verlässt definierten Bereich.', __FILE__, Error::NOTICE);
    	}
    	return true;
    }
    /**
     * Bewegt
     * den Datensatzzeiger um eins nach vorne
     * Ist der nächste Datensatz nicht verfügbar, wird
     * FALSE zurückgegeben.
     * @return assoc_array
     */
    function next()
    {
    	if ($this->moveRowPointer(1)) {
    		return true;;
    	}
    	else {
    		return false;
    	}
    }
    /**
     * Liefert den vorherigen Datensatz und setzt
     * den Datensatzzeiger um eins zurück. Ist der 
     * vorherige Datensatz nicht verfügbar, wird FALSE
     * zurückgegeben.
     * @return assoc_array
     */
    function back()
    {
        if ($this->moveRowPointer(-1)) {
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * setzt den Datensatzzeiger zurück auf
     * den ersten Datensatz.
     * @return unknown_type
     */
    function reset()
    {
    	$this->_queryRowPointer = 0;
    	return true;
    }
    /**
     * liefert die Anzahl Datensätze
     * @return integer
     */
    function getNumRows()
    {
    	if ($this->_queryFailed) {
    		return false;
    	}
    	else {
    		return $this->_queryNumRows;
    	}
    }
    /**
     * liefert die Anzahl betroffener Datensätze
     * @return integer
     */
    function getAffectedRows()
    {
        if ($this->_queryFailed) {
            return false;
        }
        else {
            return $this->_queryAffectedRows;
        }
    }
    /**
     * liefert den letzten SQL-Query zurück
     * @return string
     */
    function getLastQuery()
    {
    	return $this->_querySql;
    }
    /**
     * liefert die letzte eingefügte
     * Id (vgl. mysql_insert_id())
     * @return integer
     */
    function lastInsertId()
    {
    	if (!$this->_queryFailed) {
    		return mysql_insert_id();
    	}
    	else {
    		return false;
    	}
    }
    /**
     * ermittelt alle Datenbanktabellen
     * und gibt sie zurück
     * @return array
     */
    function getTables($tablePrefix = '')
    {
    	$escapeChars = array('_', '%');
    	$replaceBy = array('\_', '\%');
    	$cleanPrefix = str_replace($escapeChars, $replaceBy, $tablePrefix);
    	$likePattern = $cleanPrefix . '%';
    	$sql = 'SHOW TABLES LIKE "' . $likePattern . '"';
    	if ($this->query($sql)) {
    		$tables = array();
    		while ($row = $this->getRow()) {
    			$tables[] = $row['Tables_in_' . $this->getDatabaseName() . ' (' . $likePattern. ')'];
    			$this->next();
    		}
    		return $tables;
    	}
    	else {
    		return false;
    	}
    }
    /**
     * optimiert die Tabellen $tableNames
     * @param $tableNames array
     * @return bool
     */
    function optimizeTable($tableNames)
    {
    	$sql = 'OPTIMIZE TABLE ' . $this->toCsvString($tableNames);
    	return $this->query($sql);
    }
    /**
     * gibt den Namen der Datenbank zurück 
     */
    function getDatabaseName()
    {
    	return $this->_databaseName;
    }
    /**
     * Liefert dass assoziative Array $assocArray
     * als String zurück, der Schlüssel und Werte
     * als Kommagetrennte Key=Value-Paare enthält.
     * Nicht-numerische Werte werden in Anführungs-
     * und Schlusszeichen gesetzt.\n
     * Beispiel:\n
     * $string = assocArrayToCsvEqualValue( array('foo'=>43,'hi'=>'ho') );\n
     * echo $string;\n
     * Ausgabe:\n
     * foo=43, hi='ho'
     * @param assoc_array $assocArray
     * @return string
     */
    function assocArrayToCsvKeyEqualValue($assocArray)
    {
    	$keyEqualValueArray = array();
    	foreach ($assocArray as $key => $value) {
    		$keyEqualValueArray[] = $key
    		                        . '='
    		                        // Nicht-numerische Werte
    		                        // in Quotes verpacken
    		                        . ((!is_numeric($value))?
                                          "'$value'"
    		                              : $value
    		                              );
    	}
    	return implode(', ',$keyEqualValueArray);
    }
    
    function toCsvString($value)
    {
    	if (is_array($value)) {
    		return implode(',', $value);
    	}
    	else {
    		return $value;
    	}
    }
    
    function getMaskedValue($value)
    {
    	if (is_numeric($value)) {
    		return intval($value);
    	}
    	else {
    		return '"' . $value . '"';
    	}
    }
}

