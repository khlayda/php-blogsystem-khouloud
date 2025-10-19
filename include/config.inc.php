<?php
#**************************************************************************************#
#***********************************#
#********** CONFIG FILE ************#
#***********************************#
#**************************************************************************************#

//  Allgemeine Debug-Einstellungen
define('DEBUG',        true);      // Haupt-Debugschalter
define('DEBUG_F',      true);      // Formular-Debug
define('DEBUG_DB',     true);      // DB-Debug
define('DEBUG_V',      true);
define('DEBUG_COOKIE', false); 
define('DEBUG_CC',  true);         // Konstruktor/Destruktor Debugging
define('DEBUG_C',   true);         // Klassenmethoden Debugging
    // Cookie-Debug

//  Datenbank-Verbindungsdaten
define('DB_SYSTEM', 'mysql');                // z.B. 'mysql'
define('DB_HOST',   'localhost');            // Datenbank-Host
define('DB_NAME',   'blogprojekt');          // Datenbank-Name (musst du anpassen!)
define('DB_USER',   'root');                 // Datenbank-Benutzername
define('DB_PWD',    '');                     // Passwort (leer lassen falls keins)

//  Zeichensatz
define('HTML_CHARSET', 'UTF-8');             // Zeichensatz für HTML-Seiten

define('INPUT_STRING_MANDATORY', 1);    // Pflichtfeld prüfen
define('INPUT_STRING_OPTIONAL', 2);     // Optionales Feld
define('INPUT_EMAIL_MANDATORY', 3);     // Pflichtfeld E-Mail
define('INPUT_EMAIL_OPTIONAL', 4);      // Optionales E-Mail-Feld