<?php

namespace UCode;

//
// [table]
//
class TableTag extends \XBBC\SimpleTag {
	protected $max_nesting = 5;
	
	public function __construct() {
		parent::__construct('<table>', '</table>', true);
	}
	
	public function __create() {
		if(isset($this->xargs['compact'])) {
			$this->before = '<table class="compact">';
		}
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
class TableDataTag extends \XBBC\RootTag {
	protected static $text_tag;
	protected $max_nesting = 0;
	protected $table_header;
	
	public function __construct($header = false) {
		if($header)
			parent::__construct(null, "</th>", true);
		else
			parent::__construct(null, "</td>", true);
		
		$this->table_header = $header;
		$this->display = \XBBC\DISPLAY_SPECIAL;
		$this->strip_empty = false;
	}
	
	public function Reduce() {
		$this->before = $this->GenerateOpenTag();
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
		
		if(isset($this->xargs['width']))
			$attrs[] = 'style="width:'.((int) $this->xargs['width']).'%;"';
		
		$attrs = empty($attrs) ? '' : ' '.implode(' ', $attrs);
		return (($this->table_header) ? '<th' : '<td').$attrs.'>';
	}
	
	public function ReducePlaintext() {
		return "\n".parent::ReducePlaintext()."\n";
	}
}
