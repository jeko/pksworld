<?php

$user = World_Base::$USER;
if (!$user->moduleIsLoaded('messages')) {
    $user->reloadModule('messages');
}
$messages = $user->getModule('messages')->getMessages(true);

// Template aufbauen
$template->templateFile = 'messages.html';
$template->templateMacro = 'messages';
$template->contentTitle = 'Nachrichten';
$template->messages = $messages;