<?php
$user = World_Base::$USER;

// Einstellungen Speichern
if (isset($_POST['submit'])) {
    $settings = $user->getModule('settings');
    
    foreach($_POST['setting'] as $key => $value) {
        $settings->setSetting($key, $value);
    }

    $user->getModule('messages')->push('Einstellungen gespeichert.');
}