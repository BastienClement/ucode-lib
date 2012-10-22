<?php

namespace UCode;

//
// [spoiler]
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
// [toggler]
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

//
// [tabs]
//
class TabsTag extends \XBBC\TagDefinition {
	protected $display = \XBBC\DISPLAY_BLOCK;
	
	protected $tabs = array();
	protected $tabs_counter = 0;
	
	public function AllowText() {
		return false;
	}
	
	public function CanShift($tag) {
		if($tag instanceof TabTag) {
			$this->tabs[] = $tag;
			$tag->Init(++$this->tabs_counter);
			return true;
		}
		
		return false;
	}
	
	public function Reduce() {
		// Which tab is open
		$open_tab = 0;
		foreach($this->tabs as $i => $tab) {
			if($tab->IsOpen()) {
				$open_tab = $i;
				break;
			}
		}
		
		// Tabs
		$tabs = array();
		foreach($this->tabs as $i => $tab) {
			if(($icon = $tab->GetIcon()) && ($icon_url = WowheadTag::GetIconURL($icon))) {
				$icon = '<img src="'.$icon_url.'" alt="'.$icon.'" /> ';
			} else {
				$icon = '';
			}
			
			$classes = 'tab tab-'.$tab->GetId().($open_tab == $i ? ' active' : '');
			$link =  '<a href="#" class="'.$classes.'" onclick="ucode.tabs(this, \''.$tab->GetId().'\'); return false;">';
			
			$tabs[] = $link.$icon.htmlspecialchars($tab->GetTitle()).'</a>';
		}
		$tabs = '<div class="tabs">'.implode('', $tabs).'</div>';
		
		// Contents
		$contents = array();
		foreach($this->tabs as $i => $tab) {
			$classes = 'tab-content tab-content-'.$tab->GetId().($open_tab == $i ? ' active' : '');
			$contents[] = '<div class="'.$classes.'">'.$tab->GetContent().'</div>';
		}
		$contents = '<div class="tabs-contents">'.implode('', $contents).'</div>';
		
		// Everything
		return '<div class="tabs-wrapper">'.$tabs.$contents.'</div>';
	}
}

//
// [tab]
//
class TabTag extends \XBBC\RootTag {
	protected $display = \XBBC\DISPLAY_SPECIAL;
	protected $id;
	
	public function Init($id) {
		$this->id = $id;
	}
	
	public function GetId() {
		return $this->id;
	}
	
	public function GetTitle() {
		return $this->arg ? $this->arg : 'Onglet '.$this->id;
	}
	
	public function GetIcon() {
		return isset($this->xargs['icon']) ? $this->xargs['icon'] : null;
	}

	public function IsOpen() {
		return isset($this->xargs['open']);
	}
	
	public function GetContent() {
		return parent::Reduce();
	}
	
	public function Reduce() { return false; }
}
