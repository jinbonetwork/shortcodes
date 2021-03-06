<?php
require_once dirname(__FILE__).'/../contrib/simplehtmldom/simple_html_dom.php';
class KabinetShortcode_paste extends KabinetShortcode {

	public $url;
	public $page;
	public $selector;
	public $purge;
	public $refresh;
	public $test;

	protected $dataDir;
	protected $pageDir;
	protected $cacheDir;

	protected $mode;
	protected $item;
	protected $content;
	protected $pageExtension;
	protected $cacheExtension;
	protected $cacheFile;

	function __construct($attributes=array()) {
		global $conf;
		$this->dataDir = realpath(DOKU_INC.$conf['savedir']);
		$this->pageDir = $this->dataDir.'/pages';
		$this->cacheDir = $this->dataDir.'/cache/paste';
		$this->pageExtension = '.txt';
		$this->cacheExtension = '.txt';

		// acceptable options
		$defaults = array(
			'url' => '',
			'page' => '',
			'selector' => '',
			'purge' => false,
			'refresh' => true,
			'test' => false,
		);

		if($attributes['url']) {
			$this->mode = 'url';
			$attributes['url'] = strip_tags($attributes['url']);
			$defaults['selector'] = 'body > *';
		} elseif($attributes['page']) {
			$this->mode = 'page';
		} else {
			$this->mode = false;
		}

		$this->checkAttributes($attributes,$defaults,true);
		$this->output = $this->execute();
	}

	function execute() {
		$result = false;

		if($this->purge) {
			$this->remove($this->cacheDir);
		}

		switch($this->mode) {
			case 'url':
				$this->item = $this->url;
				$this->cacheFile = $this->getCacheFileByUrl($this->url);
				$this->content = $this->getContentByUrl($this->url);
				break;
			case 'page':
				$this->item = $this->page;
				$this->cacheFile = $this->getCacheFileByPage($this->page);
				$this->content = $this->getContentByPage($this->page);
				break;
			default:
				return $result;
				break;
		}
		
		if($this->content) {
			if($this->test) {
				ob_start();
				var_dump($this);
				$report = ob_get_contents();
				ob_end_clean();

				$result = sprintf(
					'<dl><dt>%s</dt><dd>%s</dd><dd>%s</dd></dl>',
					htmlentities($this->item),
					$report,
					htmlentities($this->content)
				);
			} else {
				$result = $this->content;
			}
		}

		return $result;
	}

	function getContentByUrl($url) {
		$cache = $this->cacheFile;
		$content = file_get_contents($url);
		if($content) {
			$content = $this->preprocessContent($content);
			$content = $this->postprocessContent($content);
		}
		return $content;
	}

	function getCacheFileByUrl($url) {
		$file = false;

		if(strpos($url,'://')!==false) {
			list($protocol,$url) = explode('://',$url);
		}
		$url = strtolower($url);
		$tree = preg_split('/[^a-z0-9]+/',$url);
		$tree = array_filter($tree,'trim');
		$file = implode('_',$tree);
		$file = $this->cacheDir.'/'.$file.$this->cacheExtension;

		return $file;
	}

	function getContentByPage($page) {
		$content = false;
		if(!page_exists($page)) {
			return $content;
		}

		if($this->refresh) {
			$this->remove($this->cacheFile);
		}
		if(!file_exists($this->cacheFile)) {
			$this->saveCache($page);
		}
		if(filemtime($this->cacheFile)<filemtime(wikiFN($page))) {
			$this->saveCache($page);
		}

		$content = file_get_contents($this->cacheFile);

		return $content;
	}

	function saveCache($page) {
		$result = false;

		if(!file_exists($this->cacheDir)) {
			$result = mkdir($this->cacheDir);
		}

		if(is_writable($this->cacheDir)) {
			$cache = p_wiki_xhtml($page);
			$cache = $this->preprocessContent($cache);
			$cahce = $this->postprocessContent($cache);
			$result = file_put_contents($this->cacheFile,$cache);
		}

		return $result;
	}

	function getCacheFileByPage($page) {
		$file = wikiFN($page);
		if(file_exists($file)) {
			$pattern = array(
				$this->pageDir => '',
				$this->pageExtension => $this->cacheExtension,
			);
			$file = str_replace(array_keys($pattern),array_values($pattern),$file);
			$file = $this->getCacheFileByUrl($file);
		}

		return $file;
	}

	function preprocessContent($content) {
		return $content;
	}

	function postprocessContent($content) {
		$content = $this->selectElement($content);
		$content = $this->correctReferences($content);

		$content = '<html>'.PHP_EOL.$content.PHP_EOL.'</html>'.PHP_EOL;
		return $content;
	}

	function selectElement($content,$selector='') {
		$output = '';

		$selector = $selector?$selector:$this->selector;
		if($selector) {
			$html = str_get_html($content);
			$items = $html->find($selector);
			if(!empty($items)) {
				$content = '';
				foreach($items as $item) {
					$content .= $item->outertext;
				}
			}
		}

		return $content;
	}

	function correctReferences($content) {
		switch($this->mode) {
			case 'url':
				if(strpos($this->url,'://')!==false) {
					list($protocol,$url) = explode('://',$this->url);
				}
				list($domain,$directories) = explode('/',$url);
				$domain = ($protocol?$protocol.'://':'').$domain;
				break;
			case 'page':
				global $conf;
				$domain = str_replace('/doku.php','',wl($conf['start'],'',true));
				break;
		}
		$pattern = array();
		$cases = array('href','src');
		foreach($cases as $case) {
			$pattern['/'.$case.' ?= ?\//'] = $case.'='.$domain.'/';
			$pattern['/'.$case.' ?= ?\'\//'] = $case.'=\''.$domain.'/';
			$pattern['/'.$case.' ?= ?\"\//'] = $case.'="'.$domain.'/';
		}
		$content = preg_replace(array_keys($pattern),array_values($pattern),$content);

		return $content;
	}

}
?>
