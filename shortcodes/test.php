<?php
class KabinetShortcode_test extends KabinetShortcode {
	function __construct() {
		$this->output = $this->execute();
	}
	
	function execute() {
		return 'test is successful.';
	}
}
?>
