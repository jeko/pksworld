<?php

/**
 * Repräsentiert einen Stapel von Items gleichen Typs
 *
 * @author dominique
 *
 */

class World_ItemStack extends World_Base
{
    /**
     * Zeigt auf das Item für das dieser Stapel steht 
     * @var World_Item
     */
    public $item = null;
    public $inventory = null;
    protected $_id = 0;
    static $table = 'world_item';

    /**
     * Speichert die Stückzahl des Items in diesem Stapel
     * @var integer
     */
    protected $_quantity = 0;

    function __construct($inventoryobj, $stack_id = false)
    {
        $this->inventory = $inventoryobj;
        if ($stack_id !== false) {
            $this->loadData($stack_id);
        }
    }

    function loadData($stack_id = false)
    {
        if ($stack_id !== false) {
            if ($query = self::$DB->selectById(TABLE_ITEM, array('item_id', 'quantity'), $stack_id)) {
                if ($query->getNumRows() > 0) {
                    $this->_id = $stack_id;
                    $data = $query->current();
                    $this->_quantity = $data->quantity;
                    $this->item = new World_Item($data->item_id);
                }
                else {
                    $this->error('Konnte Itemstack ' . $stack_id . ' nicht laden (Leeres Ergebnis: ' . self::$DB->getLastQuery() . ').', __FILE__, Error::DATABASE);
                    $this->item = new World_Item();
                    return false;
                }
            }
            else {
                $this->error('Konnte Itemstack nicht laden: Query fehlgeschlagen.', __FILE__);
                return false;
            }
        }
    }

    function saveData()
    {
        $fieldsValues = array();
        $fieldsValues['quantity'] = $this->_quantity;
        $fieldsValues['owner_id'] = $this->getInventory()->getOwner()->getId();
        $fieldsValues['item_id'] = $this->getItem()->getId();
            
        if (self::$DB->insert(self::$table,
        $fieldsValues,
                               'ON DUPLICATE KEY UPDATE quantity='
                               . $this->_quantity)) {
                                   return true;
                               }
                               else {
                                   $this->error('Konnte Daten nicht speichern: Query fehlgeschlagen.', __FILE__);
                                   return false;
                               }
    }

    /**
     * gibt die Stückzahl zurück
     * @return integer
     */
    function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * setzt eine neue Stückzahl.
     * Bei Fehler wird FALSE zurückgegeben, ansonsten TRUE.
     * @param integer $newquantity
     * @return bool
     */
    function setQuantity($newquantity)
    {
        if ($newquantity > 0) {
            $this->_quantity = $newquantity;
        }
        else {
            $this->error('Item Stückzahl kann nicht negativ sein.', __FILE__);
            return false;
        }
    }

    /**
     * setzt die Stückzahl um $quantity höher
     * Bei Fehler wird FALSE zurückgegeben, ansonsten die neue Stückzahl.
     * @param $quantity integer 1
     * @return integer
     */
    function add($quantity=1)
    {
        $this->_quantity += abs($quantity);
    }

    /**
     * setzt die Stückzahl um $quantity tiefer
     * oder auf 0 sollte $quantity zu gross sein.
     * Bei fehler wird FALSE zurückgegeben, ansonsten die neue Stückzahl.
     * @param $quantity integer 1
     * @return unknown_type
     */
    function remove($quantity=1)
    {
        if ($this->_quantity < $quantity) {
            $quantity = $this->_quantity;
        }
        $this->_quantity -= abs($quantity);
        // wenn kein Exemplar mehr vorhanden, Stack entfernen
        if ($this->_quantity == 0) {
            $this->inventory->removeItemStack($this->item);
        }
        return $this->_quantity;
    }
    /**
     * setzt das Item für diesen Stack.
     * @param $item World_Item
     * @return bool true wenn das neue Item gesetzt werden konnte, andernfalls false.
     */
    function setItem($item)
    {
        if (is_a($item, 'World_Item')) {
            $this->item = $item;
            $this->_quantity = 1;
            $this->saveData();
            $this->_id = self::$DB->lastInsertId();
            return true;
        }
        else {
            $this->error('Item setzen fehlgeschlagen: Parameter muss vom Typ World_Item sein.', __FILE__, Error::WARN);
            return false;
        }
    }

    function getName()
    {
        return $this->getItem()->getDisplayName();
    }

    function getItemGroup()
    {
        return $this->getItem()->getItemGroup();
    }

    function getInventory()
    {
        return $this->inventory;
    }

    function getItem()
    {
        return $this->item;
    }
}

