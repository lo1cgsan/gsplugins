<?php
/*
Plugin Name: gsform
Description: Guest/Comments Elements Bloker for Get Simple 3.x Main Class
Version: 1.1
Author: wDesign
Author URI: http://www.ecg.vot.pl/
*/

	function is_valid($type,$val) {
		switch($type) {
			case 'email':
				if (empty($val)) return true; //email isn't required
				return filter_var($val, FILTER_VALIDATE_EMAIL);
				//$pattern = '`^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$`i';
				//if(preg_match($pattern, $val)) return true; else return false;
			case 'www':
				if (empty($val)) return true; ////www isn't required
				return filter_var($val, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
				//$pattern = "`^((http|https|ftp):\/\/(www\.)?|www\.)[a-zA-Z0-9\_\-]+\.([a-zA-Z]{2,4}|[a-zA-Z]{2}\.[a-zA-Z]{2})(\/[a-zA-Z0-9\-\._\?\&=,'\+%\$#~]*)*$`i";
				//if(preg_match($pattern, $val)) return true; else return false;
			case 'ip':
				if (empty($val)) return true;
				return filter_var($val,FILTER_VALIDATE_IP); 
		}
		return false;
	}

/*
	function is_banned($bcat,$val) {
		require_once(GSPLUGINPATH.GSF_DIR.'gsf_block.php');
		$ban_e=array('mail@xg0n.net','js@gmail.com','info@strefa.ru','kobiz@wp.pl','kobiz@mail.ru','magiapoligrafii.eu','eprzeloty.pl');
		$ban_w=array('strefa.ru');
		$ban_s=array('<script','<style','abc123','work!','poker','online','casino','game','.com','bonus','free','sperm','potency','certyfikacja','certyfikat','mp3c.info','http://','www');
		$ban_i=array('195.225.177','83.238.199.3','81.190.159.123','195.205.94.145');
		$gsfblock = new GsfBlock();
		$gsfblock->getAllData();
		//switch ($bcat) {
		//	case 0://IP
		//		$gsfblock = new GsfBlock(GSF_BANIP,$bcat);
		//		if (in_array($val, $ban_e)) return true;
		//		break;
		//	case 1://WWW
		//		if (in_array($val, $ban_w)) return true;
		//		break;
		//	case 2://E-MAIL
		//		if (in_array($val, $ban_i)) return true;
		//		break;
		//	case 'comm':
		//		$tmp=strtolower($val);
		//		foreach ($ban_s as $sstr) {
		//			if (strpos($tmp,$sstr)===false) ; else return true;
		//		}
		//}
		return false;
	}
*/
	function htmlstrip($str) {
		return htmlspecialchars(stripslashes(trim($str)),ENT_QUOTES);
	}

	function cleantxt($str,$all=true) {
		//$str = strip_tags($str);
		$str = html2txt($str);
		$str = nl2brStrict($str);
		$str = preg_replace('/\s\s+/', ' ', $str); //stripping excess whitespace
		//$str = trim($str);
		if (get_magic_quotes_gpc()) $str=stripcslashes($str);
		return $str;
	}

	function nl2brStrict($str, $replacement = '<br />') {
		$txt=preg_replace('((\r\n)+)', trim($replacement), $str);
		//$txt=preg_replace('((<br />)+)','<br />',$txt);
		return $txt;
	}

	function html2txt($str){
		$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
              '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
              '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
              '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
		);
		$text = preg_replace($search, '', $str);
		return $text;
	}

	function nopl($tekst) {
   $tabela = Array(
   //WIN
        "\xb9" => "a", "\xa5" => "A", "\xe6" => "c", "\xc6" => "C",
        "\xea" => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
        "\xf3" => "o", "\xd3" => "O", "\x9c" => "s", "\x8c" => "S",
        "\x9f" => "z", "\xaf" => "Z", "\xbf" => "z", "\xac" => "Z",
        "\xf1" => "n", "\xd1" => "N",
   //UTF
        "\xc4\x85" => "a", "\xc4\x84" => "A", "\xc4\x87" => "c", "\xc4\x86" => "C",
        "\xc4\x99" => "e", "\xc4\x98" => "E", "\xc5\x82" => "l", "\xc5\x81" => "L",
        "\xc3\xb3" => "o", "\xc3\x93" => "O", "\xc5\x9b" => "s", "\xc5\x9a" => "S",
        "\xc5\xbc" => "z", "\xc5\xbb" => "Z", "\xc5\xba" => "z", "\xc5\xb9" => "Z",
        "\xc5\x84" => "n", "\xc5\x83" => "N",
   //ISO
        "\xb1" => "a", "\xa1" => "A", "\xe6" => "c", "\xc6" => "C",
        "\xea" => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
        "\xf3" => "o", "\xd3" => "O", "\xb6" => "s", "\xa6" => "S",
        "\xbc" => "z", "\xac" => "Z", "\xbf" => "z", "\xaf" => "Z",
        "\xf1" => "n", "\xd1" => "N");

   	return strtr($tekst,$tabela);
	}

	function retDate($time=null) {
		if (is_null($time)) $time=time();
		return date('d.m.Y',$time);
	}

	function addCSSJS() {
		//$code='
		//<script type="text/javascript">';
		//$code.=file_get_contents(GSPLUGINPATH.GSF_DIR.'js/jquery-1.4.3.min.js');
		//$code.='</script>
		//';
		//$code='
		//<script type="text/javascript">';
		//$code.=file_get_contents(GSPLUGINPATH.GSF_DIR.'js/gsfblock.js');
		//$code.='</script>
		//';
		//$code.='<style type="text/css">';
		//$code.=file_get_contents(GSPLUGINPATH.GSF_DIR.'css/gsfblock.css');
		//$code.='</style>
		//';
		//$this->addCode($code);
	}

?>