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
		$cmd = sprintf('SELECT id FROM literaturedb_document WHERE user_id = %s ORDER BY id',
			LibDb::secInp($userId));
		$result = LibDb::query($cmd);
		
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetchLast($userId, $offset = 0, $limit = 3){
		$internalLimit = 3;
		if(is_numeric($limit) && $limit > 0)
			$internalLimit = $limit;

		$internalOffset = 0;
		if(is_numeric($offset) && $offset >= 0)
			$internalOffset = $offset;
		
		$cmd = sprintf('SELECT id FROM literaturedb_document WHERE user_id = %s ORDER BY datetime_created DESC LIMIT '.$internalOffset.','.$internalLimit,
			LibDb::secInp($userId));
		$result = LibDb::query($cmd);
		
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetchWithoutTag($userId){
		$cmd = sprintf('SELECT id FROM literaturedb_document WHERE user_id = %s AND literaturedb_document.id NOT IN (SELECT literaturedb_asso_document_tag.document_id FROM literaturedb_asso_document_tag)',
			LibDb::secInp($userId));
		$result = LibDb::query($cmd);
		
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetchWithTag($tag, $userId){
		$cmd = sprintf('SELECT literaturedb_document.id FROM literaturedb_document, literaturedb_tag, literaturedb_asso_document_tag WHERE literaturedb_document.user_id = %s AND literaturedb_asso_document_tag.document_id = literaturedb_document.id AND literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.name = %s ORDER BY literaturedb_document.date DESC',
			LibDb::secInp($userId),
			LibDb::secInp($tag));
		$result = LibDb::query($cmd);
		
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetchWithSearch($searchString, $userId){
		$cmd = sprintf('SELECT literaturedb_document.id FROM literaturedb_document WHERE literaturedb_document.user_id = %s AND (literaturedb_document.id = %s OR literaturedb_document.title LIKE %s OR literaturedb_document.date LIKE %s OR literaturedb_document.abstract LIKE %s OR literaturedb_document.address LIKE %s OR literaturedb_document.booktitle LIKE %s OR literaturedb_document.chapter LIKE %s OR literaturedb_document.doi LIKE %s OR literaturedb_document.ean LIKE %s OR literaturedb_document.edition LIKE %s OR literaturedb_document.institution LIKE %s OR literaturedb_document.number LIKE %s OR literaturedb_document.organization LIKE %s OR literaturedb_document.pages LIKE %s OR literaturedb_document.school LIKE %s OR literaturedb_document.series LIKE %s OR literaturedb_document.url LIKE %s OR literaturedb_document.volume LIKE %s OR literaturedb_document.note LIKE %s OR literaturedb_document.filename LIKE %s OR literaturedb_document.extension LIKE %s OR literaturedb_document.datetime_created LIKE %s OR literaturedb_document.id IN (SELECT literaturedb_asso_document_author.document_id FROM literaturedb_asso_document_author, literaturedb_person WHERE literaturedb_asso_document_author.person_id = literaturedb_person.id AND (literaturedb_person.firstname LIKE %s OR literaturedb_person.lastname LIKE %s)) OR literaturedb_document.id IN (SELECT literaturedb_asso_document_editor.document_id FROM literaturedb_asso_document_editor, literaturedb_person WHERE literaturedb_asso_document_editor.person_id = literaturedb_person.id AND (literaturedb_person.firstname LIKE %s OR literaturedb_person.lastname LIKE %s)) OR literaturedb_document.id IN (SELECT literaturedb_asso_document_tag.document_id FROM literaturedb_asso_document_tag, literaturedb_tag WHERE literaturedb_asso_document_tag.tag_id = literaturedb_tag.id AND literaturedb_tag.name LIKE %s) OR literaturedb_document.journal_id IN (SELECT literaturedb_journal.id FROM literaturedb_journal WHERE literaturedb_journal.name LIKE %s) OR literaturedb_document.publisher_id IN (SELECT literaturedb_publisher.id FROM literaturedb_publisher WHERE literaturedb_publisher.name LIKE %s)) ORDER BY literaturedb_document.date DESC',
			LibDb::secInp($userId),
			LibDb::secInp($searchString),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'),
			LibDb::secInp('%'.$searchString.'%'));
		$result = LibDb::query($cmd);
		
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetchWithAuthor($authorId){
		$cmd = sprintf('SELECT literaturedb_document.id FROM literaturedb_document, literaturedb_asso_document_author WHERE literaturedb_document.id = literaturedb_asso_document_author.document_id AND literaturedb_asso_document_author.person_id = %s ORDER BY date DESC',
			LibDb::secInp($authorId));
		$result = LibDb::query($cmd);
		$documents = array();
		while($row = mysql_fetch_array($result))
			$documents[] = self::fetch($row['id']);
		return $documents;
	}
	
	static function fetch($id){
		$cmd = sprintf('SELECT literaturedb_document.id, literaturedb_document.hash, literaturedb_document.entrytype_id, literaturedb_document.title, literaturedb_document.date, literaturedb_document.abstract, literaturedb_document.address, literaturedb_document.booktitle, literaturedb_document.chapter, literaturedb_document.doi, literaturedb_document.ean, literaturedb_document.edition, literaturedb_document.institution, literaturedb_document.journal_id, literaturedb_document.number, literaturedb_document.organization, literaturedb_document.pages, literaturedb_document.publisher_id, literaturedb_document.school, literaturedb_document.series, literaturedb_document.url, literaturedb_document.volume, literaturedb_document.note, literaturedb_document.rating, literaturedb_document.filename, literaturedb_document.extension, literaturedb_document.filesize, literaturedb_document.datetime_created, literaturedb_document.user_id, literaturedb_publisher.name AS publisher_name, literaturedb_journal.name AS journal_name FROM literaturedb_document LEFT JOIN literaturedb_publisher ON literaturedb_publisher.id = literaturedb_document.publisher_id LEFT JOIN literaturedb_journal ON literaturedb_journal.id = literaturedb_document.journal_id WHERE literaturedb_document.id = %s',
			LibDb::secInp($id));
		$row = LibDb::queryArray($cmd);
		
		return self::buildDocumentArray($row);
	}
	
	static function fetchByHash($hash, $userId){
		if($hash == '')
			return;
	
		$cmd = sprintf('SELECT literaturedb_document.id, literaturedb_document.hash, literaturedb_document.entrytype_id, literaturedb_document.title, literaturedb_document.date, literaturedb_document.abstract, literaturedb_document.address, literaturedb_document.booktitle, literaturedb_document.chapter, literaturedb_document.doi, literaturedb_document.ean, literaturedb_document.edition, literaturedb_document.institution, literaturedb_document.journal_id, literaturedb_document.number, literaturedb_document.organization, literaturedb_document.pages, literaturedb_document.publisher_id, literaturedb_document.school, literaturedb_document.series, literaturedb_document.url, literaturedb_document.volume, literaturedb_document.note, literaturedb_document.rating, literaturedb_document.filename, literaturedb_document.extension, literaturedb_document.filesize, literaturedb_document.datetime_created, literaturedb_document.user_id, literaturedb_publisher.name AS publisher_name, literaturedb_journal.name AS journal_name FROM literaturedb_document LEFT JOIN literaturedb_publisher ON literaturedb_publisher.id = literaturedb_document.publisher_id LEFT JOIN literaturedb_journal ON literaturedb_journal.id = literaturedb_document.journal_id WHERE literaturedb_document.hash = %s AND literaturedb_document.user_id = %s',
			LibDb::secInp($hash),
			LibDb::secInp($userId));
		$row = LibDb::queryArray($cmd);
		
		return self::buildDocumentArray($row);
	}
	
	static function buildDocumentArray($row){
		$array = array();
		
		// copy
		$array['id'] = $row['id'];
		$array['document_address'] = self::buildCanonicalDocumentAddress($row['id']);
		$array['hash'] = $row['hash'];
		$array['entrytype_id'] = $row['entrytype_id'];
		$array['title'] = $row['title'];		
		$array['date'] = $row['date'];
		$array['abstract'] = $row['abstract'];

		$array['address'] = $row['address'];
		$array['booktitle'] = $row['booktitle'];
		$array['chapter'] = $row['chapter'];
		$array['doi'] = $row['doi'];
		$array['ean'] = $row['ean'];
		
		$array['edition'] = '';
		if($row['edition'] > 0)
			$array['edition'] = $row['edition'];	
		
		$array['institution'] = $row['institution'];
		$array['journal_id'] = $row['journal_id'];
		$array['number'] = $row['number'];
		$array['organization'] = $row['organization'];
		$array['pages'] = $row['pages'];
		$array['publisher_id'] = $row['publisher_id'];
		$array['school'] = $row['school'];
		$array['series'] = $row['series'];
		$array['url'] = $row['url'];
		$array['volume'] = $row['volume'];
		$array['note'] = $row['note'];
		$array['rating'] = $row['rating'];
		$array['filename'] = $row['filename'];
		$array['extension'] = $row['extension'];
		$array['filesize'] = $row['filesize'];
		$array['datetime_created'] = $row['datetime_created'];
		
		$entryTypes = self::fetchAllEntryTypes();
		$array['entrytype_name'] = '';
		if(isset($row['entrytype_id']) && isset($entryTypes[$row['entrytype_id']]))
			$array['entrytype_name'] = $entryTypes[$row['entrytype_id']];
		$array['publisher_name'] = $row['publisher_name'];
		$array['journal_name'] = $row['journal_name'];
		
		$array['user_id'] = $row['user_id'];

		/*
		* n:n Relationships
		*/
		$array['authors'] = LibPerson::fetchAllAuthorsForDocument($row['id']);
		$array['editors'] = LibPerson::fetchAllEditorsForDocument($row['id']);
		$array['tags'] = LibTag::fetchAllForDocument($row['id']);
		
		return $array;
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
		if(isset($document['journal_name']))
			$journalName = trim($document['journal_name']);

		if($journalName != ''){
			$cmd = sprintf('INSERT IGNORE INTO literaturedb_journal (name, user_id) VALUES (%s, %s)',
				LibDb::secInp($journalName),
				LibDb::secInp($document['user_id']));
			LibDb::query($cmd);
	
			$cmd = sprintf('SELECT id FROM literaturedb_journal WHERE name = %s AND user_id = %s', 
				LibDb::secInp($journalName),
				LibDb::secInp($document['user_id']));
			$document['journal_id'] = LibDb::queryAttribute($cmd);
		}

		/*
		* Publisher
		*/
		$publisherName = '';
		if(isset($document['publisher_name']))
			$publisherName = trim($document['publisher_name']);

		if($publisherName != ''){
			$cmd = sprintf('INSERT IGNORE INTO literaturedb_publisher (name, user_id) VALUES (%s, %s)',
				LibDb::secInp($publisherName),
				LibDb::secInp($document['user_id']));
			LibDb::query($cmd);
	
			$cmd = sprintf('SELECT id FROM literaturedb_publisher WHERE name = %s AND user_id = %s', 
				LibDb::secInp($publisherName),
				LibDb::secInp($document['user_id']));
			$document['publisher_id'] = LibDb::queryAttribute($cmd);
		}

		/*
		* Document
		*/
		$cmd = sprintf('SELECT COUNT(*) FROM literaturedb_document WHERE id = %s',
			LibDb::secInp($document['id']));
		$count = LibDb::queryAttribute($cmd);

		if($count > 0){
			// filesize, filename, extension, datetime_created, user_id may not be updated here! they are static
			$cmd = sprintf('UPDATE literaturedb_document SET entrytype_id = %s, title = %s, date = %s, abstract = %s, address = %s, booktitle = %s, chapter = %s, doi = %s, ean = %s, edition = %s, institution = %s, journal_id = %s, number = %s, organization = %s, pages = %s, publisher_id = %s, school = %s, series = %s, url = %s, volume = %s, note = %s, rating = %s WHERE id = %s',
				LibDb::secInp(LibDb::zerofy(trim($document['entrytype_id']))),
				LibDb::secInp(LibString::cleanBibtexString($document['title'])),
				LibDb::secInp(trim($document['date'])),
				LibDb::secInp(LibString::cleanBibtexString($document['abstract'])),
				LibDb::secInp(LibString::cleanBibtexString($document['address'])),
				LibDb::secInp(LibString::cleanBibtexString($document['booktitle'])),
				LibDb::secInp(LibString::cleanBibtexString($document['chapter'])),
				LibDb::secInp(LibString::cleanBibtexString($document['doi'])),
				LibDb::secInp(LibString::cleanBibtexString($document['ean'])),
				LibDb::secInp(LibDb::zerofy(LibString::cleanBibtexString($document['edition']))),
				LibDb::secInp(LibString::cleanBibtexString($document['institution'])),
				LibDb::secInp(LibDb::zerofy($document['journal_id'])),
				LibDb::secInp(LibString::cleanBibtexString($document['number'])),
				LibDb::secInp(LibString::cleanBibtexString($document['organization'])),
				LibDb::secInp(LibString::cleanBibtexString($document['pages'])),
				LibDb::secInp(LibDb::zerofy($document['publisher_id'])),
				LibDb::secInp(LibString::cleanBibtexString($document['school'])),
				LibDb::secInp(LibString::cleanBibtexString($document['series'])),
				LibDb::secInp(trim($document['url'])),
				LibDb::secInp(LibString::cleanBibtexString($document['volume'])),
				LibDb::secInp(LibString::cleanBibtexString($document['note'])),
				LibDb::secInp(LibDb::zerofy(trim($document['rating']))),
				LibDb::secInp($document['id']));
			LibDb::query($cmd);

			$id = $document['id'];
		}
		else{
			$cmd = sprintf('INSERT IGNORE INTO literaturedb_document (hash, entrytype_id, title, date, abstract, address, booktitle, chapter, doi, ean, edition, institution, journal_id, number, organization, pages, publisher_id, school, series, url, volume, note, rating, filename, extension, filesize, datetime_created, user_id) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), %s)',
				LibDb::secInp(trim($document['hash'])),
				LibDb::secInp(LibDb::zerofy(trim($document['entrytype_id']))),
				LibDb::secInp(LibString::cleanBibtexString($document['title'])),
				LibDb::secInp(trim($document['date'])),
				LibDb::secInp(LibString::cleanBibtexString($document['abstract'])),
				LibDb::secInp(LibString::cleanBibtexString($document['address'])),
				LibDb::secInp(LibString::cleanBibtexString($document['booktitle'])),
				LibDb::secInp(LibString::cleanBibtexString($document['chapter'])),
				LibDb::secInp(LibString::cleanBibtexString($document['doi'])),
				LibDb::secInp(LibString::cleanBibtexString($document['ean'])),
				LibDb::secInp(LibDb::zerofy(LibString::cleanBibtexString($document['edition']))),
				LibDb::secInp(LibString::cleanBibtexString($document['institution'])),
				LibDb::secInp(LibDb::zerofy(trim($document['journal_id']))),
				LibDb::secInp(LibString::cleanBibtexString($document['number'])),
				LibDb::secInp(LibString::cleanBibtexString($document['organization'])),
				LibDb::secInp(LibString::cleanBibtexString($document['pages'])),
				LibDb::secInp(LibDb::zerofy(trim($document['publisher_id']))),
				LibDb::secInp(LibString::cleanBibtexString($document['school'])),
				LibDb::secInp(LibString::cleanBibtexString($document['series'])),
				LibDb::secInp(trim($document['url'])),
				LibDb::secInp(LibString::cleanBibtexString($document['volume'])),
				LibDb::secInp(LibString::cleanBibtexString($document['note'])),
				LibDb::secInp(LibDb::zerofy(trim($document['rating']))),
				LibDb::secInp(trim($document['filename'])),
				LibDb::secInp(trim($document['extension'])),
				LibDb::secInp(trim($document['filesize'])),
				LibDb::secInp(trim($document['user_id'])));
			LibDb::query($cmd);

			$id = mysql_insert_id();
		}
		
		/*
		* Tags
		*/
		$cmd = sprintf('DELETE FROM literaturedb_asso_document_tag WHERE document_id = %s',
			LibDb::secInp($id));
		LibDb::query($cmd);
		
		if(isset($document['tags']) && is_array($document['tags'])){
			foreach($document['tags'] as $tag){
				if($document['user_id'] == $tag['user_id']){
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_tag (name, user_id) VALUES (%s, %s)',
						LibDb::secInp($tag['name']),
						LibDb::secInp($tag['user_id']));
					LibDb::query($cmd);
				}
			}
	
			foreach($document['tags'] as $tag){
				if($document['user_id'] == $tag['user_id']){
					$cmd = sprintf('SELECT id FROM literaturedb_tag WHERE name = %s AND user_id = %s', 
						LibDb::secInp($tag['name']),
						LibDb::secInp($tag['user_id']));
					$tagId = LibDb::queryAttribute($cmd);
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_asso_document_tag (document_id, tag_id) VALUES (%s, %s)',
						LibDb::secInp($id),
						LibDb::secInp($tagId));
					LibDb::query($cmd);
				}
			}
		}

		/*
		* Authors
		*/
		$cmd = sprintf('DELETE FROM literaturedb_asso_document_author WHERE document_id = %s',
			LibDb::secInp($id));
		LibDb::query($cmd);

		if(isset($document['authors']) && is_array($document['authors'])){
			foreach($document['authors'] as $author){
				if($document['user_id'] == $author['user_id']){
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_person (lastname, firstname, prefix, user_id) VALUES (%s, %s, %s, %s)',
						LibDb::secInp($author['lastname']),
						LibDb::secInp($author['firstname']),
						LibDb::secInp($author['prefix']),
						LibDb::secInp($author['user_id']));
					LibDb::query($cmd);
				}
			}

			$i = 1;
			foreach($document['authors'] as $author){
				if($document['user_id'] == $author['user_id']){
					$cmd = sprintf('SELECT id FROM literaturedb_person WHERE lastname = %s AND firstname = %s AND user_id = %s', 
						LibDb::secInp($author['lastname']),
						LibDb::secInp($author['firstname']),
						LibDb::secInp($author['user_id']));
			
					$authorId = LibDb::queryAttribute($cmd);
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_asso_document_author (document_id, person_id, position) VALUES (%s, %s, %s)',
						LibDb::secInp($id),
						LibDb::secInp($authorId),
						LibDb::secInp($i));
					LibDb::query($cmd);
		
					$i++;
				}
			}
		}
	
		/*
		* Editors
		*/
		$cmd = sprintf('DELETE FROM literaturedb_asso_document_editor WHERE document_id = %s',
			LibDb::secInp($id));
		LibDb::query($cmd);

		if(isset($document['editors']) && is_array($document['editors'])){
			foreach($document['editors'] as $editor){
				if($document['user_id'] == $editor['user_id']){
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_person (lastname, firstname, prefix, user_id) VALUES (%s, %s, %s, %s)',
						LibDb::secInp($editor['lastname']),
						LibDb::secInp($editor['firstname']),
						LibDb::secInp($editor['prefix']),
						LibDb::secInp($editor['user_id']));
					LibDb::query($cmd);
				}
			}

			$i = 1;
			foreach($document['editors'] as $editor){
				if($document['user_id'] == $editor['user_id']){
					$cmd = sprintf('SELECT id FROM literaturedb_person WHERE lastname = %s AND firstname = %s AND user_id = %s', 
						LibDb::secInp($editor['lastname']),
						LibDb::secInp($editor['firstname']),
						LibDb::secInp($editor['user_id']));
			
					$editorId = LibDb::queryAttribute($cmd);
					$cmd = sprintf('INSERT IGNORE INTO literaturedb_asso_document_editor (document_id, person_id, position) VALUES (%s, %s, %s)',
						LibDb::secInp($id),
						LibDb::secInp($editorId),
						LibDb::secInp($i));
					LibDb::query($cmd);

					$i++;
				}
			}
		}
		
		LibDocument::deleteOrphans();
		
		return $id;
	}
	
	static function delete($documentId){
		$deleted = false;
		
		$cmd = sprintf('SELECT hash FROM literaturedb_document WHERE id = %s',
			LibDb::secInp($documentId));
		$hash = LibDb::queryAttribute($cmd);
		
		$cmd = sprintf('SELECT COUNT(hash) FROM literaturedb_document WHERE hash = %s',
			LibDb::secInp($hash));
		$count = LibDb::queryAttribute($cmd);
		
		$cmd = sprintf('DELETE FROM literaturedb_document WHERE id = %s',
			LibDb::SecInp($documentId));
		LibDb::query($cmd);
		
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