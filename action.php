<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once dirname(__FILE__).'/shortcodes.php';

//-----------------------------------------------------------------------------------
//	Plugin	
//-----------------------------------------------------------------------------------

class action_plugin_shortcodes extends DokuWiki_Action_Plugin {

	protected $targetHooks = array('RENDERER_CONTENT_POSTPROCESS'=>'AFTER','TPL_CONTENT_DISPLAY'=>'AFTER');

	public function register(&$controller) {
		foreach($this->targetHooks as $hook => $position) {
			if(method_exists($this,$hook)) {
				$controller->register_hook($hook,$position,$this,$hook);
			}
		}
	}

	public function RENDERER_CONTENT_POSTPROCESS(&$event,$param) {
		$context = $event->data[0];
		$content = $event->data[1];
		switch($context) {
			case 'xhtml':
				$content = KabinetSHortcodes::filter($content);
				break;
			default:
				$content = $content;
				break;
		}
		$event->data[1] = $content;
	}
}
?>
