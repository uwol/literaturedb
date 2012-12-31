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

class LibPerson{
	static function fetchAllAuthorsForDocument($documentId){
		$cmd = sprintf('SELECT * FROM literaturedb_asso_document_author, literaturedb_person WHERE literaturedb_asso_document_author.person_id = literaturedb_person.id AND literaturedb_asso_document_author.document_id = %s ORDER BY literaturedb_asso_document_author.position ASC',
			LibDb::secInp($documentId));
		
		$result = LibDb::query($cmd);
		$authors = array();
		while($row = mysql_fetch_array($result)){
			$authors[] = self::buildPersonArray($row);
		}
		return $authors;
	}
	
	static function fetchAllEditorsForDocument($documentId){
		$cmd = sprintf('SELECT * FROM literaturedb_asso_document_editor, literaturedb_person WHERE literaturedb_asso_document_editor.person_id = literaturedb_person.id AND literaturedb_asso_document_editor.document_id = %s ORDER BY literaturedb_asso_document_editor.position ASC',
			LibDb::secInp($documentId));
		
		$result = LibDb::query($cmd);
		$editors = array();
		while($row = mysql_fetch_array($result)){
			$editors[] = self::buildPersonArray($row);
		}
		return $editors;
	}
			
	static function fetchAllAuthors($userId, $offset = 0, $limit = 100){
		$internalLimit = 100;
		if(is_numeric($limit) && $limit > 0)
			$internalLimit = $limit;

		$internalOffset = 0;
		if(is_numeric($offset) && $offset >= 0)
			$internalOffset = $offset;
		
		$cmd = sprintf('SELECT COUNT(*) AS weight_absolute, literaturedb_person.id, literaturedb_person.firstname, literaturedb_person.prefix, literaturedb_person.lastname, literaturedb_person.suffix, literaturedb_person.user_id FROM literaturedb_person, literaturedb_asso_document_author, literaturedb_document WHERE literaturedb_document.id = literaturedb_asso_document_author.document_id AND literaturedb_person.id = literaturedb_asso_document_author.person_id AND literaturedb_person.user_id = %s GROUP BY literaturedb_person.id ORDER BY weight_absolute DESC LIMIT '.$internalOffset.','.$internalLimit,
			LibDb::secInp($userId));
		$result = LibDb::query($cmd);
		
		$authors = array();
		while($row = mysql_fetch_array($result)){
			$authors[$row['lastname'].$row['firstname']] = self::buildPersonArray($row);
		}
		ksort($authors);
		return self::addRelativeWeights($authors);
	}
	
	static function fetchNameBeginningWith($beginning, $userId){
		$cmd = sprintf('SELECT literaturedb_person.id, literaturedb_person.firstname, literaturedb_person.prefix, literaturedb_person.lastname, literaturedb_person.suffix, literaturedb_person.user_id FROM literaturedb_person WHERE user_id = %s AND (lastname LIKE %s OR firstname LIKE %s) ORDER BY lastname, firstname',
			LibDb::secInp($userId),
			LibDb::secInp($beginning . '%'),
			LibDb::secInp($beginning . '%'));
		$result = LibDb::query($cmd);
		
		$persons = array();
		while($row = mysql_fetch_array($result)){
			$persons[$row['lastname'].$row['firstname']] = self::buildPersonArray($row);
		}
		return self::addRelativeWeights($persons);
	}
	
	//----------------------------------------------------------------------------------------
	
	static function fetch($id){
		$cmd = sprintf('SELECT * FROM literaturedb_person WHERE id = %s',
			LibDb::secInp($id));
		$row = LibDb::queryArray($cmd);
		return self::buildPersonArray($row);
	}
	
	static function save($person){
		$cmd = sprintf('SELECT COUNT(*) FROM literaturedb_person WHERE id = %s',
			LibDb::secInp($person['id']));
		$count = LibDb::queryAttribute($cmd);
	
		if($count > 0){
			$cmd = sprintf('UPDATE literaturedb_person SET firstname = %s, prefix = %s, lastname = %s, suffix = %s WHERE id = %s',
				LibDb::secInp(trim($person['firstname'])),
				LibDb::secInp(trim($person['prefix'])),
				LibDb::secInp(trim($person['lastname'])),
				LibDb::secInp(trim($person['suffix'])),
				LibDb::secInp($person['id']));
			LibDb::query($cmd);
			return $person['id'];
		}
		else{

			$cmd = sprintf('INSERT INTO literaturedb_person (firstname, prefix, lastname, suffix, user_id) VALUES (%s, %s, %s, %s, %s)',
				LibDb::secInp(trim($person['firstname'])),
				LibDb::secInp(trim($person['prefix'])),
				LibDb::secInp(trim($person['lastname'])),
				LibDb::secInp(trim($person['suffix'])),
				LibDb::secInp(trim($person['user_id'])));
			LibDb::query($cmd);
			return mysql_insert_id();
		}
	}

	static function buildPersonArray($row){
		$person = array();
		$person['id'] = $row['id'];
		$person['person_address'] = self::buildCanonicalPersonAddress($row['id']);
		$person['firstname'] = $row['firstname'];
		$person['prefix'] = $row['prefix'];
		$person['lastname'] = $row['lastname'];
		$person['suffix'] = $row['suffix'];
		$person['user_id'] = $row['user_id'];
		if(isset($row['weight_absolute']))
			$person['weight_absolute'] = min(10, $row['weight_absolute']);
		else
			$person['weight_absolute'] = '';
		return $person;
	}

	static function addRelativeWeights($persons){
		$weights = array();
		$maxWeight = 1;
		
		foreach($persons as $person){
			if($person['weight_absolute'] > $maxWeight)
				$maxWeight = $person['weight_absolute'];
		}
		
		$rate = 10 / $maxWeight;
		
		//--------------------------------
		
		$personsNew = array();
		
		foreach($persons as $key => $value){
			$personNew = self::buildPersonArray($value);
			$personNew['weight_relative'] = ceil($value['weight_absolute'] * $rate);
			$personsNew[$key] = $personNew;
		}
		return $personsNew;
	}
	
	/*
	* Helper
	*/
	
	static function buildCanonicalPersonAddress($personAddress){
		$personAddress = trim($personAddress);
		$numberOfParts = substr_count($personAddress, '@');
	
		if($numberOfParts == 0)
			return $personAddress . '@' . LibConfig::$sitePath;
		elseif($numberOfParts == 1)
			return $personAddress;
		else
			return '';
	}
	
	static function buildMinimalPersonAddress($personAddress){
		$personAddress = trim($personAddress);
		$numberOfParts = substr_count($personAddress, '@');
	
		if($numberOfParts == 0)
			return $personAddress;
		elseif($numberOfParts == 1){
			if(self::getDomainPart($personAddress) == LibConfig::$sitePath)
				return self::getLocalPart($personAddress);
			else
				return self::buildCanonicalPersonAddress($personAddress);
		}
		else
			return '';
	}
	
	static function isLocalPersonAddress($personAddress){
		return self::getDomainPart(self::buildCanonicalPersonAddress($personAddress)) == LibConfig::$sitePath;		
	}
	
	static function getPersonAddressParts($personAddress){
		$personAddress = self::buildCanonicalPersonAddress($personAddress);
		return explode('@', $personAddress);
	}
	
	static function getLocalPart($personAddress){
		$personAddressParts = self::getPersonAddressParts($personAddress);
		return trim($personAddressParts[0]);
	}
	
	static function getDomainPart($personAddress){
		$personAddressParts = self::getPersonAddressParts($personAddress);
		return trim($personAddressParts[1]);
	}

	static function isValidPersonAddress($personAddress){
		if($personAddress != "")
	    	if(preg_match("/^([0-9]+)@([a-zA-Z0-9\.\-]+)$/", $personAddress))
				return true;
		return false;
	}
	
	static function isOwnPerson($person){
		global $sessionUser;
		if(LibPerson::isLocalPersonAddress($person['person_address']) && $person['user_id'] == $sessionUser->id)
			return true;
		return false;
	}
}
?>