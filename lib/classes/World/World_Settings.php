<?php

/**
 * 
 * @author dominik
 *
 */

class World_Settings extends World_Base
{
	/*
	 * Beinhaltet Einstellungen aus der Datenbank in einem Array.
	 */
	protected $_constSettings = array();
	protected $_userSettings = array();

	function __construct()
	{
	}

	function loadData()
	{
		$userId = self::$USER->getId();
		// Konstante Einstellungen laden.
		$fields = array('setting_id', 'setting_name', 'setting_display_name', 'setting_description', 'setting_value_type', 'setting_default_value', 'setting_values');
$query = self::$DB->select(TABLE_CONST_USER_SETTINGS, $fields);
		foreach ($query as $row) {
			$values = explode('|', $row->setting_values);
			if (count($values) > 1) {
				$row->setting_values = $values;
			}

			$this->_constSettings[$row->setting_name] = $row;
			// Default Wert setzen.
			$this->_userSettings[$row->setting_name] = $row->setting_default_value;
			self::$DB->next();
		}

		// User Einstellungen laden.
		foreach($this->_constSettings as $settingName => $value) {
			$fields = array('user_id', 'setting_id', 'value');
			$query = self::$DB->selectByWhere(TABLE_USER_SETTINGS, $fields, 'user_id=' . $userId . ' AND setting_id='.$value['setting_id'], 'LIMIT 1');
			if($query->getNumRows() == 1) {
				$row = $query->current();
				// Wert setzen.
				$this->_userSettings[$settingName] = $row->value;
			}
		}
	}

	/*
	 * Speichert User Einstellungen.
	 */
	function saveData()
	{
		$userId = self::$USER->getId();
		foreach($this->_userSettings as $settingName => $value) {
			$setting = $this->getConstSetting($settingName);
			$fields = array('user_id'=>$userId, 'setting_id'=>$setting['setting_id'], 'value' => $value);
			self::$DB->replace(TABLE_USER_SETTINGS, $fields);
		}
	}

	function setSetting($name, $value)
	{
		// Exestiert Einstellung?
		if(isset($this->_userSettings[$name])) {
			$this->_userSettings[$name] = $value;
		}
		else {
			return false;
		}
	}

	function getConstSetting($name)
	{
		return $this->_constSettings[$name];
	}

	function getConstSettings()
	{
		return $this->_constSettings;
	}

	function getAllUserSettings()
	{
		return $this->_userSettings;
	}

	function getUserSettings($name)
	{
		// Exestiert Einstellung?
		if(isset($this->_userSettings[$name])) {
			return $this->_userSettings[$name];
		}
		else {
			return false;
		}
	}

}