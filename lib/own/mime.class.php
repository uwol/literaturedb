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

class LibMime{
	static function determineMime($extension){
  		$mime = '';

  		switch ($extension) {
   			case 'avi': $mime='video/x-msvideo'; break;
   			case 'bmp': $mime='image/bmp'; break;
   			case 'doc': $mime='application/msword'; break;
   			case 'eps': $mime='application/postscript'; break;
   			case 'gif': $mime='image/gif'; break;
   			case 'htm': $mime='text/html'; break;
   			case 'html': $mime='text/html'; break;
   			case 'jpeg': $mime='image/jpeg'; break;
   			case 'jpg': $mime='image/jpeg'; break;
   			case 'mid': $mime='audio/midi'; break;
   			case 'midi': $mime='audio/midi'; break;
   			case 'mov': $mime='video/quicktime'; break;
   			case 'mp3': $mime='audio/mpeg'; break;
   			case 'mpeg': $mime='video/mpeg'; break;
   			case 'mpg': $mime='video/mpeg'; break;
   			case 'pdf': $mime='application/pdf'; break;
   			case 'png': $mime='image/png'; break;
   			case 'ppt': $mime='application/vnd.ms-powerpoint'; break;
   			case 'ps':  $mime='application/postscript'; break;
   			case 'tif': $mime='image/tiff'; break;
   			case 'tiff': $mime='image/tiff'; break;
   			case 'txt': $mime='text/plain'; break;
   			case 'xhtml': $mime='application/xhtml+xml'; break;
   			case 'xls': $mime='application/vnd.ms-excel'; break;
   			case 'zip': $mime='application/zip'; break;
   			case 'zip': $mime='application/zip'; break;
   			default: $mime='application/octet-stream'; break;
  		}

  		return $mime;
	}
}
?>