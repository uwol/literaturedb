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

include "lib/masterinclude.php";

$request = LibRest::processRequest(); 
$requestVars = $request->getVars();
$askingUserAddress = '';

/*
* Authentication
*/
if(isset($requestVars['auth']) && $requestVars['auth'] == 'session'){ //is this a internal API call?
	//initialize session, slow and generates session files in the file system
	//should only be done for internal calls!
	require 'lib/initialize.php';

	if($sessionUser->isLoggedin())
		$askingUserAddress = $sessionUser->getUserAddress();
	else{
		LibRest::sendResponse(401);
		exit;
	}
}
else{ //this is an external API call
	LibDb::connect();

	$authenticated = false;
	$authUser = new LibUser();

	//authentication way 1: is the domain part of the askingUserAddress the same as the address of the caller?
	//-> only calls from host xyz.de may call with askingUserAddress 1234@xyz.de
	if($_SERVER['REMOTE_ADDR'] != '' && gethostbyname(LibUser::getDomainPart($requestVars['askingUserAddress'])) == $_SERVER['REMOTE_ADDR']){ //caller authentic because of ip address?
		$authenticated = true;
		$askingUserAddress = $requestVars['askingUserAddress'];
	}
	//authentication way 2: basic http authentication, does not work in CGI mode, only when PHP is installed as an Apache module !
	elseif(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
		$askingUserAddress = LibUser::buildMinimalUserAddress($_SERVER['PHP_AUTH_USER']);
		if($authUser->login($askingUserAddress, $_SERVER['PHP_AUTH_PW']))
			$authenticated = true;
	}
	//authentication way 3: url request authentication
	elseif(isset($requestVars['askingUserAddress']) && isset($requestVars['password'])){
		$askingUserAddress = LibUser::buildMinimalUserAddress($requestVars['askingUserAddress']);
		if($authUser->login($askingUserAddress, base64_decode($requestVars['password'])))
			$authenticated = true;
	}
	if(!$authenticated){ //authentication not OK
		//if(PHP running in module mode)
		header('WWW-Authenticate: Basic realm="API Auth"'); //show login prompt
		LibRest::sendResponse(401);
		exit;
	}

	if(isset($requestVars['askedUserAddress']) && 
			!LibUser::isLocalUserAddress($requestVars['askedUserAddress'])) //don't forward requests for remote user addresses
		LibRest::sendResponse(403, 'The askedUserAddress is not handled by this this server.');

	if(isset($requestVars['askedDocumentAddress']) && 
			!LibDocument::isLocalDocumentAddress($requestVars['askedDocumentAddress'])) //don't forward requests for remote document addresses
		LibRest::sendResponse(403, 'The askedDocumentAddress is not handled by this this server.');
		
	if(isset($requestVars['askedPersonAddress']) && 
			!LibPerson::isLocalPersonAddress($requestVars['askedPersonAddress'])) //don't forward requests for remote person addresses
		LibRest::sendResponse(403, 'The askedPersonAddress is not handled by this this server.');
}

$askingUserAddress = LibUser::buildCanonicalUserAddress($askingUserAddress);

switch($requestVars['action']){
	case 'document_fetchWithoutTag': 
			LibRest::sendResponse(200, 
				json_encode(LibRouter::document_fetchWithoutTag(array($requestVars['askedUserAddress']), $askingUserAddress)), 
				'application/json');

	case 'document_fetchWithTag':
			LibRest::sendResponse(200, 
				json_encode(LibRouter::document_fetchWithTag($requestVars['tag'], array($requestVars['askedUserAddress']), $askingUserAddress)),
				'application/json');

	case 'document_fetchWithSearch':
			LibRest::sendResponse(200, 
				json_encode(LibRouter::document_fetchWithSearch($requestVars['searchString'], array($requestVars['askedUserAddress']), $askingUserAddress)),
				'application/json');

	case 'document_fetchWithAuthor':
			LibRest::sendResponse(200, 
				json_encode(LibRouter::document_fetchWithAuthor($requestVars['authorAddress'], $askingUserAddress)),
				'application/json');
				
	case 'document_fetchLast':
			if(!isset($requestVars['dataType']) || $requestVars['dataType'] == 'json')
				LibRest::sendResponse(200, 
					json_encode(LibRouter::document_fetchLast(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit'])), 
					'application/json');
			elseif($requestVars['dataType'] == 'html'){
				$documents = LibRouter::document_fetchLast(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit']);
				LibRest::sendResponse(200, 
					LibView::documents_lastDocumentRows($documents).' ', //the ' ' is there to avoid empty HTTP answers
					'text/html');
			}

	case 'document_fetch':
			LibRest::sendResponse(200, 
				json_encode(LibRouter::document_fetch($requestVars['askedDocumentAddress'], $askingUserAddress)),
				'application/json');

	case 'document_fetchFileContents':
			LibRest::sendResponse(200, 
				LibRouter::document_fetchFileContents($requestVars['askedDocumentAddress'], $askingUserAddress), 
				'application/octet-stream');

	case 'tag_fetchAll':
			if(!isset($requestVars['dataType']) || $requestVars['dataType'] == 'json')
				LibRest::sendResponse(200, 
					json_encode(LibRouter::tag_fetchAll(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit'])),
					'application/json');
			elseif($requestVars['dataType'] == 'html'){
				$tags = LibRouter::tag_fetchAll(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit']);
				LibRest::sendResponse(200, 
					LibView::documents_tagCloud($tags).' ', //the ' ' is there to avoid empty HTTP answers
					'text/html');
			}
				
	case 'tag_fetchAllBeginningWith': 
		$res = array();
		foreach(LibRouter::tag_fetchNameBeginningWith($requestVars['tag'], array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $tag)
			$res[] = $tag['name'];
		LibRest::sendResponse(200, 
			json_encode($res),
			'application/json');
		break;

	case 'person_fetchAllAuthors':
			if(!isset($requestVars['dataType']) || $requestVars['dataType'] == 'json')
				LibRest::sendResponse(200, 
					json_encode(LibRouter::person_fetchAllAuthors(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit'])),
					'application/json');
			elseif($requestVars['dataType'] == 'html'){
				$authors = LibRouter::person_fetchAllAuthors(array($requestVars['askedUserAddress']), $askingUserAddress, $requestVars['offset'], $requestVars['limit']);
				LibRest::sendResponse(200, 
					LibView::documents_authorCloud($authors).' ', //the ' ' is there to avoid empty HTTP answers
					'text/html');
			}
	

				
	case 'person_fetchAllBeginningWith':
		$persons = LibRouter::person_fetchNameBeginningWith($requestVars['tag'], array($sessionUser->getUserAddress()), $sessionUser->getUserAddress());
		$res = array();
		foreach($persons as $person)
			$res[] = LibString::getPersonNameString($person);
		LibRest::sendResponse(200, 
			json_encode($res),
			'application/json');
		break;

	case 'person_fetch':
			LibRest::sendResponse(200, 
				json_encode(LibRouter::person_fetch($requestVars['askedPersonAddress'], $askingUserAddress)),
				'application/json');
				
	case 'journal_fetchAllBeginningWith': 
		$res = array();
		foreach(LibRouter::journal_fetchNameBeginningWith($requestVars['tag'], array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $tag)
			$res[] = $tag['name'];
		LibRest::sendResponse(200, 
			json_encode($res),	
			'application/json');
		break;
		
	case 'publisher_fetchAllBeginningWith': 
		$res = array();
		foreach(LibRouter::publisher_fetchNameBeginningWith($requestVars['tag'], array($sessionUser->getUserAddress()), $sessionUser->getUserAddress()) as $tag)
			$res[] = $tag['name'];
		LibRest::sendResponse(200, 
			json_encode($res),	
			'application/json');
		break;
		
	case 'user_fetchAllActivatedContaining':
		$res = array();
		foreach(LibUser::fetchAllActivatedContaining($requestVars['tag']) as $user)
			$res[] = $user['username'];
		LibRest::sendResponse(200, 
			json_encode($res),
			'application/json');
		break;
		
	case 'export_word2007':
		if(!is_numeric($requestVars['id']))
			LibExport::printWord2007All($sessionUser);
		else
			LibExport::printWord2007Single($sessionUser, $requestVars['id']);
		break;

	case 'export_bibtex':
		if(!is_numeric($requestVars['id']))
			LibExport::printBibtexAll($sessionUser);
		else
			LibExport::printBibtexSingle($sessionUser, $requestVars['id']);
		break;

	case 'export_bibix':
		if(!is_numeric($requestVars['id']))
			LibExport::printBibixAll($sessionUser);
		else
			LibExport::printBibixSingle($sessionUser, $requestVars['id']);
		break;

	case 'export_ris':
		if(!is_numeric($requestVars['id']))
			LibExport::printRisAll($sessionUser);
		else
			LibExport::printRisSingle($sessionUser, $requestVars['id']);
		break;

	case 'export_modsxml':
		if(!is_numeric($requestVars['id']))
			LibExport::printModsXmlAll($sessionUser);
		else
			LibExport::printModsXmlSingle($sessionUser, $requestVars['id']);
		break;

	default: 
			LibRest::sendResponse(400, 'The parameter "action" is not set correctly. Supported values are: document_fetchLast, document_fetchWithoutTag, document_fetchWithTag, document_fetchWithSearch, document_fetchWithAuthor, document_fetch, document_fetchFileContents, tag_fetchAll, person_fetchAllAuthors, person_fetch');
}