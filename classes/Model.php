<?

require_once('Question.php');
require_once('Answer.php');

class Model {
	public $db;

	private $db_host = "127.0.0.1";
	private $db_socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";
	private $db_name = "ketsugic_apps";
	private $db_user = "ketsugic_apps";
	private $db_pass = "F92EAB54D7D700";

	public $questions_table = 'questions';
	public $answers_table = 'answers';

	public $get_questions_stmt;
	public $get_question_stmt;
	public $insert_question_stmt;

	public function __construct() {
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			$connection_string = "mysql:unix_socket=$this->db_socket;dbname=$this->db_name";
		}
		else {
			$connection_string = "mysql:host=$this->db_host;dbname=$this->db_name";
		}
		
		try {
			$this->db = new PDO(
				$connection_string,
				$this->db_user,
				$this->db_pass
				);
		}
		catch (PDOException $e) {
			die("Error: " . $e->getMessage());
		}

		// Check if tables exist and create it if not
		$show_stmt = $this->db->query("SHOW TABLES LIKE '$this->questions_table'");
		
		if (empty($show_stmt->fetch())) {
			$create_stmt = $this->db->prepare("CREATE TABLE `$this->questions_table` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL DEFAULT '',
				`question` tinytext NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			$create_stmt->execute();
		}

		$show_stmt = $this->db->query("SHOW TABLES LIKE '$this->answers_table'");

		if (empty($show_stmt->fetch())) {
			$create_stmt = $this->db->prepare("CREATE TABLE `$this->answers_table` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`question_id` int(11) unsigned NOT NULL,
				`name` varchar(255) NOT NULL DEFAULT '',
				`text` tinytext NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			$create_stmt->execute();
		}

		// Prepare statements
		$this->get_questions_stmt = $this->db->prepare("SELECT * FROM $this->questions_table ORDER BY id DESC");
		$this->get_question_stmt = $this->db->prepare("SELECT * FROM $this->questions_table WHERE id = :id");
		$this->add_question_stmt = $this->db->prepare("INSERT INTO $this->questions_table (name, question) VALUES (:name, :question)");
		$this->get_answers_stmt = $this->db->prepare("SELECT * FROM $this->answers_table WHERE question_id = :question_id");
		$this->add_answer_stmt = $this->db->prepare("INSERT INTO $this->answers_table (question_id, name, answer) VALUES (:question_id, :name, :answer)");
	}

	public function get_questions() {
		$this->get_questions_stmt->execute();
		$questions = $this->get_questions_stmt->fetchAll();
		foreach ($questions as &$question) {
			$question = new Question($question['id'], $question['name'], $question['question']);
		}
		return $questions;
	}

	public function get_question($id) {
		$this->get_question_stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$this->get_question_stmt->execute();
		$question = $this->get_question_stmt->fetch();
		return new Question($question['id'], $question['name'], $question['question']);
	}

	public function add_question($name, $question) {
		$this->add_question_stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$this->add_question_stmt->bindParam(':question', $question, PDO::PARAM_STR);
		try {
			$this->add_question_stmt->execute();
			$id = $this->db->lastInsertId();
			return $this->get_question($id);
		} catch (Exception $e) {
			die("Error: " . $e->getMessage());
		}
	}

	public function get_answers($question_id) {
		$this->get_answers_stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
		$this->get_answers_stmt->execute();
		$answers = $this->get_answers_stmt->fetchAll();
		foreach ($answers as &$answer) {
			$answer = new Answer($answer['id'], $answer['question_id'], $answer['name'], $answer['answer']);
		}
		return $answers;
	}

	public function add_answer($question_id, $name, $answer) {
		$this->add_answer_stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
		$this->add_answer_stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$this->add_answer_stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
		try {
			$this->add_answer_stmt->execute();
			$id = $this->db->lastInsertId();
			return $id;
		} catch (Exception $e) {
			die("Error: " . $e->getMessage());
		}
	}
}

?>