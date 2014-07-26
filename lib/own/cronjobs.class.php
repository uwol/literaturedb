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

class LibCronjobs{
	static function run(){
		if(!self::hasHtaccessDenyFile(LibConfig::$documentDir.'/')){
			self::generateHtaccessDenyFile(LibConfig::$documentDir.'/');
		}
	}
	
	static function generateHtaccessDenyFile($directory){
		$content = "deny from all";
		self::generateHtaccessFile($directory, $content);
    }
    
    static function generateHtaccessFile($directory, $content){
    	$filename = $directory.".htaccess";
	    $handle = @fopen($filename, "w");
    	@fwrite($handle, $content);
    	@fclose($handle);
    }
    
    static function hasHtaccessDenyFile($directory){
    	$filename = $directory.".htaccess";
    	
    	if(!is_file($filename)){
    		return false;
    	}
    	
    	$handle = @fopen($filename, "r");
    	$content = @fread($handle, @filesize($filename));
    	@fclose($handle);
    	
    	if($content == "deny from all"){
    		return true;
    	}
    	else{
    		return false;
    	}
    }
    
    static function cleanDb(){
		LibDb::query('DELETE FROM literaturedb_sys_share WHERE local_user_id NOT IN (SELECT id FROM literaturedb_sys_user)');
		LibDb::query('DELETE FROM literaturedb_sys_event WHERE user_id NOT IN (SELECT id FROM literaturedb_sys_user)');
		LibDb::query('DELETE FROM literaturedb_sys_event WHERE DATEDIFF(NOW(),date) > 30');
	}
}
?>