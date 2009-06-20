<?php

/**
 * Modul
 *
 * @author dominique
 *
 */

class World_Module extends World_Base
{
	private $_moduleClassName = '';
	private $_moduleIdentifier = '';
	private $_module = null;
	private $_dataHash = 0;

	const SERIALIZED_DATA_PREFIX = 'data_';

	function __construct($identifier, $className)
	{
		$this->_moduleClassName = $className;
		$this->_moduleIdentifier = $identifier;
	}
	function getClassName()
	{
		return $this->_moduleClassName;
	}
	function getModule()
	{
		return $this->_module;
	}
	function getIdentifier()
	{
		return $this->_moduleIdentifier;
	}
	function getModuleCacheIdentifier()
	{
		return self::SERIALIZED_DATA_PREFIX . $this->getIdentifier();
	}
	function setModuleData($data)
	{
		// Klasse des Datenstücks prüfen
		$class = $this->getClassName();
		if ($data instanceof $class) {
			$this->_module = $data;
			return true;
		}
		else {
			return false;
		}
	}
	/**
	 * lädt ein Modul vom Cache bzw. baut es neu auf
	 * und speichert es in den internen Modulspeicher.
	 * @param $identifier string
	 * @return void
	 */
	function loadModule($force = false)
	{
		// Sessioncache überprüfen
		$sessionVar = $this->getModuleCacheIdentifier();
		if ($force == false && self::$SESSION->exists($sessionVar)) {
			$sessionData = self::$SESSION->get($sessionVar);
			$this->_dataHash = md5($sessionData);
			$sessionData = unserialize($sessionData);
			if (!$this->setModuleData($sessionData)) {
				$className = $this->getClassName();
				$this->_module = new $className();
				$this->_module->loadData();
				$this->_dataHash = md5(serialize($this->_module));
			}
		}
		else {
			$className = $this->getClassName();
			$this->_module = new $className();
			$this->_module->loadData();
			$this->_dataHash = md5(serialize($this->_module));
		}
	}
	/**
	 * speichert eine Serialisierung des Moduls in die Datenbank
	 * @return void
	 */
	function saveToCache()
	{
		// Cache aktualisieren
		$sessionVarIdentifier = $this->getModuleCacheIdentifier();
		$cacheData = serialize($this->getModule());
		if (md5($cacheData) != $this->_dataHash) {
			self::$SESSION->set($sessionVarIdentifier, $cacheData);
		}
	}
	function loadData() {

	}
	function saveData(){
		$this->saveToCache();
		$this->getModule()->saveData();
	}
}

