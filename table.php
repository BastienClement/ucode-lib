<?php

namespace UCode;

//
// [table]
//
class TableTag extends \XBBC\SimpleTag {
	protected $max_nesting = 5;
	
	public function __construct() {
		parent::__construct('<table cellspacing="0" cellpadding="0">', '</table>', true);
	}
	
	public function AllowText() {
		return false;
	}
	
	public function CanShift($tag) {
		return $tag instanceof TableRowTag;
	}
}

//
// [tr]
//
class TableRowTag extends \XBBC\SimpleTag {
	protected $max_nesting = 0;
	
	public function __construct() {
		parent::__construct("<tr>", "</tr>", true);
		$this->display = \XBBC\DISPLAY_SPECIAL;
	}
	
	public function AllowText() {
		return false;
	}
	
	public function CanShift($tag) {
		return $tag instanceof TableDataTag;
	}
}

//
// [td] / [th]
//
class TableDataTag extends \XBBC\SimpleTag {
	protected static $text_tag;
	protected $max_nesting = 0;
	
	public function __construct($header = false) {
		if($header)
			parent::__construct(null, "</th>", true);
		else
			parent::__construct(null, "</td>", true);
		
		$this->display = \XBBC\DISPLAY_SPECIAL;
		$this->strip_empty = false;
	}
	
	public function Bufferize($text) {
		$inner = $this->CreateInnerTag();
		$this->ctx->stack->Push($inner);
		$inner->Bufferize($text);
	}
	
	public function CanShift($tag) {
		if($tag->Display() == DISPLAY_INLINE):
			$inner = $this->CreateInnerTag();
			$this->ctx->stack->Push($inner);
		endif;
		
		return true;
	}
	
	protected function CreateInnerTag() {
		if(!self::$text_tag)
			self::$text_tag = new TableTextTag;
		return self::$text_tag->create($this->ctx);
	}
	
	public function StripWhitespaces() { return false; }
	
	public function Reduce() {
		$this->before = $this->GenerateOpenTag();
		$this->content = preg_replace('/^(\s|<br \/>)+|(\s|<br \/>)+$/', '', $this->FlushText()->content);
		return parent::Reduce();
	}
	
	protected function GenerateOpenTag() {
		$attrs = array();
		
		if(isset($this->xargs['align']))
			$attrs[] = 'align="'.htmlspecialchars($this->xargs['align']).'"';
		
		if(isset($this->xargs['valign']))
			$attrs[] = 'valign="'.htmlspecialchars($this->xargs['valign']).'"';
		
		if(isset($this->xargs['rowspan']))
			$attrs[] = 'rowspan="'.((int) $this->xargs['rowspan']).'"';
		
		if(isset($this->xargs['colspan']))
			$attrs[] = 'colspan="'.((int) $this->xargs['colspan']).'"';
		
		$attrs = empty($attrs) ? '' : ' '.implode(' ', $attrs);
		return (($header) ? '<th' : '<td').$attrs.'>';
	}
}

//
// Special handler for table inner-cell data
//
class TableTextTag extends \XBBC\MainTag {
	public function __construct() {
		parent::__construct();
		$this->before = $this->after = '';
	}
	
	protected function __create() {
		$this->element = '$table-text';
	}
}
