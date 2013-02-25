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
	
	public function ReducePlaintext() {
		return "[SPOILER]\n".\XBBC\TagTools::PrefixLines(parent::ReducePlaintext(), "| ");
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
	
	protected function GetTogglerText() {
		return $this->arg ? htmlspecialchars($this->arg) : 'Ouvrir / fermer';
	}
	
	protected function __create() {
		$text = $this->GetTogglerText();
		if(isset($this->xargs['icon']) && ($icon_url = WowheadTag::GetIconURL($this->xargs['icon']))) {
			$icon = '<img src="'.$icon_url.'" class="wow-icon" alt="'.$this->xargs['icon'].'" /"> ';
		} else {
			$icon = '';
		}
		
		$controller = '<a href="#" onclick="ucode.toggler(this); return false;" class="toggler-link">'.$icon.$text.'</a>';
		
		if(isset($this->xargs['ej'])) {
			$ejFlags = $this->xargs['ej'];
		
			for($i = 0, $len = strlen($ejFlags); $i < $len; $i++) {
				switch($ejFlags[$i]) {
					case "D":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-deadly.png" class="ej-icon" alt="" /">';
						break;
					case "I":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-important.png" class="ej-icon" alt="" /">';
						break;
					case "H":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-heroic.png" class="ej-icon" alt="" /">';
						break;
					case "m":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-magic.png" class="ej-icon" alt="" /">';
						break;
					case "p":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-poison.png" class="ej-icon" alt="" /">';
						break;
					case "o":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-disease.png" class="ej-icon" alt="" /">';
						break;
					case "t":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-tank.png" class="ej-icon" alt="" /">';
						break;
					case "h":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-healer.png" class="ej-icon" alt="" /">';
						break;
					case "d":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-dps.png" class="ej-icon" alt="" /">';
						break;
					case "k":
						$controller .= ' <img src="http://wow.zamimg.com/images/icons/ej-interrupt.png" class="ej-icon" alt="" /">';
						break;
				}
			}
		}
		
		$classes = isset($this->xargs['open']) ? 'toggler toggled' : 'toggler';
		$this->before = '<div class="'.$classes.'">'.$controller.'<div class="toggler-inner">';
	}
	
	public function ReducePlaintext() {
		return '[- '.$this->GetTogglerText()."]\n".\XBBC\TagTools::PrefixLines(parent::ReducePlaintext(), "| ");
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

	public function ReducePlaintext() {
		return \XBBC\TagTools::PrefixLines(trim(parent::ReducePlaintext()), "|");
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
	
	public function ReducePlaintext() {
		return "\n-[".$this->GetTitle()."]\n".\XBBC\TagTools::PrefixLines(parent::ReducePlaintext(), " ")."\n";
	}
}

//
// [video]
//
class VideoTag extends \XBBC\TagDefinition {
	protected $display = \XBBC\DISPLAY_BLOCK;
	
	protected function __create() {
		
	}
}
