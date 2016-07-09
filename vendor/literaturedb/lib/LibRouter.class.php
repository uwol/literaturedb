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

namespace literaturedb;

use PDO;

class LibRouter{
	/*
	* document
	*/
	//@Local ------------------------------------------------------------------
	static function document_fetchAll($askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$documents = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::document_mayFetchAll($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);

					$documents = array_merge($documents, LibDocument::fetchAll($askedUser['id']));
				}
			}
		}

		return $documents;
	}

	static function document_mayFetchAll($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote
	static function document_fetchLast($askedUserAddresses, $askingUserAddress, $offset = 0, $limit = 3){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$documents = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::document_mayFetchLast($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress, $limit);

					$documents = array_merge($documents, LibDocument::fetchLast($askedUser['id'], $offset, $limit));
				} else {
					return $askedUserAddress .' does not share documents with you.';
				}
			} else {
				$remoteDocuments = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'document_fetchLast',
					array('askedUserAddress' => $askedUserAddress, 'offset' => $offset, 'limit' => $limit));

				if(is_array($remoteDocuments)){
					$documents = array_merge($documents, $remoteDocuments);
				} else {
					return $remoteDocuments;
				}
			}
		}

		return $documents;
	}

	static function document_mayFetchLast($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote

	static function document_fetchWithoutTag($askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$documents = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::document_mayFetchWithoutTag($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$documents = array_merge($documents, LibDocument::fetchWithoutTag($askedUser['id']));
				} else {
					return $askedUserAddress .' does not share documents without tags with you.';
				}
			} else {
				$remoteDocuments = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'document_fetchWithoutTag',
					array('askedUserAddress' => $askedUserAddress));

				if(is_array($remoteDocuments)){
					$documents = array_merge($documents, $remoteDocuments);
				} else {
					return $remoteDocuments;
				}
			}
		}

		return $documents;
	}

	static function document_mayFetchWithoutTag($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote

	static function document_fetchWithTag($tag, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$documents = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::document_mayFetchWithTag($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$documents = array_merge($documents, LibDocument::fetchWithTag($tag, $askedUser['id']));
				} else {
					return $askedUserAddress .' does not share tagged documents with you.';
				}
			} else {
				$remoteDocuments = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'document_fetchWithTag',
					array('askedUserAddress' => $askedUserAddress, 'tag' => $tag));

				if(is_array($remoteDocuments)){
					$documents = array_merge($documents, $remoteDocuments);
				} else {
					return $remoteDocuments;
				}
			}
		}

		return $documents;
	}

	static function document_mayFetchWithTag($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote
	static function document_fetchWithSearch($searchString, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$documents = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::document_mayFetchWithSearch($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$documents = array_merge($documents, LibDocument::fetchWithSearch($searchString, $askedUser['id']));
				} else {
					return $askedUserAddress .' does not share searched documents with you.';
				}
			} else {
				$remoteDocuments = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'document_fetchWithSearch',
					array('askedUserAddress' => $askedUserAddress, 'searchString' => $searchString));

				if(is_array($remoteDocuments)){
					$documents = array_merge($documents, $remoteDocuments);
				} else {
					return $remoteDocuments;
				}
			}
		}

		return $documents;
	}

	static function document_mayFetchWithSearch($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote
	static function document_fetchWithAuthor($authorAddress, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$authorAddress = LibUser::buildCanonicalUserAddress($authorAddress);
		$documents = array();

		if(LibPerson::isLocalPersonAddress($authorAddress)){
			if(self::document_mayFetchWithAuthor($authorAddress, $askingUserAddress)){
				$authorId = LibPerson::getLocalPart($authorAddress);
				$documents = array_merge($documents, LibDocument::fetchWithAuthor($authorId));
			} else {
				return 'The user does not allow you to fetch his documents.';
			}
		} else {
			$remoteDocuments = self::remoteCall(
				LibPerson::getDomainPart($authorAddress),
				$askingUserAddress,
				'document_fetchWithAuthor',
				array('authorAddress' => $authorAddress));

			if(is_array($remoteDocuments)){
				$documents = array_merge($documents, $remoteDocuments);
			} else {
				return $remoteDocuments;
			}
		}

		return $documents;
	}

	static function document_mayFetchWithAuthor($askedPersonAddress, $askingUserAddress){
		$personInDb = LibPerson::fetch(LibPerson::getLocalPart($askedPersonAddress));
		$askedUser = LibUser::fetch($personInDb['user_id']);
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUser['username']);

		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//------------------------

	//@Remote
	static function document_fetch($askedDocumentAddress, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$askedDocumentAddress = LibDocument::buildCanonicalDocumentAddress($askedDocumentAddress);

		if(LibDocument::isLocalDocumentAddress($askedDocumentAddress)){
			if(self::document_mayFetch($askedDocumentAddress, $askingUserAddress)){
				return LibDocument::fetch(LibDocument::getLocalPart($askedDocumentAddress));
			} else {
				return 'The user does not allow you to fetch his documents.';
			}
		} else {
			return self::remoteCall(
				LibDocument::getDomainPart($askedDocumentAddress),
				$askingUserAddress,
				'document_fetch',
				array('askedDocumentAddress' => $askedDocumentAddress));
		}
	}

	static function document_mayFetch($askedDocumentAddress, $askingUserAddress){
		$documentInDb = LibDocument::fetch(LibDocument::getLocalPart($askedDocumentAddress));
		$askedUser = LibUser::fetch($documentInDb['user_id']);
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUser['username']);
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Remote
	static function document_fetchFileContents($askedDocumentAddress, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$askedDocumentAddress = LibDocument::buildCanonicalDocumentAddress($askedDocumentAddress);
		$document = self::document_fetch($askedDocumentAddress, $askingUserAddress);

		if(is_array($document)){ //could the document be fetched successfully?
			if(LibDocument::isLocalDocumentAddress($askedDocumentAddress)){
				if(self::document_mayFetchFileContents($askedDocumentAddress, $askingUserAddress)){
					if($document['hash'] != ''){
						$filePath = LibDocument::getFilePath($document['hash']);

						$handle = fopen($filePath, "rb");
						$contents = fread($handle, filesize($filePath));
						fclose($handle);

						return $contents;
					}
				}
			} else {
				return self::remoteCall(
					LibDocument::getDomainPart($askedDocumentAddress),
					$askingUserAddress,
					'document_fetchFileContents',
					array('askedDocumentAddress' => $askedDocumentAddress));
			}
		}
	}

	static function document_mayFetchFileContents($askedDocumentAddress, $askingUserAddress){
		$documentInDb = LibDocument::fetch(LibDocument::getLocalPart($askedDocumentAddress));
		$askedUser = LibUser::fetch($documentInDb['user_id']);
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUser['username']);
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}


	//@Local ------------------------------------------------------------------
	static function document_fetchByHash($hash, $askedUserAddress, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

		if(self::document_mayFetchByHash($askedUserAddress, $askingUserAddress)){
			$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
			return LibDocument::fetchByHash($hash, $askedUser['id']);
		}
	}

	static function document_mayFetchByHash($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Local ------------------------------------------------------------------
	static function document_save($document, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if(self::document_maySave($document, $askingUserAddress)){ //may save this document?
			return LibDocument::save($document);
		} else { //may not save this document under this id. Solution: copy document
			$documentInDb = LibDocument::fetch($document['id']);
			$documentByHash = self::document_fetchByHash($documentInDb['hash'], $askingUserAddress, $askingUserAddress);
			if(isset($documentByHash['hash']) && $documentByHash['hash'] != ''){ //is document with this hash already owned by user?
				return $documentByHash['id'];
			} else { //copy this document to the documents of the user
				$document['id'] = ''; //mark document as a new record
				$document['hash'] = $documentInDb['hash'];
				$document['filename'] = $documentInDb['filename'];
				$document['extension'] = $documentInDb['extension'];
				$document['filesize'] = $documentInDb['filesize'];
				$document['datetime_created'] = date('Y-m-d h:i:s');
				return LibDocument::save($document);
			}
		}
	}

	static function document_maySave($document, $askingUserAddress){
		if(!isset($document['id'])){
			return true;
		}

		$documentInDb = LibDocument::fetch($document['id']);
		$askingUser = LibUser::fetchByUserAddress($askingUserAddress);

		//new document or document owned by this user?
		if(!is_array($documentInDb) || !isset($documentInDb['id']) || $documentInDb['id'] == ''
				|| $documentInDb['user_id'] == $askingUser['id']){
			return true;
		}

		return false;
	}

	//@Local ------------------------------------------------------------------

	static function document_delete($documentAddress, $askingUserAddress){
		$documentId = LibDocument::getLocalPart($documentAddress);
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if(self::document_mayDelete($documentId, $askingUserAddress)){
			return LibDocument::delete($documentId);
		}
	}

	static function document_mayDelete($documentId, $askingUserAddress){
		$documentInDb = LibDocument::fetch($documentId);
		$askingUser = LibUser::fetchByUserAddress($askingUserAddress);

		if($documentInDb['id'] > 0 && $documentInDb['user_id'] == $askingUser['id']){
			return true;
		}

		return false;
	}


	/*
	* tag
	*/
	//@Remote
	static function tag_fetchAll($askedUserAddresses, $askingUserAddress, $offset = 0, $limit = 100){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$tags = array();
		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::tag_mayFetchAll($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$tags = array_merge($tags, LibTag::fetchAll($askedUser['id'], $offset, $limit));
				} else {
					return $askedUserAddress .' does not share tags with you.';
				}
			} else {
				$remoteTags = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'tag_fetchAll',
					array('askedUserAddress' => $askedUserAddress, 'offset' => $offset, 'limit' => $limit));

				if(is_array($remoteTags)){
					$tags = array_merge($tags, $remoteTags);
				} else {
					return $remoteTags;
				}
			}
		}

		return $tags;
	}

	static function tag_mayFetchAll($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Local ------------------------------------------------------------------
	static function tag_fetchNameBeginningWith($beginning, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$tags = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::tag_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$tags = array_merge($tags, LibTag::fetchNameBeginningWith($beginning, $askedUser['id']));
				}
			}
		}

		return $tags;
	}

	static function tag_mayFetchNameBeginningWith($userId, $authId){
		return true;
	}

	//------------------------

	/*
	* person
	*/
	//@Remote
	static function person_fetchAllAuthors($askedUserAddresses, $askingUserAddress, $offset = 0, $limit = 100){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$authors = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::person_mayFetchAllAuthors($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$authors = array_merge($authors, LibPerson::fetchAllAuthors($askedUser['id'], $offset, $limit));
				} else {
					return $askedUserAddress .' does not share authors with you.';
				}
			} else {
				$remoteAuthors = self::remoteCall(
					LibUser::getDomainPart($askedUserAddress),
					$askingUserAddress,
					'person_fetchAllAuthors',
					array('askedUserAddress' => $askedUserAddress, 'offset' => $offset, 'limit' => $limit));

				if(is_array($remoteAuthors)){
					$authors = array_merge($authors, $remoteAuthors);
				} else {
					return $remoteAuthors;
				}
			}
		}

		return $authors;
	}

	static function person_mayFetchAllAuthors($askedUserAddress, $askingUserAddress){
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}

	//@Local ------------------------------------------------------------------

	static function person_fetchNameBeginningWith($beginning, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$persons = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::person_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress(LibUser::getLocalPart($askedUserAddress));
					$persons = array_merge($persons, LibPerson::fetchNameBeginningWith($beginning, $askedUser['id']));
				}
			}
		}

		return $persons;
	}

	static function person_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress){
		return true;
	}

	//------------------------
	//@Remote
	static function person_fetch($askedPersonAddress, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$askedPersonAddress = LibPerson::buildCanonicalPersonAddress($askedPersonAddress);

		if(LibPerson::isLocalPersonAddress($askedPersonAddress)){
			if(self::person_mayFetch($askedPersonAddress, $askingUserAddress)){
				return LibPerson::fetch(LibPerson::getLocalPart($askedPersonAddress));
			} else {
				return 'The user does not allow you to fetch this person.';
			}
		} else {
			return self::remoteCall(
				LibDocument::getDomainPart($askedPersonAddress),
				$askingUserAddress,
				'person_fetch',
				array('askedPersonAddress' => $askedPersonAddress));
		}
	}

	static function person_mayFetch($askedPersonAddress, $askingUserAddress){
		$personInDb = LibPerson::fetch(LibPerson::getLocalPart($askedPersonAddress));
		$askedUser = LibUser::fetch($personInDb['user_id']);
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUser['username']);
		return self::mayProcessRequest($askedUserAddress, $askingUserAddress);
	}


	//@Local ------------------------------------------------------------------
	static function person_save($person, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if(self::person_maySave($person, $askingUserAddress)){
			return LibPerson::save($person);
		}
	}

	static function person_maySave($person, $askingUserAddress){
		//determine userAddress of person, and check if askingUserAddress is permitted to save it !!!
		return true;
	}


	/*
	* Publisher
	*/
	//@Local ------------------------------------------------------------------
	static function publisher_fetchNameBeginningWith($beginning, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		$publishers = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::person_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$publishers = array_merge($publishers, LibPublisher::fetchNameBeginningWith($beginning, $askedUser['id']));
				}
			}
		}

		return $publishers;
	}

	static function publisher_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress){
		return true;
	}



	/*
	* Journal
	*/
	//@Local ------------------------------------------------------------------

	static function journal_fetchNameBeginningWith($beginning, $askedUserAddresses, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		$journals = array();

		foreach($askedUserAddresses as $askedUserAddress){
			$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);

			if(LibUser::isLocalUserAddress($askedUserAddress)){
				if(self::journal_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress)){
					$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
					$journals = array_merge($journals, LibJournal::fetchNameBeginningWith($beginning, $askedUser['id']));
				}
			}
		}

		return $journals;
	}

	static function journal_mayFetchNameBeginningWith($askedUserAddress, $askingUserAddress){
		return true;
	}

	/*
	* Share
	*/
	//@Local ------------------------------------------------------------------
	static function share_fetchAllByLocalUserId($localUserId, $askingUserAddress){
		return LibShare::fetchAllByLocalUserId($localUserId);
	}

	static function share_fetchAllFollowedByLocalUserId($localUserId, $askingUserAddress){
		return LibShare::fetchAllFollowedByLocalUserId($localUserId);
	}

	//@Local ------------------------------------------------------------------
	static function share_delete($shareId, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if(self::share_mayDelete($shareId, $askingUserAddress)){
			return LibShare::delete($shareId);
		}
	}

	static function share_mayDelete($shareId, $askingUserAddress){
		$shareInDb = LibShare::fetch($shareId);
		$askingUser = LibUser::fetchByUserAddress($askingUserAddress);

		if($shareInDb['id'] > 0 && $shareInDb['local_user_id'] == $askingUser['id']){
			return true;
		}

		return false;
	}

	//@Local ------------------------------------------------------------------
	static function share_save($share, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if(self::share_maySave($share, $askingUserAddress)){
			return LibShare::save($share);
		}
	}

	static function share_maySave($share, $askingUserAddress){
		$askingUser = LibUser::fetchByUserAddress($askingUserAddress);

		if($share['local_user_id'] == $askingUser['id']){
			return true;
		}
	}

	/*
	* User
	*/
	//@Local ------------------------------------------------------------------
	static function user_fetchAll($askingUserAddress){
		return LibUser::fetchAll();
	}

	//@Local ------------------------------------------------------------------
	static function user_fetchAllActivated($askingUserAddress){
		return LibUser::fetchAllActivated();
	}

	//@Local ------------------------------------------------------------------
	static function user_delete($userId, $askingUserAddress){
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);
		if(self::user_mayDelete($userId, $askingUserAddress))
			return LibUser::delete($userId);
	}

	static function user_mayDelete($userId, $askingUserAddress){
		$userInDb = LibUser::fetch($userId);
		$askingUser = LibUser::fetchByUserAddress($askingUserAddress);

		if($userInDb['id'] > 0 && $userInDb['id'] == $askingUser['id']){
			return true;
		}

		return false;
	}

	/*
	* Helper
	*/
	static function remoteCall($askedDomain, $askingUserAddress, $action, $params = array()){
		$url = 'http://' .$askedDomain. '/api.php?action=' .$action. '&askingUserAddress='. $askingUserAddress;

		foreach($params as $key => $value){
			$url .= '&'.$key.'='.$value;
		}

		$response = self::downloadContent($url);

		if($response === false){
			return 'A connection to the remote literature database could not be established.';
		} elseif($action == 'document_fetchFileContents'){
			return $response;
		} else {
			return json_decode(utf8_encode($response), true);
		}
	}

	static function mayProcessRequest($askedUserAddress, $askingUserAddress){
		$askedUserAddress = LibUser::buildCanonicalUserAddress($askedUserAddress);
		$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

		if($askedUserAddress == $askingUserAddress){
			return true;
		}

		$askedUser = LibUser::fetchByUserAddress($askedUserAddress);
		$share = LibShare::fetchByLocalUserIdAndRemoteUserAddress($askedUser['id'], $askingUserAddress);

		if(is_array($share) && isset($share['sharing']) && $share['sharing'] == 1){
			return true;
		}

		return false;
	}

	static function downloadContent($url){
		if(ini_get('allow_url_fopen')){
			return file_get_contents($url);
		} else {
			return \httpclient\HttpClient::quickGet($url);
		}
	}
}
?>