<?php
class KabinetShortcode_paste extends KabinetShortcode {
	function __construct($attributes=array()) {
		$defaults = array(
			'url' => '',
			'page' => '',
			'selector' => '',
		);
		$this->filterAttributes($attributes,$defaults);
		$this->output = $this->execute();
	}

	function execute() {
		$result = '';

		ob_start();
		var_dump($this->attributes);
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}
}
?>
