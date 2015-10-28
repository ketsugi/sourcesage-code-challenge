<?
class Question {
	public $id;
	public $name;
	public $question;

	public function __construct($id, $name, $question) {
		$this->id = $id;
		$this->name = $name;
		$this->question = $question;
	}
}
?>