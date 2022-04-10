<?php
/*
Plugin Name: gsform
Description: Guest/Comments Book for Get Simple 3.x Main Class
Version: 1.0
Author: wDesign
Author URI: http://www.ecg.vot.pl/
*/

require_once(GSPLUGINPATH.GSF_DIR.'gsf_common.php');

class gsform {
	var $tbd=array(); //Array of form data
	var $keys=array('fid','fname','fentnr','ftitle','fquery','femail');
	var $xmlfile=null;
	var $xmldata=null;
	var $saved=true;//is user form data has been saved
	//array of global settings with defaults values
	var $cnindex=0;
	var $i18n=array();
	var $code='';

	function __construct() {
		$this->getFormsData(); //get data form's definitions
	}

	function getFormsData() {
		$this->xmldata=$this->tbd=array();
		if (file_exists(GSF_FORMS)) {
			$this->xmldata = getXML(GSF_FORMS);
			foreach ($this->xmldata as $form) {
				$tb=array();
				foreach ($this->keys as $key)
					$tb[$key]=(string)$form->$key;
				//$tb=array('fname'=>(string)$form->fname,'fentnr'=>(string)$form->fentnr,'ftitle'=>(string)$form->ftitle);
				foreach ($form->fields->field as $field) {
					$opts=array();
					foreach ($field->options->option as $opt) {
						$opt=(string)$opt;
						if (!empty($opt)) $opts[]=(string)$opt;
					}
					//$opts=implode('',$opts);
					$tb['fields'][]=array(
						'name'=>(string)$field->name,
						'label'=>(string)$field->label,
						'tip'=>(string)$field->tip,
						'type'=>(string)$field->type,
						'value'=>(string)$field->value,
						'req'=>(string)$field->req,
						'opts'=>$opts);
				}
				$this->tbd[$tb['fid']]=$tb;
			}
			//print_r($this->tbd);
		} else
			$this->xmldata = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><forms></forms>');
	}

// backend function to display list of definied forms
	function listForms() {
		$this->showCode();
		echo '<h3>'.i18n_r('gsform/GSF_LIST').'</h3>
				<form id="addform" action="?id=gsform&amp;gsform_list" method="post">
				<table cellpading="2" cellspacing="0" class="tab" id="gsffrm">
				<thead>
				<tr><th>Lp.</th><th>'.i18n_r('gsform/GSF_FNAME').'</th><th>'.i18n_r('gsform/GSF_FPASTE').'</th><th>'.i18n_r('gsform/GSF_FORMS').'</th><th>'.i18n_r('gsform/GSF_FLDDEL').'</th></tr>
				</thead>
				<tbody>';
		$i=0;
		$dir=GSDATAPATH.GSF_DIR;
		$files=array_diff(scandir($dir),array('..','.','.htaccess'));
//print_r($this->tbd);
		if (!empty($this->tbd)) {
			foreach ($this->tbd as $fid => $form) {
				echo '<tr><td>'.++$i.'</td>
					<td><a href="?id=gsform&amp;gsform_add&amp;fid='.$fid.'">'.$form['fname'].'</a></td>
					<td>(% gsform name="'.$form['fname'].'" %)</td><td>';
				foreach ($files as $file) {
					if (stripos($file,$form['fname']) !== false) {
						echo '<a href="?id=gsform&amp;gsform_list&amp;file='.$file.'&amp;fid='.$fid.'">'.$file.'</a><br />';
					}
				}
				echo '</td>
					<td><input type="checkbox" name="tdel[]" value="'.$fid.'" /></td>
					</tr>';
			}
		}
		echo '</tbody></table>
				<br /><br />
				<div class="gsffl"><input type="submit" class="submit" value="'.i18n_r('gsform/GSF_SAVE').'" /></div>
				<div style="clear:both;"></div>
				</form>';
	}

// display background form to add form's template
	function addForm($fid) {
		if (is_null($fid) || !array_key_exists($fid, $this->tbd)) {
			$fid='';
		}
		//$this->addCSSJS();
		$this->showCode();
		echo '<form id="addfrm1" action="?id=gsform&amp;gsform_add" method="post">
				<input type="hidden" name="fid" value="'.$fid.'" />
				<table>
				<tr>
				<td><label class="gsflabel">'.i18n_r('gsform/GSF_FNAME').'</label><input type="text" name="fname" size="15" value="'.@$this->tbd[$fid]['fname'].'" /></td>
				<td><label class="gsflabel">'.i18n_r('gsform/GSF_ENTRIESNR').'</label><input type="text" name="fentnr" size="5" value="'.@$this->tbd[$fid]['fentnr'].'" /></td>
				</tr>
				<tr>
				<td><label class="gsflabel">'.i18n_r('gsform/GSF_FTITLE').'</label><input type="text" name="ftitle" size="40" value="'.@$this->tbd[$fid]['ftitle'].'" /></td>
				<td><label class="gsflabel">'.i18n_r('gsform/GSF_QUERY').'</label><input type="checkbox" name="fquery" value="on"'.(@$this->tbd[$fid]['fquery']?' checked="checked"':'').' /></td>
				</tr>
				<tr><td><label class="gsflabel">'.i18n_r('gsform/GSF_FEMAILTO').'</label><input type="text" name="femail" size="40" value="'.@$this->tbd[$fid]['femail'].'" /></td></tr>
				<tr><td colspan="2">Dodano: '.(!empty($fid) ? date('Y-m-d G:i:s',$fid) : date('Y-m-d G:i:s',time())).'</td></tr>
				</table>
				<br /><p class="pb">'.i18n_r('gsform/GSF_FLDADD').':</p>
				<table cellpading="2" cellspacing="0" class="tab" id="gsftbadd">
				<thead>
				<tr><th>Lp.</th><th>'.i18n_r('gsform/GSF_FLDNAME').'</th><th>'.i18n_r('gsform/GSF_FLDLABEL').'</th><th>'.i18n_r('gsform/GSF_FLDTIP').'</th><th width="125px;">'.i18n_r('gsform/GSF_FLDTYPE').'</th><th>'.i18n_r('gsform/GSF_FLDVALUE').'</th><th>R</th><th>U</th></tr>
				</thead>
				<tbody>';
		$i=0;
		if (!empty($this->tbd[$fid]['fields'])) {
			foreach ($this->tbd[$fid]['fields'] as $k => $field) {
				echo '<tr class="sortable"><td>'.++$i.'</td>
					<td><input type="text" name="fldnames[]" size="10" value="'.$field['name'].'" class="fields req" /></td>
					<td><input type="text" name="fldlabels[]" size="10" value="'.$field['label'].'" class="fields req" /></td>
					<td><input type="text" name="fldtips[]" size="10" value="'.$field['tip'].'" class="fields" /></td>
					<td>'.$this->mksel('gsfopt','fldtypes[]',$field['type']).'
						<textarea name="fldoptions[]"'.(empty($field['opts'])?' class="gsfhide"':'').' style="width: 120px; height: 50px; padding: 2px;">'.implode('',$field['opts']).'</textarea>
					</td>
					<td><input type="text" name="fldvalues[]" size="10" value="'.$field['value'].'" /></td>
					<td><input type="checkbox" name="fldreq[]" value="'.$field['name'].'"'.($field['req']?' checked="checked"':'').' /></td>
					<td class="gsfcenter"><a href="#" class="gsfdel">X</a></td>
					</tr>';
			}
		}
		echo '<tr class="gsfhide">
				<td>'.++$i.'</td>
				<td><input type="text" name="fldnames[]" size="10" class="fields req" /></td>
				<td><input type="text" name="fldlabels[]" size="10" class="fields req" /></td>
				<td><input type="text" name="fldtips[]" size="10" class="fields" /></td>
				<td>'.$this->mksel('gsfopt','fldtypes[]').'
					<textarea name="fldoptions[]" class="gsfhide" style="width: 120px; height: 50px; padding: 2px;"></textarea>
				</td>
				<td><input type="text" name="fldvalues[]" size="10" /></td>
				<td><input type="checkbox" name="fldreq[]" class="newchk" /></td>
				<td class="gsfcenter"><a href="#" class="gsfdel">X</a></td></tr></tbody></table>';
		echo 'R - '.i18n_r('gsform/GSF_FLDREQ').'<br />U - '.i18n_r('gsform/GSF_FLDDEL');
		echo '<br /><br />';
		echo '<div class="gsffl"><input type="submit" class="submit" value="'.i18n_r('gsform/GSF_SAVE').'" /></div>
				<div class="gsffr"><input type="button" class="submit" value="'.i18n_r('gsform/GSF_FLDADD').'" id="gsfadd" /></div>
				<div style="clear:both;"></div>';
		echo '</form>';
	}

// saves to xml file form's temaplate
	function savForm() {
		if (!empty($_POST['fid']) && is_numeric($_POST['fid']) && strlen($_POST['fid'])==10) $fid=$_POST['fid']; else $fid=time();
		if (array_key_exists($fid,$this->tbd)) {
			unset($this->tbd[$fid]);
			$k=0;
			foreach ($this->xmldata as $form) {
				if ((string)$form->fid == $fid) {
					unset($this->xmldata->form[$k]);
					break;
				}
				$k++;
			}
		}
//print_r($_POST); return;
		$fname=strtolower(nopl(htmlstrip($_POST['fname'])));
		if (strlen($fname)<2) { $this->addMsg('gsferror','GSF_MINLENERR'); return; }
		foreach ($this->tbd as $form)
			if ($form['fname']==$fname) { $this->addMsg('gsferror','GSF_FEXIST'); }
		$fentnr=(int)$_POST['fentnr'];
		if (!is_int($fentnr)) $fentnr=5;
		$ftitle=cleantxt($_POST['ftitle']);
		isset($_POST['fquery'])?$fquery=$_POST['fquery']:$fquery='';
		$femail='';
		if (isset($_POST['femail'])) {
			$femail=trim($_POST['femail']);
			if (!filter_var($femail, FILTER_VALIDATE_EMAIL)) {
	  		$this->addMsg('gsferror','GSF_ERREMAIL');
	  		$femail='';
			}
		}

		array_pop($_POST['fldnames']); //get off last tr
		array_pop($_POST['fldoptions']);
		foreach ($_POST['fldnames'] as $k => $fldname) {
			$fldname=strtolower(nopl(htmlstrip($fldname)));
			if (strlen($fldname)<2) { $this->addMsg('gsferror','GSF_MINLENERR'); return; }
			$fldlabel=htmlstrip($_POST['fldlabels'][$k]);
			if (strlen($fldlabel)<2) { $this->addMsg('gsferror','GSF_MINLENERR'); return; }
			$opts=array();
			if (isset($_POST['fldoptions'][$k])) $opts=explode("\n",$_POST['fldoptions'][$k]);
			if (isset($_POST['fldreq'][$k])) $req=1; else $req=0;
			$fldstb[]=array(
				'name'=>$fldname,
				'label'=>$fldlabel,
				'tip'=>cleantxt($_POST['fldtips'][$k]),
				'type'=>$_POST['fldtypes'][$k],
				'value'=>$_POST['fldvalues'][$k],
				'req'=>$req,
				'opts'=>$opts);
		}
		//addFormXml
		$form = $this->xmldata->addChild('form');
		foreach ($this->keys as $k) $form->addChild($k,${$k});
//print_r($this->xmldata); return;
		$fields = $form->addChild('fields');
		foreach ($fldstb as $k => $tb) {
			$field = $fields->addChild('field');
			$field->addChild('name',$tb['name']);
			$field->addChild('label',$tb['label']);
			$field->addChild('tip',$tb['tip']);
			$field->addChild('type',$tb['type']);
			$field->addChild('value',$tb['value']);
			$field->addChild('req',$tb['req']);
			$options = $field->addChild('options');
			foreach ($tb['opts'] as $opt) {
				$options->addChild('option',trim($opt," "));
			}
		}
		//print_r($this->xmldata); return;
		$this->savXML();
		$this->getFormsData();
		return $fid;
	}

	function delForm() {
		$ile=count($this->xmldata->children());
		$i=0;
		for ($j=0; $j<$ile; $j++) {
			$fid=(string)$this->xmldata->form[$i]->fid;
			if (in_array($fid,$_POST['tdel'])) {
				unset($this->xmldata->form[$i]);
				$i--;
			}
			$i++;
		}

		$ile=count($this->xmldata->children());
		if ($ile > 0) {
			$this->savXML();
		} else @unlink(GSF_FORMS);
		$this->getFormsData();
	}

	function savXml() {
		if (is_null($this->xmlfile)) $this->xmlfile=GSF_FORMS;
		if (!XMLsave($this->xmldata,$this->xmlfile)) {
			$this->addMsg('gsferror','GSF_NOSAVED');
			return false;
		} else {
			$this->addMsg('info','GSF_SAVED');
			return true;
		}
	}

	function execForm($params) {
		$this->showCode();
		if (empty($this->tbd)) {
			$this->addMsg('info','GSF_NOFORM');
			return $this->showCode();
			//if (!GSF_BACKEND) $this->showForms();
		} else {
			$rep='';
			if (isset($_POST['idf']) && array_key_exists($_POST['idf'],$this->tbd)) {//save form's data
				ob_start();
				$this->saveUserForm($params);
				$this->showCode();
				$rep=ob_get_contents();
				ob_get_clean();
			}
			//if (isset($_GET['acc'])) { $this->showForm(); return; } //show form to add comments
			if (isset($_GET['success'])) { $this->addMsg('gsfsuccess','GSF_SAVED'); }
			if (isset($_GET['error'])) $this->addMsg('gsferror','GSF_NOSAVED');
			if (!isset($params['showfrm'])) $params['showfrm']=1;//dealing with pressing button add
			if (isset($params['showbtn']) && $params['showbtn']==1) $params['showfrm']=0;
			foreach ($this->tbd as $fid => $form) {
				if ($form['fname']==$params['name']) {
					if (($params['showfrm'])) $rep.=$this->showUserForm($params,$fid); //show user defined form
					if (isset($params['showent'])) $rep.=$this->showUserEn($params,$fid); //show entries
					if (isset($params['showbtn'])) $rep.='<div><a href="?id='.return_page_slug().'&amp;showfrm=1">'.i18n_r('gsform/GSF_ADDENT').'</a></div>';
					$nofrm=0;
					break;
				} else $nofrm=1;
			}
			if ($nofrm) $rep='<p class="gsfwarning">'.i18n_r('gsform/GSF_NOFRMTMP').' '.$params['name'].'.</p>';
			return $rep;
		}
	}
	// display definied forms with given $fid on page
	function showUserForm($params,$fid) {
		$form=$this->tbd[$fid];
		//print_r($_POST);
		ob_start();
		echo '<form action="?id='.return_page_slug().'" method="post" id="userForm">
			<input type="hidden" name="idf" value="'.$fid.'" />
			<input type="hidden" name="fdate" value="'.time().'" />
			<p class="gsftitle">'.$form['ftitle'].'</p>
			<table class="gsftab">';
		foreach ($form['fields'] as $field) $tbnames[]=$field['name'];
		$tbnames=array_count_values($tbnames);
		foreach ($form['fields'] as $k => $field) {
			echo '<tr><td><label class="'.$field['name'].'" for="'.$field['name'].'">'.$field['label'].($field['req']?' <span class="gsfreqmark">*</span> ':'').'</label></td><td>';
			echo '<input type="hidden" id="'.$field['name'].'c" value="'.$field['tip'].'" />'; //tooltip
			if (!$this->saved && isset($_POST[$field['name']])) $val=cleantxt($_POST[$field['name']]); else $val='';
			switch ($field['type']) {
				case 'text':
				case 'email':
				case 'www':
					echo '<input name="'.$field['name'].'" id="'.$field['name'].'" type="text" class="'.($field['req']?'gsfreq ':'').'gsfinp gsftip" value="'.$val.'" '.($field['req']?'required':'').'/>';
				break;
				case 'textarea':
					echo '<textarea name="'.$field['name'].'" id="'.$field['name'].'" class="'.($field['req']?'gsfreq ':'').'gsftxta gsftip">'.$val.'</textarea>';
				break;
				case 'checkbox':
					if ($tbnames[$field['name']]>1)
						echo '<input name="'.$field['name'].'[]" id="'.$field['name'].'" type="checkbox" class="'.($field['req']?'gsfreq ':'').'gsfchk gsftip" value="'.$k.'" />';
					else
						echo '<input name="'.$field['name'].'" id="'.$field['name'].'" type="checkbox" class="'.($field['req']?'gsfreq ':'').'gsfchk gsftip" value="'.(empty($field['value']) ? 'on' : $field['value']).'" />';
				break;
				case 'radio':
					foreach ($field['opts'] as $opt) {
						echo '<input name="'.$field['name'].'" id="'.$field['name'].'" type="radio" class="'.($field['req']?'gsfreq ':'').'gsfinp gsftip" value="'.trim($opt).'" />&nbsp;<label for="'.$field['name'].'">'.trim($opt).'</label>&nbsp;&nbsp;';
					}
				break;
				case 'select':
					echo '<select name="'.$field['name'].'" id="'.$field['name'].'" class="'.($field['req']?'gsfreq ':'').'gsfsel gsftip">';
					foreach ($field['opts'] as $opt) {
						echo '<option value="'.trim($opt).'">'.$opt.'</option>';
					}
					echo '</select>';
				break;
				case 'password':
					echo '<input name="'.$field['name'].'" id="'.$field['name'].'" type="password" class="'.($field['req']?'gsfreq ':'').'gsfinp gsftip" />';
				break;
				case 'hide':
					echo '<input name="'.$field['name'].'" type="hidden" class="gsfinp" />';
				break;
				default:
					echo '';
			}
				echo '</td></tr>';
		}
		echo '<tr><td colspan="2"><div id="gsftip">&nbsp;</div></td></tr>';
		echo '<tr><td><span class="gsfreqinfo"><span class="gsfreqmark">*</span> &#8211; '.i18n_r('gsform/GSF_REQUIRED').'</span></td><td><div class="gsffr"><input class="gsfbtn" type="submit" value="'.i18n_r('gsform/GSF_SAVE').'" />&nbsp;<input class="gsfbtn" type="reset" value="'.i18n_r('gsform/GSF_CLEAR').'" /></div></td></tr>
				</table>
				<div id="gsfinfo" class="gsfwarning"></div>
				</form><br />';
		$rep=ob_get_contents();
		ob_get_clean();
		return $rep;
	}

	function saveUserForm($params) {//saving users forms data
		//print_r($_POST); return;
		require_once(GSPLUGINPATH.GSF_DIR.'gsf_block.php');
		$gsfblock = new GsfBlock();
		if ($gsfblock->is_banned('ip',$this->_get_raddr())) {
			$this->addMsg('gsfwarning','GSF_ERRIP',': '.$this->_get_raddr());
			return;
		}

		$err=false;
		$fid=$_POST['idf'];
		$fname=$this->tbd[$fid]['fname'];
		$fentnr=$this->tbd[$fid]['fentnr'];
		$ftitle=$this->tbd[$fid]['ftitle'];
		$fquery=$this->tbd[$fid]['fquery'];
		$femail=$this->tbd[$fid]['femail'];
		$this->xmlfile = GSDATAPATH.GSF_DIR.$fname.'_'.return_page_slug().'.xml';
		if (file_exists($this->xmlfile)) {
			$this->xmldata=getXML($this->xmlfile);
			foreach ($this->xmldata->items->item as $item) {
				if ((string)$item->fdate==$_POST['fdate']) {
					$this->addMsg('gsfinfo','GSF_ALREADYSAVED');
					return;
				}
			}
		} else {
			$this->xmldata = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><form></form>');
			foreach ($this->keys as $k)
				$this->xmldata->addChild($k,${$k});
			$items=$this->xmldata->addChild('items');
		}
		$item=$this->xmldata->items->addChild('item');
		$errkom=0;
		$this->saved=false; //is data has been saved?
//print_r($this->tbd[$fid]['fields']); return;
		$message=array(); // var to store received data
		$iscontent=false;
		foreach ($this->tbd[$fid]['fields'] as $k => $field) {
			$fdata=@$_POST[$field['name']];
			if ($field['req'] && empty($fdata)) {//pole wymagane
				$this->addMsg('gsfinfo','GSF_FLDREQ',$field['name']);
				return;
			}
			switch ($field['type']) {
				case 'text':
				case 'textarea':
					$fdata=cleantxt(antixss($fdata));
					//var_dump($fdata);
					if ($word=$gsfblock->is_banned('words',strtolower($fdata))) {
						$this->addMsg('gsfwarning','GSF_ERRWORD',': '.$word);
						return;
					}
				break;
				case 'checkbox':
					if (is_array($fdata)) {
						if (in_array($k,$fdata)) $fdata=$field['label'];
						else { $fdata=''; }
					} else $fdata=cleantxt($fdata);
				break;
				case 'email':
					$fdata=cleantxt(strtolower(antixss($fdata)));
					if (empty($fdata)) break;
					if (!filter_var($fdata,FILTER_VALIDATE_EMAIL) || $gsfblock->is_banned('email',$fdata)) {
						$this->addMsg('gsfwarning','GSF_ERREMAIL',': '.$fdata);
						return;
					}
				break;
				case 'www':
					$fdata=cleantxt(strtolower(antixss($fdata)));
					if (empty($fdata)) break;
					if (strpos($fdata,'www.')===0) $fdata=substr($fdata,4);
					else if (strpos($fdata,'http://')===0) $fdata=substr($fdata,7);
					if (!filter_var('http://'.$fdata,FILTER_VALIDATE_URL) || $gsfblock->is_banned('www',$fdata)) {
						$this->addMsg('gsfwarning','GSF_ERRWWW',': '.$fdata);
						return;
					}
				break;
				case 'password':
					$fdata=cleantxt(antixss($fdata));
				break;
				default:
					$fdata=cleantxt(antixss($fdata));
			}
			if (!empty($fdata)) $iscontent=true;
			$item->addChild($field['name'],$fdata);
			$message[$field['name']] = $fdata;
		}

		if (!$iscontent) {
			$this->addMsg('gsfwarning','GSF_FORMEMPTY');
			$errkom=1;
		} else {
			$item->addChild('fdate',$_POST['fdate']);
			$item->addChild('browser',$this->_get_browser());
			$item->addChild('raddr',$this->_get_raddr());
			//print_r($this->xmldata); return;
			$this->savXML();
			$this->saved=true;
			// if femail isn't empty send email with received data
			if (!empty($femail)) {
				$message_txt = '<p><strong>'.i18n_r('gsform/GSF_ENTRYADDED').' ('.date('Y-m-d G:i:s',time()).'):</strong></p>';
				foreach ($message as $k => $v) { // read the fields names and coressponding data
					$message_txt .= '<p>'.$k.': '.$v.'</p>';
				}
				if (empty($ftitle)) $ftitle=i18n_r('gsform/GSF_ENTRY');
				$this->gsf_sendmail($femail, $ftitle, $message_txt);
			}
			if ($errkom) $this->addMsg('gsfwarning','GSF_SAVED_WITH_ERR'); // else $this->addMsg('gsfsuccess','GSF_SAVED');
		}
		return;
	}
//
// Funkcja pokazuje formularz zdefiniowany przez użytkownika
// $params - zawiera tabelę parametrów odczytaną ze strony
// $fid - identyfikator formularza
//
	function showUserEn($params,$fid) {
		//print_r($params);
		if (isset($params['file'])) $this->xmlfile=GSDATAPATH.GSF_DIR.$params['file'];
		else if (isset($params['slug'])) $this->xmlfile=GSDATAPATH.GSF_DIR.$this->tbd[$fid]['fname'].'_'.$params['slug'].'.xml';
		else $this->xmlfile=GSDATAPATH.GSF_DIR.$this->tbd[$fid]['fname'].'_'.return_page_slug().'.xml';
		if (!file_exists($this->xmlfile)) {
			$this->addMsg('gsferror','GSF_NOFILE');
			return '';
		}
		$this->xmldata=getXML($this->xmlfile);

		foreach ($this->tbd[$fid]['fields'] as $field) {
			$tbnl[$field['name']][]=$field['label'];
		}

		if (isset($_GET['j'])) $j=(int)$_GET['j']; else $j=0; //part number of entries
		$fentnr=$this->tbd[$fid]['fentnr']; //ile wpisów na stronie
		$start=$j*$fentnr;
		$stop=$start+$fentnr;
		$ile=count($this->xmldata->items->children());
		$i=0;

		//is it questionnaire? czy to ankieta?
		$qfields=array();
		if (@$params['query']) {
			$j=1;
			do {
				if (isset($params['field'.$j])) { $qfields[$params['field'.$j]]=array(); $j++; }
				else $j=false;
			} while($j);
		}

		ob_start();
		echo '<h3>'.i18n_r('gsform/GSF_ENTRIES').'</h3>';
		if (GSF_BACKEND) {
			echo '<h4 class="gsflabel">Szablon: '.$this->tbd[$fid]['fname'].'</h4>
					<h4 class="gsflabel">Plik: '.$params['file'].'</h4>
					<br />';
			echo '<table class="gsftbadm">';
		} else {
			echo '<table class="gsftab">';
		}
//echo 'Start: '.$start.' - '.$stop.' z '.$ile.'<br /><br />';
		foreach ($this->xmldata->items->item as $item) {
//print_r($item);
			if (@$params['query']) { //podsumowanie wpisów (ankieta)
				foreach ($qfields as $k => $v) {
					if ($item->$k->count()<2) $qfields[$k][]=(string)$item->$k;
					else {
						unset($item->fdate); unset($item->browser); unset($item->raddr);
						foreach ($item as $f) {
							$f=(string)$f;
							if (!empty($f)) $qfields[$k][]=$f;
						}
					}
				}
				continue;
			} else if ($i<$start) { $i++; continue; } //lista wpisów

			echo '<tr>
					<td colspan="2"><span class="gsfenh">'.i18n_r('gsform/GSF_ENTRY').' '.++$i.'</span>&nbsp;&nbsp;&nbsp;'.i18n_r('gsform/GSF_ADDED').': '.date('Y-m-d G:i:s',(int)$item->fdate).'</td>
					</tr>';

			foreach ($item as $f) {
				//echo $f->getName(); echo $f->count(); echo (string)$f;  && ($f->getName()==$tbfds[$q]['name'])
				$name=$f->getName();
				if (isset($tbnl[$name])) {
					echo '<tr><td class="gsflabel">'.current($tbnl[$name]).': </td><td class="gsfentry">'.(string)$f.'</td></tr>';
					if (next($tbnl[$name])===false) reset($tbnl[$name]);
				}
			}
/*			$a=0;
			foreach ($item as $f) {
				//echo $f->getName(); echo $f->count(); echo (string)$f;  && ($f->getName()==$tbfds[$q]['name'])
				if (isset($tbfds[$a]['name']))
					echo '<tr><td class="gsflabel">'.$tbfds[$a]['label'].': </td><td class="gsfentry">'.(string)$f.'</td></tr>';
				$a++;
			}
*/
			if (GSF_BACKEND) {
				echo '<tr><td class="gsflabel">'.i18n_r('gsform/GSF_BROWSER').': </td><td class="gsfentry">'.$item->browser.'</td></tr>';
				echo '<tr><td class="gsflabel">'.i18n_r('gsform/GSF_RADDR').': </td><td class="gsfentry">'.$item->raddr.'</td></tr>';
				echo '<tr><td>&nbsp;</td><td class="gsffr"><a href="?id=gsform&amp;gsform_list&amp;file='.$params['file'].'&amp;fid='.$fid.'&amp;item='.$i.'">'.i18n_r('gsform/GSF_DELETE').'</a></td></tr>';
			} else echo '<tr><td colspan="2">&nbsp;</td></tr>';
			if ($i>$stop-1) { break; }
		}

		foreach ($qfields as $k => $v) {
			if (count($tbnl[$k])>1) $label=$this->tbd[$fid]['ftitle'];
			else $label=current($tbnl[$k]);
			echo '<tr><td class="gsfcount" colspan="2">'.$label.'</td></tr>';
			$tb=array_count_values($v);
			foreach ($tb as $l => $m) {
				echo '<tr><td class="gsfentry">'.$l.'</td><td class="gsfentry">'.$m.'</td></tr>';
			}
			if (next($tbnl[$k])===false) reset($tbnl[$k]);
		}

		echo '</table>';
		echo '<p>';
		if (GSF_BACKEND) {
			if ($j>0) { echo '<a href="?id='.$_GET['id'].'&amp;gsform_list&amp;file='.basename($this->xmlfile).'&amp;fid='.$fid.'&amp;j='.($j-1).'">&laquo; Poprzednie</a>&nbsp;'; }
			if ($i<$ile) { echo '<a href="?id='.$_GET['id'].'&amp;gsform_list&amp;file='.basename($this->xmlfile).'&amp;fid='.$fid.'&amp;j='.($j+1).'">Następne &raquo;</a>'; }
		} else if (!@$params['query']) {
			if ($j>0) { echo '<a href="?id='.$_GET['id'].'&amp;showent=1&amp;j='.($j-1).'">&laquo; Poprzednie</a>&nbsp;'; }
			if ($i<$ile) { echo '<a href="?id='.$_GET['id'].'&amp;showent=1&amp;j='.($j+1).'">Następne &raquo;</a>'; }
		}
		echo '</p>';
		$rep=ob_get_contents();
		ob_get_clean();
		return $rep;
	}

	function delItem() {//deleting entry in users form data file
		$this->xmlfile=GSDATAPATH.GSF_DIR.$_GET['file'];
		if (!file_exists($this->xmlfile)) {
			$this->addMsg('gsferror','GSF_NOFILE');
			return '';
		}
		$this->xmldata=getXML($this->xmlfile);
		$i=(int)$_GET['item'];
		$ile=count($this->xmldata->items->children());
		unset($this->xmldata->items->item[--$i]);
		if ($ile > 1) $this->savXML(); else @unlink($this->xmlfile);
	}

	function _get_browser() {
		$browser=array("firefox", "msie", "opera mini", "opera", "chrome", "safari",
                    "seamonkey", "konqueror", "netscape", "netfront",
                    "gecko", "navigator", "mosaic", "lynx", "amaya",
                    "omniweb", "avant", "camino", "flock", "aol", "mozilla");
		$info='OTHER';
		foreach ($browser as $parent) {
			if (($s=strpos(strtolower($_SERVER['HTTP_USER_AGENT']),$parent))!==FALSE) {
				$f = $s + strlen($parent);
				$ver = substr($_SERVER['HTTP_USER_AGENT'], $f, 5);
				$ver = preg_replace('/[^0-9,.]/','',$ver);
				$info=$parent;
				$info.='_'.$ver;
				break; // first match wins
			}
		}
		return $info;
	}

	function _get_raddr() {
		$raddr = $_SERVER['REMOTE_ADDR'];
		return $raddr;
	}

	function mksel($class,$name,$selid=null) {
		if (strpos($name,'[]')>0) $id=substr($name,0,-2);
		$types=array('text'=>'FLDTXT','textarea'=>'FLDTXTA','checkbox'=>'FLDCHK','radio'=>'FLDRADIO','select'=>'FLDSEL','password'=>'FLDPWD','hide'=>'FLDHIDE','email'=>'FLDEMAIL','www'=>'FLDWWW');
		$code='<select id="'.$id.'" name="'.$name.'" class="'.$class.'">';
		foreach ($types as $typ => $msg) {
			$code.='<option value="'.$typ.'"'.($typ==$selid ? ' selected="selected"' : '').'>'.i18n_r('gsform/GSF_'.$msg).'</option>';
		}
		$code.='</select>';
		return $code;
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
	// code partially taken from Get Simple admin/inc/basic.php
	function gsf_email_template($message) {
		$data = '
		<!DOCTYPE html>
		<html lang="pl" >
		<head>
		  <meta charset="utf-8">
		  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<style>
				a:link, a:visited { font-family: arial; color:#83613B; text-decoration:none; }
				table td p {margin-bottom:15px;}
				a img {border:none;}
			</style>
		</head>
		<body style="padding:0;margin:0;background: #f3f3f3;font-family:arial, helvetica, serif">
		<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="padding: 0 0 35px 0; background: #f3f3f3;">
	  	<tr>
		    <td align="center" style="margin: 0; padding: 0;">
		      <center>
		        <table border="0" cellpadding="0" cellspacing="0" width="580" style="border-radius:3px;">
							<tr>
								<th style="padding:15px 0 15px 20px;text-align:left;vertical-align:top;background:#EFE9D9;border-radius:4px 4px 0 0;" >
									<a href="http://lo1.sandomierz.pl"><img src="http://lo1.sandomierz.pl/favicon.png" alt="I LO Collegium Gostomianum"></a>
								</th>
							</tr>
							<tr>
								<td style="background:#fff;border-bottom:1px solid #e1e1e1;border-right:1px solid #e1e1e1;border-left:1px solid #e1e1e1;font-size:13px;font-family:arial, helvetica, sans-serif;padding:20px;line-height:22px;" >
									'.$message.'
								</td>
							</tr>
							<tr>
								<td style="padding-top:10px;font-size:12px;color:#333;line-height:14px;font-family:arial, helvetica, serif" >
									<p class="meta">
										Ta wiadomość została wygenerowana automatycznie, prosimy na nią nie odpowiadać.</p>
								</td>
							</tr>
						</table>
					</center>
				</td>
			</tr>
		</table>
		</body>
		</html>
		';
		return $data;
	}
	// code partially taken from Get Simple admin/inc/basic.php
	function gsf_sendmail($to,$subject,$message) {
	  $message = $this->gsf_email_template($message);
		$fromemail = 'admin@lo1.sandomierz.pl';
		$headers  ='"MIME-Version: 1.0' . PHP_EOL;
		$headers .= 'Content-Type: text/html; charset=UTF-8' . PHP_EOL;
		$headers .= 'From: '.$fromemail . PHP_EOL;
		$headers .= 'Reply-To: '.$fromemail . PHP_EOL;
		$headers .= 'Return-Path: '.$fromemail . PHP_EOL;
		if( mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',"$message",$headers) ) {
			return true;
		} else {
			return false;
		}
	}
}
?>
