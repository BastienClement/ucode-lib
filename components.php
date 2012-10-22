<?php

namespace UCode;

//
// [spoiler], emulates a new document root
//
class SpoilerTag extends \XBBC\RootTag {
	public function __construct() {
		parent::__construct();
		$this->before = '<div class="spoiler"><div class="spoiler-inner">';
		$this->after = '</div></div>';
		$this->display = \XBBC\DISPLAY_BLOCK;
	}
}

//
// [toggler], emulates a new document root
//
class TogglerTag extends \XBBC\RootTag {
	public function __construct() {
		parent::__construct();
		$this->after = '</div></div>';
		$this->display = \XBBC\DISPLAY_BLOCK;
	}
	
	protected function __create() {
		$text = $this->arg ? htmlspecialchars($this->arg) : 'Ouvrir / fermer';
		$controller = '<a href="#" onclick="ucode.toggler(this); return false;" class="toggler-link">'.$text.'</a>';
		
		$classes = isset($this->xargs['open']) ? 'toggler toggled' : 'toggler';
		$this->before = '<div class="'.$classes.'">'.$controller.'<div class="toggler-inner">';
	}
}