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

class LibUser{
	var $id;
	var $firstname;
	var $lastname;
	var $username;
	var $emailAddress;

	var $activated;
	var $loggedIn = false;


	static function fetchAll(){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user ORDER BY username');
		$stmt->execute();
		
		$users = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$users[] = self::buildUserArray($row);
		}
		return $users;
	}
	
	static function fetchAllActivated(){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user WHERE activated = 1 ORDER BY username');
		$stmt->execute();
		
		$users = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$users[] = self::buildUserArray($row);
		}
		return $users;
	}
	
	static function fetchAllOrderByActivated(){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user ORDER BY activated, username');
		$stmt->execute();
		
		$users = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$users[] = self::buildUserArray($row);
		}
		return $users;
	}
	
	static function fetchAllActivatedContaining($name){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user WHERE activated = 1 AND (firstname LIKE :firstname OR lastname LIKE :lastname OR username LIKE :username OR emailaddress LIKE :emailaddress) LIMIT 0,50');
		$stmt->bindParam(':firstname', '%'.$name.'%');
		$stmt->bindParam(':lastname', '%'.$name.'%');
		$stmt->bindParam(':username', '%'.$name.'%');
		$stmt->bindParam(':emailaddress', '%'.$name.'%');
		$stmt->execute();
		
		$users = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$users[] = self::buildUserArray($row);
		}
		return $users;
	}
	
	static function fetch($id){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user WHERE id = :id');
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return self::buildUserArray($row);
	}
	
	static function fetchByUserAddress($userAddress){
		$userAddress = self::buildCanonicalUserAddress($userAddress);
		
		if(self::isLocalUserAddress($userAddress)){
			$username = self::getLocalPart($userAddress);
			
			$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user WHERE username = :username');
			$stmt->bindParam(':username', $username);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return self::buildUserArray($row);
		}
	}
	
	static function fetchByEmailAddress($emailAddress){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_user WHERE emailaddress = :emailaddress');
		$stmt->bindParam(':emailaddress', $emailAddress);
		$stmt->execute();
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return self::buildUserArray($row);
	}
	
	static function buildUserArray($row){
		$user = '';
	
		if(isset($row['id']) && $row['id'] != ''){
			$user = array();
		
			$user['id'] = $row['id'];
			$user['firstname'] = $row['firstname'];
			$user['lastname'] = $row['lastname'];
			$user['username'] = $row['username'];
			$user['emailaddress'] = $row['emailaddress'];
			$user['password_hash'] = $row['password_hash'];
			$user['is_admin'] = (in_array($row['username'], LibConfig::$admins)) ? 1 : 0;
			$user['activated'] = ($row['activated'] || $user['is_admin']) ? 1 : 0;
		}

		return $user;
	}
	
	//----------------------------------------------------------------------------------------
	
	static function save($user){
		if(!isset($user['id']))
			$user['id'] = '';

		$cleanFirstname = trim($user['firstname']);
		$cleanLastname = trim($user['lastname']);
		$cleanUsername = trim($user['username']);
		$cleanEmailAddress = trim($user['emailaddress']);
		$cleanPasswordHash = trim($user['password_hash']);
		$cleanActivated = trim($user['activated']);

		$stmt = LibDb::prepare('SELECT COUNT(*) AS number FROM literaturedb_sys_user WHERE id = :id');
		$stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $count);
		$stmt->fetch();
		
		if($count > 0){
			$stmt = LibDb::prepare('UPDATE literaturedb_sys_user SET firstname = :firstname, lastname = :lastname, username = :username, emailaddress = :emailaddress, password_hash = :password_hash, activated = :activated WHERE id = :id');
			$stmt->bindParam(':firstname', $cleanFirstname);
			$stmt->bindParam(':lastname', $cleanLastname);
			$stmt->bindParam(':username', $cleanUsername);
			$stmt->bindParam(':emailaddress', $cleanEmailAddress);
			$stmt->bindParam(':password_hash', $cleanPasswordHash);
			$stmt->bindParam(':activated', $cleanActivated, PDO::PARAM_BOOL);
			$stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
			$stmt->execute();
			
			return $user['id'];
		}
		else{
			$stmt = LibDb::prepare('INSERT INTO literaturedb_sys_user (firstname, lastname, username, emailaddress, password_hash, activated) VALUES (:firstname, :lastname, :username, :emailaddress, :password_hash, :activated)');
			$stmt->bindParam(':firstname', $cleanFirstname);
			$stmt->bindParam(':lastname', $cleanLastname);
			$stmt->bindParam(':username', $cleanUsername);
			$stmt->bindParam(':emailaddress', $cleanEmailAddress);
			$stmt->bindParam(':password_hash', $cleanPasswordHash);
			$stmt->bindParam(':activated', $cleanActivated, PDO::PARAM_BOOL);
			$stmt->execute();
	
			return LibDb::insertId();
		}
	}
	
	static function delete($userId){
		$documents = LibDocument::fetchAll($userId);
		foreach($documents as $document){
			LibDocument::delete($document['id']);
		}
		
		LibCronjobs::cleanDb();
		
		$stmt = LibDb::prepare('DELETE FROM literaturedb_sys_user WHERE id = :id');
		$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/*
	* Helper
	*/
	static function buildCanonicalUserAddress($userAddress){
		$userAddress = trim($userAddress);
		$numberOfParts = substr_count($userAddress, '@');
	
		if($numberOfParts == 0)
			return $userAddress . '@' . LibConfig::$sitePath;
		elseif($numberOfParts == 1)
			return $userAddress;
		else
			return '';
	}
	
	static function buildMinimalUserAddress($userAddress){
		$userAddress = trim($userAddress);
		$numberOfParts = substr_count($userAddress, '@');
	
		if($numberOfParts == 0)
			return $userAddress;
		elseif($numberOfParts == 1){
			if(self::getDomainPart($userAddress) == LibConfig::$sitePath)
				return self::getLocalPart($userAddress);
			else
				return self::buildCanonicalUserAddress($userAddress);
		}
		else
			return '';
	}
	
	static function isLocalUserAddress($userAddress){
		return self::getDomainPart(self::buildCanonicalUserAddress($userAddress)) == LibConfig::$sitePath;		
	}
	
	static function getUserAddressParts($userAddress){
		$userAddress = self::buildCanonicalUserAddress($userAddress);
		return explode('@', $userAddress);
	}
	
	static function getLocalPart($userAddress){
		$userAddressParts = self::getUserAddressParts($userAddress);
		return trim($userAddressParts[0]);
	}
	
	static function getDomainPart($userAddress){
		$userAddressParts = self::getUserAddressParts($userAddress);
		return trim($userAddressParts[1]);
	}
	
	//---------------------------------------------------------------------------------------------------------------

	function login($username, $password){
		$username = trim($username);
		$password = trim($password);

		if(trim($username) == ""){
			LibGlobal::$errorTexts[] = "The username is missing.";
			return false;
		}

		if(trim($password) == ""){
			LibGlobal::$errorTexts[] = "The password is missing.";
			return false;
		}

		if(LibGlobal::ldapIsEnabled())
			self::loginLdap($username, $password);
		else
			self::loginLocal($username, $password);
	}
	
	function loginLdap($username, $password){
		/*
		* determine LDAP server
		*/
		if(!isset(LibConfig::$ldapServer) || LibConfig::$ldapServer == ''){
			LibGlobal::$errorTexts[] = "LDAP: A LDAP server has to be configured in systemconfig.php under the setting ldapServer";
			return false;
		}
		$ldapServer = LibConfig::$ldapServer;
		
		/*
		* determine LDAP port
		*/
		if(!isset(LibConfig::$ldapPort) || LibConfig::$ldapPort == ''){
			LibGlobal::$errorTexts[] = "LDAP: A LDAP port has to be configured in systemconfig.php under the setting ldapPort";
			return false;
		}
		$ldapPort = LibConfig::$ldapPort;
		
		/*
		* determine LDAP authentication name
		*/
		if(!isset(LibConfig::$ldapAuthName) || LibConfig::$ldapAuthName == ''){
			LibGlobal::$errorTexts[] = "LDAP: A LDAP auth name has to be configured in systemconfig.php under the setting ldapAuthName";
			return false;
		}
		$ldapAuthName = str_replace(array('{username}', '{password}'), array($username, $password), LibConfig::$ldapAuthName);
		
		/*
		* determine LDAP authentication password
		*/
		if(!isset(LibConfig::$ldapAuthPassword)){
			LibGlobal::$errorTexts[] = "LDAP: The variable ldapAuthPassword is missing in systemconfig.php";
			return false;
		}
		$ldapAuthPassword = str_replace(array('{username}', '{password}'), array($username, $password), LibConfig::$ldapAuthPassword);
			
		/*
		* connect
		*/
		$ldapConnection = ldap_connect($ldapServer, $ldapPort); //should NOT be silenced with @ so that missing PHP extensions can be notified
		if($ldapConnection){
			if(@ldap_bind($ldapConnection, $ldapAuthName, $ldapAuthPassword)){
				$ldapSearchFilter = str_replace(array('{username}', '{password}'), array($username, $password), LibConfig::$ldapSearchFilter);
				$results = @ldap_search($ldapConnection, LibConfig::$ldapBaseDN, $ldapSearchFilter);
				$resultEntries = @ldap_get_entries($ldapConnection, $results);

				if($resultEntries["count"] == 1){ //exactly one user found
					$ldapUser = $resultEntries[0];

					if(!is_array($ldapUser)){
						LibGlobal::$errorTexts[] = 'LDAP: The LDAP server did not deliver valid user details.';
						return false;
					}
					
					$firstname = utf8_encode($ldapUser[LibConfig::$ldapFirstname]['0']);
					$lastname = utf8_encode($ldapUser[LibConfig::$ldapLastname]['0']);
					$emailAddress = utf8_encode($ldapUser[LibConfig::$ldapEmailAddress]['0']);

					//try to fetch user from local user database
					$user = self::fetchByUserAddress($username);

					if(isset($user['id']) && is_numeric($user['id'])){ //user found -> refresh user details with details from LDAP
						if($firstname != '')
							$user['firstname'] = $firstname;
						if($lastname != '')
							$user['lastname'] = $lastname;
						$user['username'] = $username;
						if($emailAddress != '')
							$user['emailaddress'] = $emailAddress;
						$user['is_admin'] = (in_array($username, LibConfig::$admins)) ? 1 : 0;
						$user['activated'] = 1;
					}
					else{ //user not found -> create new user with user details from LDAP
						$user = array();
						$user['firstname'] = $firstname;
						$user['lastname'] = $lastname;
						$user['username'] = $username;
						$user['emailaddress'] = $emailAddress;
						$user['is_admin'] = (in_array($username, LibConfig::$admins)) ? 1 : 0;
						$user['activated'] = 1;
					}
					$userId = self::save($user);
					$user = self::fetch($userId);
					
					//Login successful
					$this->id = $user["id"];
					$this->firstname = $user['firstname'];
					$this->lastname = $user['lastname'];
					$this->username = $user['username'];
					$this->emailAddress = $user['emailaddress'];
					$this->activated = $user['activated'];

					$this->loggedIn = true;
					return true;
				}
				else{
					LibGlobal::$errorTexts[] = "The username or password is wrong. No user account was found under this username.";
					return false;
				}
			}
			else{ 
				LibGlobal::$errorTexts[] = 'The username or password is wrong.';
				return false;
			}
		}
		else{
			LibGlobal::$errorTexts[] = 'LDAP: A connection could not be established to the LDAP server '.$ldapServer;
			return false;
		}
	}
	
	function loginLocal($username, $password){
		$username = trim($username);
		$password = trim($password);
	
		//clean memory
		$this->id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->username = '';
		$this->emailAddress = '';
		$this->activated = false;
		$this->loggedIn = false;
		
		
		/**
		* Problem cases
		*/
		
		//1. username missing?
		if($username == ''){
			LibGlobal::$errorTexts[] = "The username is missing.";
			return false;
		}
		
		//2. password missing?
		if($password == ''){
			LibGlobal::$errorTexts[] = "The password is missing.";
			return false;
		}
		
		$stmt = LibDb::prepare("SELECT * FROM literaturedb_sys_user WHERE username = :username");
		$stmt->bindParam(':username', $username);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$user = self::buildUserArray($row);
		
		//3. no user account found for username?
		if(!is_array($user) || !isset($user['id']) || !is_numeric($user['id']) || !($user['id'] > 0)){
			//ungenaue Fehlermeldung ausgeben gegen Hacks!
			LibGlobal::$errorTexts[] = 'The username or password is wrong.';
			return false;
		}		
		
		//4. user is not activated
		if(!$user['is_admin'] && $user['activated'] != 1){
			LibGlobal::$errorTexts[] = "This account is not activated.";
			return false;
		}
		
		//5. password_hash is invalid
		if(trim($user["password_hash"] == '')){
			LibGlobal::$errorTexts[] = 'The database does not contain a valid password hash for this account.';
			return false;
		}
		
		//6. too many login tries
		$stmt = LibDb::prepare("SELECT COUNT(*) AS number FROM literaturedb_sys_event WHERE user_id = :user_id AND type = 2 AND DATEDIFF(NOW(), date) = 0");
		$stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $numberOfMistakenLoginsToday);
		$stmt->fetch();
		
		if($numberOfMistakenLoginsToday > 20){
			LibGlobal::$errorTexts[] = "This account is blocked for today due to mistaken login attempts.";
			return false;
		}

		//7. check password
		if(self::checkPassword($password, $user['password_hash'])){
			//a. login successful
			$this->loggedIn = true;

			$this->id = $user["id"];
			$this->firstname = $user['firstname'];
			$this->lastname = $user['lastname'];
			$this->username = $user['username'];
			$this->emailAddress = $user['emailaddress'];
			$this->activated = $user['activated'];
			
			// Do not log successful login here, because all REST calls would be logged

			return true;
		}
		
		//8. log mistaken login
		$errorType = 2;
		
		$stmt = LibDb::prepare("INSERT INTO literaturedb_sys_event (user_id, type, date, ipaddress) VALUES (:user_id, :type, NOW(), :ipaddress)");
		$stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
		$stmt->bindParam(':type', $errorType, PDO::PARAM_INT);
		$stmt->bindParam(':ipaddress', $_SERVER['REMOTE_ADDR']);
		$stmt->execute();
	
		LibGlobal::$errorTexts[] = "The username or password is wrong.";
		return false;
	}
	
	static function encryptPassword($password){
		$phpassHasher = new PasswordHash(12, FALSE);
		return $phpassHasher->HashPassword($password);
	}
	
	static function checkPassword($password, $storedHash){
		$password = trim($password);
		$storedHash = trim($storedHash);
	
		if($password != '' && $storedHash != ''){
			$phpassHasher = new PasswordHash(12, FALSE);
			return $phpassHasher->CheckPassword($password, $storedHash);
		}

		return false;
	}

	static function isValidPassword($password){
		// min. 1 numeral, min. 1 small letter, min. 1 capital letter, no spaces, min. 10 characters
		return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{10,}$/", trim($password));
	}
	
	static function isValidEmailAddress($email){
		if($email != ""){
	    	if (preg_match("/^([a-zA-Z0-9\.\_\-]+)@([a-zA-Z0-9\.\-]+\.[A-Za-z][A-Za-z]+)$/", $email)){
				return true;
			}
		}
		return false;
	}
	
	static function isValidUserAddress($userAddress){
		if($userAddress != ""){
	    	if (preg_match("/^([a-z0-9\.\_\-]+)@([a-z0-9\.\-]+)$/", $userAddress)){
				return true;	
			}
		}
		return false;
	}
	
	static function isValidUsername($username){
		if($username != ""){
	    	if (preg_match("/^([a-z0-9\.\_\-]+)$/", $username)){
				return true;
			}
		}
		return false;
	}
	
	static function getPasswordRequirements(){
		return 'The password has to be at least 10 characters long, containing at least one numeral, one small character and one capital character. Spaces are not allowed.';
	}

	//------------------------------------------
   
	function getId(){
		return $this->id;
	}
  
	function getFirstname(){
		return $this->firstname;
	}
  
	function getLastname(){
		return $this->lastname;
	}
	
	function getEmailAddress(){
		return $this->emailAddress;
	}
  
	function isLoggedin(){
		return $this->loggedIn;
	}
	
	function getUserAddress(){
		return LibUser::buildCanonicalUserAddress($this->username);	
	}
	
	function getUsername(){
		return $this->username;
	}
	
	function isAdmin(){
		return in_array($this->username, LibConfig::$admins);
	}
}
?>