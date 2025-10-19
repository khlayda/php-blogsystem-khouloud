<?php
#**************************************************************************************#
#*********************************** SESSION ******************************************#
#**************************************************************************************#
session_name('blogprojekt');
session_start();

#**************************************************************************************#
#*********************************** CONFIG *******************************************#
#**************************************************************************************#
require_once('include/config.inc.php');
require_once('include/db.inc.php');
require_once('include/form.inc.php');

#**************************************************************************************#
#******************************* ZUGRIFFSSCHUTZ ****************************************#
#**************************************************************************************#
if( !isset($_SESSION['ID']) ) {
	if(DEBUG_V) echo "<p class='debug err'>🚫 <b>Line " . __LINE__ . "</b>: Zugriff verweigert – kein Login! <i>(dashboard.php)</i></p>\n";
	header("Location: index.php");
	exit;
}

#**************************************************************************************#
#*********************************** DEBUG SESSION ************************************#
#**************************************************************************************#
if(DEBUG_V) {
	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION Inhalt:\n";
	print_r($_SESSION);
	echo "</pre>\n";
}

#**************************************************************************************#
#**************************** FORMULAR LOGIK: KATEGORIE *******************************#
#**************************************************************************************#

$meldungKategorie = '';

if( isset($_POST['formKategorie']) ) {
	if(DEBUG_V) echo "<p class='debug ok'>✅ <b>Line " . __LINE__ . "</b>: Kategorie-Formular wurde abgeschickt <i>(dashboard.php)</i></p>\n";

	# Schritt 1: Daten auslesen und säubern
	$catLabel = sanitizeString($_POST['catLabel']);

	if(DEBUG_V) {
		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST Inhalt:\n";
		print_r($_POST);
		echo "</pre>\n";
	}

	# Schritt 2: Validierung
	$errorCatLabel = validateInputString($catLabel, INPUT_STRING_MANDATORY, 50);

	# Schritt 3: Prüfen ob Fehler
	if($errorCatLabel) {
		if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Fehlerhafte Eingabe beim Kategoriename</p>\n";
		$meldungKategorie = 'Bitte gültige Kategoriebezeichnung eingeben.';
	} else {
		# Schritt 4 DB: Verbindung
		$PDO = dbConnect('blogprojekt');

		# Schritt 5 DB: Prüfen ob Kategorie bereits existiert
		$sql = 'SELECT catID FROM categories WHERE catLabel = :catLabel';
		$params = ['catLabel' => $catLabel];

		$statement = $PDO->prepare($sql);
		$statement->execute($params);

		if($statement->rowCount()) {
			if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Kategorie existiert bereits!</p>\n";
			$meldungKategorie = 'Diese Kategorie existiert bereits!';
		} else {
			# Schritt 6 DB: Einfügen
			$sqlInsert = 'INSERT INTO categories (catLabel) VALUES (:catLabel)';
			$statementInsert = $PDO->prepare($sqlInsert);
			$statementInsert->execute(['catLabel' => $catLabel]);

			if(DEBUG_V) echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Kategorie erfolgreich eingefügt</p>\n";
			$meldungKategorie = 'Kategorie erfolgreich hinzugefügt.';
		}

		dbClose($PDO, $statement);
	}
}

#**************************************************************************************#
#**************************** FORMULAR LOGIK: BLOGEINTRAG *****************************#
#**************************************************************************************#

$meldungBlog = '';

if( isset($_POST['formBlog']) ) {
	if(DEBUG_V) echo "<p class='debug ok'>✅ <b>Line " . __LINE__ . "</b>: Blog-Formular wurde abgeschickt <i>(dashboard.php)</i></p>\n";

	# Schritt 1: Daten auslesen und säubern
	$blogHeadline = sanitizeString($_POST['blogHeadline']);
	$blogContent  = sanitizeString($_POST['blogContent']);
	$catID        = $_POST['catID']; // wird als raw übernommen (Dropdown), prüfen folgt

	if(DEBUG_V) {
		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST Inhalt:\n";
		print_r($_POST);
		echo "</pre>\n";
	}

	# Schritt 2: Validierung
	$errorHeadline = validateInputString($blogHeadline, INPUT_STRING_MANDATORY, 100);
	$errorContent  = validateInputString($blogContent, INPUT_STRING_MANDATORY, 10000);
	$errorCatID    = (ctype_digit($catID)) ? '' : 'Ungültige Kategorie';

	if($errorHeadline || $errorContent || $errorCatID) {
		if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Validierung fehlgeschlagen</p>\n";
		$meldungBlog = 'Bitte alle Felder korrekt ausfüllen!';
	} else {
		# Schritt 3 DB: Verbindung
		$PDO = dbConnect('blogprojekt');

		# Schritt 4 DB: Einfügen
		$sql = 'INSERT INTO blogs (blogHeadline, blogContent, blogDate, catID, userID) 
				VALUES (:blogHeadline, :blogContent, NOW(), :catID, :userID)';

		$params = [
			'blogHeadline' => $blogHeadline,
			'blogContent'  => $blogContent,
			'catID'        => $catID,
			'userID'       => $_SESSION['ID']
		];

		$statement = $PDO->prepare($sql);
		$statement->execute($params);

		if(DEBUG_V) echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blogeintrag erfolgreich gespeichert</p>\n";
		$meldungBlog = 'Blogeintrag erfolgreich gespeichert.';

		dbClose($PDO, $statement);
	}
}

#**************************************************************************************#
#*********************************** KATEGORIEN LADEN *********************************#
#**************************************************************************************#
$PDO = dbConnect('blogprojekt');
$sql = 'SELECT * FROM categories ORDER BY catLabel ASC';
$statement = $PDO->query($sql);
$kategorien = $statement->fetchAll(PDO::FETCH_ASSOC);
dbClose($PDO, $statement);
?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/debug.css">
  <link rel="stylesheet" href="css/main.css">
</head>
<body>

<header>
  <p>Willkommen im Dashboard, <?= htmlspecialchars($_SESSION['userFirstName']) ?>!</p>
  <p><a href="index.php">Zurück zur Startseite</a> | <a href="index.php?logout=true">Logout</a></p>
</header>

<h1>Dashboard</h1>

<section>
  <h2>Kategorie hinzufügen</h2>
  <form action="" method="POST">
    <input type="text" name="catLabel" value="<?= htmlspecialchars($_POST['catLabel'] ?? '') ?>" placeholder="Neue Kategorie"><br>
    <button name="formKategorie">Kategorie speichern</button>
  </form>
  <?php if($meldungKategorie): ?>
    <p class="meldung"><?= htmlspecialchars($meldungKategorie) ?></p>
  <?php endif; ?>
</section>

<section>
  <h2>Blogeintrag hinzufügen</h2>
  <form action="" method="POST">
    <input type="text" name="blogHeadline" value="<?= htmlspecialchars($_POST['blogHeadline'] ?? '') ?>" placeholder="Überschrift"><br>
    <textarea name="blogContent" placeholder="Inhalt"><?= htmlspecialchars($_POST['blogContent'] ?? '') ?></textarea><br>
    <select name="catID">
      <option value="">-- Kategorie wählen --</option>
      <?php foreach($kategorien as $kategorie): ?>
        <option value="<?= $kategorie['catID'] ?>" <?= (isset($_POST['catID']) && $_POST['catID'] == $kategorie['catID']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($kategorie['catLabel']) ?>
        </option>
      <?php endforeach; ?>
    </select><br>
    <button name="formBlog">Blogeintrag speichern</button>
  </form>
  <?php if($meldungBlog): ?>
    <p class="meldung"><?= htmlspecialchars($meldungBlog) ?></p>
  <?php endif; ?>
</section>

</body>
</html>
