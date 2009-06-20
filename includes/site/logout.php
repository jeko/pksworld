<?php
/**
 * Loggt einen Benutzer aus (zerstört seine Session)
 */

$user = World_Base::$USER;

// Template laden
$template->templateFile = 'logout.html';
$template->templateMacro = 'logout';
$template->contentTitle = 'Logout';
$template->viewMacro = 'guest';
$template->username = $user->getDisplayName();

// Sessiondaten löschen
World_Base::$SESSION->destroy();
// Cookies löschen
setcookie('worldSessionId', false, time() - 3600);
setcookie('worldSessionKey', false, time() - 3600);