<?php

error_reporting(E_ALL);
session_start();
ob_start();

if (isset($_GET['nr'])) {
    $itemNumber = intval($_GET['nr']);
}
else {
    die("Missing Item number (url: ?nr=#)");
}

chdir('..');
require_once('constants.php'); // Konstanten
require_once(FUNC_PATH . 'autoload.php'); // Autoloader f�r Klassen
require_once('config.php'); // Konfiguration

include(INC_INDEX_TOP);
$user = World_Base::$USER;
if ($user !== null) {
    $inventory = new World_Inventory($user, $user->getId());
    $item = new World_Item($itemNumber);
    $inventory->addItem($item, 3);

    // Neuen Stand Speichern.
    $inventory->saveData();

    Error::logErrors(World_Base::$LOG, true);
}
else {
    die("Failed creating user.");
}
