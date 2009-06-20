<?php

/**
 * Systemnachrichten für Benutzer
 * @author dominique
 *
 */

class World_Messages extends World_Base {
	protected $_messages = array();

	const FLAG_PERSISTENT = 1;

	function __construct()
	{
	}

	function saveData()
	{
		$userId = self::$USER->getId();
		$table = TABLE_MESSAGES;
		// gelesene Nachrichten löschen
		if (self::$DB->deleteByWhere($table, 'uid=' . $userId)) {
			// Nachrichten schreiben
			foreach ($this->_messages as $message) {
				$fieldsValues = array(
		    			'uid' => $userId,
		    			'text' => $message['text'],
		    			'time' => $message['time'],
		    			'persistent' => ($message['flags'] & self::FLAG_PERSISTENT)
				);
				if (!self::$DB->insert($table, $fieldsValues)) {
					$this->error('Konnte Message nicht speichern: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
				}
			}
		}
		else {
			$this->error('Kann Nachrichten nicht löschen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return false;
		}
		return true;
	}

	function loadData()
	{
		$userId = self::$USER->getId();
		$table = TABLE_MESSAGES;
		$fields = array('mid', 'text', 'time', 'persistent');
		$where = 'uid=' . $userId;
		$this->_messages = array();
		if (self::$DB->selectByWhere($table, $fields, $where)) {
			while ($row = self::$DB->getRow()) {
				$flags = 0;
				if ($row['persistent'] == 1) {
					$flags = $flags | self::FLAG_PERSISTENT;
				}
				$this->_messages[$row['mid']] = array(
	    			'text' => $row['text'],
	    			'time' => $row['time'],
	    			'flags' => $flags
				);
				self::$DB->next();
			}
			return true;
		}
		else {
			$this->error('Konnte Nachrichten nicht abrufen: Query fehlgeschlagen.', __FILE__, Error::DATABASE);
			return false;
		}
	}
	/**
	 * liefert die Flags einer Message $messageId
	 * @param $messageId integer
	 * @return integer
	 */
	function getFlags($messageId)
	{
		if (isset($this->_messages[$messageId])) {
			return $this->_messages[$messageId]['flags'];
		}
	}
	/**
	 * fügt eine Message mit Text $text mit den
	 * Flags $flags zum Queue hinzu.
	 * @param $text string
	 * @param $persistent bool
	 * @return void
	 */
	function push($text, $flags = 0)
	{
		$this->_messages[] = array(
	    	'text' => $text,
	    	'flags' => $flags,
	    	'time' => time(),
	    	'uid' => self::$USER->getId()
		);
	}
	/**
	 * entfernt eine Nachricht $messageId aus dem Queue.
	 * @param $messageId
	 * @return void
	 */
	function pop($messageId)
	{
		if (isset($this->_messages[$messageId])) {
			unset($this->_messages[$messageId]);
		}
	}
	/**
	 * liefert ein Array mit den Nachrichten seit
	 * dem Zeitpunkt $time (UNIX-Timestamp) oder
	 * alle noch vorhandenen. Ist $markAsRead true,
	 * werden die zurückgegebenen Nachrichten vom Queue
	 * entfernt (wo möglich).
	 * @param $time integer
	 * @return array
	 */
	function getMessages($markAsRead = false, $time = null)
	{
		$messages = array();
		if ($time === null) {
			$time = 0;
		}

		foreach ($this->_messages as $mId => $message) {
			if ($message['time'] > $time) {
				$messages[] = $message;
				if ($markAsRead == true) {
					$this->pop($mId);
				}
			}
		}
		return $messages;
	}
}