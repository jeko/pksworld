<?php

/**
 * Repräsentiert ein Item
 *
 * @author dominique
 *
 */

class World_Item extends World_Base
{
	protected $_id = 0;
	protected $_displayName = '';
	protected $_itemGroup = 1;
	protected $_flags = 0;
	protected $_price = 0;
	const FLAG_TRADEABLE = 1;
	const FLAG_USEABLE = 2;
	const FLAG_CARRYABLE = 4;
	const FLAG_UNIQUE = 8;

	function __construct($item_id = false)
	{
		if ($item_id !== false) {
			$this->loadData($item_id);
		}
	}

	/**
	 * gibt den Anzeigenamen des Items zurück
	 * @return string
	 */
	function getDisplayName()
	{
		return $this->_displayName;
	}

	/**
	 * gibt den Gruppe des Items zurück
	 * @return integer
	 */
	function getItemGroup()
	{
		return $this->_itemGroup;
	}

	/**
	 * gibt die Id des Items zurück
	 * @return integer
	 */
	function getId()
	{
		return $this->_id;
	}
	
	function getPrice()
	{
		return $this->_price;
	}

	function saveData()
	{
		// darf nicht implementiert werden, da Tabelle konstant.
	}

	function loadData($item_id = false)
	{
		if ($item_id !== false) {
			$fields = array(
			'wci.name',
			'wci.item_group',
			'wcig.tradeable', 'wcig.carryable', 'wcig.useable', 'wcig.unique',
			'wci.price',
			'wci.description'
			);
			$where = 'wci.id=' . $item_id;
			$onClauses = array('wcig.id=wci.item_group');
			$joinTables = array('world_const_item_group AS wcig');
			$tables = array('world_const_item AS wci');
			
			if (self::$DB->selectByJoin($tables, $fields, $joinTables, $onClauses, 'INNER JOIN', $where)) {
				$this->_id = $item_id;
				$row = self::$DB->getRow();
				$this->_displayName = $row['name'];
				$this->_itemGroup = $row['item_group'];
				$this->_description = $row['description'];
				$this->_price = $row['price'];
				// Flags setzen
				$this->_flags = 0;
				if ($row['tradeable'] == 1) $this->_flags = $this->_flags | self::FLAG_TRADEABLE;
				if ($row['carryable'] == 1) $this->_flags = $this->_flags | self::FLAG_CARRYABLE;
				if ($row['unique'] == 1)    $this->_flags = $this->_flags | self::FLAG_UNIQUE;
				if ($row['useable'] == 1)   $this->_flags = $this->_flags | self::FLAG_USEABLE;
				
				return true;
			}
			else {
				$this->error('Konnte Daten zu Item ' . $item_id . ' nicht laden: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
				return false;
			}
		}
	}
}

