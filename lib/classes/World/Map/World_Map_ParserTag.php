<?php

/**
 * Repräsentiert einen Map-Tag
 *
 * @author dominique
 *
 */

class World_Map_ParserTag extends World_Base
{
	/**
	 * erforderliche Attribute
	 * @var array
	 */
	private $_attributes = array();
	/**
	 * optionale Attribute
	 * @var array
	 */
	private $_optionalAttributes = array();
	/**
	 * Tag kann vorgeparst werden
	 * @var bool False wenn abhängig von Benutzereigenschaften
	 */
	private $_preParse = true;

	function __construct($tagName) {
		$this->loadData($tagName);
	}

	function loadData($tagName=false)
	{
		$fields = array('optional_attributes', 'attributes', 'pre_parse');
		$where = 'name="' . $tagName . '"';

		if ($query = self::$DB->selectByWhere(TABLE_CONST_MAP_TAG, $fields, $where, 'LIMIT 1')) {
			$row = $query->current();
			$this->_attributes = explode(',', $row->attributes);
			$this->_optionalAttributes = explode(',', $row->optional_attributes);
			$this->_preParse = ($row->pre_parse == 1)?true:false;
		}
		else {
			$this->error('Unbekannter Tag ' . $tagName . '.');
			return false;
		}
	}

	function saveData()
	{

	}

	/**
	 * gibt die erforderlichen Attribute zurück
	 * @return array
	 */
	function getAttributes()
	{
		return $this->_attributes;
	}

	/**
	 * gibt die erforderlichen Attribute zurück
	 * @return array
	 */
	function getOptionalAttributes()
	{
		return $this->_optionalAttributes;
	}
	/**
	 * gibt zurück, ob das Tag vorgeparst
	 * werden kann
	 * @return bool
	 */
	function isPreParse()
	{
		return $this->_preParse;
	}
}

