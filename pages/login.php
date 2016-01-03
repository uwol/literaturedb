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

if(LibGlobal::ldapIsEnabled()){
	if(!isset(LibConfig::$ldapCentralUserManagementSite)){
		LibGlobal::$errorTexts[] = "LDAP: In systemconfig.php the setting ldapCentralUserManagementSite should be configured.";
	}

	if(!isset(LibConfig::$ldapLoginExplanation)){
		LibGlobal::$errorTexts[] = "LDAP: In systemconfig.php the setting ldapLoginExplanation should be configured.";
	}
}

/*
* Action
*/
if(isset($_POST['reset_emailaddress']) && $_POST['reset_emailaddress'] != ""){
	if(LibGlobal::ldapIsEnabled()){
		die('Resetting passwords is disabled because LDAP is enabled in the config.');
	}

	if(LibUser::isValidEmailAddress($_POST['reset_emailaddress'])){ //is the email address valid?
		$user = LibUser::fetchByEmailAddress($_POST['reset_emailaddress']);

		if(is_numeric($user['id'])){
			$newPassword = LibString::randomAlphaNumericString(20);

			while(!LibUser::isValidPassword($newPassword)){
				$newPassword = LibString::randomAlphaNumericString(20);
			}

			$passwordHash = LibUser::encryptPassword($newPassword);

			$user['password_hash'] = $passwordHash;

			LibUser::save($user);

			$text = "Your password has been changed for the site ".LibConfig::$sitePath." for the username ".LibString::protectXSS($user['username'])." with the email address " .LibString::protectXSS($user['emailaddress']). ". The new password is: ".LibString::protectXSS($newPassword);

			include 'lib/thirdparty/phpmailer/class.phpmailer.php';

			$mail = new PHPMailer();
			$mail->AddAddress($user['emailaddress']);
			$mail->Subject = "[".LibConfig::$sitePath."] Password changed";
			$mail->Body = $text;
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

			$mail->Send();
		}

		LibGlobal::$notificationTexts[] = "If the email address is registered, a new password has been sent to it.";
	} else {
		LibGlobal::$errorTexts[] = "The email address is not valid.";
	}
}

/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();

?>
<div id="login_container">
	<h1>Login</h1>

	<form method="post" action="index.php">
		<fieldset>
			<label>username<br />
				<input type="text" name="loginUsername" size="15" class="input_text" />
			</label>
			<label>password<br />
				<input type="password" name="loginPassword" size="15" class="input_text" />
			</label>
			<input type="submit" value="Login" class="input_button" />
<?php
if(LibGlobal::ldapIsEnabled()){
	echo '<p>' . LibConfig::$ldapLoginExplanation . '</p>';
}
else
	echo '			<p><img src="img/icons/add.png" alt="add" /> <a href="index.php?pid=literaturedb_register">Register a new user account</a></p>';
?>
			<p style="margin-bottom:0"><img src="img/icons/heart.png" alt="add" /> <a href="http://www.literaturedb.com">Show me the features</a></p>

		</fieldset>
	</form>

	<h2>Lost your password?</h2>
<?php
if(LibGlobal::ldapIsEnabled()){
	echo 'You can change your user details <a href="'.LibConfig::$ldapCentralUserManagementSite.'">here</a>.';
} else {
	echo '<form method="post" action="index.php"><fieldset><label>email address<br /><input type="text" name="reset_emailaddress" size="15" class="input_text" /></label><input type="submit" value="Send new password" class="input_button" /></fieldset></form>';
}
?>
</div>