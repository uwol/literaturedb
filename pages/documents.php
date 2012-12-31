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

if(!$sessionUser->isLoggedin())
	die();
?>
<script type="text/javascript">
$(document).ready(function(){
	$("#loadLastDocuments").click(function(event){
		var n = $("#documents tbody tr").length; //number of trs in this table

		$.get("api.php", 
				{action: "document_fetchLast", auth: "session", askedUserAddress: "<?php echo LibGlobal::$selectedUserAddresses[0]; ?>", dataType: "html", offset: "0", limit: n * 2}, 
				function(data){
			$("#documents").empty();
			$("#documents").append(data);
		}, 'html');
		
		event.preventDefault();
	});
	$("#loadTags").click(function(event){
		var n = $("#tagcloud span").length;

		$.get("api.php", 
				{action: "tag_fetchAll", auth: "session", askedUserAddress: "<?php echo LibGlobal::$selectedUserAddresses[0]; ?>", dataType: "html", offset: "0", limit: n * 2}, 
				function(data){
			$("#tagcloud").empty();
			$("#tagcloud").append(data);
		}, 'html');
		
		event.preventDefault();
	});
	$("#loadAuthors").click(function(event){
		var n = $("#authorcloud span").length;

		$.get("api.php", 
				{action: "person_fetchAllAuthors", auth: "session", askedUserAddress: "<?php echo LibGlobal::$selectedUserAddresses[0]; ?>", dataType: "html", offset: "0", limit: n * 2}, 
				function(data){
			$("#authorcloud").empty();
			$("#authorcloud").append(data);
		}, 'html');
		
		event.preventDefault();
	});
});
</script>

<?php
/*
* Actions
*/
if(isset($_GET['mode']) && $_GET['mode'] == "delete" && isset($_GET['documentId']) && is_numeric($_GET['documentId'])){
	$deleted = LibRouter::document_delete($_GET['documentId'], $sessionUser->getUserAddress());
	if($deleted)
		LibGlobal::$notificationTexts[] = 'The document has been deleted.';
}

$searchString = '';
if(isset($_POST['searchString']))
	$searchString = trim($_POST['searchString']);

/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();


if($searchString != ''){ //searched documents
	echo '<h2>Documents fitting the search query ' .LibString::protectXSS($searchString). '</h2>';
	
	$documents = LibRouter::document_fetchWithSearch($searchString, LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());
	if(is_array($documents)){
		echo '<table style="width:100%">';
		foreach($documents as $document){
			echo '<tr>';

			echo '<td style="width:85%;padding-bottom:5px" ' .LibString::getAlienStringClassText(LibDocument::isOwnDocument($document)). '>';
			echo '<a href="index.php?pid=literaturedb_document&amp;documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '">';
			if($document['title'] != '')
				echo LibString::protectXSS(LibString::truncate($document['title'], 50, ' ...'));
			else
				echo 'misssing title';
			echo '</a><br />';
			echo '<div class="authors">' . LibDocument::buildAuthorsString($document) . '</div>';
			echo '<div class="tags">' . LibDocument::buildTagsString($document) . '</div>';
			echo '</td>';
	
			echo '<td style="width:15%;padding-bottom:5px">';
			if($document['date'] > 0)
				echo LibString::protectXSS(substr($document['date'], 0, 4));
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else
		echo $documents;
}


elseif(!isset($_GET['tag']) || $_GET['tag'] == ''){ //last documents
	echo '<p class="exportBox">';
	echo '<img src="img/icons/disk_multiple.png" alt="export" style="height:12px" /> ';
	echo '<a href="api.php?action=export_bibtex&amp;auth=session">Bibtex</a> | ';
	echo '<a href="api.php?action=export_word2007&amp;auth=session">Word</a> | ';
	echo '<a href="api.php?action=export_bibix&amp;auth=session">Endnote</a><br />';
	echo '<a href="api.php?action=export_modsxml&amp;auth=session">MODS XML</a> | ';
	echo '<a href="api.php?action=export_ris&amp;auth=session">RIS</a>';
	echo '</p>';
	
	/*
	* Add
	*/

	echo '<p><img src="img/icons/add.png" alt="add" /> <a href="index.php?pid=literaturedb_document">Add document</a></p>';
	
	/*
	* Documents
	*/
	echo '<h2>Newest documents</h2>';
	
	if(isset($_GET['limitLastDocuments']))
		$documents = LibRouter::document_fetchLast(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress(), 0, $_GET['limitLastDocuments']);
	else
		$documents = LibRouter::document_fetchLast(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());

	if(is_array($documents)){
		echo '<table style="width:100%" id="documents">';
		echo LibView::documents_lastDocumentRows($documents);
		echo '</table>';
	}
	else
		echo LibString::protectXSS($documents);
	
	if(is_array($documents) && count($documents) > 0)
		echo '<div style="text-align:center"><a id="loadLastDocuments" href="index.php?pid=literaturedb_documents&amp;limitLastDocuments=' . count($documents) * 2 . '">show more</a></div>';
}



elseif($_GET['tag'] == '!notag'){ //documents without a tag
	echo '<h2>Documents without a tag</h2>';
	
	$documents = LibRouter::document_fetchWithoutTag(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());
	if(is_array($documents)){
		echo '<table style="width:100%">';
		foreach($documents as $document){
			echo '<tr>';

			echo '<td style="width:85%;padding-bottom:5px" ' .LibString::getAlienStringClassText(LibDocument::isOwnDocument($document)). '>';
			echo '<a href="index.php?pid=literaturedb_document&amp;documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '">';
			if($document['title'] != '')
				echo LibString::protectXSS(LibString::truncate($document['title'], 50, ' ...'));
			else
				echo 'misssing title';
			echo '</a><br />';
			echo '<div class="authors">' . LibDocument::buildAuthorsString($document) . '</div>';
			echo '</td>';
	
			echo '<td style="width:15%;padding-bottom:5px">' . LibString::protectXSS(substr($document['date'], 0, 4)) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else
		echo LibString::protectXSS($documents);
}



else{ //tagged documents
	echo '<h2>Documents with tag ' .LibString::protectXSS($_GET['tag']). '</h2>';
	
	$documents = LibRouter::document_fetchWithTag($_GET['tag'], LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());
	if(is_array($documents)){
		echo '<table style="width:100%">';
		foreach($documents as $document){
			echo '<tr>';
	
			echo '<td style="width:85%;padding-bottom:5px" ' .LibString::getAlienStringClassText(LibDocument::isOwnDocument($document)). '>';
			echo '<a href="index.php?pid=literaturedb_document&amp;documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '">';
			if($document['title'] != '')
				echo LibString::protectXSS(LibString::truncate($document['title'], 50, ' ...'));
			else
				echo 'misssing title';
			echo '</a><br />';
			echo '<div class="authors">' . LibDocument::buildAuthorsString($document) . '</div>';
			echo '<div class="tags">' . LibDocument::buildTagsString($document) . '</div>';
			echo '</td>';
	
			echo '<td style="width:15%;padding-bottom:5px">';
			if($document['date'] > 0)
				echo LibString::protectXSS(substr($document['date'], 0, 4));
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else
		echo LibString::protectXSS($documents);
}


//-------------------------------------------------------------------------------------

echo '<hr />';


/*
* Search
*/
if(!isset($_GET['tag']) || $_GET['tag'] == ''){
	echo '<h2>Search</h2>';
	echo '<div><form method="post" action="index.php?pid=literaturedb_documents"><fieldset>';
	echo '<input type="text" name="searchString" size="50" /> ';
	echo '<input type="submit" value="search" />';
	echo '</fieldset></form></div>';
}




/*
* Users
*/
if((!isset($_GET['tag']) ||$_GET['tag'] == '') && $searchString == ''){
	$shares = LibRouter::share_fetchAllFollowedByLocalUserId($sessionUser->getId(), $sessionUser->getUserAddress());
	if(count($shares) > 0){
		echo '<h2>Follow</h2>';
		echo '<form action="index.php?pid=literaturedb_documents" method="post"><fieldset>';
		echo '<select name="selectedUserAddress">';

		echo '<option>' .LibString::protectXSS($sessionUser->getUsername()). '</option>';	

		foreach($shares as $share){
			$selectString = '';
			if(in_array(LibUser::buildCanonicalUserAddress($share['remote_user_address']), LibGlobal::$selectedUserAddresses))
				$selectString = ' selected="selected" ';
			echo '<option ' .$selectString. '>' .LibString::protectXSS($share['remote_user_address']). '</option>';	
		}
		
		echo '</select>';
		echo ' <input type="submit" value="show" />';
		echo '</fieldset></form>';
	}
}



/*
* Tagcloud
*/
if($searchString == ''){
	echo '<h2>Tags</h2>';
	if(isset($_GET['limitTags']))
		$tags = LibRouter::tag_fetchAll(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress(), 0, $_GET['limitTags']);
	else
		$tags = LibRouter::tag_fetchAll(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());
		
	echo '<div class="tagcloud" id="tagcloud">';
	echo LibView::documents_tagCloud($tags);
	echo '</div>';
	
	if(is_array($tags) && count($tags) > 0)
		echo '<div style="text-align:center"><a id="loadTags" href="index.php?pid=literaturedb_documents&amp;limitTags=' . count($tags) * 2 . '">show more</a></div>';
}



/*
* Authors
*/
if((!isset($_GET['tag']) || $_GET['tag'] == '') && $searchString == ''){
	echo '<h2>Authors</h2>';
	if(isset($_GET['limitAuthors']))
		$authors = LibRouter::person_fetchAllAuthors(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress(), 0, $_GET['limitAuthors']);
	else
		$authors = LibRouter::person_fetchAllAuthors(LibGlobal::$selectedUserAddresses, $sessionUser->getUserAddress());
	
	echo '<div class="tagcloud" id="authorcloud">';
	echo LibView::documents_authorCloud($authors);
	echo '</div>';
	
	if(is_array($authors) && count($authors) > 0)
		echo '<div style="text-align:center"><a id="loadAuthors" href="index.php?pid=literaturedb_documents&amp;limitAuthors=' . count($authors) * 2 . '">show more</a></div>';
}
?>