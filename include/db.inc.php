<?php
#******************************************************************************************************#

        #**********************************#
        #********** DATABASE INC **********#
        #**********************************#

#******************************************************************************************************#


        #**************************************#
        #********** DATABASE CONNECT **********#
        #**************************************#

        /**
        *
        *   Baut eine Verbindung zur Datenbank via PDO auf
        *   Die Zugangsdaten werden aus der config.inc.php geholt
        *
        *   @param  [String $DBName=DB_NAME]     Name der zu verbindenden Datenbank
        *   @return Object                       DB-Verbindungsobjekt
        *
        */
        function dbConnect($DBName=DB_NAME) {

            if(DEBUG_DB) echo "<p class='debug db'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): Versuche mit der DB '<b>$DBName</b>' zu verbinden... <i>(" . basename(__FILE__) . ")</i></p>\n";

            try {
                // Verbindung zur Datenbank aufbauen
                $PDO = new PDO(DB_SYSTEM . ":host=" . DB_HOST . "; dbname=$DBName; charset=utf8mb4", DB_USER, DB_PWD);

                // Prepared Statements und Datentypen korrekt behandeln
                $PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $PDO->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

            } catch(PDOException $error) {
                if(DEBUG_DB) echo "<p class='debug db err'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): <i>FEHLER: " . $error->GetMessage() . " </i> <i>(" . basename(__FILE__) . ")</i></p>\n";
                exit;
            }

            if(DEBUG_DB) echo "<p class='debug db ok'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): Erfolgreich mit der DB '<b>$DBName</b>' verbunden. <i>(" . basename(__FILE__) . ")</i></p>\n";

            return $PDO;
        }


#******************************************************************************************************#

        #***********************************************#
        #********** CLOSE DATABASE CONNECTION **********#
        #***********************************************#

        /**
        *
        *   Schließt eine aktive DB-Verbindung und gibt Debug-Meldung aus
        *
        *   @param  PDO &$PDO               Referenz auf das geöffnete PDO-Objekt
        *   @param  PDO &$PDOStatement=NULL Referenz auf das PDOStatement-Objekt
        *   @return void
        *
        */
        function dbClose(&$PDO, &$PDOStatement=NULL) {
            if(DEBUG_DB) echo "<p class='debug db'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __FUNCTION__ . "() <i>(" . basename(__FILE__) . ")</i></p>\n";

            $PDO = $PDOStatement = NULL;
        }

#******************************************************************************************************#
