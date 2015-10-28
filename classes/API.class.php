<?php
/*
** Base class for REST API endpoints
*/
abstract class API {

	// The HTTP method used to access this request
	protected $method = '';

	// The resource endpoint being requested
	protected $endpoint = '';

	// Additional optional verb
	protected $verb = '';

	// Additional arguments (eg item ID)
	protected $args = Array();

	// PUT request input
	protected $file = Null;

	// Input data
	protected $data = Array();

	public function __construct ($request) {
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS");
		header("Strict-Transport-Security: max-age=31536000; includeSubDomains;");
		header("Content-Type: application/json");

		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_shift($this->args);
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
			$this->verb = array_shift($this->args);
		}

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				throw new HTTPErrorException("Unexpected Header", 500);
			}
		}

		switch($this->method) {
			case 'POST':
				$this->request = $this->_cleanInputs($_POST);
				break;
			case 'GET':
				$this->request = $this->_cleanInputs($_GET);
				break;
			case 'PUT':
			case 'DELETE':
				global $_PUT;
				$this->request = $this->_cleanInputs($_PUT);
				break;
			case 'OPTIONS':
				break;
			default:
				throw new HTTPErrorException('Invalid Method', 405);
				break;
		}

	}

	public function processAPI() {
		if (method_exists($this, $this->endpoint)) {
			return $this->_response($this->{$this->endpoint}($this->args));
		} else {
			throw new HTTPErrorException('No endpoint: ' . $this->endpoint, 404);
		}
	}

	protected function _response($data, $status = 200) {
		if (array_key_exists('http_status', $data)) {
			$status = $data['http_status'];
			$data = $data['data'];
		}
		header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		return json_encode($data);
	}

	protected function _requestStatus($code) {
		$status = array(
			200 => 'OK',
			201 => 'Created'
		);
		return ($status[$code]) ? $status[$code] : $status[500];
	}

	private function _cleanInputs($data) {
		$clean_input = Array();
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$clean_input[$k] = $this->_cleanInputs($v);
			}
		} else {
			$clean_input = trim(strip_tags($data));
		}
		return $clean_input;
	}
}

class HTTPErrorException extends Exception {
	protected $http_status = '';
	protected $http_code = 500;
	protected $error = '';
	protected $error_array = Array();

	protected function _requestStatus($code) {
		$status = array(
			200 => 'OK',
			201 => 'Created',
			204 => 'Deleted',
			400 => 'Invalid Request',
			401 => 'Unauthorized',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			409 => 'Conflict',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
		);
		return ($status[$code]) ? $status[$code] : $status[500];
	}

	public function __construct($error = '', $http_code = 500, $http_status = '', $error_array = Array()) {
		$http_status = $http_status == '' ? $this->_requestStatus($http_code) : $http_status;
		$error = $error == '' ? $http_status : $error;
		parent::__construct($error, $http_code);
		$this->error = $error;
		$this->http_code = $http_code;
		$this->http_status = $http_status;
		$this->error_array = $error_array;
	}

	public function getHeader() {
		return "HTTP/1.1 " . $this->http_code . " " . $this->http_status;
	}

	public function getError() {
		$error = Array(
			'message' => $this->error
		);

		if (!empty($this->error_array)) {
			$error['errors'] = $this->error_array;
		}

		return $error;
	}

	public function __toString() {
		return $this->error;
	}
}
?>