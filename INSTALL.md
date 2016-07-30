literaturedb installation
===========================

Introduction
------------

Installation of literaturedb takes four simple steps and just a few minutes. Requirements for installation are:

* PHP >= 5.5
* MySQL >= 4.1
* PHP requires read and write access to the directory custom/documents
* UTF8 has to be enabled in PHP and MySQL for special characters


Installation
------------

### Configure

The file custom/systemconfig.php is the central configuration file. Please enter the connection settings for the MySQL server at $mysqlServer, $mysqlUser, $mysqlPass and $mysqlDb. Normally, you can find this information in the configuration menu of your web hoster.

### Upload

Upload all files and directories to the root folder of the web hosting in BINARY mode, so that it can be reached as e.g. http://literaturedb.example.org/index.php

### Create tables

Execute install.php by opening e.g. the web page http://literaturedb.example.org/install.php in your browser. All tables should be created in the database properly. Afterwards delete the file install.php.

### Register an admin account

Open the site in your browser and register a new user. After registration check that the username of the user you just created is also contained in custom/systemconfig.php in the $admins list so that the new user is an administrator. The admin user account is activated automatically. Also a registration notification should have been sent to your email address.

### Use

Log into the system with the new user account you just registered and upload some documents. Invite some colleagues for registration, activate their accounts on the admin page and share documents with them.

### Update

For updating overwrite all installed files and directories (index.php, lib, pages, ...) with files from the updated package EXCEPT the folder custom. DO NOT overwrite the folder custom, because all your document files are placed in there!