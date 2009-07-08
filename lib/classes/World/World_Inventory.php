<?php

/**
 * Repräsentiert das Userinventar (Rucksack)
 *
 * @author dominique
 *
 */

class World_Inventory extends World_Base
{
	/**
	 * Zugriff auf die Inventarbereiche
	 * @var array
	 */
	protected $_itemStacks = array();

	function __construct()
	{
	}
	
	function loadData()
	{
		$ownerId = self::$USER->getId();
		if ($ownerId !== false) {
			$fields = array('id','item_id');
			$where = 'owner_id=' . $ownerId;
			if ($query = self::$DB->selectByWhere(TABLE_ITEM,$fields,$where)) {
				$this->_itemStacks = array();
				foreach ($query as $row) {
					$this->_itemStacks[$row->item_id] = new World_ItemStack($this, $row->id);
				}
				return true;
			}
			else {
				$this->error('Konnte Daten nicht laden: Query fehlgeschlagen.', __FILE__);
				return false;
			}
		}
	}

	function getOwner()
	{
		return self::$USER;
	}

	function saveData()
	{
		foreach ($this->_itemStacks as $itemStack) {
			$itemStack->saveData();
		}
		return true;
	}

	/**
	 * gibt den Anzeigenamen der Gruppe zurück
	 * @return string
	 */
	function getDisplayName()
	{
		return $this->_displayName;
	}

	/**
	 * Zugriff auf einen Itemstapel über die Id
	 * des Items
	 * @param integer $id
	 * @return World_ItemStack
	 */
	function getItemStackByItemId($id)
	{
		if (isset($this->_itemStacks[$id])) {
			return $this->_itemStacks[$id];
		}
		else {
			$this->error('Stack mit Item-Id '.$id.' existiert nicht.', __FILE__);
			return false;
		}
	}

	/**
	 * liefert alle Itemstapel
	 * @return array
	 */
	function getItemStacks()
	{
		return $this->_itemStacks;
	}

	/**
	 * entfernt eine Anzahl eines Gegenstands aus dem Iventar
	 * @param $item World_Item
	 * @return bool
	 */
	function removeItem($item, $quantity=1)
	{
		$itemstack = $this->getItemStackByItemId($item->getId());
		if ($itemstack !== false) {
			$itemstack->remove($quantity);
			return true;
		}
		else {
			$this->error('Gegenstand kann nicht entfernt werden, da er nicht im Inventar vorhanden ist.', __FILE__, Error::WARN);
			return false;
		}
	}

	/**
	 * entfernt einen Gegenstand komplett aus dem Inventar
	 * @param $item World_Item
	 * @return bool
	 */
	function removeItemStack($item)
	{
		$itemstack = $this->getItemStackByItemId($item->getId());
		if ($itemstack !== false) {
			unset($this->_itemstacks[$item->getId()]);
			return true;
		}
		else {
			$this->error('Gegenstand nicht vorhanden, Entfernen fehlgeschlagen.', __FILE__, Error::WARN);
			return false;
		}
	}

	/**
	 *
	 * @param $item World_Item
	 * @param $quantity integer
	 * @return bool
	 */
	function addItem($item, $quantity=1)
	{
		$itemStack = $this->getItemStackByItemId($item->getId());
		if ($itemStack === false) {
			// ItemStack existiert noch nicht, neu anlegen
			$this->_itemStacks[$item->getId()] = new World_ItemStack($this);
			return $this->_itemStacks[$item->getId()]->setItem($item, $quantity);
		}
		else {
			return $itemStack->add($quantity);
		}
	}

	function itemExists($itemName)
	{
		foreach ($this->_itemStacks as $itemStack) {
			if ($itemStack->getName() == $itemName && $itemStack->getQuantity() > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Gibt Array der ItemGruppen zurück
	 * @return array
	 */
	function getItemGroups()
	{
		$itemGroups = array();
		self::$DB->select(TABLE_CONST_ITEM_GROUP, array('id', 'name'));
		while (($row = self::$DB->getRow()) !== false)
		{
			$itemGroups[$row['id']] = $row;
			self::$DB->next();
		}
		return $itemGroups;
	}
}

