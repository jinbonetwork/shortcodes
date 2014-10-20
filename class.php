<?php
//-----------------------------------------------------------------------------------
//	Executor	
//-----------------------------------------------------------------------------------

class KabinetShortcodes {
	public static $classPrefix = 'KabinetShortcode_';
	public static $shortcodes = array();
	public static $pattern;
	public static $attributesPattern;

	public static function construct() {
		self::$pattern = self::getShortcodePattern();
		self::$attributesPattern = self::getAttributesPattern();
		spl_autoload_register('KabinetShortcodes::load');
	}

	public static function load($class) {
		$shortcode = str_replace(self::$classPrefix,'',$class);
		$file = dirname(__FILE__).'/shortcodes/'.$shortcode.'.php';
		if(file_exists($file)) {
			require_once $file;
		}
	}

	//-----------------------------------------------------------------------------------
	//	Starting callback
	//		$content = (string) html markup
	//-----------------------------------------------------------------------------------
	public static function filter($content) {
		if(strpos($content,'[')===false) {
			return $content;
		}

		$pattern = '/'.self::$pattern.'/s';
		return preg_replace_callback($pattern,'KabinetShortcodes::execute',$content);
	}

	public static function execute($matches) {
		$markup = '';

		// allow [[foo]] syntax for escaping a tag
		if($matches[1]=='['&&$matches[6]==']') {
			return substr($matches[0],1,-1);
		}

		$shortcode = $matches[2];
		$attributes = self::getAttributes($matches[3]);
		$class = self::$classPrefix.$shortcode;
		$object = new $class($attributes);
		$markup = $object->output;

		return $markup;

		/* Legacy
		if(isset($matches[5])) {
			// enclosing tag - extra parameter
			return $matches[1].call_user_func($callback,$attributes,$matches[5],$shortcode).$matches[6];
		} else {
			// self-closing tag
			return $matches[1].call_user_func($callback,$attributes,null,$shortcode).$matches[6];
		}
		*/
	}

	public static function getShortcodePattern() {
		// copied from WordPress:wp-includes/shortcodes.php
		$pattern = 
			'\\['								// Opening bracket
			.'(\\[?)'							// 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			.'([a-z]+)'							// 2: Shortcode name
			.'(?![\\w-])'						// Not followed by word character or hyphen
			.'('								// 3: Unroll the loop: Inside the opening shortcode tag
			.	'[^\\]\\/]*'					// Not a closing bracket or forward slash
			.	'(?:'
			.		'\\/(?!\\])'				// A forward slash not followed by a closing bracket
			.		'[^\\]\\/]*'				// Not a closing bracket or forward slash
			.	')*?'
			.')'
			.'(?:'
			.	'(\\/)'							// 4: Self closing tag ...
			.	'\\]'							// ... and closing bracket
			.'|'
			.	'\\]'							// Closing bracket
			.	'(?:'
			.		'('							// 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.			'[^\\[]*+'				// Not an opening bracket
			.			'(?:'
			.				'\\[(?!\\/\\2\\])'	// An opening bracket not followed by the closing shortcode tag
			.				'[^\\[]*+'			// Not an opening bracket
			.			')*+'
			.		')'
			.		'\\[\\/\\2\\]'				// Closing shortcode tag
			.	')?'
			.')'
			.'(\\]?)';							// 6: Optional second closing brocket for escaping shortcodes: [[tag]]
		return $pattern;
	}

	public static function getAttributesPattern() {
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';

		return $pattern;
	}

	public static function getAttributes($string) {
		$attributes = array();

		$string = preg_replace('/[\x{00a0}\x{200b}]+/u',' ',$string);

		if(preg_match_all(self::$attributesPattern,$string,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				if(!empty($match[1])) {
					$atts[strtolower($match[1])] = stripcslashes($match[2]);
				} elseif(!empty($match[3])) {
					$attributes[strtolower($match[3])] = stripcslashes($match[4]);
				} elseif(!empty($match[5])) {
					$attributes[strtolower($match[5])] = stripcslashes($match[6]);
				} elseif(isset($match[7]) and strlen($match[7])) {
					$attributes[] = stripcslashes($match[7]);
				} elseif(isset($match[8])) {
					$attributes[] = stripcslashes($match[8]);
				}
			}
		} else {
			$attributes = ltrim($string);
		}

		return $attributes;
	}
	 
}
KabinetShortcodes::construct();

//-----------------------------------------------------------------------------------
//	Skeleton	
//-----------------------------------------------------------------------------------

class KabinetShortcode {
	public $output;
	protected $defaults;
	protected $attributes;

	function __construct($attributes=array()) {
		$this->attributes = $attributes;
	}

	function filterAttributes($given=array(),$defaults=array()) {
		$attributes = array();

		if(!empty($given)&&!empty($defaults)) {
			$this->defaults = $defaults;
			$merged = array_merge($defaults,$given);
			$filtered = array_intersect_key($given,$defaults);
			$attributes = $filtered;
		}

		$this->attributes = $attributes;
	}
}
?>
