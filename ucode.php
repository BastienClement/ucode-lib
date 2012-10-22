<?php
//
// The Unnamed UCode tag library
//

namespace UCode;

require 'basics.php';
require 'table.php';
require 'wowhead.php';
require 'components.php';

//
// A root tag wrapping contents into a .ucode div
//
class URoot extends \XBBC\RootTag {
	public function __construct() {
		parent::__construct();
		$this->before = '<div class="ucode">';
		$this->after  = '</div>';
	}
}

//
// An abstract tag allowing an argument as content
//
abstract class ArgAsContentTag extends \XBBC\TagDefinition {
	public function __create() {
		if($this->arg) {
			$this->Bufferize($this->arg);
		}
	}
	
	public function EmptyTag() {
		// Autoclose if argument is given (we already have the tag content)
		return $this->arg;
	}
}

//
// Lib loader
//
abstract class Lib {
	public static function load(\XBBC\Parser $parser) {
		// Very simple
		$parser->DefineTag('b',          new \XBBC\SimpleTag('<strong>', '</strong>'));
		$parser->DefineTag('i',          new \XBBC\SimpleTag('<em>', '</em>'));
		$parser->DefineTag('u',          new \XBBC\SimpleTag('<span style="text-decoration: underline;">', '</span>'));
		$parser->DefineTag('s',          new \XBBC\SimpleTag('<span style="text-decoration: line-through;">', '</span>'));
		$parser->DefineTag('sup',        new \XBBC\SimpleTag('<sup>', '</sup>'));
		$parser->DefineTag('sub',        new \XBBC\SimpleTag('<sub>', '</sub>'));
		$parser->DefineTag('ins',        new \XBBC\SimpleTag('<ins>', '</ins>'));
		$parser->DefineTag('del',        new \XBBC\SimpleTag('<del>', '</del>'));

		// Basics
		$parser->DefineTag('url',        new LinkTag());
		$parser->DefineTag('img',        new ImageTag());
		$parser->DefineTag('c',          new CTag());
		$parser->DefineTag('code',       new \XBBC\LeafTag('<pre><code>', '</code></pre>', true, false));
		
		$parser->DefineTag('hr',         new \XBBC\SingleTag('<div class="hr"></div>', true));
		$parser->DefineTag('center',     new \XBBC\SimpleTag('<center>', '</center>'));
		$parser->DefineTag('color',      new ColorTag());
		$parser->DefineTag('a',          new AnchorTag());
		
		// Quotes
		$parser->DefineTag('quote',      new QuoteTag());
		
		// Titles
		$parser->DefineTag('h1',         new TitleTag(3));
		$parser->DefineTag('h2',         new TitleTag(4));
		$parser->DefineTag('h3',         new TitleTag(5));
		$parser->DefineTag('h4',         new TitleTag(6));
		
		// Lists
		$parser->DefineTag('list',       new ListTag());
		$parser->DefineTag('*',          new ListItemTag());
		
		// Tables
		$parser->DefineTag('table',      new TableTag());
		$parser->DefineTag('tr',         new TableRowTag());
		$parser->DefineTag('td',         new TableDataTag());
		$parser->DefineTag('th',         new TableDataTag(true));
		
		// Wowhead Link
		$parser->DefineTag('achievement', new AchievementTag());
		$parser->DefineTag('item',       new ItemTag());
		$parser->DefineTag('npc',        new NPCTag());
		$parser->DefineTag('object',     new ObjectTag());
		$parser->DefineTag('quest',      new QuestTag());
		$parser->DefineTag('spell',      new SpellTag());
		$parser->DefineTag('db',         new DBTag());
		
		// More WoW stuff
		$parser->DefineTag('blizzquote', new BlizzquoteTag());
		$parser->DefineTag('mmoquote',   new MmoquoteTag());
		$parser->DefineTag('socket',     new SocketTag());
		$parser->DefineTag('icon',       new IconTag());
		$parser->DefineTag('class',      new ClassTag());
		$parser->DefineTag('race',       new RaceTag());
		
		// Components
		$parser->DefineTag('spoiler',    new SpoilerTag());
		$parser->DefineTag('toggler',    new TogglerTag());
		
		// Root
		$parser->RootTag(new \UCode\URoot());
		
		// Smilies
		$parser->DefineSmiley(':)', 'smile.png');
		$parser->DefineSmiley('=)', 'smile.png');
		$parser->DefineSmiley(':|', 'neutral.png');
		$parser->DefineSmiley('=|', 'neutral.png');
		$parser->DefineSmiley(':(', 'sad.png');
		$parser->DefineSmiley('=(', 'sad.png');
		$parser->DefineSmiley(':D', 'big_smile.png');
		$parser->DefineSmiley('=D', 'big_smile.png');
		$parser->DefineSmiley(':O', 'yikes.png');
		$parser->DefineSmiley(';)', 'wink.png');
		$parser->DefineSmiley(':/', 'hmm.png');
		$parser->DefineSmiley(':P', 'tongue.png');
		$parser->DefineSmiley(':lol:', 'lol.png');
		$parser->DefineSmiley(':mad:', 'mad.png');
		$parser->DefineSmiley(':rolleyes:', 'roll.png');
		$parser->DefineSmiley(':cool:', 'cool.png');
	}
}
