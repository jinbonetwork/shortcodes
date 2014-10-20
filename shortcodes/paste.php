<?php
class KabinetShortcode_paste extends KabinetShortcode {

	public $url;
	public $page;
	public $selector;

	protected $dataDir;
	protected $pageDir;
	protected $cacheDir;

	protected $mode;
	protected $item;
	protected $content;
	protected $mtime;
	protected $pageExtension;
	protected $cacheExtension;
	protected $cacheFile;

	function __construct($attributes=array()) {
		global $conf;
		$this->dataDir = realpath(DOKU_PATH.'/'.$conf['savedir']);
		$this->pageDir = $this->dataDir.'/pages';
		$this->cacheDir = $this->dataDir.'/cache/paste';
		$this->pageExtension = '.txt';
		$this->cacheExtension = '.txt';

		// acceptable options
		$defaults = array(
			'url' => '',
			'page' => '',
			'selector' => '',
		);

		if($attributes['url']) {
			$this->mode = 'url';
		} elseif($attributes['page']) {
			$this->mode = 'page';
		} else {
			$this->mode = false;
		}

		$this->filterAttributes($attributes,$defaults);
		foreach($this->attributes as $key => $value) {
			$this->$key = $value;
		}

		$this->output = $this->execute();
	}

	function execute() {
		$result = false;

		switch($this->mode) {
			case 'url':
				$this->item = $this->url;
				$this->content = $this->getContentByUrl($this->url);
				$this->cacheFile = $this->getcacheFileByUrl($this->url);
				break;
			case 'page':
				$this->item = $this->page;
				$this->content = $this->getContentByPage($this->page);
				$this->cacheFile = $this->getcacheFileByPage($this->page);
				break;
			default:
				return $result;
				break;
		}
	
		$result = sprintf(
			'<dl><dt>%s</dt><dd>%s</dd><dd>%s</dd></dl>',
			urldecode($this->item).'('.$this->mode.':'.$this->cacheFile.')',
			$this->selector,
			$this->content
		);

		return $result;
	}

	function getContentByUrl($url) {
		$content = @file_get_contents($url);
		return $content;
	}

	function getcacheFileByUrl($url) {
		$file = false;

		list($protocol,$url) = explode('://',$url);
		$tree = explode('/',$url);
		$file = implode('_',$tree).$this->cacheExtension;
		$file = $this->cacheDir.'/'.$file;

		return $name;
	}

	function getContentByPage($page) {
		$content = false;

		$file = wikiFN($page);
		if(!file_exists($file)) {
			return $content;
		}
		$content = p_wiki_xhtml($file);

		return $content;
	}

	function getcacheFileByPage($page) {
		$file = wikiFN($page);
		if(file_exists($file)) {
			$pattern = array(
				$this->pageDir => $this->cacheDir,
				$this->pageExtension => $this->cacheExtension,
			);
			$file = str_replace(array_keys($pattern),array_values($pattern),$file);
		}

		return $file;
	}

}
?>
