<?php
/**
 * Stellt Login-Formular mitsamt Bearbeitung der
 * Daten zur Verfügung 
 * Zusätzlich wird der Client auf die Fähigkeit überprüft
 * ein Session-Cookie zu speichern und ob der Cache
 * aktiviert ist.
 */

$user = World_Base::$USER;

// Template laden
$template->templateFile = 'login.html';
$template->templateMacro = 'login';
$template->contentTitle = 'Login';
$template->viewMacro = 'guest';
$template->username = '';

// Formulardaten prüfen
if (isset($_POST['username'])) {
    $username = mysql_real_escape_string($_POST['username']);
    $password = md5($_POST['password']);
    $whereString = 'name="' . $username . '" AND ' . 'password="' . $password . '"';
    
    if (World_Base::$DB->selectByWhere(TABLE_USER, array('id', 'name'), $whereString, 'LIMIT 1')) {
        if (World_Base::$DB->getNumRows() > 0) {
            $row = World_Base::$DB->getRow();
            $template->username = $row['name'];
            $template->templateMacro = 'loginSuccessful';
            World_Base::$SESSION->userId = $row['id'];
            World_Base::$LOG->write('User ' . $row['id'] .' hat sich eingeloggt.', Log::INFO);

            include(INC_LOAD);
        }
        else {
            $template->errorMessage = 'Benutzername oder Passwort nicht korrekt!';
            $template->username = $_POST['username'];
        }
    }
    else {
        // Datenbankfehler
    }
}