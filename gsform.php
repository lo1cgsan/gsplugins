<?php
/*
Plugin Name: gsform
Description: Get Simple 3.x form creator
Version: 1.1
Author: wDesign
Author URI: http://www.ecg.vot.pl/
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

define('GSF_DIR', 'gsform/');
define('GSF_FILE', GSDATAOTHERPATH.'gsformset.xml');
define('GSF_FORMS', GSDATAPATH.GSF_DIR.'gsforms.xml');
define('GSF_BANIP', GSDATAPATH.GSF_DIR.'gsfbanip.xml');
define('GSF_BANWWW', GSDATAPATH.GSF_DIR.'gsfbanwww.xml');
define('GSF_BANMAIL', GSDATAPATH.GSF_DIR.'gsfbanmail.xml');
define('GSF_BANWORDS', GSDATAPATH.GSF_DIR.'gsfbanwords.xml');

include_once(GSADMININCPATH.'security_functions.php');

# register plugin
register_plugin(
	$thisfile, 
	'GsForm',
	'1.1', 		
	'wDesign',
	'http://www.ecg.vot.pl/', 
	'Get Simple 3.x Form Creator',
	'gsform',
	'gsform_main'
);

# load i18n texts
if (basename($_SERVER['PHP_SELF']) != 'index.php') { // back end only
	define('GSF_BACKEND',true);
	i18n_merge('gsform',substr($LANG,0,2));
	i18n_merge('gsform','en');
} else {
	define('GSF_BACKEND',false);
}
# activate filter
add_action('nav-tab', 'createNavTab', array('gsform', $thisfile, i18n_r('gsform/GSF_TAB'), 'gsform_list'));
add_action('gsform-sidebar', 'createSideMenu', array($thisfile, i18n_r('gsform/GSF_LIST'), 'gsform_list'));
add_action('gsform-sidebar', 'createSideMenu', array($thisfile, i18n_r('gsform/GSF_ADD'), 'gsform_add'));
add_action('gsform-sidebar', 'createSideMenu', array($thisfile, i18n_r('gsform/GSF_BANMENU'), 'gsform_block'));
add_action('gsform-sidebar', 'createSideMenu', array($thisfile, i18n_r('gsform/GSF_SETTINGS'), 'gsform_set'));
add_action('gsform-sidebar', 'createSideMenu', array($thisfile, i18n_r('gsform/GSF_HELP'), 'gsform_usage'));
add_filter('content','gsform_content');

register_script('gsform', $SITEURL.'plugins/gsform/js/gsform.js', '', FALSE);
queue_script('gsform',GSBOTH);
register_style('gsfcss', $SITEURL.'plugins/gsform/css/gsform.css', '', 'screen');
queue_style('gsfcss',GSBOTH);


function gsform_main() {
	if (!gsform_check_dir()) { //check if plugins data dir exists
		$msg = i18n_r('gsform/NODIR');
	} else {
		require_once(GSF_DIR . 'gsf_class.php');
		if (isset($_GET['gsform_add'])) {
			gsform_add();
		} else if (isset($_GET['gsform_usage'])) {
			gsform_usage();
		} else if (isset($_GET['gsform_block'])) {
			gsform_block();
		} else if (isset($_GET['gsform_set'])) {
			gsform_set();
		} else {
			$gsform = new gsform();
			if (isset($_POST['tdel'])) $gsform->delForm(); //removing forms definition
			if (isset($_GET['item'])) $gsform->delItem(); //removing user forms data item
			if (isset($_GET['file'])) {//show user's form entries
				echo $gsform->showUserEn(array('file'=>$_GET['file']),$_GET['fid']);
				return;
			}
			$gsform->listForms();
			$gsform->showCode();
		}
	}
}

function gsform_add() {
	require_once(GSF_DIR . 'gsf_class.php');
	//print_r($_POST);
	if (isset($_GET['fid'])) $fid=$_GET['fid']; else if (isset($_POST['fid'])) $fid=$_POST['fid']; else $fid=null;
	$gsform = new gsform();
	if (isset($_POST['fname'])) {
		$fid=$gsform->savForm();//save form's template data to xml
	}
	echo '<h3>'.i18n_r("gsform/GSF_SETTINGS").'</h3>';
	$gsform->addForm($fid);
}

function gsform_block() {
	//print_r($_SERVER); echo '<br />';
	//global $SITEURL;
	require_once(GSF_DIR . 'gsf_block.php');
	if (isset($_GET['catb'])) $bcat=(int)$_GET['catb']; else if (isset($_POST['catb'])) $bcat=(int)$_POST['catb']; else $bcat=0;
	if ($bcat==1) $gsfblock = new GsfBlock(GSF_BANIP,$bcat);
	else if ($bcat==2) $gsfblock = new GsfBlock(GSF_BANWWW,$bcat);
	else if ($bcat==3) $gsfblock = new GsfBlock(GSF_BANMAIL,$bcat);
	else if ($bcat==4) $gsfblock = new GsfBlock(GSF_BANWORDS,$bcat);
	else $gsfblock = new GsfBlock();
	$gsfblock->chooseCat($bcat);
	if (isset($_POST['dane'])) $gsfblock->savForm();
	if ($bcat>0) $gsfblock->addForm();
}

function gsform_usage(){
	require_once(GSF_DIR . 'gsf_class.php');
	echo '<h3>'.i18n_r("gsform/GSF_USAGE").'</h3>';
	echo '<p>UWAGA: Nazwy szablonów formularzy oraz pól nie mogą zawierać znaków narodowych, ani zaczynać się od cyfr!</p>';
	echo '<p>W obrębie "Kodu na stronę" można używać następujących opcji:</p>';
	echo '<ul>';
	echo '<li><code>showfrm=0</code> &#8211; blokuje wyświetlanie formularza;</li>';
	echo '<li><code>showent=1</code> &#8211; wyświetla wpisy z danego formularza;</li>';
	echo '<li><code>showbtn=1</code> &#8211; wyświetla link dodaj wpis zamiast formularza;</li>';
	echo '</ul>';
	echo '<p>Przykładowe kody:</p>';
	echo '<code>(% gsform name="test" %)</code> &#8211; wyświetla tylko formularz<br />';
	echo '<code>(% gsform name="test" showent=1 %)</code> &#8211; wyświetla formularz i wpisy<br />';
	echo '<code>(% gsform name="test" showfrm=0 showent=1 %)</code> &#8211; wyświetla tylko wpisy<br />';
	echo '<code>(% gsform name="test" showent=1 showbtn=1 %)</code> &#8211; wyświetla wpisy i link dodawania';
	echo '<br /><br />';
	echo '<h3>Wygląd formularzy i podpowiedzi</h3>';
	echo '<p>Aby podpowiedzi były poprawnie pokazywane, w szablonie strony, np. na końcu, należy dodać kod:</p>';
	echo '<pre>'.htmlspecialchars('<div id="tip"><div id="tiph">tytuł podpowiedzi</div><div id="tipc"></div></div>').'</pre>';
	echo '<p><br />Wygląd formularzy definiujemy za pomocą klas w pliku <i>gsform.css</i>:</p>';
	echo '<pre>'.htmlspecialchars('
.gsfinp, .gsfsel - klasy definiujące wygląd pul tekstowych i selekcji
		').'</pre>';
	echo '<p>Wygląd podpowiedzi definiujemy w głównym pliku css serwisu:</p>';
	echo '<pre>'.htmlspecialchars('
#tip, #tiph, #tipc - kontenery tworzące okienko podpowiedzi.
		').'</pre>';
	echo '<p>Kod JavaScript obsługujący podpowiedzi przypisany jest do klasy ".przypis" i zawarty jest w pliku <i>gsform.js</i>.<br />
	Do działania wymagana jest bilioteka jQuery, którą najlepiej ładować w nagłówku szablonu strony.</p>';
	echo '<br />';
	echo '<h3>Plany</h3>';
	echo '<ul>';
	echo '<li>Dodanie paginacji do wyświetlanych wpisów. [Zrobione: 2012-11-19]</li>';
	echo '<li>Dodanie list typu banned dla domen, emaili, adresów ip oraz słów kluczowych.</li>';
	echo '<li>Dodanie weryfikacji typu captcha.</li>';
	echo '<li>Dodanie weryfikacji użytkowników.</li>';
	echo '</ul>';
}

//for future use
function gsform_set() {
	//require_once(GSF_DIR . 'gsf_settings.php');
	echo '<h3>'.i18n_r("gsform/GSF_SETTINGS").'</h3>';
	echo 'Hello';
	//$gsfset = new gsfset();
	//if (isset($_POST['commnbr'])) { echo $gsform->savSettings(); }
	//$gsfset->showSettings();
}

function gsform_content($content) {
	global $LANG;
	//i18n_merge('gsform') || i18n_merge('gsform','en_US');
	if (function_exists('i18n_load_texts')) {
  		i18n_load_texts('gsform');
	} else {  
  		i18n_merge('gsform', substr($LANG,0,2)) || i18n_merge('gsform', 'en');
	}
	if (!gsform_check_dir()) {
		return i18n_r('gsform/MISSING_DIR');
	}
	return preg_replace_callback("/(<p>\s*)?\(%\s*(gsform)(\s+(?:%[^%\)]|[^%])+)?\s*%\)(\s*<\/p>)?/",
					'gsform_replace', $content);
}

function gsform_replace($match) {
	require_once(GSF_DIR . 'gsf_class.php');
	//The following code is taken from i18n Gallery plugin by Martin Vlcek
	//http://mvlcek.bplaced.net 
	$params = array();
    $paramstr = isset($match[3]) ? html_entity_decode(trim($match[3]), ENT_QUOTES, 'UTF-8') : '';
    while (preg_match('/^([a-zA-Z][a-zA-Z0-9_-]*)[:=]([^"\'\s]*|"[^"]*"|\'[^\']*\')(?:\s|$)/', $paramstr, $pmatch)) {
		$key = $pmatch[1];
		$value = trim($pmatch[2]);
		if (substr($value,0,1) == '"' || substr($value,0,1) == "'") $value = substr($value,1,strlen($value)-2);
		$params[$key] = $value;
		$paramstr = substr($paramstr, strlen($pmatch[0]));
	}
	//end of credits
	if (isset($_GET['showent'])) $params['showent']=1;
	if (isset($_GET['j'])) $params['j']=(int)$_GET['j'];
	$gsform = new gsform();
	//show (and save data) of form
	return $gsform->execForm($params);
}

function gsform_check_dir() {
	$success = true;
	// create directory if necessary
	$gdir = GSDATAPATH . GSF_DIR;
	$succes = gsform_create_dir($gdir) && $success;
	$gdir = GSBACKUPSPATH . GSF_DIR;
	$succes = gsform_create_dir($gdir) && $success;
	return $success;
}

function gsform_create_dir($gdir) {
	$success = true;
	if (!file_exists($gdir)) {
		$success = @mkdir(substr($gdir,0,-1), 0777) && $success;
		$fp = @fopen($gdir . '.htaccess', 'w');
		if ($fp) {
			fputs($fp, 'Deny from all');
			fclose($fp);
		}
	}
	return $success;
}
?>