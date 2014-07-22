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

class LibView{
	static function documents_lastDocumentRows($documents) {
		$retstr = '';
		if(is_array($documents)){
			foreach($documents as $document){
				$retstr .= '<tr>';
	
				$retstr .= '<td style="width:85%;padding-bottom:5px" ' .LibString::getAlienStringClassText(LibDocument::isOwnDocument($document)). '>';
				$retstr .= '<a href="index.php?pid=literaturedb_document&amp;documentAddress=' .LibString::protectXSS(LibDocument::buildMinimalDocumentAddress($document['document_address'])). '">';
				if($document['title'] != '')
					$retstr .= LibString::protectXSS(LibString::truncate($document['title'], 50, ' ...'));
				else
					$retstr .= 'missing title';
				$retstr .= '</a><br />';
				$retstr .= '<div class="authors">' . LibDocument::buildAuthorsString($document) . '</div>';
				$retstr .= '<div class="tags">' . LibDocument::buildTagsString($document) . '</div>';
				$retstr .= '</td>';
		
				$retstr .= '<td style="width:15%;padding-bottom:5px">' . LibString::protectXSS(substr($document['datetime_upload'], 0, 10)) . '</td>';
				$retstr .= '</tr>';
			}
		}
		return $retstr;
	}
	
	static function documents_tagCloud($tags){
		$retstr = '';
		$tagLinks = array();	

		if(is_array($tags)){
			$tagLinks[] = '<span class="weight9"><a href="index.php?pid=literaturedb_documents&amp;tag=!notag">!notag</a></span>';

			foreach($tags as $tag){
				$tagLinks[] = '<span class="weight' . $tag['weight_relative'] . LibString::getAlienStringText(LibTag::isOwnTag($tag)). '"><a href="index.php?pid=literaturedb_documents&amp;tag=' .LibString::protectXSS($tag['name']). '">' . LibString::protectXSS($tag['name']) . '</a></span>';
			}
		}
		else
			$retstr .= $tags;
		if(count($tagLinks) > 0)
			$retstr .= implode(" ", $tagLinks);

		return $retstr;
	}
	
	static function documents_authorCloud($authors){
		$retstr = '';
		$authorLinks = array();		

		if(is_array($authors)){
			foreach($authors as $author){
				$authorLinks[] = '<span class="weight' . $author['weight_relative'] . LibString::getAlienStringText(LibPerson::isOwnPerson($author)) . '"><a href="index.php?pid=literaturedb_person&amp;personAddress=' .LibString::protectXSS(LibPerson::buildMinimalPersonAddress($author['person_address'])). '">' . LibString::protectXSS($author['lastname']) . '</a></span>';
			}
		}
		else
			$retstr .= $authors;

		if(count($authorLinks) > 0)
			$retstr .= implode(" ", $authorLinks);

		return $retstr;
	}
}
?>