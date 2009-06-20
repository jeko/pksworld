<?php
die("Script nicht mehr in Gebrauch.");
include('../../constants.php');
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
echo 'Geänderte Dateien seit dem ' . date("d.m.Y \u\m H:i", $time) . ' Uhr'. "<br />\n";
echo '
<ul>
    <li><a href="?time='.(time()-3600).'">geänderte Dateien der letzten Stunde anzeigen</a></li>
    <li><a href="?time='.(time()-86400).'">geänderte Dateien seit gestern anzeigen</a></li>
    <li><a href="?time='.(time()-86400*7).'">geänderte Dateien seit einer Woche anzeigen</a></li>
    <li><a href="?time='.(time()-86400*31).'">geänderte Dateien seit einem Monat anzeigen</a></li>
    <li><a href="?time='.(time()-86400*365).'">geänderte Dateien seit einem Jahr anzeigen</a></li>
</ul>';
for ($i=0;$i < count($s);$i++) {
	if (is_dir(WORLD . $dir . $s[$i])) continue;
	if (strpos($s[$i], ':') !== false) {
		$dirname = str_replace(':', '/', $s[$i]);
	}
	else {
        $file = $dirname.$s[$i];
        $mtime = filemtime($file);
        if ($mtime > $time) {
        	$files[$mtime] = array('dir' => substr($dirname, strlen(WORLD)), 'file' => substr($file, strlen($dirname)));
        }
	}
}

krsort($files);

foreach ($files as $key=>$value) {
	echo "[".date('d.m.Y H:i', $key)."] "
	   . '<a href="?dir='.substr($value['dir'],0,-1).'" title="Filtern nach diesem Verzeichnis">'.$value['dir'].'</a>' . $value['file'] 
	   . "<br />\n";
}