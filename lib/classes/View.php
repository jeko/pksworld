<?php

/**
 * View
 * abstrahiert die Templateengine und bietet standardisierte Methoden
 * um das Template zu bearbeiten
 * @author dominique
 *
 */

class View extends PHPTAL {
    protected $_javascript       = '';
    protected $_messages         = array();
    
    // Default-Werte
    const DEFAULT_PAGE_TITLE     = '';
    const DEFAULT_TEMPLATE_FILE  = 'missingTemplate.html';
    const DEFAULT_TEMPLATE_MACRO = 'message';

    function __construct()
    {
        // Default-Werte setzen
        $this->setPageTitle(self::DEFAULT_PAGE_TITLE);
        $this->setTemplateFile(self::DEFAULT_TEMPLATE_FILE);
        $this->setTemplateMacro(self::DEFAULT_TEMPLATE_MACRO);
        
        // Template-Konstruktor aufrufen
        parent::__construct();       
    }
    
    function __destruct()
    {
    
    }    
    
    function execute()
    {
        // Variablen an Template zuweisen
        $this->__set('javascriptContent', $this->_javascript);
        $this->__set('messages', $this->_messages);
        
        parent::execute();
    }
    
    /**
     * setzt eine Templatevariable bzw. ändert sie
     * @param $varName string Variablenname
     * @param $varValue mixed Wert
     */
    function __set($varName, $varValue)
    {
        parent::__set($varName, $varValue);
    }
    
    /**
     * liest eine Templatevariable aus und gibt sie zurück
     * @param $varName string Name der Variable
     * @return mixed Wert der Variable oder false falls diese nicht existiert
     */
    function __get($varName)
    {
    	if (isset($this->_context->$varName)) {
    		return $this->_context->$varName;
    	}
    	else {
    		return false;
    	}
    }
    
    function setPageTitle($title)
    {
        $this->__set('contentTitle', $title);
    }
    
    function setTemplateFile($file)
    {
        $this->__set('templateFile', $file);
    }
    
    function setTemplateMacro($macroId)
    {
        $this->__set('templateMacro', $macroId);
    }
    
    /**
     * fügt auszuführenden Javascript-Code zum Template hinzu
     * @param $jsCodeAsString string Javascriptcode als String
     */
    function js($jsCodeAsString)
    {
        $this->_javascript .= $jsCodeAsString;
    }
    
    /**
     * fügt eine Nachricht zum Nachrichtensystem hinzu
     * @param $messageText string Nachrichtentext
     */
    function message($messageText)
	{
        $this->_messages[] = $messageText;
    }
}

