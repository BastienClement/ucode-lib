<?php

namespace UCode;

//
// Abstract Wowhead tag
//
abstract class WowheadTag extends \XBBC\LeafTag {
	//
	// Colors stuff
	//
	
	// Public colors shared with the [color] tag
	protected static $colors = array(
		// Item qualities
		'poor'        => 'q0',
		'common'      => 'q1',
		'uncommon'    => 'q2',
		'rare'        => 'q3',
		'epic'        => 'q4',
		'legendary'   => 'q5',
		'artifact'    => 'q6',
		'heirloom'    => 'q7',
		'bonus'       => 'q8',
		'glyph'       => 'q9',
		'error'       => 'q10',
		
		// Classes
		'warrior'     => 'c1',
		'paladin'     => 'c2',
		'hunter'      => 'c3',
		'rogue'       => 'c4',
		'priest'      => 'c5',
		'deathknight' => 'c6', 'dk' => 'c6',
		'shaman'      => 'c7',
		'mage'        => 'c8', 
		'warlock'     => 'c9',
		'monk'        => 'c10',
		'druid'       => 'c11',
		
		// Difficulties
		'hard'        => 'r1',
		'medium'      => 'r2',
		'easy'        => 'r3',
		'trivial'     => 'r4',
		
		// NPC messages
		'yell'        => 's1',
		'say'         => 's2',
		'whisper'     => 's3',
		
		// Misc
		'blizzard'    => 'blizzard-blue',
		'unnamed'     => 'unnamed-green'
	);
	
	// Private colors not used by the [color] tag
	protected static $colors_private = array(
		// Item qualities
		'grey'        => 'q0',
		'white'       => 'q1',
		'green'       => 'q2',
		'blue'        => 'q3',
		'purple'      => 'q4',
		'orange'      => 'q5',
	);
	
	// Colors usable without 'color='
	protected static $aliased_colors = array(
		'poor', 'common', 'uncommon', 'rare', 'epic', 'legendary',
		'glyph', 'heirloom'
	);
	
	// Return the wowhead's class name for a given color alias
	public static function ColorAsClass($color, $use_private = false) {
		$color = strtolower($color);
		
		// Checks public colors
		if(isset(self::$colors[$color]))
			return self::$colors[$color];
		
		// Checks private colors
		if($use_private && isset(self::$colors_private[$color]))
			return self::$colors_private[$color];
		
		// Not found
		return false;
	}
	
	//
	// Tag stuff
	//
	
	protected static $domains = array(
		'fr'  => 'fr',
		'www' => 'www',
		'en'  => 'www',
		'ptr' => 'ptr'
	);
	
	protected $domain  = 'fr';
	protected $classes = array();
	protected $styles  = array();
	protected $args    = array();
	protected $link_id;
	
	protected $safe_arg = true;
	
	public function __construct() {
		parent::__construct(null, '</a>', false);
	}
	
	protected function __create() {
		// Optional domain
		if(isset($this->xargs['domain']) && isset(self::$domains[$this->xargs['domain']])) {
			$this->domain = self::$domains[$this->xargs['domain']];
		}
		
		// Sanitize the argument
		if($safe_arg) {
			if(!is_numeric($this->arg)) {
				return false;
			} else {
				$this->arg = (int) $this->arg;
			}
		}
		
		if(!($path = $this->Handle())) {
			return false;
		}
		
		// Color stuff
		foreach(self::$aliased_colors as $alias) {
			if(isset($this->xargs[$alias])) {
				$this->xargs['color'] = $alias;
				break;
			}
		}
		
		if(isset($this->xargs['color'])) {
			if($color_class = self::ColorAsClass($this->xargs['color'], true)) {
				$this->classes[] = $color_class;
			} else if($color_style = ColorTag::ParseColor($this->xargs['color'])) {
				$this->styles[] = 'color:'.$this->xargs['color'];
			}
		}
		
		// Compile classes, styles, args
		$classes = empty($this->classes) ? '' : ' class="'.implode(' ', $this->classes).'"';
		$styles  = empty($this->styles)  ? '' : ' style="'.implode(';', $this->styles).'"';
		$args    = empty($this->args)    ? '' : htmlspecialchars('&'.implode('&', $this->args));
		
		$this->before = "<a href=\"http://{$this->domain}.wowhead.com/$path$args\"$styles$classes>";
		
		if(isset($this->xargs['icon'])) {
			$icon = preg_replace('/[^a-z0-9_]/i', '', $this->xargs['icon']);
			$this->before .= "<img src=\"http://wow.zamimg.com/images/wow/icons/small/$icon.jpg\" alt=\"$icon\" class=\"db-icon\" /> ";
		}
	}
	
	protected function import_arg($key, $as = null) {
		if(!$as) {
			$as = $key;
		}
		if(isset($this->xargs[$key])) {
			$this->args[] = $as.'='.$this->xargs[$key];
		}
	}
	
	protected abstract function Handle();
}

//
// [achievement]
//
class AchievementTag extends WowheadTag {
	protected function Handle() {
		return "achievement={$this->arg}";
	}
}

//
// [db]
//
class DBTag extends WowheadTag {
	protected $safe_arg = false;
	protected $type, $id;
	
	protected function __create() {
		if(isset($this->xargs['args'])) {
			$this->args[] = $this->xargs['args'];
		}
		
		if(preg_match('/^([a-z]+):([0-9]*)$/', $this->arg, $matched)) {
			$this->type = $matched[1];
			$this->id   = (int) $matched[2];
			return parent::__create();
		} else {
			return false;
		}
	}
	
	protected function Handle() {
		return "{$this->type}={$this->id}";
	}
}

//
// [item]
//
class ItemTag extends WowheadTag {
	protected static $reforge_ids = array(
		'spi' => array(
			'dodge'   => 113,
			'parry'   => 114,
			'hit'     => 115,
			'crit'    => 116,
			'haste'   => 117,
			'exp'     => 118,
			'mastery' => 119
		),
		
		'dodge' => array(
			'spi'     => 120,
			'parry'   => 121,
			'hit'     => 122,
			'crit'    => 123,
			'haste'   => 124,
			'exp'     => 125,
			'mastery' => 126
		),
		
		'parry' => array(
			'spi'     => 127,
			'dodge'   => 128,
			'hit'     => 129,
			'crit'    => 130,
			'haste'   => 131,
			'exp'     => 132,
			'mastery' => 133
		),
		
		'parry' => array(
			'spi'     => 127,
			'dodge'   => 128,
			'hit'     => 129,
			'crit'    => 130,
			'haste'   => 131,
			'exp'     => 132,
			'mastery' => 133
		),
		
		'hit' => array(
			'spi'     => 134,
			'dodge'   => 135,
			'parry'   => 136,
			'crit'    => 137,
			'haste'   => 138,
			'exp'     => 139,
			'mastery' => 140
		),
		
		'crit' => array(
			'spi'     => 141,
			'dodge'   => 142,
			'parry'   => 143,
			'hit'     => 144,
			'haste'   => 145,
			'exp'     => 146,
			'mastery' => 147
		),
		
		'haste' => array(
			'spi'     => 148,
			'dodge'   => 149,
			'parry'   => 150,
			'hit'     => 151,
			'crit'    => 152,
			'exp'     => 153,
			'mastery' => 154
		),
		
		'exp' => array(
			'spi'     => 155,
			'dodge'   => 156,
			'parry'   => 157,
			'hit'     => 158,
			'crit'    => 159,
			'haste'   => 160,
			'mastery' => 161
		),
		
		'mastery' => array(
			'spi'     => 162,
			'dodge'   => 163,
			'parry'   => 164,
			'hit'     => 165,
			'crit'    => 166,
			'haste'   => 167,
			'exp'     => 168
		)
	);
	
	protected static function ReforgeID($reforge) {
		@list($from, $to) = explode('->', $reforge, 2);
		
		if($from && isset(self::$reforge_ids[$from]) && $to && isset(self::$reforge_ids[$from][$to])) {
			return self::$reforge_ids[$from][$to];
		}
		
		return false;
	}
	
	protected function Handle() {
		$this->import_arg('lvl');
		$this->import_arg('ench');
		$this->import_arg('socket', 'sock');
		$this->import_arg('gems');
		$this->import_arg('set', 'pcs');
		$this->import_arg('rand');
		
		// Bugged
		/*if(isset($this->xargs['reforge'])) {
			if($id = self::ReforgeID($this->xargs['reforge'])) {
				$this->args[] = "forg=$id";
			}
		}*/
		
		return "item={$this->arg}";
	}
}

//
// [npc]
//
class NPCTag extends WowheadTag {
	protected function __create() {
		parent::__create();
		
		if(isset($this->xargs['boss'])) {
			$this->content = '<img src="http://wowimg.zamimg.com/images/icons/boss.gif" class="icon-boss" alt="boss" /> ';
		}
	}
	
	protected function Handle() {
		return "npc={$this->arg}";
	}
}

//
// [object]
//
class ObjectTag extends WowheadTag {
	protected function Handle() {
		return "object={$this->arg}";
	}
}

//
// [quest]
//
class QuestTag extends WowheadTag {
	protected function __create() {
		parent::__create();
		
		if(isset($this->xargs['bare']))
			return;
		
		if(isset($this->xargs['daily'])) {
			if(isset($this->xargs['done'])) {
				$this->content = '<img src="http://wow.zamimg.com/images/wow/icons/tiny/quest_end_daily.gif" class="icon-quest" alt="" /> ';
			} else {
				$this->content = '<img src="http://wow.zamimg.com/images/wow/icons/tiny/quest_start_daily.gif" class="icon-quest" alt="" /> ';
			}
		} else {
			if(isset($this->xargs['done'])) {
				$this->content = '<img src="http://wow.zamimg.com/images/wow/icons/tiny/quest_end.gif" class="icon-quest" alt="" /> ';
			} else {
				$this->content = '<img src="http://wow.zamimg.com/images/wow/icons/tiny/quest_start.gif" class="icon-quest" alt="" /> ';
			}
		}
	}
	
	protected function Handle() {
		return "quest={$this->arg}";
	}
}

//
// [spell]
//
class SpellTag extends WowheadTag {
	protected function Handle() {
		$this->import_arg('lvl');
		$this->import_arg('buff');
		
		return "spell={$this->arg}";
	}
}


//
// [socket]
//
class SocketTag extends \XBBC\SingleTag {
	protected static $socket_classes = array(
		'meta'      => 'socket-meta',
		'red'       => 'socket-red',
		'yellow'    => 'socket-yellow',
		'blue'      => 'socket-blue',
		'prismatic' => 'socket-prismatic',
		'sha'       => 'socket-hydraulic',
		'hydraulic' => 'socket-hydraulic',
		'cogwheel'  => 'socket-cogwheel'
	);
	
	public function __construct() {
		parent::__construct(null);
	}
	
	protected function __create() {
		if(isset(self::$socket_classes[strtolower($this->arg)])) {
			$this->html = '<span class="socket '.self::$socket_classes[strtolower($this->arg)].'"></span>';
		} else {
			return false;
		}
	}
}

//
// [icon]
//
class IconTag extends \XBBC\SingleTag {
	protected $size;
	
	public function __construct() {
		parent::__construct(null);
	}
	
	protected function __create() {
		$this->html = '<img src="http://wow.zamimg.com/images/wow/icons/small/'.$this->arg.'.jpg" class="wow-icon" alt="'.$this->arg.'" /">';
	}
}

//
// [class]
//
class ClassTag extends \XBBC\SingleTag {
	protected static $class_data = array(
		'warrior'     => array('c1',  'warrior',     'Guerrier',             'Guerrière'),
		'paladin'     => array('c2',  'paladin',     'Paladin',              'Paladin'),
		'hunter'      => array('c3',  'hunter',      'Chasseur',             'Chasseresse'),
		'rogue'       => array('c4',  'rogue',       'Voleur',               'Voleuse'),
		'priest'      => array('c5',  'priest',      'Prêtre',               'Prêtresse'),
		'dk'          => array('c6',  'deathknight', 'Chevalier de la mort', 'Chevalier de la mort'),
		'deathknight' => array('c6',  'deathknight', 'Chevalier de la mort', 'Chevalier de la mort'),
		'shaman'      => array('c7',  'shaman',      'Chaman',               'CHamane'),
		'mage'        => array('c8',  'mage',        'Mage',                 'Mage'),
		'warlock'     => array('c9',  'warlock',     'Démoniste',            'Démoniste'),
		'monk'        => array('c10', 'monk',        'Moine',                'Moine'),
		'druid'       => array('c11', 'druid',       'Druide',               'Druidesse')
	);
	
	public function __construct() {
		parent::__construct(null);
	}
	
	protected function __create() {
		$arg = strtolower($this->arg);
		
		if(isset(self::$class_data[$arg])) {
			list($css_class, $img, $text_male, $text_female) = self::$class_data[$arg];
			$text = isset($this->xargs['female']) ? $text_female : $text_male;
			$this->html = "<span class=\"wow-class $css_class\"><img src=\"http://wow.zamimg.com/images/wow/icons/small/class_$img.jpg\" /> $text</span>";
		} else {
			return false;
		}
	}
}

//
// [class]
//
class RaceTag extends \XBBC\SingleTag {
	protected static $race_data = array(
		// Human
		'human'    => array("human",    'Humain',          'Humaine'),
		'nightelf' => array("nightelf", 'Elfe de la nuit', 'Elfe de la nuit'),
		'dwarf'    => array("dwarf",    'Nain',            'Naine'),
		'gnome'    => array("gnome",    'Gnome',           'Gnome'),
		'draenei'  => array("draenei",  'Draeneï',         'Draeneï'),
		'worgen'   => array("worgen",   'Worgen',          'Worgen'),
		
		// Horde
		'orc'      => array("orc",      'Orc',             'Orque'),
		'undead'   => array("scourge",  'Mort-vivant',     'Morte-vivante'),
		'troll'    => array("troll",    'Troll',           'Trollesse'),
		'tauren'   => array("tauren",   'Tauren',          'Taurène'),
		'goblin'   => array("goblin",   'Goblin',          'Gobeline'),
		'bloodelf' => array("bloodelf", 'Elfe de sang',    'Elfe de sang'),
		
		// Neutral
		'pandaren' => array("pandaren", 'Pandaren',        'Prêtre')
	);
	
	public function __construct() {
		parent::__construct(null);
	}
	
	protected function __create() {
		if(isset(self::$race_data[strtolower($this->arg)])) {
			$race_data = self::$race_data[strtolower($this->arg)];
		} else {
			return false;
		}
		
		list($img, $text_male, $text_female) = $race_data;
		
		if(isset($this->xargs['female'])) {
			$gender = 'female';
			$text   = $text_female;
		} else {
			$gender = 'male';
			$text   = $text_male;
		}
		
		$this->html = "<span class=\"wow-race\"><img src=\"http://wow.zamimg.com/images/wow/icons/small/race_{$img}_$gender.jpg\" /> $text</span>";
	}
}
