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

class LibRest{
	public static function processRequest(){
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		$request = new LibRestRequest();
		$data = array();

		switch($method){
			case 'get':
				$data = $_GET;
				break;
			case 'post':
				$data = $_POST;
				break;
		}

		$request->setMethod($method);
		$request->setVars($data);

		if(isset($data['data'])){
			$request->setData(json_decode($data['data']));
		}

		return $request;
	}

	public static function getStatusMessage($status){
		$codes = Array(
		    200 => 'OK',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	public static function sendResponse($status = 200, $body = '', $contentType = 'text/html')	{
		header('HTTP/1.1 ' . $status . ' ' . LibRest::getStatusMessage($status));
		header('Content-type: ' . $contentType);

		if($body != ''){
			echo $body;
			exit;
		} else {
			$message = '';

			switch($status){
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>' . $status . ' ' . LibRest::getStatusMessage($status) . '</title>
	</head>
	<body>
		<h1>' . LibRest::getStatusMessage($status) . '</h1>
		<p>' . $message . '</p>
	</body>
</html>';
			echo $body;
			exit;
		}
	}
}

class LibRestRequest{
	private $vars;
	private $data;
	private $httpAccept;
	private $method;

	public function __construct(){
		$this->vars	= array();
		$this->data	= '';
		$this->httpAccept = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'json';
		$this->method = 'get';
	}

	public function setData($data){
		$this->data = $data;
	}

	public function setVars($vars){
		$this->vars = $vars;
	}

	public function setMethod($method){
		$this->method = $method;
	}

	public function getData(){
		return $this->data;
	}

	public function getVars(){
		return $this->vars;
	}

	public function getMethod(){
		return $this->method;
	}

	public function getHttpAccept(){
		return $this->httpAccept;
	}
}
?>