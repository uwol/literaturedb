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

echo '<h1>Person</h1>';

$personAddress = $_REQUEST['personAddress'];

$mode = '';
if(isset($_REQUEST['mode'])){
	$mode = $_REQUEST['mode'];
}

/*
* Action
*/
if(isset($_POST['action']) && $_POST['action'] == "save" && isset($_POST['lastname']) && $_POST['lastname'] != ''){
	$person = array();
	$person['id'] = $_POST['id'];
	$person['firstname'] = $_POST['firstname'];
	$person['prefix'] = $_POST['prefix'];
	$person['lastname'] = $_POST['lastname'];
	$person['suffix'] = $_POST['suffix'];
	$person['user_id'] = $sessionUser->id;

	LibRouter::person_save($person, $sessionUser->getUserAddress());
}

/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();


$person = LibRouter::person_fetch($personAddress, $sessionUser->getUserAddress());

if(is_array($person) && isset($person['id']) && is_numeric($person['id'])){
	if(!isOwnPerson($person)){
		echo '<div class="alien">';
	}

	//Edit person
	if(isOwnPerson($person) && $mode == 'edit'){
		echo '<form method="post" action="index.php?pid=literaturedb_person">';
		echo '<fieldset>';
		echo '<input type="hidden" name="action" value="save" />';
		echo '<input type="hidden" name="id" value="' .LibString::protectXSS($person['id']). '" />';
		echo '<input type="hidden" name="personAddress" value="' .LibString::protectXSS($person['person_address']). '" />';
		echo '<input type="text" name="firstname" value="' .LibString::protectXSS($person['firstname']). '" size="30" /> Firstname<br />';
		echo '<input type="text" name="prefix" value="' .LibString::protectXSS($person['prefix']). '" size="15" /> Prefix<br />';
		echo '<input type="text" name="lastname" value="' .LibString::protectXSS($person['lastname']). '" size="30" /> Lastname<br />';
		echo '<input type="text" name="suffix" value="' .LibString::protectXSS($person['suffix']). '" size="15" /> Suffix<br />';
		echo '<input type="submit" value="Save" />';
		echo '</fieldset>';
		echo '</form>';
	}
	//Output
	else {
		echo '<p>';
		echo LibString::protectXSS($person['firstname']). ' ' .LibString::protectXSS($person['prefix']). ' ' .LibString::protectXSS($person['lastname']). ' ' .LibString::protectXSS($person['suffix']);

		if(isOwnPerson($person)){
			echo ' <a href="index.php?pid=literaturedb_person&amp;mode=edit&amp;personAddress=' .LibString::protectXSS($person['person_address']). '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
		}

		echo ' </p>';
		echo '<h2>Documents written by this person</h2>';

		$documents = LibRouter::document_fetchWithAuthor($personAddress, $sessionUser->getUserAddress());

		echo '<table style="width:100%">';

		if(is_array($documents)){
			foreach($documents as $document){
				echo '<tr>';
				echo '<td style="padding-bottom:5px" ' .getAlienStringClass(isOwnDocument($document)). '><a href="index.php?pid=literaturedb_document&amp;documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '">';

				if($document['title'] != ''){
					echo LibString::protectXSS(LibString::truncate($document['title'], 50, ' ...'));
				} else {
					echo 'missing title';
				}

				echo '</a><br />';
				echo '<div class="authors">' . LibDocument::buildAuthorsString($document) . '</div>';
				echo '<div class="tags">' . LibDocument::buildTagsString($document) . '</div>';
				echo '</td>';

				echo '<td style="width:10%;padding-bottom:5px">';

				if($document['date'] > 0){
					echo LibString::protectXSS(substr($document['date'], 0, 4));
				}

				echo '</td>';
				echo '</tr>';

			}

			echo '</table>';
		} else {
			echo LibString::protectXSS($documents);
		}
	}

	if(!isOwnPerson($person)){
		echo '</div>';
	}
} else {
	echo LibString::protectXSS($person);
}

/*
* Helper
*/
function isOwnDocument($document){
	global $sessionUser;

	if(LibDocument::isLocalDocumentAddress($document['document_address']) && $document['user_id'] == $sessionUser->id){
		return true;
	}

	return false;
}

function isOwnPerson($person){
	global $sessionUser;

	if(LibPerson::isLocalPersonAddress($person['person_address']) && $person['user_id'] == $sessionUser->id){
		return true;
	}

	return false;
}

function getAlienString($isOwn){
	if(!$isOwn){
		return ' alien ';
	}

	return '';
}

function getAlienStringClass($isOwn){
	if(!$isOwn){
		return ' class="alien" ';
	}

	return '';
}
?>