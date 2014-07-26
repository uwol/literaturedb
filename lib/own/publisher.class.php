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

class LibPublisher{
	static function fetchNameBeginningWith($beginning, $userId){
		$likeName = $beginning . '%';
	
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_publisher WHERE user_id = :user_id AND name LIKE :name ORDER BY name');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':name', $likeName);
		$stmt->execute();

		$publishers = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$publisher = array();
			$publisher['id'] = $row['id'];
			$publisher['name'] = $row['name'];
			$publisher['user_id'] = $row['user_id'];
			$publishers[] = $publisher;
		}
		return $publishers;
	}
}
?>