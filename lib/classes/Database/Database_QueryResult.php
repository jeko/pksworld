<?php

    class Database_QueryResult implements Iterator
{
    protected $_resource = null;
    protected $_rows = array();
    protected $_numRows = 0;
    protected $_affectedRows = 0;
    protected $_sql = '';
    protected $_fields = array();

    /**
     * liest alle Datensätze ein und speichert sie
     * intern ab. Gibt den Speicher der Resource danach
     * frei.
     */
    function __construct($queryResource, $sqlQuery)
    {
        $this->_resource        = $queryResource;
        $this->_numRows         = @mysql_num_rows($queryResource);
        $this->_affectedRows    = @mysql_affected_rows($queryResource);
        $this->_sql             = $sqlQuery;

        $this->readFieldNames();
        $this->readRows();
    }

    function readFieldNames()
    {
        $numFields = mysql_num_fields($this->_resource);
        $this->_fields = array();
        for ($i = 0; $i < $numFields; $i++) {
            $this->_fields[] = mysql_field_name($this->_resource, $i);
        }
    }

    function readRows()
    {
        $this->_rows = array();
        while ($row = mysql_fetch_object($this->_resource)) {
            $this->_rows[] = $row;
        }
    }

    function rewind()
    {
        return reset($this->_rows);
    }

    /**
     * bewegt den internen Datensatzzeiger um 1 und gibt
     * den dann aktuellen Datensatz zurück
     * @return object Nächster Datensatz oder false
     */
    function next()
    {
        return next($this->_rows);
    }

    /**
     * liefert den aktuellen Datensatz als
     * Object
     * @return object Datensatzobjekt (vgl. mysql_fetch_object())
     */
    function current()
    {
        return current($this->_rows);
    }

    /**
     * Alias für rewind()
     */
    function reset()
    {
        return $this->rewind();
    }

    /**
     * liefert die Number des Datensatzes startend bei 0
     * @return integer Datensatznummer
     */
    function key()
    {
        return key($this->_rows);
    }

    function valid()
    {
        return ($this->current() !== false);
    }

    /**
     * bewegt den internen Datensatzzeiger zum
     * ersten Datensatz und liefert ihn zurück
     * @return object Datensatzobject
     */
    function first()
    {
        return $this->rewind();
    }

    /**
     * bewegt den internen Datensatzzeiger zum
     * letzten Datensatz und gibt ihn zurück
     * @return object Datensatzobject
     */
    function last()
    {
        return $this->end($rows);
    }

    function sortBy($field = false)
    {
        // Ist $field nicht definiert, wird das erste Feld gewählt
        if ($field === false && isset($this->_fields[0])) {
            $field = $this->_fields[0];
        }
        // Check ob Feld in Abfrage vorhanden
        if (!in_array($field, $this->_fields)) {
            return false;
       }
        // Vergleichsfunktion für Feld erzeugen
        $compareFunction = create_function('$rowA,$rowB',
                                           'return (strcmp($rowA->' . $field . ', $rowB->' . $field .'));');
        usort($this->_rows, $compareFunction);
        return $this->_rows;
    }
}