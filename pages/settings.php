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

if(!$sessionUser->isLoggedin())
	die();
	
/*
* Actions
*/
$emailAddressMissing = false;
$emailAddressAlreadyUsed = false;
$emailAddressNotValid = false;

$firstnameMissing = false;
$lastnameMissing = false;

$oldPasswordMissing = false;
$oldPasswordIsWrong = false;

$newPassword1Missing = false;
$newPassword2Missing = false;
$newPasswordIsInvalid = false;
$newPasswordsNotEqual = false;


if(isset($_POST['action']) && $_POST['action'] == 'passwordChange'){
	$oldPassword = trim($_POST['old_password']);
	$newPassword1 = trim($_POST['new_password1']);
	$newPassword2 = trim($_POST['new_password2']);

	$user = LibUser::fetch($sessionUser->getId());

	if($oldPassword == "")
		$oldPasswordMissing = true;
	elseif(!LibUser::checkPassword($oldPassword, $user['password_hash'], $user['password_salt']))
		$oldPasswordWrong = true;

	if($newPassword1 == "")
		$newPassword1Missing = true;
	elseif(!LibUser::isValidPassword($newPassword1))
		$newPasswordIsInvalid = true;

	if($newPassword2 == "")
		$newPassword2Missing = true;
	if($newPassword1 != $newPassword2)
		$newPasswordsNotEqual = true;

	if(!$newPassword1Missing && !$newPassword2Missing && !$newPasswordIsInvalid && !$newPasswordsNotEqual && !$oldPasswordMissing && !$oldPasswordWrong){
		$passwordHash = LibUser::encryptPassword($newPassword1);
	
		$user['password_hash'] = $passwordHash;
		$user['password_salt'] = '';
		LibUser::save($user);
		LibGlobal::$notificationTexts[] = 'The password has been changed.';
	}
	else{
		if($oldPasswordMissing)
			LibGlobal::$errorTexts[] = 'The old password is missing.';
		if($oldPasswordWrong)
			LibGlobal::$errorTexts[] = 'The old password is wrong.';
		if($newPassword1Missing)
			LibGlobal::$errorTexts[] = 'The new password is missing.';
		if($newPassword2Missing)
			LibGlobal::$errorTexts[] = 'The new second password is missing.';
		if($newPasswordIsInvalid)
			LibGlobal::$errorTexts[] = 'The new password is not valid. '. LibUser::getPasswordRequirements();
		if($newPasswordsNotEqual)
			LibGlobal::$errorTexts[] = 'The new passwords are not equal.';
	}

}
if(isset($_POST['action']) && $_POST['action'] == 'userDetailsChange'){
	$firstname = LibString::protectXSS(trim($_POST['firstname']));
	$lastname = LibString::protectXSS(trim($_POST['lastname']));
	$emailAddress = LibString::protectXSS(trim($_POST['emailAddress']));

	if($emailAddress == "")
		$emailAddressMissing = true;
	elseif(!LibUser::isValidEmailAddress($emailAddress))
		$emailAddressNotValid = true;
	$user = LibUser::fetchByEmailAddress($emailAddress);
	if(is_numeric($user['id']) && $user['id'] != $sessionUser->id)
		$emailAddressAlreadyUsed = true;
	//---
	if($firstname == "")
		$firstnameMissing = true;
	if($lastname == "")
		$lastnameMissing = true;
	//-------------------------------
	if(!$emailAddressMissing && !$emailAddressAlreadyUsed && !$emailAddressNotValid && 
			!$firstnameMissing && !$lastnameMissing){	

		$user = LibUser::fetch($sessionUser->getId());

		$user['lastname'] = $lastname;

		$user['firstname'] = $firstname;
		$user['emailaddress'] = $emailAddress;

		LibUser::save($user);
		
		LibGlobal::$notificationTexts[] = 'The user details have been changed.';
	}
	else{
		if($emailAddressMissing)
			LibGlobal::$errorTexts[] = 'The email address is missing.';
		if($emailAddressAlreadyUsed)
			LibGlobal::$errorTexts[] = 'The email address is already used by another user.';
		if($emailAddressNotValid)
			LibGlobal::$errorTexts[] = 'The email address is not valid.';
		if($firstnameMissing)
			LibGlobal::$errorTexts[] = 'The firstname is missing.';
		if($lastnameMissing)
			LibGlobal::$errorTexts[] = 'The lastname is missing.';
	}

}

if(isset($_POST['action']) && $_POST['action'] == 'closeAccount' && isset($_POST['password'])){
	if(!$sessionUser->isAdmin()){ //admins cannot be deleted
		$user = LibUser::fetch($sessionUser->getId());
		if(LibUser::checkPassword($_POST['password'], $user['password_hash'], $user['password_salt'])){
			LibRouter::user_delete($sessionUser->id, $sessionUser->getUserAddress());
			LibGlobal::$notificationTexts[] = 'Your user account has been deleted.';
			$sessionUser = null;
			echo LibString::getNotificationBoxText();
			exit();
		}
		else
			LibGlobal::$errorTexts[] = 'Your user account has not been deleted because the password is wrong.';
	}
	else
		LibGlobal::$errorTexts[] = 'Your user account cannot be deleted because you are an admin.';
}



/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();
	
if(isset($_GET['action']) && $_GET['action'] == 'closeAccount'){
	echo '<h1>Delete user account</h1>';
	echo '<p style="color:red">Do you really want do delete your user account? All of your documents will be deleted.</p>';

	echo '<form action="index.php?pid=literaturedb_settings" method="post">';
	echo '<fieldset>';
	echo '<input type="hidden" name="action" value="closeAccount" />';
	echo '<label>password confirmation<br /><input type="password" name="password" size="40" value="" /></label><br />';
	echo '<input type="submit" value="Yes, delete my user account" />';
	echo '</fieldset>';
	echo '</form>';
}

/*
* user details
*/
echo '<h1>My user details</h1>';
echo '<form method="post" action="index.php?pid=literaturedb_settings">';
echo '<fieldset>';
echo 'username: '. LibString::protectXSS($sessionUser->username) .'<br />';
echo 'user address: <strong>'. LibString::protectXSS($sessionUser->getUserAddress()) .'</strong><br />';
echo '<br />';
echo '<input type="hidden" name="action" value="userDetailsChange" />';
$user = LibUser::fetch($sessionUser->id);
echo '<label>firstname<br /><input type="text" name="firstname" size="40" value="' .LibString::protectXSS($user['firstname']). '" ' .errorStyle($firstnameMissing). '/></label><br />';
echo '<label>lastname<br /><input type="text" name="lastname" size="40" value="' .LibString::protectXSS($user['lastname']). '" ' .errorStyle($lastnameMissing). '/></label><br />';
echo '<label>email address<br /><input type="text" name="emailAddress" size="40" value="' .LibString::protectXSS($user['emailaddress']). '" ' .errorStyle($emailAddressMissing || $emailAddressNotValid || $emailAddressAlreadyUsed). '/></label><br />';
echo '<input type="submit" value="Change user details" />';

echo '</fieldset>';

echo '</form>';
echo '<br />';


/*
* password change
*/
if(!LibGlobal::ldapIsEnabled()){
	echo '<form action="index.php?pid=literaturedb_settings" method="post">';
	echo '<fieldset>';
	echo '<input type="hidden" name="action" value="passwordChange" />';
	echo '<label>old password<br /><input type="password" name="old_password" size="40" value="" ' .errorStyle($oldPasswordMissing || $oldPasswordWrong). '/></label><br />';
	echo '<label>new password<br /><input type="password" name="new_password1" size="40" value="" ' .errorStyle($newPassword1Missing || $newPasswordIsInvalid || $newPasswordsNotEqual). '/></label><br />';
	echo '<label>new password (repeat)<br /><input type="password" name="new_password2" size="40" value="" ' .errorStyle($newPassword2Missing || $newPasswordIsInvalid || $newPasswordsNotEqual). '/></label><br />';
	echo '<input type="submit" value="Change password" />';
	echo '</fieldset>';
	echo '</form>';
}

/*
* Close user account
*/
echo '<p><a href="index.php?pid=literaturedb_settings&amp;action=closeAccount" onclick="return confirm(\'Are you sure you want to delete your user account?\')">Delete my user account</a></p>';

function errorStyle($condition){
	if($condition)
		return ' class="problem" ';
	return '';
}
?>