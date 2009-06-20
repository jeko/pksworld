<?php
/*
 * Zum bearbeiten der Einstellungen.
 */

$user = World_Base::$USER;
$settings = $user->getModule('settings');
$constSettings = $settings->getConstSettings();
$userSettings = $settings->getAllUserSettings();

$template->templateFile = 'settings.html';
$template->templateMacro = 'settings';
$template->contentTitle = 'Einstellungen';
$template->constSettings = $constSettings;
$template->userSettings = $userSettings;
