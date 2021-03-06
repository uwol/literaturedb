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

use literaturedb\LibCronjobs;
use literaturedb\LibDb;
use literaturedb\LibDocument;
use literaturedb\LibExport;
use literaturedb\LibGlobal;
use literaturedb\LibJournal;
use literaturedb\LibMime;
use literaturedb\LibPerson;
use literaturedb\LibPublisher;
use literaturedb\LibRest;
use literaturedb\LibRouter;
use literaturedb\LibShare;
use literaturedb\LibString;
use literaturedb\LibTag;
use literaturedb\LibUser;
use literaturedb\LibView;


if(!$sessionUser->isLoggedin())
	die();

?>
<script type="text/javascript">
$(function (){
  $('#tags').tagSuggest({
    url: '/api.php?action=tag_fetchAllBeginningWith&auth=session',
    delay: 1200
  });
  $('#authors').tagSuggest({
    url: '/api.php?action=person_fetchAllBeginningWith&auth=session',
    delay: 1200
  });
  $('#editors').tagSuggest({
    url: '/api.php?action=person_fetchAllBeginningWith&auth=session',
    delay: 1200
  });
  $('#journal_name').tagSuggest({
    url: '/api.php?action=journal_fetchAllBeginningWith&auth=session',
    delay: 1200
  });
  $('#publisher_name').tagSuggest({
    url: '/api.php?action=publisher_fetchAllBeginningWith&auth=session',
    delay: 1200
  });
});
</script>

<?php
$documentAddress = '';
if(isset($_REQUEST['documentAddress'])){
	$documentAddress = LibDocument::buildCanonicalDocumentAddress($_REQUEST['documentAddress']);
}

$mode = '';
if(isset($_REQUEST['mode'])){
	$mode = $_REQUEST['mode'];
}



/*
* Actions
*/

// adding a document
if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'document_create' || $_REQUEST['action'] == 'document_copy')){
	$documentAddress = '';
	if(isset($_GET['documentAddress'])){
		$documentAddress = trim($_GET['documentAddress']);
	}

	// ---------------------------------------------------

	/*
	* copy file contents and determine hash
	*/
	$hash = '';

	if($documentAddress != ''){
		// a document/file is copied locally
		if(LibDocument::isLocalDocumentAddress($documentAddress)){ //local file?
			$document = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());
			$hash = $document['hash'];
		}
		// a document/file is copied from remote
		else {
			$hash = sha1(LibRouter::document_fetchFileContents($documentAddress, $sessionUser->getUserAddress()));

			if(!is_file(LibDocument::getFilePath($hash))){ //file does not exist yet?
				$handle = fopen(LibDocument::getFilePath($hash), 'w+');
				fwrite($handle, LibRouter::document_fetchFileContents($documentAddress, $sessionUser->getUserAddress()));
				fclose($handle);
			}
		}
	}
	// a file could have been uploaded
	else {
		$hash = storeAndHashUploadedFile();
	}

	// ---------------------------------------------------

	/*
	* try to identify the document in the user context by the hash -> check, whether the user already owns the file
	*/
	$document = LibRouter::document_fetchByHash($hash, $sessionUser->getUserAddress(), $sessionUser->getUserAddress());

	/*
	* try to identify the document by the document address
	*/
	if(!is_array($document)){
		if($documentAddress != ''){
			// try to fetch information from the original owner the file was copied from (case 2 or case 3)
			$document = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());
			$document['id'] = '';
		}
	}

	/*
	* try to generate document meta information from an uploaded file
	*/
	if(!is_array($document)){
		$document = array();

		// derive document data from the hash
		$document['hash'] = $hash;

		// derive document data from the uploaded file
		$pathinfo = getPathinfoOfUploadedFile();
		$document['title'] = is_array($pathinfo) && isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
		$document['filename'] = is_array($pathinfo) && isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
		$document['extension'] = is_array($pathinfo) && isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
	}

	// ---------------------------------------------------

	/*
	* set up user ids of document elements
	*/
	$document['user_id'] = $sessionUser->id;

	if(isset($document['tags']) && is_array($document['tags'])){
		$newTags = array();

		foreach($document['tags'] as $tag){
			$tag['user_id'] = $sessionUser->id;
			$newTags[] = $tag;
		}

		$document['tags'] = $newTags;
	}

	if(isset($document['authors']) && is_array($document['authors'])){
		$newAuthors = array();

		foreach($document['authors'] as $author){
			$author['user_id'] = $sessionUser->id;
			$newAuthors[] = $author;
		}

		$document['authors'] = $newAuthors;
	}

	if(isset($document['editors']) && is_array($document['editors'])){
		$newEditors = array();

		foreach($document['editors'] as $editor){
			$editor['user_id'] = $sessionUser->id;
			$newEditors[] = $editor;
		}

		$document['editors'] = $newEditors;
	}

	// ---------------------------------------------------

	//save document
	$documentAddress = LibDocument::buildCanonicalDocumentAddress(LibRouter::document_save($document, $sessionUser->getUserAddress()));

	// save file info
	$documentByAddress = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());
	LibDocument::saveFileInfo($documentByAddress['id'], $document['hash'], $document['filename'], $document['extension']);

	$mode = 'edit';
}


if(isset($_POST['action']) && $_POST['action'] == "document_save"){
	$document = array();

	$document['id'] = $_POST['id'];
	$document['entrytype_id'] = $_POST['entrytype_id'];
	$document['title'] = $_POST['title'];
	$document['date'] = $_POST['date'];
	$document['abstract'] = $_POST['abstract'];

	$document['address'] = $_POST['address'];
	$document['booktitle'] = $_POST['booktitle'];
	$document['chapter'] = $_POST['chapter'];
	$document['doi'] = $_POST['doi'];
	$document['ean'] = $_POST['ean'];
	$document['edition'] = $_POST['edition'];
	$document['institution'] = $_POST['institution'];
	$document['journal_name'] = $_POST['journal_name'];
	$document['number'] = $_POST['number'];
	$document['organization'] = $_POST['organization'];
	$document['pages'] = $_POST['pages'];
	$document['publisher_name'] = $_POST['publisher_name'];
	$document['school'] = $_POST['school'];
	$document['series'] = $_POST['series'];
	$document['url'] = $_POST['url'];
	$document['volume'] = $_POST['volume'];
	$document['note'] = $_POST['note'];
	$document['rating'] = $_POST['rating'];

	$document['tags'] = LibString::parseTagString($_POST['tags'], $sessionUser->id);
	$document['authors'] = LibString::parsePersonNameString($_POST['authors'], $sessionUser->id);
	$document['editors'] = LibString::parsePersonNameString($_POST['editors'], $sessionUser->id);

	$document['user_id'] = $sessionUser->id;

	//save the document
	$documentAddress = LibDocument::buildCanonicalDocumentAddress(LibRouter::document_save($document, $sessionUser->getUserAddress()));

	// save file info
	$documentByAddress = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());

	LibGlobal::$notificationTexts[] = 'The document has been saved.';

	// if no file has been appended to this document, yet
	if($documentByAddress['hash'] == ''){
		$hash = storeAndHashUploadedFile();

		// if a file has been uploaded
		if($hash != ''){
			// try to load the document in the user context by the hash -> check, whether the user already owns the file
			$documentByHash = LibRouter::document_fetchByHash($hash, $sessionUser->getUserAddress(), $sessionUser->getUserAddress());

			// no document data found by hash? -> user does not own this document, yet
			if(!is_array($documentByHash) || !isset($documentByHash['id']) || $documentByHash['id'] == ''){
				// derive document data from the uploaded file
				$pathinfo = getPathinfoOfUploadedFile();
				if(is_array($pathinfo)){
					$filename = isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
					$extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

					// save file info
					LibDocument::saveFileInfo($document['id'], $hash, $filename, $extension);
				} else {
					LibGlobal::$errorTexts[] = 'Could not determine file information of uploaded file.';
				}
			} else {
				LibGlobal::$errorTexts[] = 'The file is already appended to document ' .$documentByHash['title']. '.';
			}
		}
	}
}




/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();



if(LibDocument::isValidDocumentAddress($documentAddress)){
	$document = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());

	if(is_array($document)){//could the document be loaded?
		$ownDocument = false;
		if(LibDocument::isLocalDocumentAddress($documentAddress) && $document['user_id'] == $sessionUser->id)
			$ownDocument = true;

		if(!$ownDocument){
			echo '<div class="alien">';
		}

		//-------------------------------------------------------------------------
		/*
		* Show mode
		*/
		if($mode != 'edit'){
			if($ownDocument){
				//show export buttons
				echo '<p class="exportBox">';
				echo '<i class="fa fa-floppy-o" aria-hidden="true"></i> ';
				echo '<a href="api.php?action=export_bibtex&amp;auth=session&amp;id=' .LibString::protectXSS($document['id']). '">Bibtex</a> | ';
				echo '<a href="api.php?action=export_word2007&amp;auth=session&amp;id=' .LibString::protectXSS($document['id']). '">Word</a> | ';
				echo '<a href="api.php?action=export_bibix&amp;auth=session&amp;id=' .LibString::protectXSS($document['id']). '">Endnote</a><br />';
				echo '<a href="api.php?action=export_modsxml&amp;auth=session&amp;id=' .LibString::protectXSS($document['id']). '">MODS XML</a> | ';
				echo '<a href="api.php?action=export_ris&amp;auth=session&amp;id=' .LibString::protectXSS($document['id']). '">RIS</a>';
				echo '</p>';
			}

			echo '<h1>Show document</h1>';

			echo '<p>';

			if($ownDocument){
				echo '<a href="index.php?pid=literaturedb_document&amp;mode=edit&amp;documentAddress=' .LibString::protectXSS($documentAddress). '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> ';
			} else {
				echo '<a href="index.php?pid=literaturedb_document&amp;action=document_copy&amp;documentAddress=' .LibString::protectXSS($documentAddress). '"><i class="fa fa-clone" aria-hidden="true"></i></a> ';
			}

			if($ownDocument){
				echo '<a href="index.php?pid=literaturedb_documents&amp;mode=delete&amp;documentId=' .LibString::protectXSS($document['id']). '" onclick="return confirm(\'Are you sure you want to delete this document?\')"><i class="fa fa-trash-o" aria-hidden="true"></i></a> ';
			}

			echo '</p>';

			echo '<h2>Document information</h2>';

			echo '<table style="width:100%">';
			echo '<tr><td style="width:18%">Entry type:</td><td>' .LibString::protectXSS($document['entrytype_name']). '</td></tr>';
			echo '<tr><td>Title:</td><td>' .LibString::protectXSS($document['title']). '</td></tr>';

			if($document['date'] > 0){
				echo '<tr><td>Date:</td><td>' .LibString::protectXSS($document['date']). '</td></tr>';
			}

			echo '<tr><td>Authors:</td><td>'. LibDocument::buildAuthorsString($document).'</td></tr>';

			if($document['abstract']){
				echo '<tr><td>Abstract:</td><td>' .nl2br(LibString::protectXSS($document['abstract'])). '</td></tr>';
			}

			echo '<tr><td>Tags:</td><td>' .LibDocument::buildTagsString($document). '</td></tr>';
			echo '<tr><td>Created:</td><td>' .LibString::protectXSS($document['datetime_created']). '</td></tr>';

			/*
			* bibtex meta data
			*/
			echo '<tr><td colspan="2"><hr /></td></tr>';

			if($document['address']){
				echo '<tr><td>Address:</td><td>' .LibString::protectXSS($document['address']). '</td></tr>';
			}

			if($document['booktitle']){
				echo '<tr><td>Booktitle:</td><td>' .LibString::protectXSS($document['booktitle']). '</td></tr>';
			}

			if($document['chapter']){
				echo '<tr><td>Chapter:</td><td>' .LibString::protectXSS($document['chapter']). '</td></tr>';
			}

			if($document['doi']){
				echo '<tr><td>DOI:</td><td>' .LibString::protectXSS($document['doi']). '</td></tr>';
			}

			if($document['ean']){
				echo '<tr><td>EAN:</td><td>' .LibString::protectXSS($document['ean']). '</td></tr>';
			}

			if($document['edition']){
				echo '<tr><td>Edition:</td><td>' .LibString::protectXSS($document['edition']). '</td></tr>';
			}

			if(is_array($document['editors']) && count($document['editors']) > 0){
				echo '<tr><td>Editors:</td><td>'. LibDocument::buildEditorsString($document). '</td></tr>';
			}

			if($document['institution']){
				echo '<tr><td>Institution:</td><td>' .LibString::protectXSS($document['institution']). '</td></tr>';
			}

			if($document['journal_name']){
				echo '<tr><td>Journal:</td><td>' .LibString::protectXSS($document['journal_name']). '</td></tr>';
			}

			if($document['number']){
				echo '<tr><td>Number / Issue:</td><td>' .LibString::protectXSS($document['number']). '</td></tr>';
			}

			if($document['organization']){
				echo '<tr><td>Organization:</td><td>' .LibString::protectXSS($document['organization']). '</td></tr>';
			}

			if($document['pages']){
				echo '<tr><td>Pages:</td><td>' .LibString::protectXSS($document['pages']). '</td></tr>';
			}

			if($document['publisher_name']){
				echo '<tr><td>Publisher:</td><td>' .LibString::protectXSS($document['publisher_name']). '</td></tr>';
			}

			if($document['school']){
				echo '<tr><td>School:</td><td>' .LibString::protectXSS($document['school']). '</td></tr>';
			}

			if($document['series']){
				echo '<tr><td>Series:</td><td>' .LibString::protectXSS($document['series']). '</td></tr>';
			}

			if($document['url']){
				echo '<tr><td>Url:</td><td><a href="' .LibString::protectXSS($document['url']). '">' .LibString::protectXSS($document['url']). '</a></td></tr>';
			}

			if($document['volume']){
				echo '<tr><td>Volume:</td><td>' .LibString::protectXSS($document['volume']). '</td></tr>';
			}

			if($document['note']){
				echo '<tr><td>Note:</td><td>' .nl2br(LibString::protectXSS($document['note'])). '</td></tr>';
			}

			if($document['rating'] > 0){
				echo '<tr><td>Rating:</td><td>';

				for($i=0;$i<5;$i++){
					if($i < $document['rating']){
						echo '<i class="fa fa-star" aria-hidden="true"></i>';
					} else {
						echo '<i class="fa fa-star-o" aria-hidden="true"></i>';
					}
				}

				echo '</td></tr>';
			}

			echo '</table>';

			/*
			* references
			*/
			echo '<h2>References</h2>';
			echo '<table style="width:100%">';
			echo '<tr><td>Bibtex:</td><td>'.LibString::protectXSS(LibDocument::getId_Bibtex($document)).'</td></tr>';
			echo '<tr><td>MODS XML:</td><td>'.LibString::protectXSS(LibDocument::getId_ModsXml($document['id'])).'</td></tr>';
			echo '<tr><td style="width:18%">Word:</td><td>'.LibString::protectXSS(LibDocument::getId_Word2007($document['id'])).'</td></tr>';
			echo '</table>';

			// if the document has a hash, i. e. has a file
			if($document['hash'] != ''){
				/*
				* static file meta infos
				*/
				echo '<h2>File meta information</h2>';
				echo '<table style="width:100%">';
				echo '<tr><td style="width:18%">Filename:</td><td>' .
				LibString::protectXSS(substr(LibString::truncate($document['filename'], 50, ''), 0, 50)). '.' .LibString::protectXSS($document['extension']). '</td></tr>';

				$filesize = $document['filesize'] == '' ? '' : round($document['filesize'] / 1000, 0) . ' KB';

				echo '<tr><td>Size:</td><td>' .$filesize. '</td></tr>';
				echo '<tr><td>Hash:</td><td>' .LibString::protectXSS($document['hash']). '</td></tr>';
				echo '<tr><td colspan="2"><a href="download.php?documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '&amp;mode=literaturedb_document">Download document</a></td></tr>';
				echo '</table>';
			}
		}
		//-------------------------------------------------------------------------
		/*
		* edit mode
		*/
		else {
			echo '<h1>Edit document</h1>';

			echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=literaturedb_document">';
			echo '<fieldset>';

			if($ownDocument){
				echo '<input type="submit" value="save" />';
			} else {
				echo '<input type="submit" value="copy" />';
			}

			echo '<br /><hr />';


			echo '<input type="hidden" name="action" value="document_save" />';
			echo '<input type="hidden" name="id" value="' .LibString::protectXSS($document['id']). '" />';

			echo '<input type="hidden" name="documentAddress" value="' .LibString::protectXSS($documentAddress). '" />';

			//Entry type
			echo '<select name="entrytype_id">';

			foreach(LibDocument::fetchAllEntryTypes() as $key => $value){
				echo '<option ';

				if($key == $document['entrytype_id']){
					echo 'selected="selected" ';
				}

				echo 'value="' .$key. '">' .$value;
				echo '</option>';
			}

			echo '</select> Entry type<br />';

			echo '<hr />';


			echo '<input type="text" name="title" value="' .LibString::protectXSS($document['title']). '" size="60" /> Title<br />';

			echo '<input type="text" name="date" value="' .LibString::protectXSS($document['date']). '" size="10" /> Date<br />';

			echo '<div>Authors <i class="fa fa-lightbulb-o" aria-hidden="true" title="E.g. &quot;Friedrich August von Hayek and Winston Leonard {Spencer Churchill}&quot;"></i><br />';
			echo '<input type="text" name="authors" id="authors" value="' .LibString::getPersonsNameString($document['authors']). '" size="75" /></div>';

			echo 'Abstract<br />';

			echo '<textarea name="abstract" cols="60" rows="4">' . LibString::protectXSS($document['abstract']) . '</textarea><br />';
			echo '<div>Tags <i class="fa fa-lightbulb-o" aria-hidden="true" title="E.g. &quot;city newyork webdesign&quot;"></i>';

			$tagNames = array();

			foreach($document['tags'] as $tag){
				$tagNames[] = LibString::protectXSS($tag['name']);
			}

			echo '<br /><input type="text" name="tags" id="tags" value="' .implode(' ', $tagNames). '" size="75" /></div>';


			/*
			* bibtex meta data
			*/
			echo '<hr />';

			echo '<input type="text" name="address" value="' .LibString::protectXSS($document['address']). '" size="40" /> Address<br />';
			echo '<input type="text" id="booktitle" name="booktitle" value="' .LibString::protectXSS($document['booktitle']). '" size="40" /> Booktitle <i class="fa fa-lightbulb-o" aria-hidden="true" title="Used as the conference name in Word 2007 export."></i><br />';
			echo '<input type="text" name="chapter" value="' .LibString::protectXSS($document['chapter']). '" size="40" /> Chapter<br />';
			echo '<input type="text" name="doi" value="' .LibString::protectXSS($document['doi']). '" size="40" /> DOI<br />';
			echo '<input type="text" name="ean" value="' .LibString::protectXSS($document['ean']). '" size="40" /> Ean<br />';
			echo '<input type="text" name="edition" value="' .LibString::protectXSS($document['edition']). '" size="40" /> Edition<br />';

			echo '<div>Editors <i class="fa fa-lightbulb-o" aria-hidden="true" title="E.g. &quot;Friedrich August von Hayek and Winston Leonard {Spencer Churchill}&quot;"></i><br />';
			echo '<input type="text" name="editors" id="editors" value="' .LibString::protectXSS(LibString::getPersonsNameString($document['editors'])). '" size="50" /></div>';

			echo '<input type="text" name="institution" value="' .LibString::protectXSS($document['institution']). '" size="40" /> Institution<br />';
			echo 'Journal <div><input type="text" name="journal_name" id="journal_name" value="' .LibString::protectXSS($document['journal_name']). '" size="50" /></div>';
			echo '<input type="text" name="number" value="' .LibString::protectXSS($document['number']). '" size="40" /> Number / Issue<br />';
			echo '<input type="text" name="organization" value="' .LibString::protectXSS($document['organization']). '" size="40" /> Organization<br />';
			echo '<input type="text" name="pages" value="' .LibString::protectXSS($document['pages']). '" size="40" /> Pages<br />';
			echo 'Publisher <div><input type="text" name="publisher_name" id="publisher_name" value="' .LibString::protectXSS($document['publisher_name']). '" size="50" /></div>';
			echo '<input type="text" name="school" value="' .LibString::protectXSS($document['school']). '" size="40" /> School<br />';
			echo '<input type="text" name="series" value="' .LibString::protectXSS($document['series']). '" size="40" /> Series<br />';
			echo '<input type="text" name="url" value="' .LibString::protectXSS($document['url']). '" size="40" /> Url<br />';
			echo '<input type="text" name="volume" value="' .LibString::protectXSS($document['volume']). '" size="40" /> Volume<br />';

			echo 'Note<br /><textarea name="note" cols="60" rows="4">' . LibString::protectXSS($document['note']) . '</textarea><br />';
			echo '<input type="text" name="rating" value="' .LibString::protectXSS($document['rating']). '" size="1" /> Rating (1-5)';

			echo '<hr />';

			if($document['hash'] == '' && $ownDocument){
				echo '<input name="file" type="file" size="30" /> Append a file (max. ' .calculateMaxFilesize(). ' MB)<br />';
				echo '<hr />';
			}

			if($ownDocument){
				echo '<input type="submit" value="save" />';
			} else {
				echo '<input type="submit" value="copy" />';
			}

			echo '</fieldset>';

			echo '</form>';
		}
		//-------------------------------------------------------------------------
		if(!$ownDocument){
			echo '</div>';
		}
	} else {
		echo LibString::protectXSS($document);
	}
} else {
	echo '<h1>Add a document</h1>';

	echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=literaturedb_document">';
	echo '<fieldset>';

	echo '<p>Append a file (max. ' .calculateMaxFilesize(). ' MB): <input name="file" type="file" size="30" /></p>';
	echo '<p><input type="submit" value="Add a document" /></p>';

	echo '<input type="hidden" name="action" value="document_create" />';

	echo '</fieldset>';
	echo '</form>';
}


// ---------------------------------------------------------------

function calculateMaxFilesize(){
	$memoryLimit = (int) substr(ini_get("memory_limit"), 0, -1);
	$maxFilesize = $memoryLimit / 6;
	return round($maxFilesize, 0);
}

function storeAndHashUploadedFile(){
	$hash = '';

	// if a file has been uploaded
	if(isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != ''){
		$hash = sha1_file($_FILES['file']['tmp_name']);

		if($hash == ''){
			LibGlobal::$errorTexts[] = 'Could not calculate the hash of the uploaded file.';
		}
		// file does not exist yet?
		elseif(!is_file(LibDocument::getFilePath($hash))){
			//copy the file
			copy($_FILES['file']['tmp_name'], LibDocument::getFilePath($hash));
		}
	}

	return $hash;
}

function getPathinfoOfUploadedFile(){
	if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
		return pathinfo($_FILES['file']['name']);
	}
}
?>