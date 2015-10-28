<?php
require_once('classes/API.class.php');
require_once('classes/Model.php');

class QAAPI extends API {
	public function __construct($request) {
		parent::__construct($request);
		$this->model = new Model();
	}

	public function test() {
		throw new HTTPErrorException('test', 500);
	}

	public function question() {
		switch ($this->method) {
			case 'GET':
				$question_id = intval($this->args[0]);
				if (empty($question_id)) {
					return $this->model->get_questions();
				}
				else {
					return $this->model->get_question($question_id);
				}
				break;
			case 'POST':
				$question = $this->model->add_question($this->request['name'], $this->request['question']);
				return ['http_status' => 201, 'data' => $question];
				break;
			default:
				throw new HTTPErrorException('', 405);
		}
	}

	public function answer() {
		switch ($this->method) {
			case 'GET':
				$question_id = intval($this->args[0]);
				if (empty($question_id)) {
					throw new HTTPErrorException('', 400);
				}
				else {
					return $this->model->get_answers($question_id);
				}
				break;
			case 'POST':
				$answer = $this->model->add_answer($this->request['question_id'], $this->request['name'], $this->request['answer']);
				return ['http_status' => 201, 'data' => $answer];
				break;
			default:
				throw new HTTPErrorException('', 405);
		}
	}
}

try {
	$API = new QAAPI ($_REQUEST['request']);
	echo $API->processAPI();
} catch (Exception $e) {
	header($e->getHeader());
	echo json_encode($e->getError());
}

?>