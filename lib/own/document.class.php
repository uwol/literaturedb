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

class LibDocument{
	private static $fields = array('id', 'hash', 'entrytype_id', 'title', 'date', 'abstract', 'address', 'booktitle', 'chapter', 'doi', 'ean', 'edition', 'institution', 'journal_id', 'number', 'organization', 'pages', 'publisher_id', 'school', 'series', 'url', 'volume', 'note', 'rating', 'filename', 'extension', 'filesize', 'datetime_created', 'user_id');
	
	static function fetchAll($userId){
		$stmt = LibDb::prepare('SELECT id FROM literaturedb_document WHERE user_id = :user_id ORDER BY id');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}		
		return $documents;
	}
	
	static function fetchLast($userId, $offset = 0, $limit = 3){
		$internalLimit = 3;
		if(is_numeric($limit) && $limit > 0)
			$internalLimit = (int) $limit;

		$internalOffset = 0;
		if(is_numeric($offset) && $offset >= 0)
			$internalOffset = (int) $offset;
		
		$stmt = LibDb::prepare('SELECT id FROM literaturedb_document WHERE user_id = :user_id ORDER BY datetime_created DESC LIMIT :offset,:limit');
		$stmt->bindParam(':user_id', $userId);
		$stmt->bindParam(':offset', $internalOffset, PDO::PARAM_INT);
		$stmt->bindParam(':limit', $internalLimit, PDO::PARAM_INT);		
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}		
		return $documents;
	}
	
	static function fetchWithoutTag($userId){
		$stmt = LibDb::prepare('SELECT id FROM literaturedb_document WHERE user_id = :user_id AND literaturedb_document.id NOT IN (SELECT literaturedb_asso_document_tag.document_id FROM literaturedb_asso_document_tag)');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}
		return $documents;
	}
	
	static function fetchWithTag($tag, $userId){
		$stmt = LibDb::prepare('SELECT literaturedb_document.id FROM literaturedb_document, literaturedb_tag, literaturedb_asso_document_tag WHERE literaturedb_document.user_id = :user_id AND literaturedb_asso_document_tag.document_id = literaturedb_document.id AND literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.name = :tag ORDER BY literaturedb_document.date DESC');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':tag', $tag);
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}
		return $documents;
	}
	
	static function fetchWithSearch($searchString, $userId){
		$likeSearchString = '%'.$searchString.'%';
	
		$stmt = LibDb::prepare('SELECT literaturedb_document.id FROM literaturedb_document 
			WHERE literaturedb_document.user_id = :user_id AND 
			(literaturedb_document.id = :id 
			OR literaturedb_document.title LIKE :title OR literaturedb_document.date LIKE :date 
			OR literaturedb_document.abstract LIKE :abstract OR literaturedb_document.address LIKE :address 
			OR literaturedb_document.booktitle LIKE :booktitle OR literaturedb_document.chapter LIKE :chapter 
			OR literaturedb_document.doi LIKE :doi OR literaturedb_document.ean LIKE :ean 
			OR literaturedb_document.edition LIKE :edition OR literaturedb_document.institution LIKE :institution 
			OR literaturedb_document.number LIKE :number OR literaturedb_document.organization LIKE :organization 
			OR literaturedb_document.pages LIKE :pages OR literaturedb_document.school LIKE :school 
			OR literaturedb_document.series LIKE :series OR literaturedb_document.url LIKE :url 
			OR literaturedb_document.volume LIKE :volume OR literaturedb_document.note LIKE :note 
			OR literaturedb_document.filename LIKE :filename OR literaturedb_document.extension LIKE :extension 
			OR literaturedb_document.datetime_created LIKE :datetime_created 
			OR literaturedb_document.id IN (
				SELECT literaturedb_asso_document_author.document_id FROM literaturedb_asso_document_author, literaturedb_person 
				WHERE literaturedb_asso_document_author.person_id = literaturedb_person.id 
				AND (literaturedb_person.firstname LIKE :author_firstname OR literaturedb_person.lastname LIKE :author_lastname)) 
			OR literaturedb_document.id IN (
				SELECT literaturedb_asso_document_editor.document_id FROM literaturedb_asso_document_editor, literaturedb_person 
				WHERE literaturedb_asso_document_editor.person_id = literaturedb_person.id 
				AND (literaturedb_person.firstname LIKE :editor_firstname OR literaturedb_person.lastname LIKE :editor_lastname)) 
			OR literaturedb_document.id IN (SELECT literaturedb_asso_document_tag.document_id FROM literaturedb_asso_document_tag, literaturedb_tag 
				WHERE literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.name LIKE :tag) 
			OR literaturedb_document.journal_id IN (
				SELECT literaturedb_journal.id FROM literaturedb_journal WHERE literaturedb_journal.name LIKE :journal_name) 
			OR literaturedb_document.publisher_id IN (
				SELECT literaturedb_publisher.id FROM literaturedb_publisher WHERE literaturedb_publisher.name LIKE :publisher_name)) 
			ORDER BY literaturedb_document.date DESC');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':id', $searchString, PDO::PARAM_INT);
		$stmt->bindParam(':title', $likeSearchString);
		$stmt->bindParam(':date', $likeSearchString);
		$stmt->bindParam(':abstract', $likeSearchString);
		$stmt->bindParam(':address', $likeSearchString);
		$stmt->bindParam(':booktitle', $likeSearchString);
		$stmt->bindParam(':chapter', $likeSearchString);
		$stmt->bindParam(':doi', $likeSearchString);
		$stmt->bindParam(':ean', $likeSearchString);
		$stmt->bindParam(':edition', $likeSearchString);
		$stmt->bindParam(':institution', $likeSearchString);
		$stmt->bindParam(':number', $likeSearchString);
		$stmt->bindParam(':organization', $likeSearchString);
		$stmt->bindParam(':pages', $likeSearchString);
		$stmt->bindParam(':school', $likeSearchString);
		$stmt->bindParam(':series', $likeSearchString);
		$stmt->bindParam(':url', $likeSearchString);
		$stmt->bindParam(':volume', $likeSearchString);
		$stmt->bindParam(':note', $likeSearchString);
		$stmt->bindParam(':filename', $likeSearchString);
		$stmt->bindParam(':extension', $likeSearchString);
		$stmt->bindParam(':datetime_created', $likeSearchString);
		$stmt->bindParam(':author_firstname', $likeSearchString);
		$stmt->bindParam(':author_lastname', $likeSearchString);
		$stmt->bindParam(':editor_firstname', $likeSearchString);
		$stmt->bindParam(':editor_lastname', $likeSearchString);
		$stmt->bindParam(':tag', $likeSearchString);
		$stmt->bindParam(':journal_name', $likeSearchString);
		$stmt->bindParam(':publisher_name', $likeSearchString);
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}
		return $documents;
	}
	
	static function fetchWithAuthor($authorId){
		$stmt = LibDb::prepare('SELECT literaturedb_document.id FROM literaturedb_document, literaturedb_asso_document_author WHERE literaturedb_document.id = literaturedb_asso_document_author.document_id AND literaturedb_asso_document_author.person_id = :id ORDER BY date DESC');
		$stmt->bindParam(':id', $authorId, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('id', $rowId);
		
		$documents = array();
		while($stmt->fetch()){
			$documents[] = self::fetch($rowId);
		}
		return $documents;
	}
	
	static function fetch($id){
		$stmt = LibDb::prepare('SELECT literaturedb_document.id, literaturedb_document.hash, literaturedb_document.entrytype_id, literaturedb_document.title, literaturedb_document.date, literaturedb_document.abstract, literaturedb_document.address, literaturedb_document.booktitle, literaturedb_document.chapter, literaturedb_document.doi, literaturedb_document.ean, literaturedb_document.edition, literaturedb_document.institution, literaturedb_document.journal_id, literaturedb_document.number, literaturedb_document.organization, literaturedb_document.pages, literaturedb_document.publisher_id, literaturedb_document.school, literaturedb_document.series, literaturedb_document.url, literaturedb_document.volume, literaturedb_document.note, literaturedb_document.rating, literaturedb_document.filename, literaturedb_document.extension, literaturedb_document.filesize, literaturedb_document.datetime_created, literaturedb_document.user_id, literaturedb_publisher.name AS publisher_name, literaturedb_journal.name AS journal_name FROM literaturedb_document LEFT JOIN literaturedb_publisher ON literaturedb_publisher.id = literaturedb_document.publisher_id LEFT JOIN literaturedb_journal ON literaturedb_journal.id = literaturedb_document.journal_id WHERE literaturedb_document.id = :id');
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return self::buildDocumentArray($row);
	}
	
	static function fetchByHash($hash, $userId){
		if($hash == '')
			return;
	
		$stmt = LibDb::prepare('SELECT literaturedb_document.id, literaturedb_document.hash, literaturedb_document.entrytype_id, literaturedb_document.title, literaturedb_document.date, literaturedb_document.abstract, literaturedb_document.address, literaturedb_document.booktitle, literaturedb_document.chapter, literaturedb_document.doi, literaturedb_document.ean, literaturedb_document.edition, literaturedb_document.institution, literaturedb_document.journal_id, literaturedb_document.number, literaturedb_document.organization, literaturedb_document.pages, literaturedb_document.publisher_id, literaturedb_document.school, literaturedb_document.series, literaturedb_document.url, literaturedb_document.volume, literaturedb_document.note, literaturedb_document.rating, literaturedb_document.filename, literaturedb_document.extension, literaturedb_document.filesize, literaturedb_document.datetime_created, literaturedb_document.user_id, literaturedb_publisher.name AS publisher_name, literaturedb_journal.name AS journal_name FROM literaturedb_document LEFT JOIN literaturedb_publisher ON literaturedb_publisher.id = literaturedb_document.publisher_id LEFT JOIN literaturedb_journal ON literaturedb_journal.id = literaturedb_document.journal_id WHERE literaturedb_document.hash = :hash AND literaturedb_document.user_id = :user_id');
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':hash', $hash);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return self::buildDocumentArray($row);
	}
	
	static function buildDocumentArray($row){
		$document = '';
	
		if(isset($row['id']) && $row['id'] != ''){
			$document = array();
		
			// copy
			$document['id'] = $row['id'];
			$document['document_address'] = self::buildCanonicalDocumentAddress($row['id']);
			$document['hash'] = $row['hash'];
			$document['entrytype_id'] = $row['entrytype_id'];
			$document['title'] = $row['title'];		
			$document['date'] = $row['date'];
			$document['abstract'] = $row['abstract'];

			$document['address'] = $row['address'];
			$document['booktitle'] = $row['booktitle'];
			$document['chapter'] = $row['chapter'];
			$document['doi'] = $row['doi'];
			$document['ean'] = $row['ean'];
		
			$document['edition'] = '';
			if($row['edition'] > 0)
				$document['edition'] = $row['edition'];	
		
			$document['institution'] = $row['institution'];
			$document['journal_id'] = $row['journal_id'];
			$document['number'] = $row['number'];
			$document['organization'] = $row['organization'];
			$document['pages'] = $row['pages'];
			$document['publisher_id'] = $row['publisher_id'];
			$document['school'] = $row['school'];
			$document['series'] = $row['series'];
			$document['url'] = $row['url'];
			$document['volume'] = $row['volume'];
			$document['note'] = $row['note'];
			$document['rating'] = $row['rating'];
			$document['filename'] = $row['filename'];
			$document['extension'] = $row['extension'];
			$document['filesize'] = $row['filesize'];
			$document['datetime_created'] = $row['datetime_created'];
		
			$entryTypes = self::fetchAllEntryTypes();
			$document['entrytype_name'] = '';
			if(isset($row['entrytype_id']) && isset($entryTypes[$row['entrytype_id']]))
				$document['entrytype_name'] = $entryTypes[$row['entrytype_id']];
			$document['publisher_name'] = $row['publisher_name'];
			$document['journal_name'] = $row['journal_name'];
		
			$document['user_id'] = $row['user_id'];

			/*
			* n:n Relationships
			*/
			$document['authors'] = LibPerson::fetchAllAuthorsForDocument($row['id']);
			$document['editors'] = LibPerson::fetchAllEditorsForDocument($row['id']);
			$document['tags'] = LibTag::fetchAllForDocument($row['id']);
		}
		
		return $document;
	}
	
	static function save($document){
		foreach(self::$fields as $field)
			if(!isset($document[$field]))
				$document[$field] = '';

		$document['rating'] = min(5, $document['rating']);
		
		/*
		* Journal
		*/
		$journalName = '';
		if(isset($document['journal_name'])){
			$journalName = trim($document['journal_name']);
		}

		if($journalName != ''){
			$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_journal (name, user_id) VALUES (:name, :user_id)');
			$stmt->bindParam(':name', $journalName);
			$stmt->bindParam(':user_id', $document['user_id'], PDO::PARAM_INT);
			$stmt->execute();
			
			$stmt = LibDb::prepare('SELECT id FROM literaturedb_journal WHERE name = :name AND user_id = :user_id');
			$stmt->bindParam(':name', $journalName);
			$stmt->bindParam(':user_id', $document['user_id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('id', $journalId);
			$stmt->fetch();
			
			$document['journal_id'] = $journalId;
		}

		/*
		* Publisher
		*/
		$publisherName = '';
		if(isset($document['publisher_name'])){
			$publisherName = trim($document['publisher_name']);
		}

		if($publisherName != ''){
			$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_publisher (name, user_id) VALUES (:name, :user_id)');
			$stmt->bindParam(':name', $publisherName);
			$stmt->bindParam(':user_id', $document['user_id'], PDO::PARAM_INT);
			$stmt->execute();
	
			$stmt = LibDb::prepare('SELECT id FROM literaturedb_publisher WHERE name = :name AND user_id = :user_id');
			$stmt->bindParam(':name', $publisherName);
			$stmt->bindParam(':user_id', $document['user_id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('id', $publisherId);
			$stmt->fetch();

			$document['publisher_id'] = $publisherId;
		}

		/*
		* Document
		*/
		$cleanEntryTypeId = LibDb::zerofy(trim($document['entrytype_id']));
		$cleanTitle = LibString::cleanBibtexString($document['title']);
		$cleanDate = trim($document['date']);
		$cleanAbstract = LibString::cleanBibtexString($document['abstract']);
		$cleanAddress = LibString::cleanBibtexString($document['address']);
		$cleanBooktitle = LibString::cleanBibtexString($document['booktitle']);
		$cleanChapter = LibString::cleanBibtexString($document['chapter']);
		$cleanDoi = LibString::cleanBibtexString($document['doi']);
		$cleanEan = LibString::cleanBibtexString($document['ean']);
		$cleanEdition = LibDb::zerofy(LibString::cleanBibtexString($document['edition']));
		$cleanInstitution = LibString::cleanBibtexString($document['institution']);
		$cleanJournalId = LibDb::zerofy($document['journal_id']);
		$cleanNumber = LibString::cleanBibtexString($document['number']);
		$cleanOrganization = LibString::cleanBibtexString($document['organization']);
		$cleanPages = LibString::cleanBibtexString($document['pages']);
		$cleanPublisherId = LibDb::zerofy($document['publisher_id']);
		$cleanSchool = LibString::cleanBibtexString($document['school']);
		$cleanSeries = LibString::cleanBibtexString($document['series']);
		$cleanUrl = trim($document['url']);
		$cleanVolume = LibString::cleanBibtexString($document['volume']);
		$cleanNote = LibString::cleanBibtexString($document['note']);
		$cleanRating = LibDb::zerofy(trim($document['rating']));

		$stmt = LibDb::prepare('SELECT COUNT(*) AS number FROM literaturedb_document WHERE id = :id');
		$stmt->bindParam(':id', $document['id'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $count);
		$stmt->fetch();

		if($count > 0){
			// filesize, filename, extension, datetime_created, user_id may not be updated here!
			$stmt = LibDb::prepare('UPDATE literaturedb_document SET entrytype_id = :entrytype_id, 
				title = :title, date = :date, abstract = :abstract, address = :address, 
				booktitle = :booktitle, chapter = :chapter, doi = :doi, 
				ean = :ean, edition = :edition, institution = :institution, journal_id = :journal_id, 
				number = :number, organization = :organization, 
				pages = :pages, publisher_id = :publisher_id, school = :school, series = :series, url = :url, 
				volume = :volume, note = :note, rating = :rating WHERE id = :id');
			$stmt->bindParam(':entrytype_id', $cleanEntryTypeId, PDO::PARAM_INT);
			$stmt->bindParam(':title', $cleanTitle);
			$stmt->bindParam(':date', $cleanDate);
			$stmt->bindParam(':abstract', $cleanAbstract);
			$stmt->bindParam(':address', $cleanAddress);
			$stmt->bindParam(':booktitle', $cleanBooktitle);
			$stmt->bindParam(':chapter', $cleanChapter);
			$stmt->bindParam(':doi', $cleanDoi);
			$stmt->bindParam(':ean', $cleanEan);
			$stmt->bindParam(':edition', $cleanEdition);
			$stmt->bindParam(':institution', $cleanInstitution);
			$stmt->bindParam(':journal_id', $cleanJournalId, PDO::PARAM_INT);
			$stmt->bindParam(':number', $cleanNumber, PDO::PARAM_INT);
			$stmt->bindParam(':organization', $cleanOrganization);
			$stmt->bindParam(':pages', $cleanPages);
			$stmt->bindParam(':publisher_id', $cleanPublisherId);
			$stmt->bindParam(':school', $cleanSchool);
			$stmt->bindParam(':series', $cleanSeries);	
			$stmt->bindParam(':url', $cleanUrl);
			$stmt->bindParam(':volume', $cleanVolume);
			$stmt->bindParam(':note', $cleanNote);
			$stmt->bindParam(':rating', $cleanRating, PDO::PARAM_INT);
			$stmt->bindParam(':id', $document['id'], PDO::PARAM_INT);
			$stmt->execute();

			$id = $document['id'];
		}
		else{
			$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_document 
				(entrytype_id, title, date, abstract, address, booktitle, chapter, doi, ean, edition, 
				institution, journal_id, number, organization, pages, publisher_id, school, series, 
				url, volume, note, rating, datetime_created, user_id) 
				VALUES (:entrytype_id, :title, :date, :abstract, :address, :booktitle, :chapter, :doi, :ean, :edition, 
				:institution, :journal_id, :number, :organization, :pages, :publisher_id, :school, :series, 
				:url, :volume, :note, :rating, NOW(), :user_id)');
			$stmt->bindParam(':entrytype_id', $cleanEntryTypeId, PDO::PARAM_INT);
			$stmt->bindParam(':title', $cleanTitle);
			$stmt->bindParam(':date', $cleanDate);
			$stmt->bindParam(':abstract', $cleanAbstract);
			$stmt->bindParam(':address', $cleanAddress);
			$stmt->bindParam(':booktitle', $cleanBooktitle);
			$stmt->bindParam(':chapter', $cleanChapter);
			$stmt->bindParam(':doi', $cleanDoi);
			$stmt->bindParam(':ean', $cleanEan);
			$stmt->bindParam(':edition', $cleanEdition);
			$stmt->bindParam(':institution', $cleanInstitution);
			$stmt->bindParam(':journal_id', $cleanJournalId, PDO::PARAM_INT);
			$stmt->bindParam(':number', $cleanNumber, PDO::PARAM_INT);
			$stmt->bindParam(':organization', $cleanOrganization);
			$stmt->bindParam(':pages', $cleanPages);
			$stmt->bindParam(':publisher_id', $cleanPublisherId);
			$stmt->bindParam(':school', $cleanSchool);
			$stmt->bindParam(':series', $cleanSeries);	
			$stmt->bindParam(':url', $cleanUrl);
			$stmt->bindParam(':volume', $cleanVolume);
			$stmt->bindParam(':note', $cleanNote);
			$stmt->bindParam(':rating', $cleanRating, PDO::PARAM_INT);
			$stmt->bindParam(':user_id', $document['user_id'], PDO::PARAM_INT);
			$stmt->execute();

			$id = LibDb::insertId();
		}
				
		/*
		* Tags
		*/
		$stmt = LibDb::prepare('DELETE FROM literaturedb_asso_document_tag WHERE document_id = :document_id');
		$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
		$stmt->execute();
		
		if(isset($document['tags']) && is_array($document['tags'])){
			foreach($document['tags'] as $tag){
				if($document['user_id'] == $tag['user_id']){
					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_tag (name, user_id) VALUES (:name, :user_id)');
					$stmt->bindParam(':name', $tag['name']);
					$stmt->bindParam(':user_id', $tag['user_id'], PDO::PARAM_INT);
					$stmt->execute();
				}
			}
	
			foreach($document['tags'] as $tag){
				if($document['user_id'] == $tag['user_id']){
					$stmt = LibDb::prepare('SELECT id FROM literaturedb_tag WHERE name = :name AND user_id = :user_id');
					$stmt->bindParam(':name', $tag['name']);
					$stmt->bindParam(':user_id', $tag['user_id'], PDO::PARAM_INT);
					$stmt->execute();
					$stmt->bindColumn('id', $tagId);
					$stmt->fetch();
					
					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_asso_document_tag (document_id, tag_id) VALUES (:document_id, :tag_id)');
					$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
					$stmt->bindParam(':tag_id', $tagId, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
		}

		/*
		* Authors
		*/
		$stmt = LibDb::prepare('DELETE FROM literaturedb_asso_document_author WHERE document_id = :document_id');
		$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
		$stmt->execute();

		if(isset($document['authors']) && is_array($document['authors'])){
			foreach($document['authors'] as $author){
				if($document['user_id'] == $author['user_id']){
					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_person (firstname, prefix, lastname, suffix, user_id) VALUES (:firstname, :prefix, :lastname, :suffix, :user_id)');
					$stmt->bindParam(':firstname', $author['firstname']);
					$stmt->bindParam(':prefix', $author['prefix']);
					$stmt->bindParam(':lastname', $author['lastname']);
					$stmt->bindParam(':suffix', $author['suffix']);
					$stmt->bindParam(':user_id', $author['user_id'], PDO::PARAM_INT);
					$stmt->execute();
				}
			}

			$i = 1;
			foreach($document['authors'] as $author){
				if($document['user_id'] == $author['user_id']){
					$stmt = LibDb::prepare('SELECT id FROM literaturedb_person WHERE lastname = :lastname AND firstname = :firstname AND user_id = :user_id');
					$stmt->bindParam(':lastname', $author['lastname']);
					$stmt->bindParam(':firstname', $author['firstname']);
					$stmt->bindParam(':user_id', $author['user_id'], PDO::PARAM_INT);
					$stmt->execute();
					$stmt->bindColumn('id', $authorId);
					$stmt->fetch();

					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_asso_document_author (document_id, person_id, position) VALUES (:document_id, :person_id, :position)');
					$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
					$stmt->bindParam(':person_id', $authorId, PDO::PARAM_INT);
					$stmt->bindParam(':position', $i, PDO::PARAM_INT);
					$stmt->execute();
		
					$i++;
				}
			}
		}

		/*
		* Editors
		*/
		$stmt = LibDb::prepare('DELETE FROM literaturedb_asso_document_editor WHERE document_id = :document_id');
		$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
		$stmt->execute();

		if(isset($document['editors']) && is_array($document['editors'])){
			foreach($document['editors'] as $editor){
				if($document['user_id'] == $editor['user_id']){
					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_person (firstname, prefix, lastname, suffix, user_id) VALUES (:firstname, :prefix, :lastname, :suffix, :user_id)');
					$stmt->bindParam(':firstname', $editor['firstname']);
					$stmt->bindParam(':prefix', $editor['prefix']);
					$stmt->bindParam(':lastname', $editor['lastname']);
					$stmt->bindParam(':suffix', $editor['suffix']);
					$stmt->bindParam(':user_id', $editor['user_id'], PDO::PARAM_INT);
					$stmt->execute();
				}
			}

			$i = 1;
			foreach($document['editors'] as $editor){
				if($document['user_id'] == $editor['user_id']){
					$stmt = LibDb::prepare('SELECT id FROM literaturedb_person WHERE lastname = :lastname AND firstname = :firstname AND user_id = :user_id');
					$stmt->bindParam(':lastname', $editor['lastname']);
					$stmt->bindParam(':firstname', $editor['firstname']);
					$stmt->bindParam(':user_id', $editor['user_id'], PDO::PARAM_INT);
					$stmt->execute();
					$stmt->bindColumn('id', $editorId);
					$stmt->fetch();

					$stmt = LibDb::prepare('INSERT IGNORE INTO literaturedb_asso_document_editor (document_id, person_id, position) VALUES (:document_id, :person_id, :position)');
					$stmt->bindParam(':document_id', $id, PDO::PARAM_INT);
					$stmt->bindParam(':person_id', $editorId, PDO::PARAM_INT);
					$stmt->bindParam(':position', $i, PDO::PARAM_INT);
					$stmt->execute();

					$i++;
				}
			}
		}
		
		LibDocument::deleteOrphans();
		
		return $id;
	}
	
	static function saveFileInfo($documentId, $hash, $filename, $extension){
		$documentId = trim($documentId);
		$hash = trim($hash);
		$filename = trim($filename);
		$extension = trim($extension);

		if($documentId == ''){
			LibGlobal::$errorTexts[] = 'Could not save file info due to an undefined document id.';
		}elseif($hash != '' && $filename != ''){
			$filesize = filesize(LibDocument::getFilePath($hash));
			
			$stmt = LibDb::prepare('UPDATE literaturedb_document SET hash = :hash, filename = :filename, extension = :extension, filesize = :filesize WHERE id = :id AND (hash = "" OR hash IS NULL)');
			$stmt->bindParam(':hash', $hash);
			$stmt->bindParam(':filename', $filename);
			$stmt->bindParam(':extension', $extension);
			$stmt->bindParam(':filesize', $filesize, PDO::PARAM_INT);
			$stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
			$stmt->execute();
		}
	}
	
	static function delete($documentId){
		$deleted = false;
		
		$stmt = LibDb::prepare('SELECT hash FROM literaturedb_document WHERE id = :id');
		$stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('hash', $hash);
		$stmt->fetch();
		
		$stmt = LibDb::prepare('SELECT COUNT(hash) AS number FROM literaturedb_document WHERE hash = :hash');
		$stmt->bindParam(':hash', $hash);
		$stmt->execute();
		$stmt->bindColumn('number', $count);
		$stmt->fetch();
		
		$stmt = LibDb::prepare('DELETE FROM literaturedb_document WHERE id = :id');
		$stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
		$stmt->execute();
		
		LibDocument::deleteOrphans();
		
		$deleted = true;

		if($count < 2){
			$filePath = self::getFilePath($hash);
			if(is_file($filePath)){
				unlink($filePath);
			}
		}
		return $deleted;
	}
	
	static function deleteOrphans(){
		/*
		* Delete orphaned associations between documents and authors
		*/
		$cmd = 'DELETE FROM literaturedb_asso_document_author WHERE document_id NOT IN (SELECT id FROM literaturedb_document) OR person_id NOT IN (SELECT id FROM literaturedb_person)';
		LibDb::query($cmd);

		/*
		* Delete orphaned associations between documents and editors
		*/		
		$cmd = 'DELETE FROM literaturedb_asso_document_editor WHERE document_id NOT IN (SELECT id FROM literaturedb_document) OR person_id NOT IN (SELECT id FROM literaturedb_person)';
		LibDb::query($cmd);

		/*
		* Delete orphaned associations between documents and tags
		*/	
		$cmd = 'DELETE FROM literaturedb_asso_document_tag WHERE document_id NOT IN (SELECT id FROM literaturedb_document) OR tag_id NOT IN (SELECT id FROM literaturedb_tag)';
		LibDb::query($cmd);
	
		/*
		* Delete orphaned journals
		*/		
		$cmd = 'DELETE FROM literaturedb_journal WHERE id NOT IN (SELECT journal_id FROM literaturedb_document)';
		LibDb::query($cmd);
		
		/*
		* Delete orphaned publishers
		*/		
		$cmd = 'DELETE FROM literaturedb_publisher WHERE id NOT IN (SELECT publisher_id FROM literaturedb_document)';
		LibDb::query($cmd);
		
		/*
		* Delete orphaned tags
		*/
		$cmd = 'DELETE FROM literaturedb_tag WHERE id NOT IN (SELECT tag_id FROM literaturedb_asso_document_tag)';
		LibDb::query($cmd);
		
		/*
		* Delete orphaned authors and editors
		*/
		$cmd = 'DELETE FROM literaturedb_person WHERE id NOT IN (SELECT person_id FROM literaturedb_asso_document_author) AND id NOT IN (SELECT person_id FROM literaturedb_asso_document_editor)';
		LibDb::query($cmd);	
	}
	
	/*
	* Output
	*/
	static function buildTagsString($document){	
		$tagLinks = array();
		foreach($document['tags'] as $tag)
			$tagLinks[] = '<a href="index.php?pid=literaturedb_documents&amp;tag=' .LibString::protectXSS($tag['name']). '">' . LibString::protectXSS($tag['name']) .'</a>';
		return implode(' ', $tagLinks);
	}
	
	static function buildAuthorsString($document){
		$authorsStrings = array();
		foreach($document['authors'] as $author)
			$authorsStrings[] = '<a href="index.php?pid=literaturedb_person&amp;personAddress=' .LibString::protectXSS(LibUser::buildMinimalUserAddress($author['person_address'])). '">' .LibString::protectXSS($author['lastname']). '</a>';
		return implode(', ', $authorsStrings);
	}
	
	static function buildEditorsString($document){
		$editorsStrings = array();
		foreach($document['editors'] as $editor)
			$editorsStrings[] = '<a href="index.php?pid=literaturedb_person&amp;personAddress=' .LibString::protectXSS(LibUser::buildMinimalUserAddress($editor['person_address'])). '">' .LibString::protectXSS($editor['lastname']). '</a>';
		return implode(', ', $editorsStrings);	
	}
	
	
	
	/*
	* Addressing
	*/
	static function buildCanonicalDocumentAddress($documentAddress){
		$documentAddress = trim($documentAddress);
		$numberOfParts = substr_count($documentAddress, '@');
	
		if($numberOfParts == 0)
			return $documentAddress . '@' . LibConfig::$sitePath;
		elseif($numberOfParts == 1)
			return $documentAddress;
		else
			return '';
	}
	
	static function buildMinimalDocumentAddress($documentAddress){
		$documentAddress = trim($documentAddress);
		$numberOfParts = substr_count($documentAddress, '@');

		if($numberOfParts == 0)
			return $documentAddress;
		elseif($numberOfParts == 1){
			if(self::getDomainPart($documentAddress) == LibConfig::$sitePath)
				return self::getLocalPart($documentAddress);
			else
				return self::buildCanonicalDocumentAddress($documentAddress);
		}
		else
			return '';
	}

	static function isLocalDocumentAddress($documentAddress){
		return self::getDomainPart(self::buildCanonicalDocumentAddress($documentAddress)) == LibConfig::$sitePath;		
	}

	static function getDocumentAddressParts($documentAddress){
		$documentAddress = self::buildCanonicalDocumentAddress($documentAddress);
		return explode('@', $documentAddress);
	}

	static function getLocalPart($documentAddress){
		$documentAddressParts = self::getDocumentAddressParts($documentAddress);
		return trim($documentAddressParts[0]);
	}

	static function getDomainPart($documentAddress){
		$documentAddressParts = self::getDocumentAddressParts($documentAddress);
		return trim($documentAddressParts[1]);
	}

	static function isValidDocumentAddress($documentAddress){
		if($documentAddress != "")
	    	if(preg_match("/^([0-9]+)@([a-zA-Z0-9\.\-]+)$/", $documentAddress))
				return true;
		return false;
	}
	
	static function fetchAllEntryTypes(){
		return array(0 => '', 1=>'article', 2=>'book', 3=>'booklet', 4=>'conference', 5=>'inbook', 6=>'incollection', 7=>'inproceedings', 8=>'manual', 9=>'mastersthesis', 10=>'misc', 11=>'phdthesis', 12=>'proceedings', 13=>'techreport', 14=>'unpublished');
	}
	
	static function getFilePath($hash){
		self::createFileDirectories($hash);
		return LibConfig::$documentDir.'/'.substr($hash, 0, 1).'/'.substr($hash, 1, 1).'/'.$hash;
	}
	
	static function createFileDirectories($hash){
		if(!is_dir(LibConfig::$documentDir . '/'. substr($hash, 0, 1)))
			mkdir(LibConfig::$documentDir . '/'. substr($hash, 0, 1));
		if(!is_dir(LibConfig::$documentDir . '/'. substr($hash, 0, 1). '/'.substr($hash, 1, 1)))
			mkdir(LibConfig::$documentDir . '/'. substr($hash, 0, 1). '/'. substr($hash, 1, 1));
	}
	
	/*
	* Export
	*/
	static function getId_Word2007($id){
		return $id . '.' . LibConfig::$siteUri;
	}
	
	static function getId_Bibtex($document){
		$authors = $document['authors'];
		$authorsString = '';
		$dateString = '';
		
		if(is_array($authors)){
			$numberOfAuthors = count($authors);
		
			if($numberOfAuthors == 1){
				$author = $authors[0];
				$authorsString = substr(LibDocument::replaceSpecialChars(trim($author['lastname'])), 0, 3);
			}
			elseif($numberOfAuthors > 1){
				$i = 0;

				foreach($authors as $author){
					if($i < 4)
						$authorsString .= substr(LibDocument::replaceSpecialChars(trim($author['lastname'])), 0, 1);
					if($i == 4)
						$authorsString .= '+';
					$i++;
				}
			}
		}
				
		if($document['date'] > 0)
			$dateString = substr($document['date'], 2, 2);
		
		return $authorsString . $dateString . '.' . $document['id'] . '.' . LibConfig::$siteUri;
	}
	
	static function getId_ModsXml($id){
		return $id . '.' . LibConfig::$siteUri;
	}
		
	/*
	* Helper
	*/
	static function replaceSpecialChars($string){
		//removes all special characters, that would disturb Bibtex IDs
		$needle = array('ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö');
		$replacement = array('ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe');
		$string = str_ireplace($needle, $replacement, $string);
		return preg_replace('/[^a-zA-Z0-9\-]/', '', $string);
	}
	
	static function isOwnDocument($document){
		global $sessionUser;
		if(self::isLocalDocumentAddress($document['document_address']) && $document['user_id'] == $sessionUser->id)
			return true;
		return false;
	}
}
?>