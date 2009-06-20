<?php
/**
 * Konfigurationsvariablen
 *
 * ACHTUNG: Dieses Dokument nicht von Doxygen dokumentieren lassen!
 * Es befinden sich unter anderem mySQL Zugangsdaten darin.
 */
$config                                            = array();

// MySQL Zugangsdaten
$config['mySql']['host']                           = 'localhost';
$config['mySql']['db']                             = 'pks_dev';
$config['mySql']['user']                           = 'pksdev_world';
$config['mySql']['pass']                           = 'vhG1MRyGQj';

// Anzuzeigende Boxen rund ums Interface
$config['sideBoxes']                               = array();

// Gültige Get-Parameter und die einzubindenden Dateien
$config['GetParamFile']['inventory']               = INC_INVENTORY;
$config['GetParamFile']['messages']                = INC_MESSAGES;
$config['GetParamFile']['logout']                  = INC_LOGOUT;
$config['GetParamFile']['mapeditor']               = INC_MAP_EDITOR;
$config['GetParamFile']['optimisation']            = INC_OPTIMISATION;
$config['GetParamFile']['teleport']                = INC_TELEPORT;
$config['GetParamFile']['team']                    = INC_TEAM;
$config['GetParamFile']['lastmodifiedfiles']       = INC_LAST_MODIFIED_FILES;
$config['GetParamFile']['storagepc']               = INC_STORE_BOX;
$config['GetParamFile']['heal']                    = INC_HEAL;
$config['GetParamFile']['fight']                   = INC_FIGHT;
$config['GetParamFile']['trainerfight']            = INC_TRAINER_FIGHT_PANEL;
$config['GetParamFile']['map']                     = INC_MAP;
$config['GetParamFile']['shop']                    = INC_SHOP;
$config['GetParamFile']['pokeshop']                = INC_POKESHOP;
$config['GetParamFile']['trade']                   = INC_TRADE;
$config['GetParamFile']['giveitem']                = INC_GIVE_ITEM;
$config['GetParamFile']['npc']                     = INC_NPC;
$config['GetParamFile']['logviewer']               = INC_LOG_VIEWER;
$config['GetParamFile']['settings']                = INC_SETTINGS;
$config['GetParamFile']['sortiement_editor']       = INC_SORTIEMENT_EDITOR;
$config['GetParamFile']['shop']                    = INC_SHOP;

// Aktionen
$config['GetParamFile']['action']                  = array();
$config['GetParamFile']['action']['changemap']     = INC_CHANGEMAP;
$config['GetParamFile']['action']['heal']          = INC_HEAL;
$config['GetParamFile']['action']['changeslot']    = INC_CHANGESLOT;
$config['GetParamFile']['action']['addpokemon']    = INC_ADDPOKEMON;
$config['GetParamFile']['action']['saveSettings']  = INC_SAVE_SETTINGS;

// Wenn Benutzer nicht eingeloggt
$config['GetParamFile']['notLoggedIn']             = array();
$config['GetParamFile']['notLoggedIn']['register'] = INC_REGISTER;
$config['GetParamFile']['notLoggedIn']['login']    = INC_LOGIN;

