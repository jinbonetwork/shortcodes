<?php
class KabinetShortcode_paste extends KabinetShortcode {
	function __construct($attributes=array()) {
		global $INFO,$conf;
		$defaults = array(
			'dir' => realpath(DOKU_PATH.$conf['savedir']).'/cache/paste', // read/write permission required.
			'mode' => '', // automatic
			'url' => '',
			'page' => '',
			'selector' => '',
		);

		if($attributes['url']) {
			$attributes['url'] = urlencode($attributes['url']);
			$attributes['mode'] = 'url';
		} elseif($attributes['page']) {
			$attributes['mode'] = 'page';
		} else {
			$attributes['mode'] = false;
		}

		$this->filterAttributes($attributes,$defaults);
		$this->output = $this->execute();
	}

	function execute() {
		$result = false;
		$content = '';
		$filepath = '';
		$cachefile = '';
		extract($this->attributes);

		switch($mode) {
			case 'url':
				$id = $url;
				$content = $this->getContentByUrl($id);
				$cachefile = $this->getCacheNameByUrl($id);
				break;
			case 'page':
				$id = $page;
				$content = $this->getContentByPage($id);
				$cachefile = $this->getCacheNameByPage($id);
				break;
			default:
				return $result;
				break;
		}
		
		print_r($this->attributes);
		if($content) {
			$result = sprintf('<dl><dt>%s</dt><dd>%s</dd><dd>%s</dd></dl>',$id.'('.$mode.':'.$cachefile.')',$selector,htmlentities($content));
		}

		return $result;
	}

	function getContentByUrl($url) {
		$content = file_get_contents($url);
		return $content;
	}

	function getCacheNameByUrl($url) {
		$name = false;

		list($protocol,$url) = explode('://',$url);
		$tree = explode('/',$url);
		$name = implode('_',$tree).$this->cacheExtension;

		return $name;
	}

	function getContentByPage($page) {
		$content = false;

		$filepath = wikiFN($page);
		if(!file_exists($filepath)) {
			return $content;
		}
		$content = p_wiki_xhtml($filepath);

		return $content;
	}

	function getCacheNameByPage($page) {
		$name = false;

		$filepath = wikiFN($page);
		if(file_exists($filepath)) {
			$name = $this->getCacheNameByUrl($filename);
		}

		return $name;
	}

}
?>
