<?php

/**
 * Fehlerkonstanten
 *
 * @author dominique
 *
 */

interface Error_Constants
{
    
    // Fehlercodes
    const FATAL     = 0;  // Fataler Fehler: System ist nicht verwendbar
    const CRIT      = 1;  // kritischer Fehler
    const DATABASE  = 2;  // Datenbankfehler
    const PARAM     = 3;  // Parameter-Fehler (falsche Parameter)
    const ERR       = 4;  // Unbekannter Fehler
    const WARN      = 5;  // Warnung
    const NOTICE    = 6;  // Notiz
    const INFO      = 7;  // Informativ: Informative Nachrichten
    const DEBUG     = 8;  // Debug: Debug Nachrichten
}

