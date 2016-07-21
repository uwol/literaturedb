<?php
/*
This file is part of literaturedb.

literaturedb is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

literaturedb is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with literaturedb. If not, see <http://www.gnu.org/licenses/>.
*/

require_once('custom/systemconfig.php');
require_once('vendor/literaturedb/initialize.php');


//select page
if($sessionUser->isLoggedIn()){
	$pages = array(
		'literaturedb_login' => 'documents.php',
		'literaturedb_collaborate' => 'collaborate.php',
		'literaturedb_settings' => 'settings.php',
		'literaturedb_documents' => 'documents.php',
		'literaturedb_document' => 'document.php',
		'literaturedb_person' => 'person.php');

	if($sessionUser->isAdmin()){
		$pages = array_merge($pages, array('literaturedb_admin' => 'admin.php',));
	}

	$pidFile = "documents.php";
} else {
	$pages = array(
		'literaturedb_login' => 'login.php',
		'literaturedb_register' => 'register.php');

	$pidFile = "login.php";
}

if($pid != '' && isset($pages[$pid])){
	$pidFile = $pages[$pid];
}

//check problems
if($pidFile == ""){
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	header('Connection: close');
	exit();
}

require('vendor/literaturedb/layout/header.php');
require('vendor/literaturedb/pages/' .$pidFile);
require('vendor/literaturedb/layout/footer.php');
?>