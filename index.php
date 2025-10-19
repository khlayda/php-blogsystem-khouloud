<?php
#**************************************************************************************#
#*********************************** SESSION ******************************************#
#**************************************************************************************#
session_name('blogprojekt');
session_start();

#**************************************************************************************#
#*********************************** CONFIG *******************************************#
#**************************************************************************************#
require_once('include/form.inc.php');
require_once('include/config.inc.php');
require_once('include/db.inc.php');

#**************************************************************************************#
#*********************************** LOGOUT *******************************************#
#**************************************************************************************#
if( isset($_GET['logout']) ) {
	if(DEBUG_V) echo "<p class='debug'>🔒 <b>Line " . __LINE__ . "</b>: Logout initiiert <i>(index.php)</i></p>\n";

	session_destroy();

	if(DEBUG_V) echo "<p class='debug ok'>✅ <b>Line " . __LINE__ . "</b>: Session zerstört, Weiterleitung erfolgt... <i>(index.php)</i></p>\n";

	header('Location: index.php');
	exit;
}

#**************************************************************************************#
#********************************** FORMULAR LOGIK ************************************#
#**************************************************************************************#

$errorLogin = ''; // Variable für Login-Fehlermeldung initialisieren

# Schritt 1: Prüfen, ob das Formular abgeschickt wurde
if( isset($_POST['login']) ) {
	if(DEBUG_V) echo "<p class='debug ok'>✅ <b>Line " . __LINE__ . "</b>: Login-Formular wurde abgeschickt <i>(index.php)</i></p>\n";

	# Schritt 2: Formulardaten auslesen und entschärfen
	$userEmail    = sanitizeString($_POST['userEmail']);
	$userPassword = sanitizeString($_POST['userPassword']);

	if(DEBUG_V) {
		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST Inhalt:\n";
		print_r($_POST);
		echo "</pre>\n";
	}

	# Schritt 3: Validierung vorbereiten
	$errorEmail    = '';
	$errorPassword = '';

	# Schritt 4: Felder validieren
	$errorEmail    = validateEmail($userEmail);
	$errorPassword = validateInputString($userPassword, INPUT_STRING_MANDATORY, 256, 6);

	# Schritt 5: Validierungsergebnis prüfen
	if($errorEmail OR $errorPassword) {
		if(DEBUG_V) echo "<p class='debug err'>❌ <b>Line " . __LINE__ . "</b>: Validierung fehlgeschlagen <i>(index.php)</i></p>\n";
		$errorLogin = 'Bitte gültige Email und Passwort eingeben!';
	} else {
		if(DEBUG_V) echo "<p class='debug ok'>✅ <b>Line " . __LINE__ . "</b>: Validierung erfolgreich <i>(index.php)</i></p>\n";

		# Schritt 1 DB: Verbindung zur Datenbank aufbauen
		$PDO = dbConnect('blogprojekt');

		# Schritt 2 DB: SQL und Placeholder definieren
		$sql = 'SELECT userID, userFirstName, userPassword FROM users WHERE userEmail = :userEmail';
		$placeholders = array('userEmail' => $userEmail);

		# Schritt 3 DB: Prepared Statement
		try {
			$PDOStatement = $PDO->prepare($sql);
			$PDOStatement->execute($placeholders);
		} catch(PDOException $error) {
			if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: DB-Fehler: " . $error->getMessage() . "</p>\n";
			$errorLogin = 'Datenbankfehler!';
		}

		# Schritt 4 DB: Ergebnis auswerten
		$userDataSet = $PDOStatement->fetch(PDO::FETCH_ASSOC);
		dbClose($PDO, $PDOStatement);

		# Schritt 5: Ergebnis prüfen
		if(!$userDataSet) {
			if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Kein User gefunden!</p>\n";
			$errorLogin = 'Benutzername oder Passwort falsch!';
		} else {
			if(password_verify($userPassword, $userDataSet['userPassword']) === false) {
				if(DEBUG_V) echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Passwort ist falsch!</p>\n";
				$errorLogin = 'Benutzername oder Passwort falsch!';
			} else {
				if(DEBUG_V) echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Login erfolgreich</p>\n";

				$_SESSION['ID']            = $userDataSet['userID'];
				$_SESSION['userFirstName'] = $userDataSet['userFirstName'];
				$_SESSION['IPADDRESS']     = $_SERVER['REMOTE_ADDR'];

				if(DEBUG_V) {
					echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION Inhalt:\n";
					print_r($_SESSION);
					echo "</pre>\n";
				}

				header("Location: dashboard.php");
				exit;
			}
		}
	}
}

#**************************************************************************************#
#*********************************** BLOG-ANZEIGE *************************************#
#**************************************************************************************#

if(DEBUG) echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Blogdaten aus der DB... <i>(index.php)</i></p>\n";

# Schritt 1 DB: Verbindung aufbauen
$PDO = dbConnect('blogprojekt');

# Schritt 2 DB: Kategorien auslesen
$sqlKategorien = 'SELECT * FROM categories ORDER BY catLabel ASC';
$PDOStatementKategorien = $PDO->query($sqlKategorien);
$kategorien = $PDOStatementKategorien->fetchAll(PDO::FETCH_ASSOC);

if(DEBUG_V) {
	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$kategorien:\n";
	print_r($kategorien);
	echo "</pre>\n";
}

# Schritt 3 DB: Blogs abrufen (mit oder ohne Filter)
if( isset($_GET['catID']) AND ctype_digit($_GET['catID']) ) {
	$sqlBlogs = 'SELECT blogs.*, categories.catLabel, users.userFirstname, users.userLastname, users.userCity 
				 FROM blogs 
				 JOIN categories ON blogs.catID = categories.catID
				 JOIN users ON blogs.userID = users.userID
				 WHERE blogs.catID = :catID
				 ORDER BY blogDate DESC';

	$placeholders = ['catID' => $_GET['catID']];
	$PDOStatementBlogs = $PDO->prepare($sqlBlogs);
	$PDOStatementBlogs->execute($placeholders);
} else {
	$sqlBlogs = 'SELECT blogs.*, categories.catLabel, users.userFirstname, users.userLastname, users.userCity 
				 FROM blogs 
				 JOIN categories ON blogs.catID = categories.catID
				 JOIN users ON blogs.userID = users.userID
				 ORDER BY blogDate DESC';

	$PDOStatementBlogs = $PDO->query($sqlBlogs);
}

# Schritt 4 DB: Ergebnisse abrufen
$blogs = $PDOStatementBlogs->fetchAll(PDO::FETCH_ASSOC);

if(DEBUG_V) {
	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$blogs:\n";
	print_r($blogs);
	echo "</pre>\n";
}

# Schritt 5 DB: Verbindung schließen
dbClose($PDO, $PDOStatementBlogs);
?>

<!doctype html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<title>PHP-Projekt Blog</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/debug.css">
	<link rel="stylesheet" href="css/main.css">
</head>
<body>

<header>
  <?php if( isset($_SESSION['userFirstName']) ): ?>
    <p>Willkommen, <?= htmlspecialchars($_SESSION['userFirstName']) ?>!</p>
    <p><a href="dashboard.php">Dashboard</a> | <a href="?logout=true">Logout</a></p>
  <?php endif; ?>
</header>

<h1>PHP-Projekt Blog</h1>

<form action="" method="POST">
	<input name="userEmail" value="<?= htmlspecialchars($_POST['userEmail'] ?? '') ?>" placeholder="Email"><br>
	<input name="userPassword" value="<?= htmlspecialchars($_POST['userPassword'] ?? '') ?>" placeholder="Passwort"><br>
	<button name="login">Login</button>
</form>

<?php if(isset($errorLogin)): ?>
	<p class="error"><?= htmlspecialchars($errorLogin) ?></p>
<?php endif; ?>

<nav>
	<h2>Kategorien filtern</h2>
	<ul>
		<li><a href="index.php">Alle Beiträge</a></li>
		<?php foreach($kategorien as $kategorie): ?>
			<li><a href="index.php?catID=<?= $kategorie['catID'] ?>"><?= htmlspecialchars($kategorie['catLabel']) ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>

<section>
	<h2>Alle Blogeinträge</h2>

	<?php foreach($blogs as $blog): ?>
		<article class="blog-entry">
			<h3><?= htmlspecialchars($blog['blogHeadline']) ?></h3>
			<p><strong>Kategorie:</strong> <?= htmlspecialchars($blog['catLabel']) ?></p>
			<p>
				<strong>Von:</strong> <?= htmlspecialchars($blog['userFirstname'] . ' ' . $blog['userLastname']) ?> aus <?= htmlspecialchars($blog['userCity']) ?> |
				<em><?= date("d.m.Y H:i", strtotime($blog['blogDate'])) ?></em>
			</p>
			<p><?= nl2br(htmlspecialchars($blog['blogContent'])) ?></p>
		</article>
	<?php endforeach; ?>
</section>

</body>
</html>
