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

ini_set('arg_separator.output', '&amp;');

@session_start();

if((isset($_REQUEST['session_destroy']) && $_REQUEST['session_destroy'] == 1) ||
		(isset($_SESSION['session_timeout_timestamp']) && 
		($_SESSION['session_timeout_timestamp'] == "" || $_SESSION['session_timeout_timestamp'] < time()))){
	@session_destroy();
	@session_start();
}

$_SESSION['session_timeout_timestamp'] = time() + 43200;


/**
* DB
*/
LibDb::connect();


/**
* Authentication
*/
if(isset($_SESSION['sessionUser'])){
	$sessionUser =& $_SESSION['sessionUser'];
}
else{
	$sessionUser = new LibUser();
}

if(isset($_REQUEST['loginUsername']) && isset($_REQUEST['loginPassword'])){
	$_SESSION['sessionUser'] = new LibUser();
	$sessionUser =& $_SESSION['sessionUser'];
	$sessionUser->login($_REQUEST['loginUsername'], $_REQUEST['loginPassword']);
	$_SESSION['selectedUserAddresses'] = array($sessionUser->getUserAddress());

	LibCronjobs::cleanDb();
}


/*
* Cronjobs
*/
LibCronjobs::run();


/*
* UserAddresses to show documents from
*/
if(isset($_POST['selectedUserAddress'])){
	$selectedUserAddresses = array();
	//foreach($_POST['selectedUserAddresses'] as $selectedUserAddress)
	if(!in_array($_POST['selectedUserAddress'], $selectedUserAddresses))
		$selectedUserAddresses[] = LibUser::buildCanonicalUserAddress($_POST['selectedUserAddress']);
	$_SESSION['selectedUserAddresses'] = $selectedUserAddresses;
}

if(isset($_SESSION['selectedUserAddresses']))
	LibGlobal::$selectedUserAddresses = $_SESSION['selectedUserAddresses'];


$pid = '';
if(isset($_REQUEST['pid']))
	$pid = $_REQUEST['pid'];
?>