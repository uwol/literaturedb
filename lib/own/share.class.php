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

class LibShare{
	static function fetchAllByLocalUserId($localUserId){
		$cmd = sprintf('SELECT * FROM literaturedb_sys_share WHERE local_user_id = %s',
			LibDb::secInp($localUserId));
		$result = LibDb::query($cmd);

		$shares = array();
		while($row = mysql_fetch_array($result))
			$shares[$row['remote_user_address']] = self::buildShareArray($row);
		return $shares;
	}
	
	static function fetchAllFollowedByLocalUserId($localUserId){
		$cmd = sprintf('SELECT * FROM literaturedb_sys_share WHERE following = 1 AND local_user_id = %s',
			LibDb::secInp($localUserId));
		$result = LibDb::query($cmd);

		$shares = array();
		while($row = mysql_fetch_array($result))
			$shares[$row['remote_user_address']] = self::buildShareArray($row);
		return $shares;
	}
	
	static function fetch($id){
		$cmd = sprintf('SELECT * FROM literaturedb_sys_share WHERE id = %s',
			LibDb::secInp($id));
		$row = LibDb::queryArray($cmd);
		return self::buildShareArray($row);
	}
	
	static function fetchByLocalUserIdAndRemoteUserAddress($localUserId, $remoteUserAddress){
		$cmd = sprintf('SELECT * FROM literaturedb_sys_share WHERE local_user_id = %s AND (remote_user_address = %s OR remote_user_address = %s)',
			LibDb::secInp($localUserId),
			LibDb::secInp(LibUser::buildMinimalUserAddress($remoteUserAddress)),
			LibDb::secInp($remoteUserAddress));
		$row = LibDb::queryArray($cmd);
		return self::buildShareArray($row);
	}
	
	static function delete($subscriptionId){
		$cmd = sprintf('DELETE FROM literaturedb_sys_share WHERE id = %s',
			LibDb::SecInp($subscriptionId));
		LibDb::query($cmd);
	}

	static function save($share){
		$cmd = sprintf('SELECT COUNT(*) FROM literaturedb_sys_share WHERE local_user_id = %s AND remote_user_address = %s',
			LibDb::secInp(trim($share['local_user_id'])),
			LibDb::secInp(trim($share['remote_user_address'])));
		$count = LibDb::queryAttribute($cmd);

		if($count > 0){
			$cmd = sprintf('UPDATE literaturedb_sys_share SET following = %s, sharing = %s WHERE local_user_id = %s AND remote_user_address = %s',
				LibDb::secInp(trim($share['following'])),
				LibDb::secInp(trim($share['sharing'])),
				LibDb::secInp(trim($share['local_user_id'])),
				LibDb::secInp(trim($share['remote_user_address'])));
			LibDb::query($cmd);

			$id = $share['id'];
		}
		else{
			$cmd = sprintf('INSERT INTO literaturedb_sys_share (local_user_id, remote_user_address, following, sharing) VALUES (%s, %s, %s, %s)',
				LibDb::secInp(trim($share['local_user_id'])),
				LibDb::secInp(trim($share['remote_user_address'])),
				LibDb::secInp(trim($share['following'])),
				LibDb::secInp(trim($share['sharing'])));
			LibDb::query($cmd);
	
			return mysql_insert_id();
		}
	}

	static function buildShareArray($row){
		$share = array();
		$share['id'] = $row['id'];
		$share['local_user_id'] = $row['local_user_id'];
		$share['remote_user_address'] = $row['remote_user_address'];
		$share['following'] = $row['following'];
		$share['sharing'] = $row['sharing'];
		return $share;
	}
}
?>