<?php

class Database_QueryResult implements Iterator
{
	protected $_resource = null;
	protected $_rows = array();
	
	/**
	 * liest alle Datensätze ein und speichert sie 
	 * intern ab. Gibt den Speicher der Resource danach
	 * frei.
	 */
	function __construct($queryResource)
	{
		$this->_resource = $queryResource;
		$this->_rows = array();
		while ($row = mysql_fetch_object($queryResource)) {
			$this->_rows[] = $row;
		}
		mysql_free_result($queryResource);
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
		$this->rewind();
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
}