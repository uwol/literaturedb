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
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_share WHERE local_user_id = :local_user_id');
		$stmt->bindValue(':local_user_id', $localUserId, PDO::PARAM_INT);
		$stmt->execute();

		$shares = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$shares[$row['remote_user_address']] = self::buildShareArray($row);
		}

		return $shares;
	}

	static function fetchAllFollowedByLocalUserId($localUserId){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_share WHERE following = 1 AND local_user_id = :local_user_id');
		$stmt->bindValue(':local_user_id', $localUserId, PDO::PARAM_INT);
		$stmt->execute();

		$shares = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$shares[$row['remote_user_address']] = self::buildShareArray($row);
		}

		return $shares;
	}

	static function fetch($id){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_share WHERE id = :id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return self::buildShareArray($row);
	}

	static function fetchByLocalUserIdAndRemoteUserAddress($localUserId, $remoteUserAddress){
		$minimalRemoteUserAddress = LibUser::buildMinimalUserAddress($remoteUserAddress);

		$stmt = LibDb::prepare('SELECT * FROM literaturedb_sys_share WHERE local_user_id = :local_user_id AND (remote_user_address = :minimal_remote_user_address OR remote_user_address = :remote_user_address)');
		$stmt->bindValue(':local_user_id', $localUserId, PDO::PARAM_INT);
		$stmt->bindValue(':minimal_remote_user_address', $minimalRemoteUserAddress);
		$stmt->bindValue(':remote_user_address', $remoteUserAddress);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return self::buildShareArray($row);
	}

	static function delete($subscriptionId){
		$stmt = LibDb::prepare('DELETE FROM literaturedb_sys_share WHERE id = :id');
		$stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
		$stmt->execute();
	}

	static function save($share){
		$cleanLocalUserId = trim($share['local_user_id']);
		$cleanRemoteUserAddress = trim($share['remote_user_address']);
		$cleanFollowing = trim($share['following']);
		$cleanSharing = trim($share['sharing']);

		$stmt = LibDb::prepare('SELECT COUNT(*) AS number FROM literaturedb_sys_share WHERE local_user_id = :local_user_id AND remote_user_address = :remote_user_address');
		$stmt->bindValue(':local_user_id', $cleanLocalUserId, PDO::PARAM_INT);
		$stmt->bindValue(':remote_user_address', $cleanRemoteUserAddress);
		$stmt->execute();
		$stmt->bindColumn('number', $count);
		$stmt->fetch();

		if($count > 0){
			$stmt = LibDb::prepare('UPDATE literaturedb_sys_share SET following = :following, sharing = :sharing WHERE local_user_id = :local_user_id AND remote_user_address = :remote_user_address');
			$stmt->bindValue(':local_user_id', $cleanLocalUserId, PDO::PARAM_INT);
			$stmt->bindValue(':remote_user_address', $cleanRemoteUserAddress);
			$stmt->bindValue(':following', $cleanFollowing, PDO::PARAM_BOOL);
			$stmt->bindValue(':sharing', $cleanSharing, PDO::PARAM_BOOL);
			$stmt->execute();
		} else {
			$stmt = LibDb::prepare('INSERT INTO literaturedb_sys_share (local_user_id, remote_user_address, following, sharing) VALUES (:local_user_id, :remote_user_address, :following, :sharing)');
			$stmt->bindValue(':local_user_id', $cleanLocalUserId, PDO::PARAM_INT);
			$stmt->bindValue(':remote_user_address', $cleanRemoteUserAddress);
			$stmt->bindValue(':following', $cleanFollowing, PDO::PARAM_BOOL);
			$stmt->bindValue(':sharing', $cleanSharing, PDO::PARAM_BOOL);
			$stmt->execute();

			return LibDb::insertId();
		}
	}

	static function buildShareArray($row){
		$share = '';

		if(isset($row['id']) && $row['id'] != ''){
			$share = array();

			$share['id'] = $row['id'];
			$share['local_user_id'] = $row['local_user_id'];
			$share['remote_user_address'] = $row['remote_user_address'];
			$share['following'] = $row['following'];
			$share['sharing'] = $row['sharing'];
		}

		return $share;
	}
}
?>