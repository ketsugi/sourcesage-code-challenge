<?
int error_reporting(E_STRICT);
include 'classes/Model.php';

phpinfo();

try {
	$a = new Model();
	//$q = $a->add_answer(4, 'test', 'Not more than 1');
	//print_r($q);
	print json_encode($a->get_answers(4));
} catch (Exception $e) {
	print($e->getMessage());
}

?>