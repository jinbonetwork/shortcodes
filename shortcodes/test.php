<?php
class KabinetShortcode_test extends KabinetShortcode {
	function __construct($attributes,$content,$shortcode) {
		$this->output = $this->execute($attributes,$content,$shortcode);
	}
	
	function execute($attributes=array(''),$content='',$shortcode='') {
		$output = null;

		ob_start();
		print_r($attributes);
		$attributes = ob_get_contents();
		ob_end_clean();

		//$content = htmlentities($content);

		$output = '<dl>'.PHP_EOL;
		$output .= "<dt class='shortcode'>{$shortcode}</dt>".PHP_EOL;
		$output .= "<dd class='attributes'>{$attributes}</dd>".PHP_EOL;
		$output .= "<dd class='content'>{$content}</dd>".PHP_EOL;
		$output .= '</dl>'.PHP_EOL;

		return $output;
	}
}
?>
