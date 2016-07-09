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

namespace literaturedb;

use PDO;
use LibConfig;

class LibDb{
	public static $connection;

	static function connect(){
		$mysqlPort = 3306;

		if(LibConfig::$mysqlPort != ""){
			$mysqlPort = LibConfig::$mysqlPort;

			if(LibConfig::$mysqlServer == 'localhost'){
				// required fix due to http://php.net/manual/de/pdo.connections.php
				LibConfig::$mysqlServer = '127.0.0.1';
			}
		}

		$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8',
			LibConfig::$mysqlServer,
			$mysqlPort,
			LibConfig::$mysqlDb);

		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

		try {
			self::$connection = new PDO($dsn, LibConfig::$mysqlUser, LibConfig::$mysqlPass, $options);
		} catch (PDOException $e) {
			die('Error: the connection to the MySQL database could not be established. Probably the MySQL parameters in custom/systemconfig.php are invalid.');
			// 'The error message is: ' . $e->getMessage()
		}
	}

	static function insertId(){
		return self::$connection->lastInsertId();
	}

	static function prepare($stmt){
		return self::$connection->prepare($stmt);
	}

	static function query($stmt){
		return self::$connection->query($stmt);
	}

	static function zerofy($value){
		if($value == '' || $value < 0){
			return 0;
		}

		return $value;
	}
}
?>