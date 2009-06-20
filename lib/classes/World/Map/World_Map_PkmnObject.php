<?php

/**
 * Repräsentiert eine LayerObject
 *
 * @author dominique
 *
 */

class World_Map_PkmnObject
{
	/**
	 * @var string Name des Objekts
	 */
    public $name = 'Unknown Object';
    /**
     * @var string Verweisziel
     */
    public $href = '#';
    /**
     * @var string Angezeigter Text beim Darüberfahren
     */
    public $text = '...';
    /**
     * @var integer X-Position auf der Karte
     */
    public $x = 0;
    /**
     * @var integer Y-Position auf der Karte
     */
    public $y = 0;
    /**
     * @var integer Breite des Objekts
     */
    public $w = 0;
    /**
     * @var integer Höhe des Objekts
     */
    public $h = 0;
    /**
     * @var integer X-Position der unteren rechten Ecke
     */
    public $wx = 0;
    /**
     * 
     * @var integer Y-Position der unteren rechten Ecke
     */
    public $hy = 0;
    /**
     * 
     * @var array Bedingungen für die Anzeige dieses Felds
     */
    private $_conditions = array();
    /**
     * initialisiert die Eigenschaften des LayerObjekts
     * @param $name string
     * @param $x integer
     * @param $y integer
     * @param $w integer
     * @param $h integer
     * @param $text string
     * @param $href string
     * @return void
     */
    function __construct($name, $x, $y, $w, $h, $text = '...', $href = '#')
    {
    	$this->name = $name;
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
        $this->h = $h;
        $this->wx = $w + $x;
        $this->hy = $h + $y;
        $this->text = $text;
        $this->href = $href;
    }
    
    function addCondition($identifier, $value)
    {
    	$this->_conditions[$identifier] = $value;
    }
    
    function hasConditions()
    {
        if (count($this->_conditions) > 0) {
        	return true;
        }
        else {
        	return false;
        }
    }
    
    function getConditions()
    {
    	return $this->_conditions;
    }
    
    function getCondition($identifier)
    {
    	if (isset($this->_conditions[$identifier])) {
    		return $this->_conditions[$identifier];
    	}
    	else {
    		return false;
    	}
    }
    
    function getConditionsForQuery()
    {
    	$csvArray = array();
    	foreach ($this->_conditions as $id => $value) {
    		$csvArray[] = $id . '=' . $value;
    	}
    	return implode(',', $csvArray);
    }
}

