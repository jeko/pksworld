<?php
$user = World_Base::$USER;

if (isset($_GET['log'])) {
	$logFile = LOG_DIR . $_GET['log'] . '.log';
}
else {
	$logFile = LOG_FILE;
}

$logViewer = new Log_Reader($logFile);
$lines = $logViewer->getAll();

$template->templateFile = 'logViewer.html';
$template->templateMacro = 'logViewer';
$template->viewMacro = 'admin';
$template->contentTitle = 'Logfile: ' . basename($logFile);
$template->logLines = $lines;