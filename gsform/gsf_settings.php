<?php
/*
Plugin Name: gsform
Description: Guest/Comments Book for Get Simple 3.x Main Class
Version: 1.0
Author: wDesign
Author URI: http://www.ecg.vot.pl/
*/

class gsfset {
	var $tbd=array(); //Array of form data
	var $xmlfile=null;
	var $xmldata=null;
	var $data=array(); //Array of arrays containing forms definitions
	//array of global settings with defaults values
	var $settings=array();
	var $i18n=array();
	var $code='';

	function __construct() {
		$this->getSettings(); //get settings
	}

	function getSettings() {
		if (file_exists(GSF_FILE)) {
			$data = getXML(GSF_FILE);
			foreach ($data->children() as $child)
				$this->settings[$child->getName()] = (string) $child;
		} else {
			$this->settings=array();
		}
	}

	function savSettings() {
		foreach ($this->settings as $k => $v)
			if (isset($_POST[$k])) $this->settings[$k]=trim($_POST[$k]); else $this->settings[$k]=0;
		if ($this->settings['commnbr'] == 0) $this->settings['commnbr'] = 5;
		$data= @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
		foreach ($this->settings as $k => $v)
			$data->addChild($k,$v);
		if (!XMLsave($data,GSF_FILE)) {
			$error = i18n_r('CHMOD_ERROR');
		} else $error=i18n_r('gsform/GSF_SAVED');
		return $error;
	}

	function addCode($code) {
		$this->code.=$code;
	}
	function addMsg($class,$msg,$add=null,$ret=false) {
		$str='<p class="'.$class.'">'.i18n_r('gsform/'.$msg).(is_null($add)?'':$add).'</p>';
		if ($ret) return $str; else $this->code.=$str;
	}
	function showCode() {
		echo $this->code;
	}
}
?>