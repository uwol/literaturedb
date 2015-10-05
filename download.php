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
include "lib/initialize.php";
if(!$sessionUser->isLoggedIn())
	die();

if($_GET['mode'] == 'literaturedb_document'){
	$documentAddress = $_GET['documentAddress'];
	$document = LibRouter::document_fetch($documentAddress, $sessionUser->getUserAddress());
	
	$mime = LibMime::determineMime($document['extension']);
	$extensionString = $document['extension'] != '' ? '.' . $document['extension'] : '';
	$filename = $document['filename'] . $extensionString;

	/*
	* disable caching
	*/
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	// mime
	header('Content-Type: ' . $mime);
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	//header('Content-Length: ' . $document['filesize']);

	echo LibRouter::document_fetchFileContents($documentAddress, $sessionUser->getUserAddress());
}
?>