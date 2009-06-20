<?php

/**
 * Magische Autoloadfunktion
 * Lädt Klassen nach wenn sie benötigt werden
 * @param string $className
 * @return void
 */
function __autoload($className)
{	
    $classDir = '';
    $delimiterPos = strrpos($className, '_');
    if ($delimiterPos !== false) {
    	$classDir = str_replace('_', '/', substr($className, 0, $delimiterPos)) . '/';
    }
    
    $classFile = CLASS_PATH . $classDir . $className . '.php';
    require_once($classFile);
}