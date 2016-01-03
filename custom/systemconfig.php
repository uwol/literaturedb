<?php
class LibConfig{
	/**
	* MySQL database settings
	*/
	public static $mysqlServer = "localhost"; //address of the database server. Often localhost
	public static $mysqlUser = "username"; //username
	public static $mysqlPass = "password"; //password
	public static $mysqlDb = "database"; //database
	public static $mysqlPort = ""; //the port can be left blank, default value 3306

	/**
	* General options
	*/
	//the name will appear in the header, registration emails etc.
	public static $systemName = "literaturedb";
	//registration emails will be sent to this email address
	public static $emailRegistration = "some.email.address@example.org";

	//the sitePath must contain your site's domain name (no @ or so...)
	//it should not be changed after the installation
	//for example this would be OK: literaturedb.example.org
	//BUT NOT www.example.org/somedirectory/literaturedb/
	//AND NOT http:// or https://literaturedb.example.org
	public static $sitePath = "literaturedb.example.org";

	//chose a short siteUri (2-3 letters)!
	//this string appears in each exported bibtex entry
	//it should not be changed after the installation
	public static $siteUri = "xyz";

	//the usernames of admins are put in this array e.g.
	//public static $admins = array('admin1', 'admin2', 'admin3');
	public static $admins = array('admin1');



	/*
	* Optional settings, not relevant in most cases
	*/
	//you can send registration mails etc. over an smtp relay
	public static $smtpHost = '';
	public static $smtpUsername = '';
	public static $smtpPassword = '';

	//you can place the document files in another directory
	public static $documentDir = 'custom/documents';

	//interface options
	public static $headerSubtitle = 'the collaborative bibliography tool';
	public static $footer = '<a href="http://validator.w3.org/check?uri=referer" title="W3C HTML Validation">XHTML</a> | Rayk <a href="http://creativecommons.org/licenses/by/1.0/">CC</a>';



	/*
	* LDAP settings, only important for large organizations
	* !!! extension=php_ldap.dll has to be enable in php.ini for using ldap !!!
	*/

	//- part 1: general settings --------------------------------------------------------------

	//change to 1 to enable LDAP authentication
	public static $ldapEnabled = 0;

	//the LDAP server to use for authentication
	//e.g. 'ldapserver.yourorganizationdomain.com' for LDAP and
	//e.g. 'ldaps://ldap.example.com/' for LDAPS
	//e.g. University of Muenster: 'wwusv1.uni-muenster.de'
	public static $ldapServer = '';

	//LDAP server port
	//default value is 389 for LDAP, 636 for LDAPS
	public static $ldapPort = 389;

	//central user management site, where users can change their LDAP user details
	//e.g. University of Muenster: 'http://www.uni-muenster.de/ZIV/MeinZIV/'
	public static $ldapCentralUserManagementSite = '';

	//an explanatory text telling the user to use his central login account
	//e.g. University of Muenster: 'Please log in with your ZIV user account.'
	public static $ldapLoginExplanation = 'Please log in with your central user account.';

	//- part 2: LDAP authentication -----------------------------------------------------------

	//LDAP auth login name for LDAP binding
	//{username} will be replaced with the username entered at user login
	//{password} will be replaced with the password entered at user login
	//e.g. University of Muenster: 'cn={username},ou=Projekt-Benutzer,dc=uni-muenster,dc=de'
	public static $ldapAuthName = '';

	//LDAP auth password
	//{username} will be replaced with the username entered at user login
	//{password} will be replaced with the password entered at user login
	//e.g. University of Muenster: '{password}'
	public static $ldapAuthPassword = '';

	//- part 3: user account search -------------------------------------------------------------------

	//LDAP base DN object, where LDAP user objects should be searched
	//e.g. University of Muenster: 'ou=Projekt-Benutzer,dc=uni-muenster,dc=de'
	public static $ldapBaseDN = '';

	//LDAP search filter for finding the LDAP user account object of the user logging in
	//{username} will be replaced with the username entered at user login
	//{password} will be replaced with the password entered at user login
	//e.g. University of Muenster: 'cn={username}'
	public static $ldapSearchFilter = '';

	//- part 4: value mappings ----------------------------------------------------------------

	//which LDAP attributes are mapped to which literaturedb user properties:
	public static $ldapFirstname = 'givenname';
	public static $ldapLastname = 'sn';
	public static $ldapEmailAddress = 'mail';
}
?>