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

class LibString{
	static function truncate($string, $start = 50, $replacement = ' ...') {
		if(strlen($string) <= $start)
			return $string;

		$whitespaceposition = strpos($string, ' ', $start);

		if(is_numeric($whitespaceposition)){
			$string = substr($string, 0, $whitespaceposition);
			return substr_replace($string, $replacement, $whitespaceposition);
		} else {
			return $string;
		}
	}

	static function randomAlphaNumericString($len, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'){
		$string = '';

		for ($i = 0; $i < $len; $i++){
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}

		return $string;
	}

	static function getPersonNameString($person){
		$nameParts = array();

		$nameParts[] = $person['firstname'];
		if($person['prefix'] != ''){
			$nameParts[] = $person['prefix'];
		}

		$lastNames = explode(' ', $person['lastname']);

		if(count($lastNames) > 1){
			$nameParts[] = '{' . implode(' ', $lastNames) . '}';
		} else {
			$nameParts[] = implode(' ', $lastNames);
		}

		if($person['suffix'] != ''){
			$nameParts[] = $person['suffix'];
		}

		return implode(' ', $nameParts);
	}

	static function getPersonsNameString($persons){
		$personNameStrings = array();

		foreach($persons as $person){
			$personNameStrings[] = self::getPersonNameString($person);
		}

		return implode(' and ', $personNameStrings);
	}

	static function parsePersonNameString($personNameString, $userId){
		$persons = array();

		// if the person name string does contain ands, then we assume that ands are used as separators between persons
		if(strstr($personNameString, ' and ')){
			$personNameStrings = explode(' and ', $personNameString);
		} else {
			$personNameStrings = explode(',', $personNameString);
		}

		foreach($personNameStrings as $personNameString){ //for each person
			$firstnames = array();
			$prefixes = array();
			$lastnames = array();

			if(!strstr($personNameString, '{')){ //lastname not labeled by { ... }
				$nameParts = explode(' ', trim($personNameString));

				$numberOfNameParts = count($nameParts);

				$lastnames[] = trim($nameParts[$numberOfNameParts - 1]);

				$prefixFound = false;

				for($i=0; $i < ($numberOfNameParts - 1); $i++){
					$namePart = trim($nameParts[$i]);

					if(!$prefixFound && ctype_upper(substr($namePart, 0, 1))){ //first letter upper case -> firstname?
						$firstnames[] = trim($namePart);
					} else { //lower case -> prefix
						$prefixes[] = trim($namePart);
						$prefixFound = true; //mark, that no firstnames can be following
					}
				}
			}
			//lastname labeled by { ... }
			else {
				$personNameString = str_replace('}', ' ', trim($personNameString));
				$namePartsLarge = explode('{', $personNameString);

				if(count($namePartsLarge == 2)){ //correct namestring?
					$namePartsFirstnamesAndPrefixesString = $namePartsLarge[0];
					$namePartsLastnamesString = $namePartsLarge[1];

					$namePartsFirstnamesAndPrefixes = explode(' ', $namePartsFirstnamesAndPrefixesString);
					$namePartsLastnames = explode(' ', $namePartsLastnamesString);

					foreach($namePartsLastnames as $lastname){
						$lastnames[] = trim($lastname);
					}

					$prefixFound = false;

					foreach($namePartsFirstnamesAndPrefixes as $namePart){
						if(!$prefixFound && ctype_upper(substr($namePart, 0, 1))){ //first letter upper case -> firstname?
							$firstnames[] = trim($namePart);
						} else { //lower case -> prefix
							$prefixes[] = trim($namePart);
							$prefixFound = true; //mark, that no firstnames can be following
						}
					}
				}
			}

			$person = array();
			$person['firstname'] = trim(implode(' ', $firstnames));
			$person['prefix'] = trim(implode(' ', $prefixes));
			$person['lastname'] = trim(implode(' ', $lastnames));
			$person['suffix'] = '';
			$person['user_id'] = $userId;

			if($person['lastname'] != ''){
				$persons[] = $person;
			}
		}

		return $persons;
	}

	static function parseTagString($tagString, $userId){
		$tagString = str_replace(',', ' ', $tagString);
		$tagNamesDirty = explode(' ', $tagString);

		$tagsClean = array();
		foreach($tagNamesDirty as $tagNameDirty){
			$tagNameClean = trim(strtolower(preg_replace("/[^\p{L}\p{N}]/u", '', $tagNameDirty)));

			if(strlen($tagNameClean) > 0){
				$tag = array();
				$tag['name'] = $tagNameClean;
				$tag['user_id'] = $userId;
				$tagsClean[] = $tag;
			}
		}

		return $tagsClean;
	}


	static function protectXSS($value){
		return htmlspecialchars($value, ENT_NOQUOTES, "UTF-8");
	}

	static function getNotificationBoxText(){
		$notificationTextsEscaped = array();

		if(isset(LibGlobal::$notificationTexts) && is_array(LibGlobal::$notificationTexts)){
			foreach(LibGlobal::$notificationTexts as $text){
				$notificationTextsEscaped[] = LibString::protectXSS($text);
			}
		}

		if(count($notificationTextsEscaped) > 0){
			return '<p class="notificationBox">'. implode('<br />', $notificationTextsEscaped). '</p>';
		}
	}

	static function getErrorBoxText(){
		$errorTextsEscaped = array();

		if(isset(LibGlobal::$errorTexts) && is_array(LibGlobal::$errorTexts)){
			foreach(LibGlobal::$errorTexts as $text){
				$errorTextsEscaped[] = LibString::protectXSS($text);
			}
		}

		if(count($errorTextsEscaped) > 0){
			return '<p class="errorBox">'. implode('<br />', $errorTextsEscaped). '</p>';
		}
	}

	static function getAlienStringClassText($isOwn){
		if(!$isOwn){
			return ' class="alien" ';
		}

		return '';
	}

	static function getAlienStringText($isOwn){
		if(!$isOwn){
			return ' alien ';
		}

		return '';
	}

	static function cleanBibtexString($string){
		return trim(str_replace('--', '-', $string));
	}
}
?>