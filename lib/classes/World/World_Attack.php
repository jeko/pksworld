<?php

/**
 * Repräsentiert eine Attacke
 *
 * @author dominique
 *
 */

class World_Attack extends World_Base
{
    protected $_id = 0;
    protected $_displayName = '';

    function __construct($attackId = false)
    {
        if ($attackId !== false) {
            $this->loadData($attackId);
        }
    }

    function loadData($attackId = false)
    {
        if ($attackId !== false) {
            $fields = array('name');

            if (self::$DB->selectById(TABLE_CONST_ATTACK,$fields,$attackId)) {
                if (self::$DB->getNumRows() > 0) {
                    $row = self::$DB->getRow();
                    $this->_id = $attackId;
                    $this->setDisplayName($row['name']);
                }
            }
        }
    }

    function saveData()
    {
        // darf nicht implementiert werden, da Tabelle konstant.
        $this->error('Ungültiger Aufruf von saveData: Tabelle darf nicht verändert werden.', __FILE__);
    }

    function getName()
    {
        return $this->_displayName;
    }
}
