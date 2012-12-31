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

class LibDb{
	public static $connection;
	
	static function connect(){
		$portString = '';

		if(LibConfig::$mysqlPort != "")
			$portString = ":".LibConfig::$mysqlPort;

		self::$connection = mysql_connect(LibConfig::$mysqlServer . $portString, LibConfig::$mysqlUser, LibConfig::$mysqlPass);
		if (!self::$connection)
    		die('Error: the connection to the MySQL database could not be established. Probably the MySQL parameters in custom/systemconfig.php are invalid. The error message is: ' . mysql_error());
		mysql_select_db(LibConfig::$mysqlDb);
		mysql_query("SET NAMES 'utf8'");
	}

	static function query($cmd){
		$result=mysql_query($cmd);
		LibGlobal::$numberOfMysqlQueries++;
		if (!$result)
    		echo('SQL query problem: ' . $cmd .' <br /><b>'. mysql_error()."</b><br /><br />");
		return $result;
	}
	
	static function queryLoudWithoutDie($cmd){
		$result=mysql_query($cmd);
		LibGlobal::$numberOfMysqlQueries++;
		if (!$result)
    		echo('SQL query problem: ' . $cmd .' <br /><b>'. mysql_error()."</b><br /><br />");
		return $result;
	}
	

	static function queryQuiet($cmd){
		LibGlobal::$numberOfMysqlQueries++;
		return @mysql_query($cmd);
	}

	static function queryAttribute($cmd){
		$result = self::query($cmd);
		LibGlobal::$numberOfMysqlQueries++;
		$row = mysql_fetch_row($result);
 		$value = $row[0];
 		return $value;
	}

	static function queryRow($cmd)	{
		$result = self::query($cmd);
		LibGlobal::$numberOfMysqlQueries++;
		$row = mysql_fetch_row($result);
		return $row;
	}

	static function queryArray($cmd){
		$result = self::query($cmd);
		LibGlobal::$numberOfMysqlQueries++;
		$row = mysql_fetch_array($result);
		return $row;
	}

	static function quoteSmart($value){
		if(get_magic_quotes_gpc())
			$value = stripslashes($value);

		if($value == 'NULL')
			return 'NULL';

		return "'" . mysql_real_escape_string($value) . "'";
	}

	static function secInp($value){
		$value = self::quoteSmart($value);
		return $value;
	}
	
	static function zerofy($value){
		if($value == '' || $value < 0)
			return 0;
		return $value;
	}
}
?>