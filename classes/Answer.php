<?
class Answer {
	public $id = 0;
	public $question_id = 0;
	public $name = '';
	public $answer = '';

	public function __construct($id, $question_id, $name, $answer) {
		$this->id = $id;
		$this->question_id = $question_id;
		$this->name = $name;
		$this->answer = $answer;
	}
}
?>