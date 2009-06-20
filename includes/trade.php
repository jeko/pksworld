<?php

$user = World_Base::$USER;
if ($user->getMap()->getFlags() & World_Map::FLAG_TRADE) {
	//TODO Tausch Interface
	
	$template->setTemplate('trade.html');
	$template->contentTitle = 'Tausch';
}
else {
	$user->error('Karte verfügt über keinen Tauschspot.', __FILE__, Error::WARN);
	include(INC_MAP);
}