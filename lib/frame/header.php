<?php
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo LibConfig::$systemName; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="literaturedb" />
	<meta name="keywords" content="literaturedb" />
<?php
if($sessionUser->isLoggedIn()){
	echo '	<meta name="robots" content="noindex, nofollow, noarchive" />'.PHP_EOL;
} else {
	echo '	<meta name="robots" content="index, noarchive" />'.PHP_EOL;
}
?>
	<link rel="stylesheet" type="text/css" href="custom/design/style.css" media="screen, print" />
	<script type="text/javascript" src="javascript/jquery.js"></script>
	<script type="text/javascript" src="javascript/tag.js"></script>
</head>
<body>
<div id="centerColumn">
	<div id="header">
		<h1><?php echo LibConfig::$systemName; ?></h1>
<?php
$headerSubtitle = isset(LibConfig::$headerSubtitle) ? LibConfig::$headerSubtitle : 'the collaborative bibliography tool';

if($headerSubtitle != ''){
	echo '<h2>' .$headerSubtitle. '</h2>';
}
?>
		<div id="navtabs">
<?php
if($sessionUser->isLoggedIn()){
	echo '<ul>';
	echo '<li><a href="index.php?pid=literaturedb_documents" title="Documents"><span>Documents</span></a></li>';
	echo '<li><a href="index.php?pid=literaturedb_collaborate" title="Collaborate"><span>Collaborate</span></a></li>';
	echo '<li><a href="index.php?pid=literaturedb_settings" title="Settings"><span>Settings</span></a></li>';

	if($sessionUser->isAdmin()){
		echo '<li><a href="index.php?pid=literaturedb_admin" title="Admin"><span>Admin</span></a></li>';
	}

	echo '<li><a href="index.php?session_destroy=1" title="Logout"><span>Logout</span></a></li>';
	echo '</ul>';
}
?>
		</div>
	</div>