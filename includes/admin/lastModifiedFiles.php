<?php

/**
 * Die zuletzt geänderten Dateien anzeigen
 */

$template->templateFile = 'lastModifiedFiles.html';
$template->templateMacro = 'list';
$template->contentTitle = 'Zuletzt geänderte Dateien';

if (isset($_GET['dir'])) {
    $dir = $_GET['dir'];
}
else {
    $dir = '';
}
if (isset($_GET['time'])) {
    $time = $_GET['time'];
}
else {
    $time = time()-3600;
}
exec('/bin/ls -R ' . WORLD . $dir .'',$s);
$dirname = '';
$directories = '';
$files = array();
$template->displayedTime = date("d.m.Y \u\m H:i", $time);


for ($i=0;$i < count($s);$i++) {
    if (is_dir(WORLD . $dir . $s[$i])) continue;
    if (strpos($s[$i], ':') !== false) {
        $dirname = str_replace(':', '/', $s[$i]);
    }
    else {
        $file = $dirname.$s[$i];
        $mtime = filemtime($file);
        if ($mtime > $time) {
            $files[$mtime] = array('time' => $mtime, 'dir' => substr($dirname, strlen(WORLD)), 'fileName' => substr($file, strlen($dirname)));
        }
    }
}

krsort($files);
$template->files = $files;