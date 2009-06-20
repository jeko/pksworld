<?php
/**
 * Lädt die erfoderlichen Dateien zur Darstellung der
 * Sideboxes (Interfacerand) wie zum Beispiel das Team
 */

$sideBoxes = array();
foreach ($config['sideBoxes'] as $box) {
	include($box);
	$sideBoxes[] = array(
	   'templateFile' => $template->getTemplateVar('templateFile'),
	   'templateMacro' => $template->getTemplateVar('templateMacro'),
	   'contentTitle' => $template->getTemplateVar('contentTitle')
	);
}

$template->sideBoxes = $sideBoxes;