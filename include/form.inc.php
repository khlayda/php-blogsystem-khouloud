<?php
#**************************************************************************************#

#**************************************************************************************#

          // Optionales Feld für E-Mail

define('INPUT_STRING_MAX_LENGTH', 256);     // Maximale Eingabelänge für Strings
define('INPUT_STRING_MIN_LENGTH', 0);       // Minimale Eingabelänge für Strings

				#*************************************#
				#********** SANITIZE STRING **********#
				#*************************************#
				
				/**
				*
				*	Ersetzt potentiell gefährliche Steuerzeichen durch HTML-Entities
				*	Entfernt vor und nach einem String Whitespaces
				*	Ersetzt Leerstrings durch NULL
				*
				*	@params		String	$value	Die zu bereinigende Zeichenkette
				*
				*	@return		String				Die bereinigte Zeichenkette
				*
				*/
				function sanitizeString($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug sanitizeString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					/*
						SCHUTZ GEGEN EINSCHLEUSUNG UNERWÜNSCHTEN CODES (XSS-Attacken):
						Damit so etwas nicht passiert: <script>alert("HACK!")</script>
						muss der empfangene String ZWINGEND entschärft werden!
						htmlspecialchars() wandelt potentiell gefährliche Steuerzeichen wie
						< > " & in HTML-Code um (&lt; &gt; &quot; &amp;).
						
						Der Parameter ENT_QUOTES wandelt zusätzlich einfache ' in &apos; um.
						Der Parameter ENT_HTML5 sorgt dafür, dass der generierte HTML-Code HTML5-konform ist.
						
						Der 1. optionale Parameter regelt die zugrundeliegende Zeichencodierung 
						(NULL=Zeichencodierung wird vom Webserver übernommen)
						
						Der 2. optionale Parameter bestimmt die Zeichenkodierung
						
						Der 3. optionale Parameter regelt, ob bereits vorhandene HTML-Entities erneut entschärft werden
						(false=keine doppelte Entschärfung)
					*/
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
					
					
					/*
						trim() entfernt VOR und NACH einem String (aber nicht mitten drin) 
						sämtliche sog. Whitespaces (Leerzeichen, Tabs, Zeilenumbrüche)
					*/
					$value = trim($value);
					
					
					/*
						Leerstrings aus dem Formular in NULL umwandeln, damit in der DB vorhandene
						NULL-Werte nicht mit Leerstrings überschrieben werden.
					*/
					if($value === '') $value = NULL;
					
					
					// Entschärften und getrimmten Wert zurückgeben
					return $value;
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#

				
				#*******************************************#
				#********** VALIDATE INPUT STRING **********#
				#*******************************************#
				
				/**
				*
				*	Prüft einen übergebenen String auf Maximallänge sowie optional 
				* 	auf Mindestlänge und Pflichtangabe.
				*	Generiert Fehlermeldung bei Leerstring und gleichzeitiger Pflichtangabe 
				*	oder bei ungültiger Länge.
				*
				*	@param	String		$value											Der zu validierende String
				*	@param	Boolean		$mandatory=INPUT_STRING_MANDATORY		Angabe zu Pflichteingabe
				*	@param	Integer		$maxLength=INPUT_STRING_MAX_LENGTH		Die zu prüfende Maximallänge
				*	@param	Integer		$minLength=INPUT_STRING_MIN_LENGTH		Die zu prüfende Mindestlänge															
				*
				*	@return	String|NULL														Fehlermeldung | ansonsten NULL
				*
				*/
				function validateInputString(	$value, 
														$mandatory=INPUT_STRING_MANDATORY, 
														$maxLength=INPUT_STRING_MAX_LENGTH, 
														$minLength=INPUT_STRING_MIN_LENGTH )
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateInputString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' [$minLength|$maxLength] mandatory:$mandatory ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $mandatory === true AND $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}
					

					#********** MAXIMUM LENGTH CHECK **********#
					/*
						Da die Felder in der Datenbank oftmals eine Längenbegrenzung besitzen,
						die Datenbank aber bei Überschreiten dieser Grenze keine Fehlermeldung
						ausgibt, sondern alles, das über diese Grenze hinausgeht, stillschweigend 
						abschneidet, muss vorher eine Prüfung auf diese Maximallänge durchgeführt 
						werden. Nur so kann dem User auch eine entsprechende Fehlermeldung ausgegeben
						werden.
					*/
					/*
						mb_strlen() erwartet als Datentyp einen String. Wenn (später bei der OOP)
						jedoch ein anderer Datentyp wie Integer oder Float übergeben wird, wirft
						mb_strlen() einen Fehler. Da es ohnehin keinen Sinn macht, einen Zahlenwert
						auf seine Länge (Anzahl der Zeichen) zu prüfen, wird diese Prüfung nur für
						den Datentyp 'String' durchgeführt.
					*/
					/*
						Da die Übergabe von NULL an PHP-eigene Funktionen in künftigen PHP-Versionen 
						nicht mehr erlaubt ist, muss vor jedem Aufruf einer PHP-Funktion sichergestellt 
						werden, dass der zu übergebende Wert nicht NULL ist.
					*/
					if( $value !== NULL AND mb_strlen($value) > $maxLength ) {
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein!";
					}
										
					
					#********** MINIMUM LENGTH CHECK **********#
					/*
						Es gibt Sonderfälle, bei denen eine Mindestlänge für einen Userinput
						vorgegeben ist, beispielsweise bei der Erstellung von Passwörtern.
						Damit nicht-Pflichtfelder aber auch weiterhin leer sein dürfen, muss
						die Mindestlänge als Standardwert mit 0 vorbelegt sein.
						
						Bei einem optionalen Feldwert, der gleichzeitig eine Mindestlänge
						einhalten muss, darf die Prüfung keine Leersrtings validieren, da 
						diese nie die Mindestlänge erfüllen und somit der Wert nicht mehr 
						optional wäre.
					*/
					/*
						Da die Übergabe von NULL an PHP-eigene Funktionen in künftigen PHP-Versionen 
						nicht mehr erlaubt ist, muss vor jedem Aufruf einer PHP-Funktion sichergestellt 
						werden, dass der zu übergebende Wert nicht NULL ist.
					*/
					if( $value !== NULL AND mb_strlen($value) < $minLength  ) {
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein!";
					}				
					
					
					#********** NO ERROR **********#
					return NULL;
					
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#

				
				#********************************************#
				#********** VALIDATE EMAIL ADDRESS **********#
				#********************************************#
				
				/**
				*
				*	Prüft einen übergebenen String auf eine valide Email-Adresse und auf Leerstring.
				*	Generiert Fehlermeldung bei ungültiger Email-Adresse oder Leerstring
				*
				*	@param	String	$value							Der zu übergebende String
				*
				*	@return	String|NULL									Fehlermeldung | ansonsten NULL
				*
				*/
				function validateEmail(	$value, $mandatory=INPUT_STRING_MANDATORY ) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateEmail'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' | mandatory:$mandatory ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $mandatory === true AND $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}
					

					#********** VALIDATE EMAIL ADDRESS FORMAT **********#
					if( filter_var( $value, FILTER_VALIDATE_EMAIL) === false ) {
						// Fehlerfall
						return 'Dies ist keine gültige Email-Adresse!';
					}					
					
					
					#********** NO ERROR **********#
					return NULL;
					
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#
?>
