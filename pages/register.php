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

if(LibGlobal::ldapIsEnabled())
	die('Registering new users is disabled because LDAP is enabled in the config.');

/*
* Actions
*/
$username = '';
if(isset($_POST['username'])){
	$username = LibString::protectXSS(trim($_POST['username']));
}

$firstname = '';
if(isset($_POST['firstname'])){
	$firstname = LibString::protectXSS(trim($_POST['firstname']));
}

$lastname = '';
if(isset($_POST['lastname'])){
	$lastname = LibString::protectXSS(trim($_POST['lastname']));
}

$emailAddress = '';
if(isset($_POST['emailAddress'])){
	$emailAddress = LibString::protectXSS(trim($_POST['emailAddress']));
}

$password1 = '';
if(isset($_POST['password1'])){
	$password1 = trim($_POST['password1']);
}

$password2 = '';
if(isset($_POST['password2'])){
	$password2 = trim($_POST['password2']);
}

$registrationDataComplete = false;
$mailSent = false;

$usernameMissing = false;
$usernameAlreadyUsed = false;
$usernameNotValid = false;

$emailAddressMissing = false;
$emailAddressAlreadyUsed = false;
$emailAddressNotValid = false;

$firstnameMissing = false;
$lastnameMissing = false;

$password1Missing = false;
$password2Missing = false;
$passwordIsInvalid = false;
$passwordsNotEqual = false;


if(isset($_POST['action']) && $_POST['action'] == 'register'){
	if($username == ""){
		$usernameMissing = true;
	} elseif(!LibUser::isValidUsername($username)){
		$usernameNotValid = true;
	}

	$user = LibUser::fetchByUserAddress($username);

	if(isset($user['id']) && is_numeric($user['id'])){
		$usernameAlreadyUsed = true;
	}

	//---

	if($emailAddress == ""){
		$emailAddressMissing = true;
	} elseif(!LibUser::isValidemailAddress($emailAddress)){
		$emailAddressNotValid = true;
	}

	$user = LibUser::fetchByemailAddress($emailAddress);

	if(isset($user['id']) && is_numeric($user['id'])){
		$emailAddressAlreadyUsed = true;
	}

	//---

	if($firstname == ""){
		$firstnameMissing = true;
	}

	if($lastname == ""){
		$lastnameMissing = true;
	}

	//---

	if($password1 == ""){
		$password1Missing = true;
	} elseif(!LibUser::isValidPassword($password1)){
		$passwordIsInvalid = true;
	}

	//---

	if($password2 == ""){
		$password2Missing = true;
	}

	if($password1 != $password2){
		$passwordsNotEqual = true;
	}

	//-------------------------------

	if(!$usernameMissing && !$usernameAlreadyUsed && !$usernameNotValid &&
			!$emailAddressMissing && !$emailAddressAlreadyUsed && !$emailAddressNotValid &&
			!$firstnameMissing && !$lastnameMissing  &&
			!$password1Missing && !$password2Missing && !$passwordIsInvalid && !$passwordsNotEqual){ //valid data?
		$registrationDataComplete = true;

		$passwordHash = LibUser::encryptPassword($password1);

		$user = array();
		$user['firstname'] = $firstname;
		$user['lastname'] = $lastname;
		$user['username'] = $username;
		$user['emailaddress'] = $emailAddress;
		$user['password_hash'] = $passwordHash;
		$user['activated'] = (in_array($username, LibConfig::$admins)) ? 1 : 0;
		LibUser::save($user);

		$text = "A registration request for " .LibConfig::$sitePath. " has been sent from \n\n" .
				$firstname . " " . $lastname . " \n".
				"username: " . $username . " \n".
				"email address: " . $emailAddress . " \n\n".
				"Please check the identity of this person and activate the account.";

		require_once("lib/thirdparty/phpmailer/class.phpmailer.php");

		$mail = new PHPMailer();
		$mail->AddAddress(LibConfig::$emailRegistration);
		//$mail->FromName = $emailAddress;
		$mail->Subject = '[literaturedb] Registration for: '.$emailAddress;
		$mail->Body = $text;
		$mail->AddReplyTo($emailAddress);
		$mail->CharSet = "UTF-8";

		/*
		* Use a smtp relay
		*/
		if(LibConfig::$smtpHost != ''){
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			$mail->Host = LibConfig::$smtpHost;
			$mail->Username = LibConfig::$smtpUsername;
			$mail->Password = LibConfig::$smtpPassword;
		}

		if($mail->Send()){
			$mailSent = true;
		}
	}
	//invalid data
	else{
		if($usernameMissing){
			LibGlobal::$errorTexts[] = 'The username is missing.';
		}

		if($usernameNotValid){
			LibGlobal::$errorTexts[] = 'The username is not valid.';
		}

		if($usernameAlreadyUsed){
			LibGlobal::$errorTexts[] = 'The username is already in use. Please choose another one.';
		}

		if($emailAddressMissing){
			LibGlobal::$errorTexts[] = 'The email address is missing.';
		}

		if($emailAddressNotValid){
			LibGlobal::$errorTexts[] = 'The email address is not valid.';
		}

		if($emailAddressAlreadyUsed){
			LibGlobal::$errorTexts[] = 'The email address is already in use. You can recover the password for this email address on the login page.';
		}

		if($firstnameMissing){
			LibGlobal::$errorTexts[] = 'The firstname is missing.';
		}

		if($lastnameMissing){
			LibGlobal::$errorTexts[] = 'The lastname is missing.';
		}

		if($password1Missing || $password2Missing){
			LibGlobal::$errorTexts[] = 'The password is missing.';
		}

		if($passwordIsInvalid){
			LibGlobal::$errorTexts[] = 'The password is not valid. '. LibUser::getPasswordRequirements();
		}

		if($passwordsNotEqual){
			LibGlobal::$errorTexts[] = 'The passwords are not equal.';
		}
	}
}


/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();


echo '<div id="login_container">';

// problem with registration information
if(!$registrationDataComplete){
	echo '<h1>Registration</h1>';

	echo '<form method="post" action="index.php?pid=literaturedb_register">';
	echo '<fieldset>';
	echo '<input type="hidden" name="action" value="register" />';
	echo '<label>username<br /><input type="text" name="username" size="25" value="' .$username. '" ' .errorStyle($usernameMissing || $usernameNotValid || $usernameAlreadyUsed). '/></label>';
	echo '<label>email address<br /><input type="text" name="emailAddress" size="25" value="' .$emailAddress. '" ' .errorStyle($emailAddressMissing || $emailAddressNotValid || $emailAddressAlreadyUsed). '/></label>';
	echo '<label>password<br /><input type="password" name="password1" size="25" value="" ' .errorStyle($password1Missing || $passwordIsInvalid || $passwordsNotEqual). '/></label>';
	echo '<label>password (repeat)<br /><input type="password" name="password2" size="25" value="" ' .errorStyle($password2Missing || $passwordIsInvalid || $passwordsNotEqual). '/></label>';
	echo '<label>firstname<br /><input type="text" name="firstname" size="25" value="' .$firstname. '" ' .errorStyle($firstnameMissing). '/></label>';
	echo '<label>lastname<br /><input type="text" name="lastname" size="25" value="' .$lastname. '" ' .errorStyle($lastnameMissing). '/></label>';
	echo '<input type="submit" value="Register" />';
	echo '<p style="margin-bottom:0"><a href="index.php">Back to login</a></p>';
	echo '</fieldset>';
	echo '</form>';
}
// registration OK
else {
	if($mailSent){
		echo "<h2>Registration request sent</h2><p>Your registration request has been sent to the administrator of this literature database.</p><p>A notification will be sent to your email address from the administrator, when the account is activated.</p>";
	} else {
		echo "<h2>Error</h2>Your registration request could not be sent. Please contact ". LibConfig::$emailRegistration . ' directly.';
	}

	echo '<p style="margin-bottom:0"><a href="index.php">Back to login</a></p>';
}

echo '</div>';


function errorStyle($condition){
	if($condition){
		return ' class="problem" ';
	}

	return '';
}
?>