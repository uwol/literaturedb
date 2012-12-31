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

if(!$sessionUser->isLoggedin() || !$sessionUser->isAdmin())
	die();

/*
* Action
*/
if(isset($_GET['action']) && $_GET['action'] == 'activate' && isset($_GET['userId']) && is_numeric($_GET['userId'])){
	$user = LibUser::fetch($_GET['userId']);
	if($user['id'] != ''){
		$user['activated'] = 1;
		LibUser::save($user);
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'deactivate' && isset($_GET['userId']) && is_numeric($_GET['userId'])){
	$user = LibUser::fetch($_GET['userId']);
	if($user['id'] != ''){
		$user['activated'] = 0;
		LibUser::save($user);
	}
}
elseif(isset($_POST['action']) && $_POST['action'] == 'delete' && 
		isset($_POST['userId']) && is_numeric($_POST['userId']) &&
		isset($_POST['password'])){
	$user = LibUser::fetch($_POST['userId']);

	if(!$user['is_admin']){ //admins cannot be deleted
		$adminUser = LibUser::fetch($sessionUser->getId());
		if(LibUser::checkPassword($_POST['password'], $adminUser['password_hash'], $adminUser['password_salt'])){
			LibUser::delete($user['id']);
			LibGlobal::$notificationTexts[] = 'The user account has been deleted.';
		}
		else
			LibGlobal::$errorTexts[] = 'The user account has not been deleted because you typed in a wrong password.';
	}
	else
		LibGlobal::$errorTexts[] = 'The user account cannot be deleted because the user is an admin.';
}



/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();


if(isset($_GET['action']) && $_GET['action'] == 'delete' && 
		isset($_GET['userId']) && is_numeric($_GET['userId'])){
	$user = LibUser::fetch($_GET['userId']);
	
	echo '<h1>Delete user account</h1>';
	echo '<p style="color:red">Do you really want do delete the user "' .LibString::protectXSS($user['username']). '"? All documents of this user will be deleted.</p>';

	echo '<form action="index.php?pid=literaturedb_admin" method="post">';
	echo '<fieldset>';
	echo '<input type="hidden" name="action" value="delete" />';
	echo '<input type="hidden" name="userId" value="' .LibString::protectXSS($user['id']). '" />';
	echo '<label>password confirmation<br /><input type="password" name="password" size="40" value="" /></label><br />';
	echo '<input type="submit" value="Yes, delete user '.LibString::protectXSS($user['username']).'" />';
	echo '</fieldset>';
	echo '</form>';
}

echo '<h1>Administration</h1>';

echo '<p>This page is accessible to you because your username "'.LibString::protectXSS($sessionUser->username). '" is contained in custom/systemconfig.php in the array $admins.</p>';

/*
* User management
*/
echo '<h2>User management</h2>';
echo '<table>';
echo '<tr><th>username</th>';
if(!LibGlobal::ldapIsEnabled())
	echo '<th>activated</th>';
echo '<th>user information</th>';
if(!LibGlobal::ldapIsEnabled())
	echo '<th>(de/)activate</th>';
echo '<th>delete</th></tr>';


foreach(LibUser::fetchAllOrderByActivated($sessionUser->getUserAddress()) as $user){
	echo '<tr>';

	echo '<td>'. LibString::protectXSS($user['username']) .'</td>';
	
	if(!LibGlobal::ldapIsEnabled()){
		echo '<td style="text-align:center">';
		if($user['activated'])
			echo '<img src="img/icons/bullet_green.png" alt="activate"/>';
		else
			echo '<img src="img/icons/bullet_red.png" alt="deactivated"/>';
		echo '</td>';
	}
	
	echo '<td>'. LibString::protectXSS($user['firstname']) .' '.LibString::protectXSS($user['lastname']).'<br />' .LibString::protectXSS($user['emailaddress']). '</td>';

	if(!LibGlobal::ldapIsEnabled()){
		echo '<td style="text-align:center">';
		if(!$user['is_admin']){
			if(!$user['activated'])
				echo '<a href="index.php?pid=literaturedb_admin&amp;action=activate&amp;userId=' .LibString::protectXSS($user['id']). '" onclick="return confirm(\'Are you sure you want to activate this user account?\')"><img src="img/icons/tick.png" alt="activate"/></a>';
			else
				echo '<a href="index.php?pid=literaturedb_admin&amp;action=deactivate&amp;userId=' .LibString::protectXSS($user['id']). '" onclick="return confirm(\'Are you sure you want to deactivate this user account?\')"><img src="img/icons/delete.png" alt="deactivate"/></a>';
		}
		else
			echo 'admin';
		echo '</td>';
	}

	echo '<td style="text-align:center">';
	if(!$user['is_admin'])
		echo '<a href="index.php?pid=literaturedb_admin&amp;action=delete&amp;userId=' .LibString::protectXSS($user['id']). '" onclick="return confirm(\'Are you sure you want to DELETE this user account?\')"><img src="img/icons/cross.png" alt="delete"/></a>';
	else
		echo 'admin';
	echo '</td>';
	
	echo '</tr>';
}
echo '</table>';
?>