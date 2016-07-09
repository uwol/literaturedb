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
  $('#users').tagSuggest({
    url: '/api.php?action=user_fetchAllActivatedContaining&auth=session',
    delay: 1200
  });
});
</script>

<?php
/*
* Actions
*/
if(isset($_GET['action']) && $_GET['action'] == 'deleteShare' && isset($_GET['shareId']) && is_numeric($_GET['shareId'])){
	LibRouter::share_delete($_GET['shareId'], $sessionUser->getUserAddress());
	$_SESSION['selectedUserAddresses'] = array($sessionUser->getUserAddress()); //reset selected user addresses
}

if(isset($_REQUEST['action']) &&
		$_REQUEST['action'] == 'saveShare' &&
		isset($_REQUEST['remoteUserAddress']) &&
		LibUser::isValidUserAddress(LibUser::buildCanonicalUserAddress(trim($_REQUEST['remoteUserAddress']))) &&
		LibUser::buildCanonicalUserAddress(trim($_REQUEST['remoteUserAddress'])) != $sessionUser->getUserAddress()){

	$sharing = isset($_REQUEST['sharing']) && $_REQUEST['sharing'] ? 1 : 0;
	$following = isset($_REQUEST['following']) && $_REQUEST['following'] ? 1 : 0;

	$share = array();
	$share['local_user_id'] = $sessionUser->getId();
	$share['remote_user_address'] = LibUser::buildMinimalUserAddress(trim($_REQUEST['remoteUserAddress']));
	$share['following'] = $following;
	$share['sharing'] = $sharing;
	LibRouter::share_save($share, $sessionUser->getUserAddress());

	$_SESSION['selectedUserAddresses'] = array($sessionUser->getUserAddress()); //reset selected user addresses
}

/*
* Output
*/
echo LibString::getNotificationBoxText();
echo LibString::getErrorBoxText();

/*
* shares
*/
echo '<h1>Collaborate</h1>';
echo '<p>You can follow other users and share documents with them.</p>';

echo '<table>';

$shares = LibRouter::share_fetchAllByLocalUserId($sessionUser->getId(), $sessionUser->getUserAddress());

if(count($shares) > 0){
	echo '<tr><td></td><td style="text-align:center">follow <br /><i class="fa fa-plug" aria-hidden="true"></i></td><td style="text-align:center">share <br /><i class="fa fa-share-alt" aria-hidden="true"></i></td></tr>';
}

foreach($shares as $share){
	echo '<tr>';
	echo '<form action="index.php?pid=literaturedb_collaborate" method="post">';
	echo '<input type="hidden" name="action" value="saveShare" />';
	echo '<td><input type="hidden" name="remoteUserAddress" value="' .LibString::protectXSS($share['remote_user_address']). '" />'. LibString::protectXSS($share['remote_user_address']) .'</td>';
	echo '<td style="text-align:center"><input type="checkbox" name="following"';

	if($share['following']){
		echo ' checked="checked" ';
	}

	echo '/></td>';
	echo '<td style="text-align:center"><input type="checkbox" name="sharing"';

	if($share['sharing']){
		echo ' checked="checked" ';
	}

	echo '/></td>';
	echo '<td><a href="index.php?pid=literaturedb_collaborate&amp;action=deleteShare&amp;shareId=' .LibString::protectXSS($share['id']). '" onclick="return confirm(\'Are you sure you want to delete this share?\')"><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>';
	echo '<td><input type="submit" value="save" /></td>';
	echo '</form>';
	echo '</tr>';
}

// divider
if(count($shares) > 0){
	echo '<tr><td colspan="5" style="border-top:1px solid black"></td></tr>';
}

$shares = LibRouter::share_fetchAllByLocalUserId($sessionUser->getId(), $sessionUser->getUserAddress());
$smallOrgaInterface = true;

if(count($shares) > 20){
	$smallOrgaInterface = false;
}

// small organization?
if($smallOrgaInterface){
	// all users not added as shares, yet
	foreach(LibRouter::user_fetchAll($sessionUser->getUserAddress()) as $user){
		if(($user['activated'] || $user['is_admin']) && !array_key_exists($user['username'], $shares) && $user['username'] != $sessionUser->username){
			echo '<tr>';
			echo '<td>'. LibString::protectXSS($user['username']) .'</td>';
			echo '<td></td><td></td>';
			echo '<td>';
			echo '<a href="index.php?pid=literaturedb_collaborate&amp;action=saveShare&amp;remoteUserAddress=' .LibString::protectXSS($user['username']). '&amp;following=1&amp;sharing=1"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>';
			echo '</td>';
			echo '</tr>';
		}
	}
}

// input for arbitrary user addresses
echo '<form action="index.php?pid=literaturedb_collaborate" method="post"><fieldset style="border: 0px">';
echo '<tr>';
echo '<td>';
echo '<input type="hidden" name="action" value="saveShare" />';
echo '<input type="hidden" name="following" value="1" />';
echo '<input type="hidden" name="sharing" value="1" />';

$value = '';
if($smallOrgaInterface){
	$value = 'username@someremotedomain.org';
}

echo '<input type="text" id="users" name="remoteUserAddress" size="40" value="' .$value. '" /> ';
echo '</td>';
echo '<td style="text-align:center"><i class="fa fa-lightbulb-o" aria-hidden="true" title="External users can be added by typing in their user address. E.g. your user address is '. LibString::protectXSS($sessionUser->getUserAddress()) .'" style="margin:0;vertical-align:middle"></i></td>';
echo '<td></td>';
echo '<td><input type="submit" value="add" /></td>';
echo '</tr>';
echo '</fieldset></form>';

echo '</table>';

if(!$smallOrgaInterface){
	echo '<p>You can search for users by typing in their first name, last name, username or email address.</p>';
}
?>