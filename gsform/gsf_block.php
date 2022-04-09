<?php
/*
Plugin Name: gsform
Description: Guest/Comments Elements Bloker for Get Simple 3.x Main Class
Version: 1.1
Author: wDesign
Author URI: http://www.ecg.vot.pl/
*/

require_once(GSPLUGINPATH.GSF_DIR.'gsf_common.php');

class GsfBlock {
	var $tbd=array(); //Array of form data
	var $keys=array();
	var $bcat = 0;
	var $xmlfile=null;
	var $xmldata=null;
	var $data=array(); //Array of arrays containing forms definitions
	var $i18n=array();
	var $code='';
	var $ilewp=3;

	function __construct($dfile='',$bcat=0) {
		$this->bcat=$bcat;
		$this->xmlfile=$dfile;
		if (file_exists($dfile)) {
			$this->keys=array('dane','daty','ile');
			$this->getData(); //get data from xml file
		} else $this->xmldata = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><items></items>');
	}

	function getData($key=null) {
		if (!isset($key)) $this->tbd=array();
		$this->xmldata==array();
		if (file_exists($this->xmlfile)) {
			$this->xmldata = getXML($this->xmlfile);
			$i=0;
			foreach ($this->xmldata as $item) {
				if (isset($key)) $this->tbd[$key][]=(string)$item->dane;
				else {
					foreach ($this->keys as $k) {
						$this->tbd[$i][$k] = (string)$item->$k;
					}
					$i++;
				}
			}
		}
	}

	function is_banned($key,$val) {
		$files=array('ip'=>GSF_BANIP,'www'=>GSF_BANWWW,'email'=>GSF_BANMAIL,'words'=>GSF_BANWORDS);
		if (!isset($this->tbd[$key])) {
			$this->xmlfile=$files[$key];
			$this->getData($key);
			if (!isset($this->tbd[$key])) $this->tbd[$key]=array(); 
		}
		switch ($key) {
			case 'ip':
				if (in_array($val,$this->tbd[$key])) return true; break;
			case 'www':
				if (in_array($val,$this->tbd[$key])) return true; break;
			case 'email':
				if (in_array($val,$this->tbd[$key])) return true; break;
			case 'words':
				foreach ($this->tbd[$key] as $word)
					if (strpos($val,$word)!==false) return $word; break;
		}
		return false;
	}
// Displays backend form with category lists of banned items
	function chooseCat($bcat=0) {
		$this->showCode();
		echo '<h3>'.i18n_r('gsform/GSF_BANCAT').'</h3>';
		echo '<select id="selcat" class="gsfban gsfsel">';
		echo '<option value="0" '.($bcat==0?'selected':'').'>'.i18n_r('gsform/GSF_BANCHOOSE').'</option>';
		echo '<option value="1" '.($bcat==1?'selected':'').'>'.i18n_r('gsform/GSF_BANIP').'</option>';
		echo '<option value="2" '.($bcat==2?'selected':'').'>'.i18n_r('gsform/GSF_BANWWW').'</option>';
		echo '<option value="3" '.($bcat==3?'selected':'').'>'.i18n_r('gsform/GSF_BANMAIL').'</option>';
		echo '<option value="4" '.($bcat==4?'selected':'').'>'.i18n_r('gsform/GSF_BANWORDS').'</option>';
		echo '</select>';
	}

// display background form to add form's template
	function addForm() {
		$this->showCode();

		if (isset($_GET['j'])) $j=(int)$_GET['j']; else if (isset($_POST['j'])) $j=(int)$_POST['j']; else $j=0; //part number of entries
		$start=$j*$this->ilewp;
		$stop=$start+$this->ilewp;
		$ile=count($this->tbd);
		$i=0;

		echo '<form id="addfrm2" action="?id=gsform&amp;gsform_block" method="post">';
		echo '<input type="hidden" name="catb" value="'.$this->bcat.'" />';
		echo '<input type="hidden" name="j" value="'.$j.'" />';
		echo '<br /><br /><p class="pb">'.i18n_r('gsform/GSF_FLDADD').':</p>';
		echo '<table cellpading="2" cellspacing="0" class="tab" id="gsftbadd">';
		echo '<thead><tr><th>ID</th><th>'.i18n_r('gsform/GSF_DATA').'</th><th>'.i18n_r('gsform/GSF_ADDED').'</th><th>'.i18n_r('gsform/GSF_COUNT').'</th><th>U</th></tr></thead>';
		echo '<tbody>';

		if (!empty($this->tbd)) {
			foreach ($this->tbd as $k => $item) {
				if ($i<$start) { $i++; continue; }
				echo '<tr class="sortable">
					<td>'.++$i.'</td>
					<td><input type="text" name="dane[]" size="45" value="'.$item['dane'].'" class="fields req" /><input type="hidden" name="ids[]" value="'.$k.'" /></td>
					<td><input type="hidden" name="daty[]" value="'.$item['daty'].'" />'.retDate($item['daty']).'</td>
					<td><input type="hidden" name="ile[]" value="'.$item['ile'].'" />'.$item['ile'].'</td>
					<td><a href="#" class="gsfdel">X</a></td>
					</tr>';
				if ($i>$stop-1) { break; }
			}
		}
		echo '<tr class="gsfhide">
				<td>'.($i+1).'</td>
				<td><input type="text" name="dane[]" size="45" class="fields req" /></td>
				<td>-</td>
				<td>-</td>
				<td><a href="#" class="gsfdel">X</a></td>
				</tr></tbody></table>';
		echo '<div class="gsffl"><input type="submit" class="submit" value="'.i18n_r('gsform/GSF_SAVE').'" /></div>
				<div class="gsffr"><input type="button" class="submit" value="'.i18n_r('gsform/GSF_FLDADD').'" id="gsfadd" /></div>
				<div style="clear:both;"></div>';
		echo '</form>';
		
		echo '<p>';

		if (GSF_BACKEND) {
			if ($j>0) { echo '<a href="?id='.$_GET['id'].'&amp;gsform_block&amp;catb='.$this->bcat.'&amp;j='.($j-1).'">&laquo; Poprzednie</a>&nbsp;'; }
			if ($i<$ile) { echo '<a href="?id='.$_GET['id'].'&amp;gsform_block&amp;catb='.$this->bcat.'&amp;j='.($j+1).'">Następne &raquo;</a>'; }
		} else {
			if ($j>0) { echo '<a href="?id='.$_GET['id'].'&amp;showent=1&amp;j='.($j-1).'">&laquo; Poprzednie</a>&nbsp;'; }
			if ($i<$ile) { echo '<a href="?id='.$_GET['id'].'&amp;showent=1&amp;j='.($j+1).'">Następne &raquo;</a>'; }
		}
		echo '</p>';

	}

// saves to xml file form's temaplate
	function savForm() {

		//echo '<br />'; print_r($_POST); echo '<br />';
		
		$start=(int)$_POST['j']*$this->ilewp;
		$stop=$start+$this->ilewp;
		$ile=count($this->tbd);
		if ($stop>$ile) $stop=$ile;
		for ($i=$start; $i<$stop; $i++) {
			if (!isset($_POST['ids']) || !in_array($i,$_POST['ids'])) unset($this->tbd[$i]);
		}
		//print_r($this->tbd); return;
		$tbdane=array();
		foreach ($this->tbd as $item) {
			$tbdane[]=$item['dane'];
		}
		array_pop($_POST['dane']); //get off last tr
		foreach ($_POST['dane'] as $k => $dane) {
			//$dane=cleantxt($dane);
			if (empty($dane)) continue;
			$ok=false;
			switch($this->bcat) {
				case 1:
					$ok=filter_var($dane,FILTER_VALIDATE_IP);
					if (!$ok) $this->addMsg('gsfwarning','GSF_ERRIP',$dane); break;
				case 2:
					if (strpos($dane,'www.')===0) $dane=substr($dane,4);
					else if (strpos($dane,'http://')===0) $dane=substr($dane,7);
					$ok=filter_var('http://'.$dane,FILTER_VALIDATE_URL);
					if (!$ok) $this->addMsg('gsfwarning','GSF_ERRWWW',$dane); break;
				case 3:
					$ok=filter_var($dane,FILTER_VALIDATE_EMAIL);
					if (!$ok) $this->addMsg('gsfwarning','GSF_ERREMAIL',$dane); break;
				case 4:
					$ok=true;
				break;
				default:
					$this->addMsg('gsfwarning','GSF_ERRWORD',$data);
					continue;
			}
			//echo $dane.' + ';
			if ($ok) {
				@$id=$_POST['ids'][$k];
				if (!in_array($dane,$tbdane)) {
					if (isset($this->tbd[$id])) $this->tbd[$id]=array_combine($this->keys,array($dane,$_POST['daty'][$k],$_POST['ile'][$k]));
					else $this->tbd[]=array_combine($this->keys,array($dane,time(),0));
				} else if (!isset($this->tbd[$id])) $this->addMsg('gsfwarning','GSF_ENTEXIST',': '.$dane);
			}
		}

		//print_r($this->tbd); return;

		$this->xmldata = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><items></items>');
		foreach ($this->tbd as $dane) {
			$item = $this->xmldata->addchild('item');
			foreach ($this->keys as $k)
				$item->addChild($k,$dane[$k]);
		}
//		print_r($this->xmldata); return;
		$this->savXML();
		$this->getData();
	}

	function savXml() {
		if (file_exists($this->xmlfile)) {
			rename($this->xmlfile, GSBACKUPSPATH.GSF_DIR.basename($this->xmlfile,'.xml').'.xml');
		}
		if (is_null($this->xmlfile) || !XMLsave($this->xmldata,$this->xmlfile)) {
			$this->addMsg('gsferror','GSF_NOSAVED');
			return false;
		} else {
			$this->addMsg('info','GSF_SAVED');
			return true;
		}
	}

	function addCode($code) {
		$this->code.=$code;
	}
	function addMsg($class,$msg,$add=null,$ret=false) {
		$str='<p class="'.$class.'">'.i18n_r('gsform/'.$msg).(is_null($add)?'':': '.$add).'</p>';
		if ($ret) return $str; else $this->code.=$str;
	}
	function showCode() {
		echo $this->code;
	}
}
?>