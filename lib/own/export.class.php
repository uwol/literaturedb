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


class LibExport{
	/*
	* Word2007
	*/
	static function printWord2007All($sessionUser){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="Sources.xml"');
		header("Content-Transfer-Encoding: binary");

		echo '<?xml version="1.0"?>';
		echo '<b:Sources SelectedStyle="" xmlns:b="http://schemas.openxmlformats.org/officeDocument/2006/bibliography" xmlns="http://schemas.openxmlformats.org/officeDocument/2006/bibliography">';

		foreach(LibRouter::document_fetchAll(array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $document){
			self::printWord2007_Source($document);
		}

		echo '</b:Sources>';
	}

	static function printWord2007Single($sessionUser, $id){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="Sources.xml"');
		header("Content-Transfer-Encoding: binary");

		echo '<?xml version="1.0"?>';
		echo '<b:Sources SelectedStyle="" xmlns:b="http://schemas.openxmlformats.org/officeDocument/2006/bibliography" xmlns="http://schemas.openxmlformats.org/officeDocument/2006/bibliography">';

		$document = LibRouter::document_fetch($id, $sessionUser->getUserAddress());

		if(is_array($document)){
			self::printWord2007_Source($document);
		}

		echo '</b:Sources>';
	}

	static function printWord2007_Source($document){
		//position 1 should be JournalArticle, although mistakenly shown in German Word 2007 as "Zeitungsartikel"
		$sourceTypes = array(0 => 'Misc', 1 => 'JournalArticle', 2 => 'Book', 3 => 'Book', 4 => 'ConferenceProceedings', 5 => 'BookSection', 6 => 'Misc', 7 => 'ConferenceProceedings', 8 => 'Report', 9 => 'Misc', 10 => 'Misc', 11 => 'Misc', 12 => 'ConferenceProceedings', 13 => 'Report', 14 => 'Misc');

		echo '<b:Source>';

		echo '<b:Tag>' . LibDocument::getId_Word2007($document['id']) . '</b:Tag>';
		echo '<b:SourceType>' .$sourceTypes[$document['entrytype_id']]. '</b:SourceType>';
		//echo '<b:Guid>{' .$document['id']. '}</b:Guid>'; //E8706D6D-EE7A-48AF-AF62-7CFC26A06F62
		echo '<b:LCID>0</b:LCID>'; //language code, 0=Standard

		if($document['title'] != ''){
			echo '<b:Title>' .self::xmlEscape($document['title']). '</b:Title>';
		}

		if(substr($document['date'], 0, 4) > 0){
			echo '<b:Year>' .self::xmlEscape(substr($document['date'], 0, 4)). '</b:Year>';
		}

		if(substr($document['date'], 5, 2) > 0){
			echo '<b:Month>' .self::xmlEscape(substr($document['date'], 5, 2)). '</b:Month>';
		}

		if(substr($document['date'], 8, 2) > 0){
			echo '<b:Day>' .self::xmlEscape(substr($document['date'], 8, 2)). '</b:Day>';
		}

		/*
		* Authors and Editors
		*/
		echo '<b:Author>';

		echo '<b:Author><b:NameList>';

		foreach($document['authors'] as $author){
			self::printWord2007_Person($author);
		}

		echo '</b:NameList></b:Author>';

		echo '<b:Editor><b:NameList>';

		foreach($document['editors'] as $editor){
			self::printWord2007_Person($editor);
		}

		echo '</b:NameList></b:Editor>';

		echo '</b:Author>';

		if($document['address'] != ''){
			echo '<b:City>' .self::xmlEscape($document['address']). '</b:City>';
		}

		if($document['booktitle'] != ''){
			echo '<b:BookTitle>' .self::xmlEscape($document['booktitle']). '</b:BookTitle>';
			echo '<b:ConferenceName>' .self::xmlEscape($document['booktitle']). '</b:ConferenceName>';
		}

		//chapter is missing in sources.xml dtd
		if($document['edition'] != ''){
			echo '<b:Edition>' .self::xmlEscape($document['edition']). '.</b:Edition>'; //adding a dot behind the number!
		}

		$institutions = array();

		if($document['institution'] != ''){
			$institutions[] = $document['institution'];
		}

		if($document['organization'] != ''){
			$institutions[] = $document['organization'];
		}

		if($document['school'] != ''){
			$institutions[] = $document['school'];
		}

		if(count($institutions) > 0){
			echo '<b:Institution>' .self::xmlEscape(implode(', ', $institutions)). '</b:Institution>';
		}

		if($document['journal_name'] != ''){
			echo '<b:PeriodicalTitle>' .self::xmlEscape($document['journal_name']). '</b:PeriodicalTitle>';
			echo '<b:JournalName>' .self::xmlEscape($document['journal_name']). '</b:JournalName>';
		}

		if($document['number'] != ''){
			echo '<b:Issue>' .self::xmlEscape($document['number']). '</b:Issue>';
		}

		if($document['pages'] != ''){
			echo '<b:Pages>' .self::xmlEscape(str_replace('--', '-', $document['pages'])). '</b:Pages>';
		}

		if($document['publisher_name'] != ''){
			echo '<b:Publisher>' .self::xmlEscape($document['publisher_name']). '</b:Publisher>';
		}

		//series is missing in sources.xml dtd
		if($document['volume'] != ''){
			echo '<b:Volume>' .self::xmlEscape($document['volume']). '</b:Volume>';
		}

		if($document['entrytype_id'] == 9){
			echo '<b:Medium>Master Thesis</b:Medium> ';
		} elseif($document['entrytype_id'] == 11){
			echo '<b:Medium>PhD Thesis</b:Medium> ';
		}

		echo '</b:Source>';
	}

	static function printWord2007_Person($person){
		if($person['lastname'] != ''){
			$prefixString = '';
			if($person['prefix'] != ''){
				$prefixString = $person['prefix']. ' ';
			}

			$suffixString = '';
			if($person['suffix'] != ''){
				$suffixString = $person['suffix']. ' ';
			}

			$firstnames = explode(' ', $person['firstname']);

			echo '<b:Person>';
			echo '<b:Last>' .self::xmlEscape($prefixString . $person['lastname'] . $suffixString). '</b:Last>';

			if(isset($firstnames[0])){
				echo '<b:First>' .self::xmlEscape($firstnames[0]). '</b:First>';
			}

			if(count($firstnames) > 1){
				array_shift($firstnames);
				echo '<b:Middle>' .self::xmlEscape(implode(' ', $firstnames)). '</b:Middle>';
			}

			echo '</b:Person>';
		}
	}


	//----------------------------------------------------------------------------------------
	/*
	* Bibtex
	*/
	static function printBibtexAll($sessionUser){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature.bib"');
		header("Content-Transfer-Encoding: binary");

		foreach(LibRouter::document_fetchAll(array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $document)
			self::printBibtex_Entry($document);
	}

	static function printBibtexSingle($sessionUser, $id){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature_'.$id.'.bib"');
		header("Content-Transfer-Encoding: binary");

		$document = LibRouter::document_fetch($id, $sessionUser->getUserAddress());

		if(is_array($document)){
			self::printBibtex_Entry($document);
		}
	}

	static function printBibtex_Entry($document){
		$retstr = '';
		$br = "\r\n";

		$entryTypes = array(0 => 'Misc', 1 => 'Article', 2 => 'Book', 3 => 'Booklet', 4 => 'Conference', 5 => 'Inbook', 6 => 'Incollection', 7 => 'Inproceedings', 8 => 'Manual', 9 => 'Mastersthesis', 10 => 'Misc', 11 => 'Phdthesis', 12 => 'Proceedings', 13 => 'Techreport', 14 => 'Unpublished');

		$retstr .= '@' . $entryTypes[$document['entrytype_id']] . '{' . LibDocument::getId_Bibtex($document) . ','. $br . '  ';

		$dataEntries = array();

		/*
		* Authors
		*/
		if(count($document['authors']) > 0){
			$dataEntries[] = self::getBibtex_Persons($document['authors'], 0);
		}

		if(count($document['editors']) > 0){
			$dataEntries[] = self::getBibtex_Persons($document['editors'], 1);
		}

		$dataEntries[] = 'title        = ' .self::bibtexFormatAndEscape($document['title']);

		if(substr($document['date'], 0, 4) > 0){
			$dataEntries[] = 'year         = ' .substr($document['date'], 0, 4);
		}

		if(self::getMonth(substr($document['date'], 5, 2)) != ''){
			$dayString = '';

			if(substr($document['date'], 8, 2) > 0){
				$day = (int) substr($document['date'], 8, 2);
				$dayString = ' # "~' .$day. ',"';
			}

			$dataEntries[] = 'month        = ' .self::getMonth(substr($document['date'], 5, 2)) . $dayString;
		}

		if($document['address'] != ''){
			$dataEntries[] = 'address      = ' .self::bibtexFormatAndEscape($document['address']);
		}

		if($document['booktitle'] != ''){
			$dataEntries[] = 'booktitle    = ' .self::bibtexFormatAndEscape($document['booktitle']);
		}

		if($document['chapter'] != ''){
			$dataEntries[] = 'chapter      = ' .self::bibtexFormatAndEscape($document['chapter']);
		}

		if($document['edition'] != ''){
			$dataEntries[] = 'edition      = ' .self::bibtexFormatAndEscape($document['edition']);
		}

		if($document['ean'] != ''){
			$dataEntries[] = 'isbn         = ' .self::bibtexFormatAndEscape($document['ean']);
		}

		if($document['institution'] != ''){
			$dataEntries[] = 'institution  = ' .self::bibtexFormatAndEscape($document['institution']);
		}

		if($document['number'] != ''){
			$dataEntries[] = 'number       = ' .self::bibtexFormatAndEscape($document['number']);
		}

		if($document['note'] != ''){
			$dataEntries[] = 'annote       = ' .self::bibtexFormatAndEscape($document['note']);
		}

		if($document['organization'] != ''){
			$dataEntries[] = 'organization = ' .self::bibtexFormatAndEscape($document['organization']);
		}

		if($document['pages'] != ''){
			$dataEntries[] = 'pages        = ' .self::bibtexFormatAndEscape($document['pages']);
		}

		if($document['school'] != ''){
			$dataEntries[] = 'school       = ' .self::bibtexFormatAndEscape($document['school']);
		}

		if($document['series'] != ''){
			$dataEntries[] = 'series       = ' .self::bibtexFormatAndEscape($document['series']);
		}

		if($document['volume'] != ''){
			$dataEntries[] = 'volume       = ' .self::bibtexFormatAndEscape($document['volume']);
		}

		if($document['publisher_name'] != ''){
			$dataEntries[] = 'publisher    = ' .self::bibtexFormatAndEscape($document['publisher_name']);
		}

		if($document['journal_name'] != ''){
			$dataEntries[] = 'journal      = ' .self::bibtexFormatAndEscape($document['journal_name']);
		}

		if(count($document['tags'] > 0)){
			$tagNames = array();

			foreach($document['tags'] as $tag){
				$tagNames[] = $tag['name'];
			}

			$dataEntries[] = 'keywords     = ' .self::bibtexEscape(implode(' ', $tagNames));
		}

		if($document['url'] != ''){
			//$dataEntries[] = 'url          = ' .self::bibtexEscape($document['url']);
			$dataEntries[] = 'howpublished = ' .self::bibtexEscape('\url{'.$document['url'].'}');
		}

		//Output
		$retstr .= implode(','.$br.'  ' , $dataEntries);

		$retstr .= $br. '}'. $br. $br;
		echo $retstr;
	}

	static function getBibtex_Persons($persons, $type = 0){
		$personsStrings = array();

		foreach($persons as $person){
			$prefixString = '';
			if($person['prefix'] != ''){
				$prefixString = ' '. $person['prefix'];
			}

			$suffixString = '';
			if($person['suffix'] != ''){
				$suffixString = ' ' . $person['suffix'];
			}

			$personsStrings[] = $person['firstname'] .$prefixString. ' {' . $person['lastname'] . '}' . $suffixString;
		}

		if($type == 0){
			return 'author       = '. self::bibtexEscape(implode(' and ', $personsStrings));
		} else {
			return 'editor       = '. self::bibtexEscape(implode(' and ', $personsStrings));
		}
	}

	static function getMonth($month){
		$monthNames = array(1=>'jan', 2=>'feb', 3=>'mar', 4=>'apr', 5=>'may', 6=>'jun', 7=>'jul', 8=>'aug', 9=>'sep', 10=>'oct', 11=>'nov', 12=>'dec');
		$month = (int) $month;

		if(isset($monthNames[$month])){
			return $monthNames[$month];
		}
	}

	static function bibtexFormatAndEscape($string){
		$string = str_replace('-', '--', $string);
		return self::bibtexEscape($string);
	}

	static function bibtexEscape($string){
		$retstr = utf8_decode($string);
		$retstr = str_replace(chr(150), "-", $retstr);
		$retstr = str_replace(chr(151), "-", $retstr);
		$retstr = str_replace(array('&', '%', '#', '_', 'ß'), array('\&', '\%', '\#', '\_', '\ss{}'), $retstr); // $ should not be escaped, as else latex expressions would break
		$retstr = str_replace('"', '', $retstr);
		return '"'.$retstr.'"';
	}


	//----------------------------------------------------------------------------------------

	/*
	* Refer/Bibix for Endnote
	*/
	static function printBibixAll($sessionUser){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature.enw"');
		header("Content-Transfer-Encoding: binary");

		foreach(LibRouter::document_fetchAll(array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $document)
			self::printBibix_Entry($document);
	}

	static function printBibixSingle($sessionUser, $id){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature_'.$id.'.enw"');
		header("Content-Transfer-Encoding: binary");

		$document = LibRouter::document_fetch($id, $sessionUser->getUserAddress());
		if(is_array($document))
			self::printBibix_Entry($document);
	}

	static function printBibix_Entry($document){
		$br = "\r\n";

		$entryTypes = array(0 => 'Generic', 1 => 'Journal Article', 2 => 'Book', 3 => 'Book', 4 => 'Conference Proceedings', 5 => 'Book Section', 6 => 'Manuscript', 7 => 'Conference Paper', 8 => 'Report', 9 => 'Thesis', 10 => 'Generic', 11 => 'Thesis', 12 => 'Conference Proceedings', 13 => 'Report', 14 => 'Unpublished Work');

		$dataEntries = array();

		$dataEntries[] = '%0 ' . $entryTypes[$document['entrytype_id']];

		if($document['title'] != ''){
			$dataEntries[] = '%T ' .$document['title'];
		}

		foreach($document['authors'] as $author){
			$dataEntries[] = self::getBibix_Person($author, 0);
		}

		foreach($document['editors'] as $editor){
			$dataEntries[] = self::getBibix_Person($editor, 1);
		}

		if($document['date'] > 0){
			$dataEntries[] = '%D ' .substr($document['date'], 0, 4);
		}

		if($document['address'] != ''){
			$dataEntries[] = '%C ' .$document['address'];
		}

		if($document['booktitle'] != ''){
			$dataEntries[] = '%B ' .$document['booktitle'];
		}

		if($document['chapter'] != ''){
			$dataEntries[] = '	%& ' .$document['chapter'];
		}

		if($document['doi'] != ''){
			$dataEntries[] = '%R ' .$document['doi'];
		}

		if($document['edition'] != ''){
			$dataEntries[] = '%7 ' .$document['edition'];
		}

		if($document['ean'] != ''){
			$dataEntries[] = '%@ ' .$document['ean']; //hier evtl. noch Check auf ISBN-EAN einbauen !!!
		}

		if($document['number'] != ''){
			$dataEntries[] = '%N ' .$document['number'];
		}

		if($document['pages'] != ''){
			$dataEntries[] = '%P ' .str_replace('--', '-', $document['pages']);
		}

		if($document['url'] != ''){
			$dataEntries[] = '%U ' .$document['url'];
		}

		if($document['volume'] != ''){
			$dataEntries[] = '%V ' .$document['volume'];
		}

		if($document['publisher_name'] != ''){
			$dataEntries[] = '%I ' .$document['publisher_name'];
		}

		if($document['journal_name'] != ''){
			$dataEntries[] = '%J ' .$document['journal_name'];
		}

		if($document['school'] != ''){
			$dataEntries[] = '%1 ' .$document['school'];
		} elseif($document['organization'] != ''){
			$dataEntries[] = '%1 ' .$document['organization'];
		} elseif($document['institution'] != ''){
			$dataEntries[] = '%1 ' .$document['institution'];
		}

		if($document['series'] != ''){
			$dataEntries[] = '%S ' .$document['series'];
		}

		if(count($document['tags'] > 0)){
			$tagNames = array();

			foreach($document['tags'] as $tag){
				$tagNames[] = $tag['name'];
			}

			$dataEntries[] = '%K ' .implode(' ', $tagNames);
		}

		//Output
		echo self::bibixEncode(implode($br , $dataEntries));

		echo $br.$br;
	}

	static function getBibix_Person($person, $mode = 0){
		$prefixString = '';
		if($person['prefix'] != ''){
			$prefixString = ' '. $person['prefix'];
		}

		$suffixString = '';
		if($person['suffix'] != ''){
			$suffixString = ' ' . $person['suffix'];
		}

		if($mode == 0){
			return '%A '. $person['lastname'] . $suffixString .', '. $person['firstname'] .$prefixString;
		} else {
			return '%E '. $person['lastname'] . $suffixString .', '. $person['firstname'] .$prefixString;
		}
	}

	static function bibixEncode($string){
		return utf8_decode($string);
	}

	//----------------------------------------------------------------------------------------
	/*
	* RIS
	*/
	static function printRisAll($sessionUser){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature.ris"');
		header("Content-Transfer-Encoding: binary");

		foreach(LibRouter::document_fetchAll(array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $document){
			self::printRis_Entry($document);
		}
	}

	static function printRisSingle($sessionUser, $id){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature_'.$id.'.ris"');
		header("Content-Transfer-Encoding: binary");

		$document = LibRouter::document_fetch($id, $sessionUser->getUserAddress());

		if(is_array($document)){
			self::printRis_Entry($document);
		}
	}

	static function printRis_Entry($document){
		$br = "\r\n";

		$entryTypes = array(0 => 'GEN', 1 => 'JOUR', 2 => 'BOOK', 3 => 'BOOK', 4 => 'CONF', 5 => 'CHAP', 6 => 'CHAP', 7 => 'CONF', 8 => 'RPRT', 9 => 'THES', 10 => 'GEN', 11 => 'THES', 12 => 'CONF', 13 => 'RPRT', 14 => 'UNPB');

		$dataEntries = array();

		$dataEntries[] = 'TY  - ' . $entryTypes[$document['entrytype_id']];

		$dataEntries[] = 'ID  - ' .$document['id'];

		if($document['title'] != ''){
			$dataEntries[] = 'T1  - ' .$document['title'];
		}

		if($document['booktitle'] != ''){
			$dataEntries[] = 'BT  - ' .$document['booktitle'];
		}

		foreach($document['authors'] as $author){
			$prefixString = '';
			if($author['prefix'] != ''){
				$prefixString = ' '. $author['prefix'];
			}

			$suffixString = '';
			if($author['suffix'] != ''){
				$suffixString = ',' . $author['suffix'];
			}

			$dataEntries[] = 'A1  - '. self::risClean($author['lastname'] .','. $author['firstname'] .$prefixString.$suffixString);
		}

		foreach($document['editors'] as $editor){
			$prefixString = '';
			if($editor['prefix'] != ''){
				$prefixString = ' '. $editor['prefix'];
			}

			$suffixString = '';
			if($editor['suffix'] != ''){
				$suffixString = ',' . $editor['suffix'];
			}

			$dataEntries[] = 'A2  - '. self::risClean($editor['lastname'] .','. $editor['firstname'] .$prefixString.$suffixString);
		}

		if($document['date'] > 0){
			$year = '';

			if(substr($document['date'], 0, 4) > 0){
				$year = substr($document['date'], 0, 4);
			}

			$month = '';
			if(substr($document['date'], 5, 2) > 0){
				$month = substr($document['date'], 5, 2);
			}

			$day = '';
			if(substr($document['date'], 8, 2) > 0){
				$day = substr($document['date'], 8, 2);
			}

			$dataEntries[] = 'PY  - ' .$year.'/'.$month.'/'.$day.'/';
		}

		if(count($document['tags'] > 0)){
			foreach($document['tags'] as $tag){
				$dataEntries[] = 'KW  - ' .self::risClean($tag['name']);
			}
		}

		if($document['journal_name'] != ''){
			$dataEntries[] = 'JF  - ' .self::risClean($document['journal_name']);
		}

		if($document['volume'] != ''){
			$dataEntries[] = 'VL  - ' .$document['volume'];
		}

		if($document['number'] != ''){
			$dataEntries[] = 'IS  - ' .$document['number'];
		} elseif($document['edition'] != ''){
			$dataEntries[] = 'IS  - ' .$document['edition'];
		}

		if($document['pages'] != ''){
			$pages = str_replace('--','-',$document['pages']);
			$pages_array = explode('-', $pages);

			if(isset($pages_array[0])){
				$dataEntries[] = 'SP  - ' .$pages_array[0];
			}

			if(isset($pages_array[1])){
				$dataEntries[] = 'EP  - ' .$pages_array[1];
			}
		}

		if($document['publisher_name'] != ''){
			$dataEntries[] = 'PB  - ' .$document['publisher_name'];
		} elseif($document['school'] != ''){
			$dataEntries[] = 'PB  - ' .$document['school'];
		} elseif($document['organization'] != ''){
			$dataEntries[] = 'PB  - ' .$document['organization'];
		} elseif($document['institution'] != ''){
			$dataEntries[] = 'PB  - ' .$document['institution'];
		}

		if($document['ean'] != ''){
			$dataEntries[] = 'SN  - ' .$document['ean']; //hier evtl. noch Check auf ISBN-EAN einbauen !!!
		}

		if($document['address'] != ''){
			$dataEntries[] = 'CY  - ' .$document['address'];
		}

		if($document['url'] != ''){
			$dataEntries[] = 'UR  - ' .$document['url'];
		}

		if($document['series'] != ''){
			$dataEntries[] = 'T3  - ' .$document['series'];
		}

		if($document['note'] != ''){
			$dataEntries[] = 'N1  - ' .str_replace("\r", " ", str_replace("\n", " ", $document['note']));
		}

		if($document['abstract'] != ''){
			$dataEntries[] = 'N2  - ' .str_replace("\r", " ", str_replace("\n", " ", $document['abstract']));
		}

		$dataEntries[] = 'ER  - ';

		//Output
		echo self::risEncode(implode($br , $dataEntries));

		echo $br;
	}

	static function risEncode($string){
		return utf8_decode($string);
	}

	static function risClean($string){
		//asterisk (character 42) is not allowed in the author, keywords or periodical name fields
		return str_replace('#','', $string);
	}


	//----------------------------------------------------------------------------------------

	/*
	* MODS XML
	*/
	static function printModsXmlAll($sessionUser){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature.xml"');
		header("Content-Transfer-Encoding: binary");

		echo '<?xml version="1.0"?>';
		echo '<modsCollection xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.loc.gov/mods/v3" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-3.xsd">';

		foreach(LibRouter::document_fetchAll(array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $document){
			self::printModsXml_Source($document);
		}

		echo '</modsCollection>';
	}

	static function printModsXmlSingle($sessionUser, $id){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="literature_'.$id.'.xml"');
		header("Content-Transfer-Encoding: binary");

		echo '<?xml version="1.0"?>';
		echo '<modsCollection xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-2.xsd" xmlns="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';

		$document = LibRouter::document_fetch($id, $sessionUser->getUserAddress());

		if(is_array($document)){
			self::printModsXml_Source($document);
		}

		echo '</modsCollection>';
	}

	static function printModsXml_Source($document){
		$isPartOfRelatedItem = false;
		if($document['entrytype_id'] == 1 || $document['entrytype_id'] == 5 ||
				$document['entrytype_id'] == 6 || $document['entrytype_id'] == 7)
			$isPartOfRelatedItem = true;

		echo '<mods>';

		if($document['title'] != ''){
			echo '<titleInfo><title>' .self::xmlEscape($document['title']). '</title></titleInfo>';
		}

		echo '<typeOfResource>text</typeOfResource>';

		switch($document['entrytype_id']){
			case 1:
				echo '<genre authority="local">journalArticle</genre>';
				echo '<genre authority="marcgt">periodical</genre>';
				break;
			case 2:
			case 3:
			case 4:
			case 6:
			case 8:
			case 10:
			case 12:
				echo '<genre authority="local">book</genre>';
				echo '<genre authority="marcgt">book</genre>';
				break;
			case 5:
				echo '<genre authority="local">bookSection</genre>';
				echo '<genre authority="marcgt">book</genre>';
				break;
			case 7:
				echo '<genre authority="local">conferencePaper</genre>';
				break;
			case 9:
			case 11:
				echo '<genre authority="local">thesis</genre>';
				echo '<genre authority="marcgt">theses</genre>';
    			break;
			case 13:
				echo '<genre authority="local">report</genre>';
				break;
			case 14:
				echo '<genre authority="local">manuscript</genre>';
				break;
		}

		/*
		* Authors and Editors
		*/
		foreach($document['authors'] as $author){
			self::printModsXml_Person($author, 0);
		}

		if(!$isPartOfRelatedItem){
			foreach($document['editors'] as $editor){
				self::printModsXml_Person($editor, 1);
			}

			if($document['entrytype_id'] == 4 || $document['entrytype_id'] == 7){
				echo '<name type="conference">'.$document['booktitle'].'<namePart></namePart><role><roleTerm type="text">creator</roleTerm></role></name>';
			}
		}


		echo '<originInfo>';

		if($document['institution'] != ''){
			echo '<name type="corporate"><namePart>'.self::xmlEscape($document['institution']).'</namePart></name>';
		}

		if($document['school'] != ''){
			echo '<name type="corporate"><namePart>'.self::xmlEscape($document['school']).'</namePart></name>';
		}

		if($document['organization'] != ''){
			echo '<name type="corporate"><namePart>'.self::xmlEscape($document['organization']).'</namePart></name>';
		}

		if($document['address'] != ''){
			echo '<place><placeTerm type="text">' .self::xmlEscape($document['address']). '</placeTerm></place>';
		}

		if($document['edition'] != ''){
			echo '<edition>' .self::xmlEscape($document['edition']). '</edition>';
		}

		if(!$isPartOfRelatedItem){
			if($document['publisher_name'] != ''){
				echo '<publisher>' .self::xmlEscape($document['publisher_name']). '</publisher>';
			}

			if($document['date'] > 0){
				echo '<dateIssued encoding="w3cdtf" keyDate="yes">' .$document['date']. '</dateIssued>';
			}
		}

		echo '</originInfo>';

		if($isPartOfRelatedItem){
			echo '<relatedItem type="host">';

			echo '<titleInfo>';

			if($document['journal_name'] != ''){
				echo '<title>'.self::xmlEscape($document['journal_name']).'</title>';
			}

			if($document['booktitle'] != ''){
				echo '<title>'.self::xmlEscape($document['booktitle']).'</title>';
			}

			echo '</titleInfo>';

			foreach($document['editors'] as $editor){
				self::printModsXml_Person($editor, 1);
			}

			if($document['entrytype_id'] == 4 || $document['entrytype_id'] == 7){
				echo '<name type="conference">'.$document['booktitle'].'<namePart></namePart><role><roleTerm type="text">creator</roleTerm></role></name>';
			}

			echo '<originInfo>';

			if($document['entrytype_id'] == 1){
				echo '<issuance>continuing</issuance>';
			}

			if($document['date'] > 0){
				echo '<dateIssued encoding="w3cdtf" keyDate="yes">' .$document['date']. '</dateIssued>';
			}

			if($document['publisher_name'] != ''){
				echo '<publisher>' .self::xmlEscape($document['publisher_name']). '</publisher>';
			}

			echo '</originInfo>';

			echo '<part>';

			if($document['volume'] != ''){
				echo '<detail type="volume"><number>'.self::xmlEscape($document['volume']).'</number></detail>';
			}

			if($document['number'] != ''){
				echo '<detail type="issue"><number>'.self::xmlEscape($document['number']).'</number></detail>';
			}

			if($document['pages'] != ''){
				echo '<extent unit="pages">';
				$pages = str_replace('--','-',$document['pages']);
				$pages_array = explode('-', $pages);

				if(isset($pages_array[0])){
					echo '<start>' .self::xmlEscape($pages_array[0]). '</start>';
				}

				if(isset($pages_array[1])){
					echo '<end>' .self::xmlEscape($pages_array[1]). '</end>';
				}

				echo '</extent>';
			}

			echo '</part>';

			echo '</relatedItem>';
		}

		if(count($document['tags'] > 0)){
			foreach($document['tags'] as $tag){
				echo '<subject><topic>'.self::xmlEscape($tag['name']). '</topic></subject>';
			}
		}

		if($document['series'] != ''){
			echo '<relatedItem type="series"><titleInfo><title>' .self::xmlEscape($document['series']). '</title></titleInfo></relatedItem>';
		}

		if($document['note'] != ''){
			echo '<note type="content">' .self::xmlEscape($document['note']). '</note>';
		}

		if($document['abstract'] != ''){
			echo '<abstract>' .self::xmlEscape($document['abstract']). '</abstract>';
		}

		if($document['doi'] != ''){
			echo '<identifier type="doi">'.self::xmlEscape($document['doi']).'</identifier>';
		}

		if($document['ean'] != ''){
			echo '<identifier type="isbn">'.self::xmlEscape($document['ean']).'</identifier>';
		}

		if($document['url'] != ''){
			echo '<location><url>'.self::xmlEscape($document['url']).'</url></location>';
		}

		echo '<identifier>'.LibDocument::getId_ModsXml($document['id']).'</identifier>';

		echo '</mods>';
	}


	//type: 0=author, 1=editor
	static function printModsXml_Person($person, $role = 0){
		echo '<name type="personal">';

		if($person['lastname'] != ''){
			$prefixString = '';
			if($person['prefix'] != ''){
				$prefixString = $person['prefix']. ' ';
			}

			$suffixString = '';
			if($person['suffix'] != ''){
				$suffixString = $person['suffix']. ' ';
			}

			echo '<namePart type="family">' .self::xmlEscape($prefixString . $person['lastname'] . $suffixString). '</namePart>';
		}

		if($person['firstname'] != ''){
			echo '<namePart type="given">' .$person['firstname']. '</namePart>';
		}

		if($role == 0){
			echo '<role><roleTerm type="text">author</roleTerm></role>';
		} elseif($role == 1){
			echo '<role><roleTerm type="text">editor</roleTerm></role>';
		}

		echo '</name>';
	}

	//----------------------------------------------------------------------------------------
	static function xmlEscape($string){
		if(strpos($string, '<') !== false || strpos($string, '>') !== false || strpos($string, '&') !== false){
			return '<![CDATA[' .$string. ']]>';
		}

		return $string;
	}
}
?>