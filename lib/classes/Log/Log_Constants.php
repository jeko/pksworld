<?php

/**
 * sämtliche Konstanten, die in der Log-Klasse benutzt werden.
 *
 * @author dominique
 *
 */

interface Log_Constants
{	
	// Log-Einstellungen
    const DEFAULT_LOG_FORMAT = '[%timestamp] %type %message';
    const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';  
    const DEFAULT_ENTRY_SEPARATOR = "\n";
    
    // PokeSturm Codes
    const MONEY        = 50;  // Geld
    const MONEY_LOSS    = 51;  // Geld: Verminderung des Geldbestandes
    const MONEY_GAIN    = 52;  // Geld: Zuwachs des Geldbestandes
    
    const PKMN         = 60;  // Pokemon
    const PKMN_FREE     = 61;  // Pokemon: Freilassen eines Pkmns
    const PKMN_TRADE    = 62;  // Pokemon: Tausch eines Pkmns
    const PKMN_CATCH    = 63;  // Pokemon: Fang eines Pkmns
    const PKMN_BUY      = 64;  // Pokemon: Kauf eines Pkmns
    const PKMN_MOVE     = 65;  // Pokemon: Pokemonbewegung (Verschiebung in Team/Box)
    
    const ITEM         = 70;  // Item
    const ITEM_GAIN     = 71;  // Item: Erhalt eines Items
    const ITEM_LOSS     = 72;  // Item: Verlust eines Items
    const ITEM_SELL     = 73;  // Item: Verkauf eines Items
    const ITEM_BUY      = 74;  // Item: Kauf eines Items
    
    const MAP          = 80;  // Map
    const MAP_ENTER     = 81;  // Map: Betreten einer Map
    const MAP_LEAVE     = 82;  // Map: Verlassen einer Map
}

