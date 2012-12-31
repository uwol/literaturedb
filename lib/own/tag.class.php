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

class LibTag{
	static function fetchAll($userId, $offset = 0, $limit = 100){
		$internalLimit = 100;
		if(is_numeric($limit) && $limit > 0)
			$internalLimit = $limit;

		$internalOffset = 0;
		if(is_numeric($offset) && $offset >= 0)
			$internalOffset = $offset;
		
		$cmd = sprintf('SELECT COUNT(name) AS weight_absolute, literaturedb_tag.name, literaturedb_tag.id, literaturedb_tag.user_id FROM literaturedb_tag, literaturedb_asso_document_tag, literaturedb_document WHERE literaturedb_asso_document_tag.document_id = literaturedb_document.id AND literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.user_id = %s GROUP BY literaturedb_tag.name ORDER BY weight_absolute DESC LIMIT '.$internalOffset.','.$internalLimit,
			LibDb::secInp($userId));

		$result = LibDb::query($cmd);
		$tags = array();
		while($row = mysql_fetch_array($result))

			$tags[$row['name']] = self::buildTagArray($row);
		ksort($tags);
		return self::addRelativeWeights($tags);
	}
	
	static function fetchNameBeginningWith($beginning, $userId){
		$cmd = sprintf('SELECT COUNT(name) AS weight_absolute, literaturedb_tag.name, literaturedb_tag.id, literaturedb_tag.user_id FROM literaturedb_tag, literaturedb_asso_document_tag, literaturedb_document WHERE literaturedb_asso_document_tag.document_id = literaturedb_document.id AND literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.user_id = %s AND literaturedb_tag.name LIKE %s GROUP BY literaturedb_tag.name ORDER BY literaturedb_tag.name',
			LibDb::secInp($userId),
			LibDb::secInp($beginning . '%'));

		$result = LibDb::query($cmd);
		$tags = array();
		while($row = mysql_fetch_array($result))

			$tags[$row['name']] = self::buildTagArray($row);
		return self::addRelativeWeights($tags);
	}
	
	static function fetchAllForDocument($documentId){
		$cmd = sprintf('SELECT literaturedb_tag.name, literaturedb_tag.id, literaturedb_tag.user_id FROM literaturedb_tag, literaturedb_asso_document_tag WHERE literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_asso_document_tag.document_id = %s ORDER BY literaturedb_tag.name',
			LibDb::secInp($documentId));

		$result = LibDb::query($cmd);
		$tags = array();
		while($row = mysql_fetch_array($result))

			$tags[$row['name']] = self::buildTagArray($row);
		return self::addRelativeWeights($tags);
	}
	
	static function buildTagArray($row){
		$tag = array();
		$tag['id'] = $row['id'];
		$tag['name'] = $row['name'];
		$tag['user_id'] = $row['user_id'];
		if(isset($row['weight_absolute']))
			$tag['weight_absolute'] = ($row['weight_absolute'] > 0) ? min(10, $row['weight_absolute']) : 1;
		else
			$tag['weight_absolute'] = '';
		$tag['tag_address'] = self::buildCanonicalTagAddress($row['id']);
		return $tag;
	}

	
	static function addRelativeWeights($tags){
		$weights = array();
		$maxWeight = 1;
		foreach($tags as $tag){
			if($tag['weight_absolute'] > $maxWeight)
				$maxWeight = $tag['weight_absolute'];
		}
		$rate = 10 / $maxWeight;
		
		//--------------------------------
		
		$tagsNew = array();
		foreach($tags as $key => $value){
			$tagNew = self::buildTagArray($value);
			if(substr($value['name'], 0, 1) == '!')
				$tagNew['weight_relative'] = 1;
			else
				$tagNew['weight_relative'] = ceil($value['weight_absolute'] * $rate);
			$tagsNew[$key] = $tagNew;
		}
		return $tagsNew;
	}
	
	/*
	* Helper
	*/
	static function buildCanonicalTagAddress($tagAddress){
		$tagAddress = trim($tagAddress);
		$numberOfParts = substr_count($tagAddress, '@');
	
		if($numberOfParts == 0)
			return $tagAddress . '@' . LibConfig::$sitePath;
		elseif($numberOfParts == 1)
			return $tagAddress;
		else
			return '';
	}
	
	static function isLocalTagAddress($tagAddress){
		return self::getDomainPart(self::buildCanonicalTagAddress($tagAddress)) == LibConfig::$sitePath;		
	}
	
	static function getTagAddressParts($tagAddress){
		$tagAddress = self::buildCanonicalTagAddress($tagAddress);
		return explode('@', $tagAddress);
	}
	
	static function getLocalPart($tagAddress){
		$tagAddressParts = self::getTagAddressParts($tagAddress);
		return trim($tagAddressParts[0]);
	}
	
	static function getDomainPart($tagAddress){
		$tagAddressParts = self::getTagAddressParts($tagAddress);
		return trim($tagAddressParts[1]);
	}
	
	static function isOwnTag($tag){
		global $sessionUser;
		if(LibTag::isLocalTagAddress($tag['tag_address']) && $tag['user_id'] == $sessionUser->id)
			return true;
		return false;
	}
}
?>