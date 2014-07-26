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

class LibGlobal{
	public static $version = "1.30";
	public static $selectedUserAddresses = array();
	
	public static $notificationTexts = array();
	public static $errorTexts = array();
	
	public static function ldapIsEnabled(){
		if(isset(LibConfig::$ldapEnabled) && LibConfig::$ldapEnabled > 0)
			return true;
		else
			return false;
	}
}
?>