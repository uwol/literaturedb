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
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_asso_document_author, literaturedb_person WHERE literaturedb_asso_document_author.person_id = literaturedb_person.id AND literaturedb_asso_document_author.document_id = :document_id ORDER BY literaturedb_asso_document_author.position ASC');
		$stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
		$stmt->execute();

		$authors = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$authors[] = self::buildPersonArray($row);
		}
		return $authors;
	}
	
	static function fetchAllEditorsForDocument($documentId){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_asso_document_editor, literaturedb_person WHERE literaturedb_asso_document_editor.person_id = literaturedb_person.id AND literaturedb_asso_document_editor.document_id = :document_id ORDER BY literaturedb_asso_document_editor.position ASC');
		$stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
		$stmt->execute();
		
		$editors = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$editors[] = self::buildPersonArray($row);
		}
		return $editors;
	}
			
	static function fetchAllAuthors($userId, $offset = 0, $limit = 100){
		$internalLimit = 100;
		if(is_numeric($limit) && $limit > 0)
			$internalLimit = (int) $limit;

		$internalOffset = 0;
		if(is_numeric($offset) && $offset >= 0)
			$internalOffset = (int) $offset;
		
		$stmt = LibDb::prepare('SELECT COUNT(*) AS weight_absolute, literaturedb_person.id, literaturedb_person.firstname, literaturedb_person.prefix, literaturedb_person.lastname, literaturedb_person.suffix, literaturedb_person.user_id FROM literaturedb_person, literaturedb_asso_document_author, literaturedb_document WHERE literaturedb_document.id = literaturedb_asso_document_author.document_id AND literaturedb_person.id = literaturedb_asso_document_author.person_id AND literaturedb_person.user_id = :user_id GROUP BY literaturedb_person.id ORDER BY weight_absolute DESC LIMIT :offset,:limit');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $internalOffset, PDO::PARAM_INT);
		$stmt->bindParam(':limit', $internalLimit, PDO::PARAM_INT);
		$stmt->execute();
		
		$authors = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$authors[$row['lastname'].$row['firstname']] = self::buildPersonArray($row);
		}
		ksort($authors);
		return self::addRelativeWeights($authors);
	}
	
	static function fetchNameBeginningWith($beginning, $userId){
		$likeFirstname = $beginning . '%';
		$likeLastname = $beginning . '%';
	
		$stmt = LibDb::prepare('SELECT literaturedb_person.id, literaturedb_person.firstname, literaturedb_person.prefix, literaturedb_person.lastname, literaturedb_person.suffix, literaturedb_person.user_id FROM literaturedb_person WHERE user_id = :user_id AND (lastname LIKE :lastname OR firstname LIKE :firstname) ORDER BY lastname, firstname');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':firstname', $likeFirstname);
		$stmt->bindParam(':lastname', $likeLastname);
		$stmt->execute();
		
		$persons = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$persons[$row['lastname'].$row['firstname']] = self::buildPersonArray($row);
		}
		return self::addRelativeWeights($persons);
	}
	
	//----------------------------------------------------------------------------------------
	
	static function fetch($id){
		$stmt = LibDb::prepare('SELECT * FROM literaturedb_person WHERE id = :id');
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return self::buildPersonArray($row);
	}
	
	static function save($person){
		$cleanFirstname = trim($person['firstname']);
		$cleanPrefix = trim($person['prefix']);
		$cleanLastname = trim($person['lastname']);
		$cleanSuffix = trim($person['suffix']);

		$stmt = LibDb::prepare('SELECT COUNT(*) AS number FROM literaturedb_person WHERE id = :id');
		$stmt->bindParam(':id', $person['id'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $count);
		$stmt->fetch();
	
		if($count > 0){
			$stmt = LibDb::prepare('UPDATE literaturedb_person SET firstname = :firstname, prefix = :prefix, lastname = :lastname, suffix = :suffix WHERE id = :id');
			$stmt->bindParam(':firstname', $cleanFirstname);
			$stmt->bindParam(':prefix', $cleanPrefix);
			$stmt->bindParam(':lastname', $cleanLastname);
			$stmt->bindParam(':suffix', $cleanSuffix);
			$stmt->bindParam(':id', $person['id'], PDO::PARAM_INT);
			$stmt->execute();

			return $person['id'];
		}
		else{
			$stmt = LibDb::prepare('INSERT INTO literaturedb_person (firstname, prefix, lastname, suffix, user_id) VALUES (:firstname, :prefix, :lastname, :suffix, :user_id)');
			$stmt->bindParam(':firstname', $cleanFirstname);
			$stmt->bindParam(':prefix', $cleanPrefix);
			$stmt->bindParam(':lastname', $cleanLastname);
			$stmt->bindParam(':suffix', $cleanSuffix);
			$stmt->bindParam(':id', $person['user_id'], PDO::PARAM_INT);
			$stmt->execute();

			return LibDb::insertId();
		}
	}

	static function buildPersonArray($row){
		$person = '';
	
		if(isset($row['id']) && $row['id'] != ''){
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
		}
			
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