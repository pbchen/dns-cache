<?php
/*
 +-------------------------------------------------------------—-----------+
 | Project     : jiayuan.com Website New Version
 | ========================================================================
 | Version     : 1.0.0
 | File        : includes/function.inc.php
 | Created     : 2006-02-14 By Tony Guo
 | Modified    : 2010-02-04 By Leding Du
 | Comments    : Use the PHPDocumentor Spec
 | Copyright   : (C) 2002-2010 jiayuan.com . All rights reserved.
 |--------------------------------------------------------------------------+
 | Description : all common functions lib
 |--------------------------------------------------------------------------+
 */
defined('ROOT_PATH') ?  '' : define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT']."/");

require_once(WWW_ROOT_PATH . 'includes/msg.inc.php');
require_once(WWW_ROOT_PATH . 'includes/GID.php');
require_once(WWW_ROOT_PATH . 'includes/encode.inc.php');

/**
 * 发送某种管理员信件
 *
 */
function send_msg_template($name,$uid){
	global $SESSION,$DC;
	$msg =  get_msg_template($name);
	$obj_user = $DC->get_user($uid);

	$msg_content=str_replace("#nickname#",$obj_user->nickname,$msg->content);
	$msg_content=str_replace("#name#",$obj_user->name,$msg_content);
	$msg_content=str_replace("#uid#",$obj_user->uid,$msg_content);
	$msg_content=str_replace("#nickname#",$obj_user->nickname,$msg_content);
	$msg_content=str_replace("#service_id#",$SESSION->info->aid,$msg_content);

	$uid_array[0] = $uid;
	send_admin_message($uid_array, $msg->subject, $msg_content, 6);
}

function build_success_msg($info){
	$mysmarty	=	new Mysmarty('./',true);
	if (IMG_BASE_URL == 'http://images.jiayuan.com/m4' ||IMG_BASE_URL == 'http://images.jiayuan.com/m4' ) {
		$tpl = 'success_m4.html';
	}else{
		$tpl = 'success_w4.html';
	}


	if($_SERVICE['HTTP_REFERER']){
		$pre_url = $_SERVICE['HTTP_REFERER'];
	}
	else{
		$pre_url = "/";
	}

	$info['url']?"":$info['url'] = $pre_url;
	$info['back_url']?"":$info['back_url'] = $pre_url;

	$mysmarty->assign('info',$info);
	$mysmarty->display($tpl);
	exit;

}

function TimeFormat($timestamp, $method = 'TIME')
{
	global $week_array;

	$timestamp = $timestamp > 0 ? $timestamp : time();

	$time = $timestamp + (TIME_OFFSET + DST_TIME) * 3600;

	switch ($method)
	{
		case 'DATE' :
			return gmdate('Y年m月d日', $time);
			break;
		case 'WEEK' :
			return gmdate('Y年m月d日', $time) . ' ' . $week_array[gmdate('w',$time)];
			break;
		case 'TIME' :
		default     :
			return gmdate('Y年m月d日 H:i', $time);
			break;
	}

}


function get_caonima_loc($match_array, $show_location, $uid=0)
{
	global $SDB;

	$sql = "select match_work_location_all from user_match_all where uid='".$uid."'";
	$more_loc = $SDB->result($sql);
	if($more_loc == '')
	{
		$more_loc = '0_0#0_0#0_0#0_0#0_0#0_0#';
	}
	$more_loc = substr($more_loc, 0, strlen($more_loc)-1);
	$more_loc_arr = explode("#", $more_loc);

	for($i=0; $i<=5; $i++)
	{
		$j=$i+5;

		if($more_loc_arr[$i] != "0_0")
		{
			$sb = $more_loc_arr[$i];
			$sb_loc = substr($sb, 0, strpos($sb, '_'));
			$sb_sub_loc = substr($sb, strpos($sb, "_")+1);

			$match_array['work_location'.$j] = $sb_loc;
			$match_array['work_sublocation'.$j] = $sb_sub_loc;
		}
	}

	$fuck_show_arr_tmp = array();

	for($i=1; $i<=10; $i++)
	{
		if(!isset($match_array['work_location'.$i]) && isset($match_array['match_work_location'.$i]))
		{
			$match_array['work_location'.$i] = $match_array['match_work_location'.$i];
		}
		if(!isset($match_array['work_sublocation'.$i]) && isset($match_array['match_work_sublocation'.$i]))
		{
			$match_array['work_sublocation'.$i] = $match_array['match_work_sublocation'.$i];
		}
		$aaa = get_location_output2($match_array['work_location'.$i], $match_array['work_sublocation'.$i], 1);
		if($aaa == '#')
		{
			continue;
		}
		$fo = substr($aaa, 0, strpos($aaa, "#"));
		$be = substr($aaa, strpos($aaa, "#")+1);
		$fuck_show_arr_tmp[] = array($fo,$be);
	}

	$fuck_show_arr_tmp_1 = $fuck_show_arr_tmp_2 = array();
	for($i=0; $i<count($fuck_show_arr_tmp); $i++)
	{
		$fo = $fuck_show_arr_tmp[$i][0];
		$be = $fuck_show_arr_tmp[$i][1];
		if($be == '')
		{
			$fuck_show_arr_tmp_1[] = $fo;
		}
		else
		{
			$fuck_show_arr_tmp_2[md5($fo.$be)] = array($fo, $be);
		}
	}
	$fuck_show_arr_tmp_1 = array_unique($fuck_show_arr_tmp_1);
	if(count($fuck_show_arr_tmp_1) == 0 && count($fuck_show_arr_tmp_2) == 0)
	{
		$fuck_show_loc = '不限';
	}
	else if(count($fuck_show_arr_tmp_1) == 0)
	{
		//for($i=0; $i<count($fuck_show_arr_tmp_2); $i++)
		while(list($key, $value) = each($fuck_show_arr_tmp_2))
		{
			$fuck_show_loc .= $value[0].$value[1].',';
		}
		$fuck_show_loc = substr($fuck_show_loc, 0, strlen($fuck_show_loc)-1).$show_location;
	}
	else if(count($fuck_show_arr_tmp_2) == 0)
	{
		//for($i=0; $i<count($fuck_show_arr_tmp_1); $i++)
		while(list($key, $value) = each($fuck_show_arr_tmp_1))
		{
			$fuck_show_loc .= $value.',';
		}
		$fuck_show_loc = substr($fuck_show_loc, 0, strlen($fuck_show_loc)-1).$show_location;
	}
	else
	{
		while(list($key, $value) = each($fuck_show_arr_tmp_1))
		{
			$fo = $value;
			while(list($key2, $value2) = each($fuck_show_arr_tmp_2))
			{
				if($value2[0] == $fo)
				{
					unset($fuck_show_arr_tmp_2[$key2]);
				}
			}
			reset($fuck_show_arr_tmp_2);
		}
		reset($fuck_show_arr_tmp_1);
		reset($fuck_show_arr_tmp_2);
		//for($i=0; $i<count($fuck_show_arr_tmp_2); $i++)
		while(list($key, $value) = each($fuck_show_arr_tmp_2))
		{
			$fuck_show_loc .= $value[0].$value[1].',';
		}
		//for($i=0; $i<count($fuck_show_arr_tmp_1); $i++)
		while(list($key, $value) = each($fuck_show_arr_tmp_1))
		{
			$fuck_show_loc .= $value.',';
		}
		$fuck_show_loc = substr($fuck_show_loc, 0, strlen($fuck_show_loc)-1).$show_location;
	}

	return $fuck_show_loc;
}


function get_user_certificates($cert=0)
{
	global $certificate_array, $certificate_param;
	$certificates = array();
	foreach ($certificate_array as $cert_key => $cert_name)
	{
		if ($cert & $certificate_param[$cert_key])
		{
			$certificates[] = $cert_name;
		}
	}
	return $certificates;
}

function add_new_certificate($cert=0,$key)
{
	global $certificate_param;
	return $cert | $certificate_param[$key];
}

function remove_old_certificate($cert=0,$key)
{
	global $certificate_param;
	return $cert &~ $certificate_param[$key];
}

function number_to_string(&$id)
{
	$id = "'" . $id . "'";
}

function build_query_extra($array=array())
{
	$total = count($array);
	if ($total == 1)
	{
		return '=' . intval($array[0]);
	}
	else
	{
		//		array_walk($array, 'number_to_string');
		return ' IN(' . implode(',' , $array) . ')';
	}
}

function build_search($conditions_string)
{
	list($age_range, $other_conditions) = explode('|', $conditions_string);
	$conditions = array();
	list($conditions['sex'], $conditions['min_age'], $conditions['max_age']) = explode(',', $age_range);
	$conditions_array = array();
	$conditions_array = explode(';', $other_conditions);
	foreach ($conditions_array as $condition_str)
	{
		if (!empty($condition_str))
		{
			list($key, $value) = explode('=', $condition_str);
			$conditions[$key] = $value;
		}
	}
	return $conditions;
}

function build_condition($conditions_string)
{
	list($age_range, $other_conditions) = explode('|', $conditions_string);
	$conditions = array();
	list($conditions['sex'], $conditions['min_age'], $conditions['max_age'], $conditions['min_height'], $conditions['max_height'], $conditions['education_up']) = explode(',', $age_range);
	$conditions_array = array();
	$conditions_array = explode(';', $other_conditions);
	foreach ($conditions_array as $condition_str)
	{
		if (!empty($condition_str))
		{
			list($key, $value) = explode('=', $condition_str);
			$conditions[$key] = $value;
		}
	}
	return $conditions;
}

function get_ku6video_count($uid, $type = 1)
{
	global $DC, $SDB_LY;

	if($type == 1)
	{
		$sql = "select count(*) from ku6_video where uid='$uid' and (status1=3 or status1 = 2) and status2 >= 20";
	}
	else
	{
		$sql = "select count(*) from ku6_video where uid='$uid'";
	}
	return $SDB_LY->result($sql);
}
function format_error($error = array())
{
	global $site_title,$img_base_url;
	if (REG_HOST == 1)
	{
		$output = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html lang="zh" xml:lang="zh" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-cn" />
<meta name="keywords" content="交友; 爱情; 婚姻; 婚介; 交友中心; 世纪佳缘; 世纪家园; 复旦；小龙女;龚海燕;神经元; 上海交友; 北京交友; 广州交友; 杭州交友; 武汉交友;大连交友; 天津交友; 深圳交友; 南京交友;西安交友; 成都交友; 重庆交友; 厦门交友; 哈尔滨交友; 长春交友; 沈阳交友; 济南交友;" />
<meta name="description" content="如果你正在苦苦寻觅你的爱情，那么世纪佳缘也许是最好的选择。世纪佳缘交友网是一个纯洁的以爱情为目的的交友网。其主要特点是：纯洁性（寻觅爱情是唯一的目的），高品味（研究生及以上学历占一半左右）" />
<meta http-equiv="refresh" content="5; url=/" />
<style type="text/css">
html,body{ height: 100%; background: white; }
.err_box{ border: 1px solid #ABCEEE; width: 398px; margin: 50px auto 0; height: 158px; background: url(http://images.jiayuan.com/msn/images/system/err_bg.gif) white no-repeat right bottom; }
.err_box h1{ width: 100%; float: left; text-align: center; line-height: 24px; background: #EBF4FB; font-size: 12px; font-weight: bolder; color: #0072B4; border-bottom: 1px solid #ABCEEE; margin: 0;}
.err_box dl{ width: 345px; margin: 20px 0 0 26px !important; margin-left: 13px; color: #383167; font-size: 12px; float: left;}
.err_box input{ width: 87px; height: 29px; padding-top: 2px; text-align: center; color: white; font-size: 12px; font-weight: bolder; background: url(http://images.jiayuan.com/msn/images/system/back_bn_bg.gif) no-repeat; border: 1px solid #AACCEE;}
</style>
<title>系统提示_佳缘交友_MSN中国</title>
</head>
<body>
<script language="javascript">
function back()
{
	location.href='/';
}
</script>
<div class="err_box">
<h1>系统提示</h1>
<dl>由于系统繁忙，您的操作已失败，请您重新操作，5秒钟之后将自动跳转！<br />
<input type="button" value="立即返回" style="margin: 20px 0 0 130px;" onclick="back();"/>
</dl>
</div>

</body>
</html>
EOF;
	}
	else
	{
		$output = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html lang="zh" xml:lang="zh" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-cn" />
<meta name="keywords" content="交友; 爱情; 婚姻; 婚介; 交友中心; 世纪佳缘; 世纪家园; 复旦；小龙女;龚海燕;神经元; 上海交友; 北京交友; 广州交友; 杭州交友; 武汉交友;大连交友; 天津交友; 深圳交友; 南京交友;西安交友; 成都交友; 重庆交友; 厦门交友; 哈尔滨交友; 长春交友; 沈阳交友; 济南交友;" />
<meta name="description" content="如果你正在苦苦寻觅你的爱情，那么世纪佳缘也许是最好的选择。世纪佳缘交友网是一个纯洁的以爱情为目的的交友网。其主要特点是：纯洁性（寻觅爱情是唯一的目的），高品味（研究生及以上学历占一半左右）" />
<link href="http://images.jiayuan.com/love21cn/system/css/style.css" rel="stylesheet" type="text/css" media="all" />
<title>{$site_title}:系统繁忙</title>
<meta http-equiv="refresh" content="2; url=/" />
</head>

<body>
<div id="container">
<div id="container_c">

<!--  header  -->
<script type="text/javascript" src="$img_base_url/scripts/head.js"></script>

<div id="navigation">
<a href="/">{$site_name}首页</a>&gt;&gt;系统信息
</div>

<!-- article -->

<div class="box">
<div class="boxin">

<div class="err_box"><h1>系统繁忙</h1>
<dl>
<dt>错误原因：系统繁忙</dt>
<dt>解决方法：请稍候重新操作/<a href="/">点击下方链接返回首页</a></dt>
<dd>联系客服：010-64450168<br />
		返    回：<a href="/">返回首页</a><!--（系统将与5秒后自动返回）--></dd>
</dl>
</div>
</div>

<div class="err_map"><img src="http://images.jiayuan.com/love21cn/system/images/map_site.jpg" usemap="#Map" />
<map name="Map" id="Map"><area shape="rect" coords="26,5,87,77" href="/ask/" alt="帮帮地带" /><area shape="rect" coords="123,5,185,82" href="/index.php?mod=service&act=contact" alt="联系客服" /><area shape="rect" coords="208,4,271,82" href="/index.php?mod=service&act=contact#form" alt="在线提问" /><area shape="rect" coords="299,5,365,82" href="http://corp.jiayuan.com/knowledge.html" alt="交友须知" /><area shape="rect" coords="381,6,459,77" href="/" alt="返回首页" /></map></div>

</div>





<!--  footer  -->
<script type="text/javascript" src="$img_base_url/scripts/foot.js"></script>


</div>
</div>
</body>
</html>
EOF;
	}


	echo $output;
	exit();
}

function get_user_directory($uid_hash)
{
	$url = substr($uid_hash, 0, 2) . '/' . substr($uid_hash, 30, 2) . '/' . substr($uid_hash, 2, 28);
	//$url = str_replace('ad', 'aq', $url);
	return $url;
}

function load_cache($keys='')
{
	global $SDB, $DC;

	$CACHE = array();
	$not_get_data = 0;
	$keys = str_replace(" ", '', $keys);

	$keys_mc = str_replace("'", '', $keys);
	$keys_array = explode(',', $keys_mc);
	foreach ($keys_array as $key)
	{
		if($key == '')
		{
			continue;
		}
		$value = $DC->get_cache($key.'_www');

		if (empty($value) || ($value === false))
		{
			$not_get_data = 1;
		}
		$CACHE[$key] = $value;
	}
	if (empty($CACHE) or (count($CACHE) == 0) or ($not_get_data == 1))
	{
		$SDB->query('SELECT * FROM cache WHERE cache_key IN('.$keys.')');

		while ( $r = $SDB->fetch_array() )
		{
			if ( $r['is_array'] )
			{
				$CACHE[ $r['cache_key'] ] = unserialize(stripslashes($r['cache_value']));
				if (!$DC->update_cache($r['cache_key'].'_www', $CACHE[$r['cache_key']], 3600))
				{
					$DC->set_cache($r['cache_key'].'_www', $CACHE[$r['cache_key']], 3600);
				}
			}
			else
			{
				$CACHE[ $r['cache_key'] ] = $r['cache_value'];
			}
		}
	}
	return $CACHE;
}

function create_cache($key,$value,$is_array=0)
{
	global $MDB, $DC;

	$DC->set_cache($key.'_www', $value, 3600);
	$value = $is_array == 1 ? addslashes(serialize($value)) : addslashes($value);
	$MDB->query("INSERT INTO cache (cache_key, cache_value, is_array) VALUES('".$key."','".$value."','".$is_array."')");
}

function update_cache($key,$value,$is_array=0)
{
	global $MDB, $DC;

	if (!$DC->update_cache($key.'_www', $value, 3600))
	{
		$DC->set_cache($key.'_www', $value, 3600);
	}

	$value = $is_array == 1 ? addslashes(serialize($value)) : addslashes($value);
	$MDB->query("UPDATE cache SET cache_value='".$value."' WHERE cache_key='".$key."'");
}

function display_antispam($hash)
{
	switch (ANTISPAM_METHOD)
	{
		case "multi_gif" :
			return '<img src="/antispam.php?hash='.$hash.'&amp;pos=1" style="width:18px;height:18px;" alt="" /> <img src="/antispam.php?hash='.$hash.'&amp;pos=2" style="width:18px;height:18px;" alt="" /> <img src="/antispam.php?hash='.$hash.'&amp;pos=3" style="width:18px;height:18px;" alt="" /> <img src="/antispam.php?hash='.$hash.'&amp;pos=4" style="width:18px;height:18px;" alt="" />';
			break;
		case "gd_gen" :
			return '<img src="/antispam.php?hash='.$hash.'" style="width:75px;height:18px;" alt="" />';
			break;
		case "ascii":
		default :
			return '<script language="JavaScript" src="/antispam.php?hash='.$hash.'"></script>';
			break;
	}
}

function generate_antispam()
{
	global $MDB, $IP;

	$code = mt_rand(0, 99999);
	$code = str_repeat("0", 5 - strlen($code)) . $code;
	$hash = sha1(uniqid("Love21cn.com Antispam Code", true));
	if (!is_object($MDB))
	{
		$MDB = new Database(MDB_HOST, MDB_PORT, MDB_USER, MDB_PASSWORD, MDB_DATABASE);
	}
	$MDB->query("INSERT INTO " . TABLE_PREFIX . "antispam (as_hash, as_code, timestamp, ip_address) VALUES('".$hash."','".$code."','".time()."','".$IP."')");

	return $hash;
}

function verify_antispam($hash, $input_code)
{
	global $MDB, $IP;
	if (!is_object($MDB))
	{
		$MDB = new Database(MDB_HOST, MDB_PORT, MDB_USER, MDB_PASSWORD, MDB_DATABASE);
	}
	$code = $MDB->result("SELECT as_code FROM " . TABLE_PREFIX . "antispam WHERE as_hash='" . HashFilter($hash) . "' AND as_code='" . format_string($input_code) . "' AND timestamp>" . (time() - ANTISPAM_EXPIRE) . " AND ip_address='" . $IP . "'");

	return $code ? true : false;
}


function display_antispam_v2($hash)
{
	switch (ANTISPAM_METHOD)
	{
		case "multi_gif" :
			return '<img src="/antispam_v2.php?hash='.$hash.'&amp;pos=1" style="width:18px;height:18px;" alt="" /> <img src="antispam_v2.php?hash='.$hash.'&amp;pos=2" style="width:18px;height:18px;" alt="" /> <img src="antispam_v2.php?hash='.$hash.'&amp;pos=3" style="width:18px;height:18px;" alt="" /> <img src="antispam_v2.php?hash='.$hash.'&amp;pos=4" style="width:18px;height:18px;" alt="" />';
			break;
		case "gd_gen" :
			return '<img src="/antispam_v2.php?hash='.$hash.'" style="width:75px;height:18px;vertical-align: middle;" alt="" id="antispam_v2"/><script type="text/javascript">function con_code(){var ran= Math.round((Math.random()) * 100000000);document.getElementById("antispam_v2").src = "/antispam_v2.php?r=" + ran;}</script>';
			break;
		case "ascii":
		default :
			return '<script language="JavaScript" src="/antispam_v2.php?hash='.$hash.'"></script>';
			break;
	}
}

function generate_antispam_v2()
{
	return '';
	$code = mt_rand(0, 99999);
	$code = str_repeat("0", 5 - strlen($code)) . $code;
	$hash = sha1(uniqid("Love21cn.com Antispam Code", true));

	session_start();
	$hash_tmp	=	$_SESSION['antispam'];
	unset($_SESSION[$hash_tmp]);
	$_SESSION[$hash]	=	$code;
	$_SESSION['antispam']	=	$hash;
	return $hash;
}

function verify_antispam_v2($hash, $input_code,$sess = 'ant_hash')
{
	session_start();
	if($_SESSION[$sess]	==	md5(strtolower($input_code)))
	{
		unset($_SESSION[$sess]);
		return true;
	}
	else
	{
		//unset($_SESSION['hash']);
		return false;
	}
	return false;
}
/**
 * 函数verify_antispam_ajax在注册页面通过ajax返回验证码填写是否正确
 *
 * @param string $input_code
 * @return
 */
function verify_antispam_ajax($input_code,$sess = 'ant_hash')
{
	session_start();
	if($_SESSION[$sess]	==	md5(strtolower($input_code)))
	{
		return true;
	}
	else
	{
		return false;
	}
	return false;
}

function NumFormat($num)
{
	return number_format($num, 0, '.', thou_sep);
}

function ByteFormat($byte)
{
	$units = array(" Bytes", " KB", " MB", " GB", " TB", " PB");

	$i = 0;
	while ($byte >= 1000)
	{
		$byte /= 1024;
		$i++;
	}

	return number_format($byte, 2) . $units[$i];
}

function get_cookie($key)
{
	return isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL;
}

function _strtolower($str)
{
	return strtr($str, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz");
}

function _IPFilter($key)
{
	$key = preg_replace("/[^0-9.]/", "", $key);
	return preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $key) ? $key : "0.0.0.0";
}

function _stripslashes($str)
{
	global $magic_quotes_gpc;

	if ($magic_quotes_gpc)
	{
		$str = stripslashes($str);
	}
	return $str;
}

function _CommonFilter($str)
{

	$str = str_replace( "&#032;"			, " "			, $str );
	$str = preg_replace( "/\\\$/"			, "&#036;"		, $str );
	//	$str = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;"		, $str );

	$str = _stripslashes($str);

	//	$str = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;"	, $str );

	return $str;
}

function _LineFilter($str)
{
	return strtr($str, array("\r" => "", "\n" => "<br />"));
}

function _TagFilter($str)
{
	$str = preg_replace( "/javascript/i" , "j&#097;v&#097;script", $str );
	$str = preg_replace( "/alert/i"      , "&#097;lert"          , $str );
	$str = preg_replace( "/about:/i"     , "&#097;bout:"         , $str );
	$str = preg_replace( "/onmouseover/i", "&#111;nmouseover"    , $str );
	$str = preg_replace( "/onclick/i"    , "&#111;nclick"        , $str );
	$str = preg_replace( "/onload/i"     , "&#111;nload"         , $str );
	$str = preg_replace( "/onsubmit/i"   , "&#111;nsubmit"       , $str );
	$str = preg_replace( "/<script/i"	 , "&#60;script"		 , $str );
	$str = preg_replace( "/document\./i" , "&#100;ocument."      , $str );

	return $str;
}

function _unhtmlspecialchars($str)
{
	$str = str_replace( "&amp;" , "&", $str );
	$str = str_replace( "&lt;"  , "<", $str );
	$str = str_replace( "&gt;"  , ">", $str );
	$str = str_replace( "&quot;", '"', $str );
	$str = str_replace( "&#039;", "'", $str );

	return $str;
}

function _br2newline($str)
{
	$str = preg_replace( "#(?:\n|\r)?<br />(?:\n|\r)?#", "\n", $str );
	$str = preg_replace( "#(?:\n|\r)?<br>(?:\n|\r)?#"  , "\n", $str );

	return $str;
}

function EditReverter($str)
{
	$str = _unhtmlspecialchars($str);
	$str = _br2newline($str);
	return $str;
}

function TitleFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\n\e\0\r\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _TagFilter($str);
	$str = _CommonFilter($str);
	return $str;
}

function NameFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\n\e\0\r\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _CommonFilter($str);
	return $str;
}

function PasswdFilter($str)
{
	return preg_replace( '/[\a\f\n\e\0\r\t\x0B]/is', "", trim($str) );
}

function EmailFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\n\e\0\r\t\x0B\;\#\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]/is', "", $str );
	$str = _CommonFilter($str);

	if (substr_count($str, '@') > 1)
	{
		return FALSE;
	}

	if (preg_match('/^[_a-zA-Z0-9\-\.]+@([\-_a-zA-Z0-9]+\.)+[a-zA-Z0-9]{2,4}$/', $str))
	{
		return $str;
	}
	else
	{
		return FALSE;
	}
}

function ContentFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\e\0\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _TagFilter($str);
	$str = _CommonFilter($str);
	$str = _LineFilter($str);
	return $str;
}

function TextFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\e\0\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _TagFilter($str);
	$str = _CommonFilter($str);
	return $str;
}

function CacheFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\e\0\t\x0B]/is', "", $str );
	$str = _CommonFilter($str);
	return $str;
}

function PathFilter($str)
{
	$str = _strtolower($str);
	$str= preg_replace("#[^a-z0-9\.\_\-\/]#", "", $str);
	return $str;
}

function HashFilter($str)
{
	$str = _strtolower($str);
	return preg_replace("/[^a-f0-9]/", "", $str);
}

function KeywordsFilter($str="", $crap=0)
{
	$str = trim($str);
	$str = urldecode($str);
	$str = preg_replace( '/[\a\f\e\0\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _CommonFilter($str);
	$str = str_replace( "%", "\\%", $str);
	$str = _strtolower($str);
	$str = _LineFilter($str);
	$str = preg_replace( "/\s+(and|or)$/" , "" , $str );
	//$str = str_replace( "*", "%", $str );
	$str = str_replace( "_", "\\_", $str );
	$str = str_replace( '|', "&#124;", $str );
	if ($crap == 0)
	{
		$str = preg_replace( "/[\|\[\]\{\}\(\)\,:\\\\\/\"']|&quot;/", "", $str );
	}
	$str = preg_replace( "/^(?:img|quote|code|html|javascript|a href|color|span|div|border|style)$/", "", $str );
	return " ".preg_quote($str)." ";
}

function get_client_ip()
{
	if (isset($_SERVER['HTTP_CLIENT_IP']) and !empty($_SERVER['HTTP_CLIENT_IP']))
	{
		return _IPFilter($_SERVER['HTTP_CLIENT_IP']);
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ip = strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
		do
		{
			$ip = ip2long($ip);

			//-------------------
			// skip private ip ranges
			//-------------------
			// 10.0.0.0 - 10.255.255.255
			// 172.16.0.0 - 172.31.255.255
			// 192.168.0.0 - 192.168.255.255
			// 127.0.0.1, 255.255.255.255, 0.0.0.0
			//-------------------
			if (!(($ip == 0) or ($ip == 0xFFFFFFFF) or ($ip == 0x7F000001) or
			(($ip >= 0x0A000000) and ($ip <= 0x0AFFFFFF)) or
			(($ip >= 0xC0A8FFFF) and ($ip <= 0xC0A80000)) or
			(($ip >= 0xAC1FFFFF) and ($ip <= 0xAC100000))))
			{
				return long2ip($ip);
			}
		} while ($ip = strtok(','));
	}
	if (isset($_SERVER['HTTP_PROXY_USER']) and !empty($_SERVER['HTTP_PROXY_USER']))
	{
		return _IPFilter($_SERVER['HTTP_PROXY_USER']);
	}
	if (isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR']))
	{
		return _IPFilter($_SERVER['REMOTE_ADDR']);
	}
	else
	{
		return "0.0.0.0";
	}
}

function get_bot($agent)
{
	$SpiderArr = array(
	"ia_archiver"	=> "Alexa/The Internet Archive robot",
	"Indy Library"	=> "Borland Internet Direct Library",
	"grub"			=> "Grub Crawler",
	"googlebot"		=> "Google.com",
	"slurp@inktomi" => "Hot Bot",
	"ask jeeves"	=> "Ask Jeeves",
	"lycos"			=> "Lycos.com",
	"whatuseek"		=> "What You Seek",
	"ia_archiver"	=> "Archive.org",
	"Baiduspider"	=> "Baidu Spider",
	"msnbot"		=> "MSN Bot",
	"SpiderMan"		=> "SpiderMan",
	"Yahoo"			=> "Yahoo Bot",
	"sohu-search"	=> "Sohu Bot",
	"EmeraldShield" => "EmeraldShield.com ",
	"Crawler"		=> "Internet Crawler",
	"sogou spider"	=>	"sogou spider",
	'KooxooSearch/1.0' => 'KooxooSearch/1.0',
	);
	foreach ($SpiderArr as $pattern => $result)
	{
		if (stristr($agent, $pattern) !== FALSE)
		{
			return array(true,$result);
		}
	}
	return array(false,'Unknown');
}

/**
 *
 * 截取字符串长，一个汉字为2个字符记算
 * author:ouyang
 * modify time :2013.2.21
 */
function str_cut($string, $length=30) {


	return sub_str_chn($string,$length);
}


function str_line_cut($str, $length=66, $line=6)
{
	return strtr($str, array("\r" => "", "\n" => "", "<br>" => "", "<br />" => ""));
}

/**
 * 根据constants.inc.php里的数组生成各字段的option list
 *
 * @param unknown_type $arrayList
 * @param unknown_type $select
 * @param unknown_type $showId
 * @return unknown
 */
function generateOptionList($arrayList, $select = -999, $certify = 0) {

	$optionls = '';

	$i = 0;
	while (list($key, $value) = each($arrayList))
	{
		if (empty($value))
		{
			continue;
		}
		$htmlName = $value;
		if ($certify == 0)
		{
			if (($select != -999) && ($key == $select))
			{
				$optionls .= '<option value="' . $key . '" selected="selected">' . $htmlName . '</option>\n';
			}
			else
			{
				$optionls .= '<option value="' . $key . '">' . $htmlName . '</option>\n';
			}
		}
		else
		{
			$i++;

			if (($select != -999) && ($i == $select))
			{
				$optionls .= "<option value=\"" . $i . "\" selected>" . $htmlName . "</option>\n";
			}
			else
			{
				$optionls .= "<option value=\"" . $i . "\">" . $htmlName . "</option>\n";
			}
		}
	}
	return $optionls;
}

/**
 * 根据生日生成星座
 *
 * @param unknown_type $birthday
 * @return unknown
 */
function generate_zodiac($birthday)
{
	$zodiac = 0;
	$birthday = intval($birthday);
	if ($birthday > 320 && $birthday < 420)
	{
		$zodiac = 1;
	}
	elseif ($birthday > 419 && $birthday < 521)
	{
		$zodiac = 2;
	}
	elseif ($birthday > 520 && $birthday < 622)
	{
		$zodiac = 3;
	}
	elseif ($birthday > 621 && $birthday < 723)
	{
		$zodiac = 4;
	}
	elseif ($birthday > 722 && $birthday < 823)
	{
		$zodiac = 5;
	}
	elseif ($birthday > 822 && $birthday < 923)
	{
		$zodiac = 6;
	}
	elseif ($birthday > 922 && $birthday < 1024)
	{
		$zodiac = 7;
	}
	elseif ($birthday > 1023 && $birthday < 1122)
	{
		$zodiac = 8;
	}
	elseif ($birthday > 1121 && $birthday < 1222)
	{
		$zodiac = 9;
	}
	elseif (($birthday > 1221 && $birthday < 1232) || ($birthday > 100 && $birthday < 120))
	{
		$zodiac = 10;
	}
	elseif ($birthday > 119 && $birthday < 219)
	{
		$zodiac = 11;
	}
	elseif ($birthday > 218 && $birthday < 321)
	{
		$zodiac = 12;
	}

	return $zodiac;
}

/**
 * 根据给出的生日日期，生成生肖的代码
 *
 * @param unknown_type $birth_year
 * @param unknown_type $birthday
 * @return unknown
 */
function generate_animal($birth_year, $birthday)
{
	global $springfestival_array;
	$birth_year = intval($birth_year);
	$birthday = intval($birthday);

	if ($birthday < $springfestival_array[$birth_year])
	{
		$birth_year_lunar = $birth_year - 1;
	}
	else
	{
		$birth_year_lunar = $birth_year;
	}

	$animal = ($birth_year_lunar - 1923+120) % 12;

	if ($animal == 0)
	{
		$animal = 12;
	}

	return $animal;
}

/**
 * 返回给出的$strPassword是否为有效的密码
 *
 * @param unknown_type $strPassword
 * @return unknown
 */
function valid_password($strPassword)
{
	return ereg("^[a-zA-Z0-9]{6,}$", $strPassword);
}

/**
 * 返回给出的$strMobile是否为有效的手机号码
 *
 * @param unknown_type $strMobile
 * @return unknown
 */
function valid_mobile($strMobile)
{
	return ereg("^1[0-9]{10}$", $strMobile);
}

/**
 * 判断输入是否为整数
 *
 * @param unknown_type $value
 * @return unknown
 */
function valid_int ($value){
	return  ereg("^[1-9][0-9]+", $value);
}

function generate_pageindex($all_page, $current_page, $pre_url, $line_num = 15)
{
	$page_index = '';
	if ($all_page > 1)
	{
		if ($all_page > $line_num)
		{
			$first_page = max($current_page - floor($line_num / 2), 1); //保证当前页左边不空
			$first_page = min($first_page, $all_page - $line_num + 1);	//保证当前页右边不空
			$last_page = $first_page + $line_num - 1;
		}
		else
		{
			$first_page = 1;
			$last_page = $all_page;
		}
		for ($i=$first_page;$i<=$last_page;$i++)
		{
			if ($i == $current_page)
			{
				$page_index .= ' ' . $i . ' ';
			}
			else
			{
				$page_index .= ' <a href="' . $pre_url . $i . '">['. $i .']</a> ';
			}
		}
		if ($current_page != 1)
		{
			$page_index .= '<a href="' . $pre_url . ($current_page - 1) . '">上一页</a> ';
		}
		else
		{
			$page_index .= '上一页 ';
		}
		if ($current_page < $all_page)
		{
			$page_index .= '<a href="' . $pre_url . ($current_page + 1) . '">下一页</a>';
		}
		else
		{
			$page_index .= '下一页';
		}
	}
	return $page_index;
}

function word_filter($str)
{
	return $str;
}

/**
 * 写文件函数
 *
 * @param string $content 文件内容
 * @param string $url 文件地址
 * @return true为写成功，false为写失败
 */
function write_to_file($content, $url)
{
	$handle = fopen($url, "w");
	$file_size = fwrite($handle, $content);
	$cc = fclose($handle);
	return ($file_size && $cc) ? true : false;
}

function smtp_mail($to_email, $subject, $content, $html = false,$send_type = 0)
{
	return fun_smtp_mail($to_email, $subject, $content, $html,$send_type);
}
function send_admin_message($uid_array , $subject , $content , $is_admin = 1)
{
	foreach($uid_array as $to_uid)
	{
		send_admin_msg($uid_array[0], $subject, $content);
		break;  // 只发送一个就结束
	}
}

function get_from_memcache($str)
{
	global $DC, $SDB;
	if ($result = $DC->get_cache($str))
	{
		return $result;
	}
	else
	{
		list($key,$table) = explode('_',$str);
		if ($key && $table)
		{
			switch (strtolower($table))
			{
				case 'user':
					return $DC->get_user_by_hash($key);
					break;
				case 'search':
					return $DC->get_search($key);
					break;
				case 'detail':
					return $DC->get_detail($key);
					break;
				case 'views':
					return $DC->get_views($key);
					break;
				case 'session':
					$result = $SDB->result('SELECT * FROM '.TABLE_PREFIX.'session WHERE session_hash="'.$key.'"');
					if ($result)
					{
						$DC->set_cache($str, $result, 86400);
						return $result;
					}
					break;
				case 'checking':
					return $DC->get_checking($key);
					break;

				case 'advertise':
					list($location,$sublocation,$position) = explode('-',$key);
					$result = $SDB->query('SELECT * FROM advertise WHERE location="'.$location.'" and sublocation="'.$sublocation.'" and position="'.$position .'"');
					$result = $SDB->fetch_array($result);
					if ($result)
					{
						$DC->set_cache($str, $result, 3600);
						return $result;
					}
					break;
				case 'note':
					return $DC->get_note($key);
					break;
				case 'condition':
					$result = get_object_vars($SDB->result('SELECT conditions,update_time FROM '.TABLE_PREFIX.'user_custom_search WHERE uid='.$key));
					if ($result)
					{
						$DC->set_cache($str, $result, 86400);
						return $result;
					}
					else
					{
						return false;
					}
					break;
			}
		}
	}
	if (!strpos($str,'usercp'))
	{
		require_once(WWW_ROOT_PATH .'Log/Log.php');
		global $SESSION;
		$date = date("[d/M/Y:H:i:s O]");
		$log_string = <<<EOF
		{$SESSION->info->ip} - - $date "{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}?NotFound:$str" 200 10000 "{$_SERVER['HTTP_REFERER']}" "{$_SERVER['HTTP_USER_AGENT']}"
EOF;
		$logger = &Log::singleton('file', ROOT_PATH.'error/memcache_log_'.date("Y-m-d").'.dat', 'ident');
		$logger->log($log_string);
	}
	return false;
}

/**
 * 生成sublocation_array及university_array
 *
 * @param unknown_type $sublocation  判断是否输出sublocation_array
 * @param unknown_type $university  判断是否输出university_array
 * @return $js_array   返回一段包含sublocation_array(或university_array)的js
 */

function print_arrays($sublocation = true, $university = true)
{
	global $location_array, $university_array, $sublocation_array;
	if (!$location_array)
	{
		require_once(WWW_ROOT_PATH.'includes/location.inc.php');
	}
	if (!$university_array)
	{
		require_once(WWW_ROOT_PATH.'includes/university.inc.php');
	}
	$js = '<script language="javascript" type="text/javascript">';


	if ($sublocation)
	{
		$js .= <<<EOF
			var sublocation_array = new Array();\n
EOF;

		foreach ($location_array as $key => $value)
		{
			$js .= <<<EOF
				sublocation_array[{$key}] = new Array();\n
EOF;
		}

		foreach ($sublocation_array as $skey => $svalue)
		{
			$lkey = substr($skey, 0, 2);
			$js .= <<<EOF
				sublocation_array[{$lkey}][{$skey}] = '{$svalue}';\n
EOF;
		}
	}

	if ($university)
	{
		$js .= <<<EOF
			var university_array = new Array();\n
EOF;

		foreach ($location_array as $key => $value)
		{
			$js .= <<<EOF
				university_array[{$key}] = new Array();\n
EOF;
		}

		foreach ($university_array as $ukey => $uvalue)
		{
			$lkey = substr($ukey, 0, 2);
			$js .= <<<EOF
				university_array[{$lkey}][{$ukey}] = '{$uvalue}';\n
EOF;
		}
	}

	$js .= '</script>';

	return $js;
}

function get_disp_base_avatar_url($uid_hash) {
	global $avatar_base_url;
	$index = strtolower(substr($uid_hash, 0, 1));
	$dec  =  hexdec($index);
	if($dec >= 0 && $dec <= 7) {
		$avatar_url = $avatar_base_url[1];
	} else {
		$avatar_url = $avatar_base_url[2];
	}
	return $avatar_url;
}

function get_disp_base_photo_url($uid_hash) {
	global $photo_base_url;
	$index = strtolower(substr($uid_hash, 0, 1));
	$dec  =  hexdec($index);
	if($dec >= 0 && $dec <= 7) {
		$photo_url = $photo_base_url[1];
	} else {
		$photo_url = $photo_base_url[2];
	}
	return $photo_url;
}
/**
 * 用于得到用户分配到的文件显示服务器
 * 形如 http://photos.jiayuan.com, http://uploads.jiayuan.com
 * @param unknown_type $uid_hash
 * @return unknown
 */
function get_disp_base_url($uid_hash)
{
	global $photos_base_url;
	$index = strtolower(substr($uid_hash,0,1));
	switch ($index)
	{
		case '0':
			$photo_url = $photos_base_url[1];
			break;
		case '1':
			$photo_url = $photos_base_url[9];
			break;
		case '2':
			$photo_url = $photos_base_url[5];
			break;
		case '3':
			$photo_url = $photos_base_url[13];
			break;
		case '4':
			$photo_url = $photos_base_url[2];
			break;
		case '5':
			$photo_url = $photos_base_url[10];
			break;
		case '6':
			$photo_url = $photos_base_url[6];
			break;
		case '7':
			$photo_url = $photos_base_url[14];
			break;
		case '8':
			$photo_url = $photos_base_url[3];
			break;
		case '9':
			$photo_url = $photos_base_url[11];
			break;
		case 'a':
			$photo_url = $photos_base_url[7];
			break;
		case 'b':
			$photo_url = $photos_base_url[15];
			break;
		case 'c':
			$photo_url = $photos_base_url[4];
			break;
		case 'd':
			$photo_url = $photos_base_url[12];
			break;
		case 'e':
			$photo_url = $photos_base_url[8];
			break;
		case 'f':
			$photo_url = $photos_base_url[16];
			break;
		default:
			$photo_url = $photos_base_url[1];
			break;
	}
	return $photo_url;
}

/**
 * 用于得到用户分配到的文件存储服务器
 * 形如 http://photos.jiayuan.com, http://uploads.jiayuan.com
 * @param unknown_type $uid_hash
 * @return unknown
 */
function get_upload_base_url($uid_hash)
{
	global $photos_path;
	$index = strtolower(substr($uid_hash,0,1));
	switch ($index)
	{
		case '0':
			$photo_url = $photos_path[1];
			break;
		case '1':
			$photo_url = $photos_path[9];
			break;
		case '2':
			$photo_url = $photos_path[5];
			break;
		case '3':
			$photo_url = $photos_path[13];
			break;
		case '4':
			$photo_url = $photos_path[2];
			break;
		case '5':
			$photo_url = $photos_path[10];
			break;
		case '6':
			$photo_url = $photos_path[6];
			break;
		case '7':
			$photo_url = $photos_path[14];
			break;
		case '8':
			$photo_url = $photos_path[3];
			break;
		case '9':
			$photo_url = $photos_path[11];
			break;
		case 'a':
			$photo_url = $photos_path[7];
			break;
		case 'b':
			$photo_url = $photos_path[15];
			break;
		case 'c':
			$photo_url = $photos_path[4];
			break;
		case 'd':
			$photo_url = $photos_path[12];
			break;
		case 'e':
			$photo_url = $photos_path[8];
			break;
		case 'f':
			$photo_url = $photos_path[16];
			break;
		default:
			$photo_url = $photos_path[1];
			break;
	}
	return $photo_url;
}

/**
 * 得到用于显示的用户目录
 * 形如 http://photos.jiayuan.com/00/1...1/11
 *
 * @param $uid_hash
 */

function get_disp_url($uid_hash)
{
	$url =  get_disp_base_url($uid_hash) .get_user_directory($uid_hash);
	$url = str_replace('ad', 'aq', $url);
	return $url;
}

//新头像地址url add by zhangbiao
function get_disp_avatar_url($uid_hash) {
	$url =  get_disp_base_avatar_url($uid_hash) .get_user_directory($uid_hash);
	$url = str_replace('ad', 'aq', $url);
	return $url;
}

//新照片地址url add by zhangbiao
function get_disp_photo_url($uid_hash) {
	$url =  get_disp_base_photo_url($uid_hash) .get_user_directory($uid_hash);
	$url = str_replace('ad', 'aq', $url);
	return $url;
}

//照片刚上传时所在upload地址
function get_pic_upload_url($uid_hash){
	global $pic_upload_base_url;
	$url =  $pic_upload_base_url[1] . get_user_directory($uid_hash);
	$url = str_replace('ad', 'aq', $url);
	return $url;
}

//云端地址 -搜狐
function get_pic_scs_url($uid_hash){
	global $pic_scs_base_url;
	$index = strtolower(substr($uid_hash, 0, 1));
	$dec  =  hexdec($index);
	if($dec >= 0 && $dec <= 7) {
		$photo_url = $pic_scs_base_url[1];
	} else {
		$photo_url = $pic_scs_base_url[2];
	}
	$photo_url.= get_user_directory($uid_hash);
	$photo_url = str_replace('ad','aq',$photo_url);
	return $photo_url;
}


/**
 * 得到用于显示的用户目录
 * 形如 http://photos.jiayuan.com/00/1...1/11
 *
 * @param $uid_hash
 */

function get_upload_url($uid_hash)
{
	$url =  get_upload_base_url($uid_hash) . '/' .get_user_directory($uid_hash);
	return $url;
}

function get_location_output($location=0,$sublocation=0,$is_love_location=0, $only_province=0)
{
	global $location_array,$sublocation_array;
	if (!$location_array)
	{
		require(WWW_ROOT_PATH . 'includes/location.inc.php');
	}
	if (array_key_exists($location,$location_array))
	{
		$location_output = $location_array[$location];
		if($only_province)
		{
			return $location_output;
		}
		if (array_key_exists($sublocation,$sublocation_array) && intval(substr($sublocation,0,2)) == $location)
		{
			$sublocation_output = $sublocation_array[$sublocation];
			/*if (in_array($location,array(71,81,82,99))) // 台港澳，国外
			 {
			 $location_output = '';
			 }*/
		}
		else
		{
			$sublocation_output = '';
		}
	}
	elseif (array_key_exists($location,$sublocation_array))
	{
		$location_output = '';
		$sublocation_output = $sublocation_array[$sublocation];
	}
	elseif ($is_love_location)
	{
		$location_output = '不限地区';
		$sublocation_output = '';
	}
	else
	{
		$location_output = '';
		$sublocation_output = '';
	}
	return $location_output . $sublocation_output;
}

function save_upload_log($path,$operation=0)
{
	global $photos_path, $photos_log_path;
	$operation_array = array(
	0	=>	'add',
	1	=>	'del'
	);
	for ($i=1;$i<=4;$i++)
	{
		if (strpos($path,$photos_path[$i]) === 0)
		{
			$content = str_replace($photos_path[$i],$photos_log_path[$i],$path);
			$content = substr($content,strlen($photos_log_path[$i]));
			$sub_path = substr($content,0,strrpos($content,'/')+1);
			$file_name = substr($content,strrpos($content,'/')+1);
			$handle = fopen($photos_path[$i] . 'log/' . date('Y-m-d') . '.dat' , 'a');
			flock($handle, LOCK_EX);
			$file_size = fwrite($handle, $operation_array[$operation] . ' ' . $photos_log_path[$i] . ' ' . $sub_path . ' ' . $file_name . "\n");
			flock($handle, LOCK_UN);
			$cc = fclose($handle);
			return ($file_size && $cc) ? true : false;
		}
	}
	return false;
}


// add by zlp
/**
 * 获得可视化页面编辑器
 *
 * @param unknown_type $content
 * @param unknown_type $id
 * @return unknown
 */
function get_webeditor($content, $id="form_content", $width=500, $height = 300)
{
	$content = htmlspecialchars($content, ENT_QUOTES);
	$content = str_replace('<BR>', '<br />', $content);
	$ret = <<<EOF
	<input name="{$id}" type="hidden" id="{$id}" value="{$content}" />
	<iframe id="webedit1" src="webeditor/index.php?id={$id}&style=standard&fullscreen=0" frameborder="0" scrolling="no" width="{$width}" height="{$height}"></iframe>
EOF;
	return $ret;
}
/**
 * 读取信件模板
 * @param  name    模板名称  参考 msg_template 表
 * @return array   信件subject & content
 */

function get_msg_template($name='', $tid = 0)
{
	global $SDB;
	$sql = "SELECT subject, content FROM msg_template WHERE name = '{$name}'";
	if($tid > 0)
	{
		$sql = "SELECT subject, content FROM msg_template WHERE tid = {$tid}";
	}
	$obj = $SDB->result($sql);
	$msg['subject'] = $obj->subject;
	$msg['content'] = $obj->content;
	return $msg;
}
/**
 * 根据出生年和性别获得表名
 * @param int $birth_year
 * @param string $sex
 * @return  a string of table_name
 */
function get_table_name($birth_year, $sex)
{
	$table_name = $birth_year . $sex;
	if($birth_year < 1970)
	{
		$table_name = '1970'. $sex;
	}
	return $table_name;
}


/**
 * 设置memcache数据
 * @param string $uid_or_uid_hash
 * @param string $table_name
 * @param string $item
 * @param unknown_type $value
 * @param int $isobject
 */
function fun_set_memcache_data($uid_or_uid_hash, $table_name, $item, $value, $isobject=0)
{
	global $DC;
	$cache_info = fun_get_memcache_data($uid_or_uid_hash, $table_name);
	if($isobject == 1)
	{
		$cache_info->{$item} = $value;
	}
	else
	{
		$cache_info[$item] = $value;
	}
	switch ($table_name)
	{
		case 'user':
			$DC->memcache->replace($uid_or_uid_hash.'_user', $cache_info);
			break;
		case 'search':
			$DC->memcache->replace($uid_or_uid_hash.'_search', $cache_info);
			break;
		case 'detail':
			$DC->memcache2->replace($uid_or_uid_hash.'_detail', $cache_info);
			break;
		case 'checking':
			$DC->memcache2->replace($uid_or_uid_hash.'_checking', $cache_info);
			break;
		case 'note':
			$DC->memcache2->replace($uid_or_uid_hash.'_note', $cache_info);
			break;
		default:
			break;
	}
}

/**
 * 读取memcache
 * @param  $uid_or_uid_hash
 * @param string $table_name
 */
function fun_get_memcache_data($uid_or_uid_hash , $table_name)
{
	global $DC;
	if($table_name == 'user')
	{
		return $DC->get_user_by_hash($uid_or_uid_hash);
	}
	elseif($table_name == 'search')
	{
		return $DC->get_search($uid_or_uid_hash);
	}
	elseif($table_name === 'checking')
	{
		$DC->get_checking($uid_or_uid_hash);
	}
	elseif($table_name === 'detail')
	{
		$DC->get_detail($uid_or_uid_hash);
	}
	elseif($table_name === 'note')
	{
		$DC->get_note($uid_or_uid_hash);
	}
	else
	{
		return false;
	}
}

function get_disp_uid($uid)
{
	if ($uid)
	{
		return $uid+1000000;
	}
	else
	{
		return 0;
	}
}

function get_real_uid($disp_uid)
{
	$uid = $disp_uid > 1000000 ? $disp_uid - 1000000 : false;
	return $uid;
}

function trim_dot($str)
{
	$str = str_replace(",,",",",$str);
	if(strpos("a".$str,",,"))
	{
		return trim_dot($str);
	}
	else
	{
		return $str;
	}
}

function trim_period($str)
{
	$str = str_replace("..",".",$str);
	if(strpos("a".$str,".."))
	{
		return trim_period($str);
	}
	else
	{
		return $str;
	}
}

function trim_sigh($str)
{
	$str = str_replace("!!!!","!!!",$str);
	if(strpos("a".$str,"!!!!"))
	{
		return trim_sigh($str);
	}
	else
	{
		return $str;
	}
}

function trim_dot_zh($str)
{
	$str = str_replace("，，","，",$str);
	if(strpos("a".$str,"，，"))
	{
		return trim_dot_zh($str);
	}
	else
	{
		return $str;
	}
}

function trim_period_zh($str)
{
	$str = str_replace("。。","。",$str);
	if(strpos("a".$str,"。。"))
	{
		return trim_period_zh($str);
	}
	else
	{
		return $str;
	}
}

function trim_sigh_zh($str)
{
	$str = str_replace("！！！！","！！！",$str);
	if(strpos("a".$str,"！！！！"))
	{
		return trim_sigh_zh($str);
	}
	else
	{
		return $str;
	}
}


function build_text($str)
{
	$str = str_replace("\n\n","\n",$str);
	if(strpos("a".$str,"\n\n"))
	{
		return build_text($str);
	}
	else
	{
		return $str;
	}
}

function trim_text($str)
{
	$str = str_replace("  "," ",$str);
	if(strpos("a".$str,"  "))
	{
		return trim_text($str);
	}
	else
	{
		return $str;
	}
}

function format_text($text)
{
	$text = html_entity_decode($text);
	$enter = array("\r","<br />","<br>","<br/>","<p>","</p>");
	$text = str_replace($enter,"\n",$text);
	$words = array("　","","&quot;","&lt;","&gt;","&amp;","&reg;","&copy;","&#174;","&#169;");
	$text = str_replace($words,"",$text);
	$text = trim($text);
	$text = trim_text($text);
	$text = str_replace("\n \n","\n",$text);
	$text = trim_dot($text);
	//$text = trim_period($text);
	$text = trim_sigh($text);
	$text = trim_dot_zh($text);
	$text = trim_period_zh($text);
	$text = trim_sigh_zh($text);
	$text = build_text($text);
	$text = "　　".$text;
	$text = str_replace("\n","\n\n　　",$text);
	$text = str_replace("　　 ","　　",$text);
	$text = nl2br($text);

	return $text;
}

function get_table($table,$birth_year,$sex)
{
	$birth_year = $birth_year<1970 ? 1970 : $birth_year;
	if ($table == 'photo')
	{
		return "user_photo_$sex";
	}
	else
	{
		return "user_$table";
	}

}

function get_req_value($value_name,$method	=	'request',$def	=	'')
{
	$value	=	"";
	switch ($method)
	{
		case "get":
			$value	=	$_GET[$value_name];
			break;
		case "post":
			$value	=	$_POST[$value_name];
			break;
		default:
			$value	=	$_REQUEST[$value_name];
			break;
	}

	if(is_array($value))
	{
		while(list($key,$val)	=	each($value))
		{
			$val	=	ContentFilter($val);
			$val	=	addslashes($val);
			$val	=	trim($val);
			$value[$key]	=	$val;
		}

		return $value;
	}

	$value	=	ContentFilter($value);
	$value	=	addslashes($value);
	//$value	=	htmlentities($value);
	$value	=	trim($value);
	if($value	==	'')
	{
		return $def;
	}
	else
	{
		return $value;
	}
}


/**
 *
 * 截取字符串长，一个汉字为3个字符记算
 * author:ouyang
 * modify time :2013.2.21
 */
function sub_str_chn($string, $length,$dot="…") {

	//当字符长度小于要取的长度直接返回
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';

	if(strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 3;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 3;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 3;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 3;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 3;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}
		//为兼容上一个版本“...”省略号计算在内，把截取长度再3
		if($length > 3)
		{
		$n -= 3;
		}
		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return $strcut.$dot;
}

function ban_ip()
{
	if(!defined('BAN_IP'))
	{
		define('BAN_IP', 200);
	}
	if(!USE_BAN_IP)
	{
		return false;
	}
	$agent	=	$_SERVER['HTTP_USER_AGENT'];
	list($is_bot,$bot_name)	=	get_bot($agent);
	if($is_bot)
	{
		return false;
	}
	global $DC;

	$IP	=	get_client_ip();

	$ip_str	=	'IP_'.$IP;
	$ip_arr	=	$DC->get_cache($ip_str);
	if($ip_arr['count']	>=	BAN_IP && $ip_arr['ban'])
	{
		return false; //true
	}
	$date_str	=	floor(time()/30);
	if($ip_arr['time']	!=	$date_str)
	{
		$ip_arr['count']	=	1;
		$ip_arr['time']		=	$date_str;
		$DC->set_cache($ip_str,$ip_arr, 864000);
		return false;
	}
	else
	{
		$ip_arr['count']++;
		if($ip_arr['count']	>=	BAN_IP)
		{
			$ip_arr['ban']	=	1;
			$DC->set_cache($ip_str,$ip_arr, 864000);
			fopen('http://10.0.0.11/ip_ban/index.php?ip='.$IP.'_'.BAN_IP.'&time='.date('Y-m-d H:i:s').'&url='.urlencode($_SERVER['REQUEST_URI']),'r');
			return false; //true
		}
		$ip_arr['ban']	=	0;
		$DC->set_cache($ip_str,$ip_arr, 864000);
	}
	return false;
}

function load_ad($site, $page)
{
	global $SDB;
	$dir = '/var/admin/linsc/'.$site.'_'.$page.'_';
	$adl_str = file_exists($dir.'l_.js') ? file_get_contents($dir.'l_.js') : '';
	$adr_str = file_exists($dir.'r_.js') ? file_get_contents($dir.'r_.js') : '';
	$adt_str = file_exists($dir.'t_.js') ? file_get_contents($dir.'t_.js') : '';
	$output=<<<EOF
	<script type="text/javascript">
	lastScrollY=0;
function heartBeat(){
var diffY;
if (document.documentElement && document.documentElement.scrollTop)
        diffY = document.documentElement.scrollTop;
else if (document.body)
        diffY = document.body.scrollTop;
else
    {/*Netscape stuff*/}

//alert(diffY);
percent=.1*(diffY-lastScrollY);
if(percent>0)percent=Math.ceil(percent);
else percent=Math.floor(percent);
document.getElementById("love21cnadl").style.top=parseInt(document.getElementById("love21cnadl").style.top)+percent+"px";
document.getElementById("love21cnadr").style.top=parseInt(document.getElementById("love21cnadl").style.top)+percent+"px";

lastScrollY=lastScrollY+percent;
//alert(lastScrollY);
}
suspendcode12="<DIV id=\"love21cnadl\" style='left:5px;POSITION:absolute;TOP:120px;'>{$adl_str}</div>";
suspendcode14="<DIV id=\"love21cnadr\" style='right:5px;POSITION:absolute;TOP:120px;'>{$adr_str}</div>";
var object = document.getElementById('love21cnadt');
if(object)
{
object.innerHTML="{$adt_str}";
}
document.write(suspendcode12);
document.write(suspendcode14);
window.setInterval("heartBeat()",1);
</script>
EOF;
	return $output;
}

function love_encode($str)
{
	$str	=	md5($str.'^@$');
	$str	=	md5($str.'*UF');
	$rand	=	rand(0,1000);
	$rand	=	dechex($rand);
	$str	=   md5($str.$rand);
	$ret	=	'';
	for($i=0;$i<strlen($str);$i++)
	{
		$ret	.=	$str{$i};
		if($i<strlen($rand))
		{
			$ret	.=	$rand{$i};
		}
	}
	return $ret;
}

function love_decode($str,$code)
{
	$len	=	strlen($code)	-	32;
	$rand 	=	'';
	for($i=0;$i<$len;$i++)
	{
		$k	=	$i*2+1;
		$rand	.=	$code{$k};
	}
	$str	=	md5($str.'^@$');
	$str	=	md5($str.'*UF');
	$str	=   md5($str.$rand);
	for($i=0;$i<strlen($str);$i++)
	{
		$ret	.=	$str{$i};
		if($i<strlen($rand))
		{
			$ret	.=	$rand{$i};
		}
	}
	if($ret	==	$code)
	{
		return true;
	}
	else
	{
		return false;
	}
}
//密码加密/解密编码函数
function love_encode_pwd($str){
	$str	=	md5($str.'^@$H');//md5($str.'^@$K');
	$str	=	md5($str.'*UU#');//md5($str.'*UF#');
	$rand	=	time();
	$rand	=	dechex($rand);
	$str	=   md5($str.$rand);
	$ret	=	'';
	for($i=0;$i<strlen($str);$i++){
		$ret .= $str{$i};
		if($i<strlen($rand)){
			$ret .= $rand{$i};
		}
	}
	return $ret;
}

function love_decode_pwd($str,$code,$is_dead = true,$deadline = 86400){
	$len = strlen($code) - 32;
	$rand = '';
	for($i=0;$i<$len;$i++){
		$k = $i*2+1;
		$rand .= $code{$k};
	}
	$str	=	md5($str.'^@$H');//md5($str.'^@$K');
	$str	=	md5($str.'*UU#');//md5($str.'*UF#');
	$str	=   md5($str.$rand);
	for($i=0;$i<strlen($str);$i++){
		$ret	.=	$str{$i};
		if($i<strlen($rand)){
			$ret .= $rand{$i};
		}
	}
	if($ret == $code && $is_dead){
		$time = (time() - $deadline) - hexdec($rand);
		if($time > 0) return false;
	}
	return ($ret == $code) ? true : false;
}

function get_age($birthyear,$birthday,$year=false,$date=false)
{
	if ($year === false)
	{
		$year = date('Y');
	}
	if ($date === false)
	{
		$date = date('md');
	}
	$add_age = $birthday < $date ? 0 : -1;
	$age = $year - $birthyear + $add_age;
	if ($age < 18)
	{
		$age = 18;
	}
	return $age;
}

function get_tag_array($tag_dec)
{
	$limit = pow(2,32);
	$dec_high = floor($tag_dec / $limit);
	$dec_low = $tag_dec - $dec_high*$limit;
	$tag_bin_low = str_pad(decbin($dec_low),32,"0",STR_PAD_LEFT);
	$tag_bin_high = str_pad(decbin($dec_high),32,"0",STR_PAD_LEFT);
	$tag_bin = $tag_bin_high.$tag_bin_low;
	$result_tag = array();
	for ($i=1;$i<38;$i++)
	{
		$result_tag[$i] = $tag_bin{63-$i};
	}
	return $result_tag;
}

function relocate($url='/index.php')
{
	global $SESSION;
	header("Location: $url");
	exit();
}

function get_relation_array($uid, $relation_type='friends', $reverse=false)
{
	global $DC;
	return $DC->get_relation($uid, $relation_type, $reverse);
}

function update_relation_array($uid, $uid_array, $relation_type='friends')
{
	global $DC;
	return $DC->update_relation($uid, $relation_type, $uid_array);
}

function get_search_start($count, $result_per_page, &$p, $pname='p')
{
	if ($p<=0)
	{
		$p = isset($_REQUEST[$pname]) && intval($_REQUEST[$pname]) > 0 ? intval($_REQUEST[$pname]) : 1;
	}
	$page	=	ceil($count/$result_per_page);
	$page = $page==0 ? 1 : $page;
	$p = $p>$page ? $page : $p;
	$start = ($p-1) * $result_per_page;
	return array($start, $page);
}

function get_language($lang)
{
	$lang_bin = str_pad(decbin($lang),30,"0",STR_PAD_LEFT);
	$result_lang = array();
	for ($i=1;$i<30;$i++)
	{
		if ($lang_bin{29-$i})
		{
			$result_lang[$i] = true;
		}
	}
	return $result_lang;
}

function get_language_dec($lang)
{
	global $language_array;
	$lang_bin = str_pad(decbin($lang),30,"0",STR_PAD_LEFT);
	$result_lang = array();
	for ($i=1;$i<30;$i++)
	{
		if ($lang_bin{29-$i})
		{
			$result_lang[] = $language_array[$i];
		}
	}
	$result_lang = array_filter($result_lang);
	return $result_lang;
}

function get_multi_result($dec)
{
	$limit = pow(2,31);
	if ($dec < $limit)
	{
		$bin = decbin($dec);
	}
	else
	{
		$dec_high = floor($dec / $limit);
		$dec_low = $dec - $dec_high*$limit;
		$bin_low = str_pad(decbin($dec_low),31,"0",STR_PAD_LEFT);
		$bin_high = decbin($dec_high);
		$bin = $bin_high.$bin_low;
	}
	$len = strlen($bin);
	$result = array();
	for ($i=1;$i<$len;$i++)
	{
		$result[$i] = $bin{$len-1-$i};
	}
	return $result;
}

function get_multi_disp($dec, $array)
{
	$dec_array = get_multi_result($dec);
	$result = array();
	foreach ($array as $key => $value)
	{
		if ($dec_array[$key])
		{
			$result[] = $value;
		}
	}
	return $result;
}

function get_shortlocation_output($location=0,$sublocation=0,$is_love_location=0)
{
	global $location_array,$sublocation_array;
	if (!$location_array)
	{
		require(WWW_ROOT_PATH . 'includes/location.inc.php');
	}
	if (array_key_exists($sublocation, $sublocation_array) AND !in_array($location,array(11,12,31,50)))
	{
		return $sublocation_array[$sublocation];
	}
	elseif (array_key_exists($location, $location_array))
	{
		return $location_array[$location];
	}
	elseif ($is_love_location)
	{
		return '不限';
	}
	else
	{
		return '';
	}
}

function get_brightlist($site=0)
{
	global $SDB, $DC;
	$site = $site == 1 ? 1 : 0;
	$result_array = $DC->get_cache("brightlist_new");
	$year = date("Y");
	$md = date("md");
	if (empty($result_array))
	{
		$query_string = "SELECT uid,start_date,end_date,location,match_work_location,match_work_sublocation,birth_year,birthday,nickname,work_location,work_sublocation,avatar,privacy FROM brightlist INNER JOIN user_search_f USING (uid) WHERE brightlist.status=1";
		$result_array = array();
		$query = $SDB->query($query_string);
		while ($result = $SDB->fetch_assoc($query))
		{
			$age = get_age($result['birth_year'], $result['birth_day']);
			$result_array[$result['uid']] = array(
			0 => $result['start_date'],
			1 => $result['end_date'],
			2 => $result['location'],
			'age' => $age,
			'sex' => 'f',
			'match_work_location' => $result['match_work_location'],
			'match_work_sublocation' => $result['match_work_sublocation'],
			'nickname' => $result['nickname'],
			'work_location' => $result['work_location'],
			'work_sublocation' => $result['work_sublocation'],
			'avatar' => $result['avatar'],
			'privacy' => $result['privacy'],
			);
		}
		$query_string = "SELECT uid,start_date,end_date,location,match_work_location,match_work_sublocation,birth_year,birthday,nickname,work_location,work_sublocation,avatar,privacy FROM brightlist INNER JOIN user_search_m USING (uid) WHERE brightlist.status=1";
		$query = $SDB->query($query_string);
		while ($result = $SDB->fetch_assoc($query))
		{
			$age = get_age($result['birth_year'], $result['birth_day']);
			$result_array[$result['uid']] = array(
			0 => $result['start_date'],
			1 => $result['end_date'],
			2 => $result['location'],
			'age' => $age,
			'sex' => 'm',
			'match_work_location' => $result['match_work_location'],
			'match_work_sublocation' => $result['match_work_sublocation'],
			'nickname' => $result['nickname'],
			'work_location' => $result['work_location'],
			'work_sublocation' => $result['work_sublocation'],
			'avatar' => $result['avatar'],
			'privacy' => $result['privacy'],
			);
		}
		if (!$DC->set_cache("brightlist_new", $result_array, 600))
		{
			$DC->update_cache("brightlist_new", $result_array, 600);
		}
	}
	return $result_array;
}

function get_priority($site=0)
{
	global $SDB, $DC;
	$site = $site == 1 ? 1 : 0;
	$result_array = $DC->get_cache("priority_new");
	if (empty($result_array))
	{
		$query_string = "SELECT uid,start_date,expire_date FROM priority WHERE status=1";
		$result_array = array();
		$query = $SDB->query($query_string);
		while ($result = $SDB->fetch_assoc($query))
		{
			$result_array[$result['uid']] = array($result['start_date'], $result['expire_date']);
		}
		if (!$DC->set_cache("priority_new", $result_array, 600))
		{
			$DC->update_cache("priority_new", $result_array, 600);
		}
	}
	return $result_array;
}

function get_happiness_count()
{
	global $SDB, $DC;
	$happiness_count = $DC->get_cache("happiness_count");
	if (empty($happiness_count))
	{
		$today_timestamp = strtotime(date("Y-m-d", time()));
		$today = ceil(($SDB->result('SELECT count(*) FROM happiness WHERE status>1 AND create_date>'.$today_timestamp))*2.5);
		if($today	==	0)
		{
			$today	=	10;
		}
		$all = 648888 + ceil(($SDB->result('SELECT count(*) FROM happiness WHERE status>1 AND create_date>'.mktime(0,0,0,8,1,2007)))*2.5);
		$happiness_count = array($today, $all);
		if (!$DC->set_cache("happiness_count", $happiness_count, 60))
		{
			$DC->update_cache("happiness_count", $happiness_count, 60);
		}
	}
	return $happiness_count;
}

//老英文站重开调试需要 Add By ZhuShunqing
function get_usercp_stats($uid=0){
	return get_usercp_stats_cmi_new($uid);
}

//获取我的佳缘用户状态数据
function get_usercp_stats_cmi_new($uid=0)
{
	//CMI
	require_once(WWW_ROOT_PATH . 'includes/CMI/CMI.inc.php');
	$cmi = &$GLOBALS['CMI'];//改为引用全局CMI
	$cmi->mod('profile');	//使用mod方法加载其它模块
	$cmi->mod('session');
	$cmi->mod('account');
	$cmi->mod('photo');
	$cmi->mod('msg');
    $cmi->mod('zhuanti');		//增加专题功能控制

	$SESSION = $cmi->session;

	$menu_conf_arr = include(WWW_ROOT_PATH . 'usercp/conf/menu_config_www.conf.php');	//加载新版左菜单配置文件

	if(!$SESSION->is_login()){
        $cache['menu_config'] = get_usercp_menu($menu_conf_arr);	//未登录，处理未登录左菜单
		return $cache;
	}

    $login_user = $SESSION->get_user();
    if(is_array($login_user) && count($login_user) > 0){
		foreach($login_user as $key => $value){
			$USER->$key = $value;	//获取登录用户信息
		}
	}
	$uid = $USER->uid;
	$now = time();

	$memc = $cmi->memc->loadMemc('usercp_menu');
	$cache = $memc->get("{$uid}_usercpstats_new");
	if(empty($cache)){
 		$user_info = $cmi->profile->get_userinfo($uid, array('new_msg','uid_disp', 'uid_hash', 'nickname', 'name', 'age', 'ms_mobile', 'register_time', 'upload_count', 'gid', 'work_location', 'work_sublocation', 'sex', 'article', 'level', 'complete', 'login_count', 'avatar'));
 		$obj_checking = $cmi->profile->get_checking($uid);
 		if(in_array($user_info['avatar'],array(0,2,4)) && !$obj_checking['avatar']){
 			$no_avatar = true;
 		}
 		if($no_avatar && $now - $user_info['register_time'] < 3600*72){
 			$avatar = IMG_BASE_URL . '/profile_new/i/portrait.jpg';
 			$new_reg_no_avatar = true;
 		}else{
 			//头像
			$avatar = $cmi->photo->get_useravatar($uid, false, 'b', 2);

			//取mem用于及时显示审核通过的头像
			$key = 'check_avatar_'.$uid;
			$mem_avatar = $memc->get($key);
			$avatar .= $mem_avatar == 'checking' ? '?'.time() : '';
 		}
		//判断用户没有照片
		$have_photo = 1;
		$my_photo_list = $cmi->photo->get_userphoto($uid, $uid, 'desc', true);
		if($my_photo_list < 0){
			$my_photo_list = array();
		}
		$my_photo_count_num = count($my_photo_list);	//查看用户照片数
		if(!$my_photo_count_num && $no_avatar){
			$have_photo = 0;
		}
        $user_info['is_vip'] = 0;//是否VIP会员
        $user_info['is_chat'] = 0;//聊天
        $user_info['is_readmsg'] = 0;//看信
        $user_info['is_diamond'] = 0;//钻石
        $user_info['is_sendmsg'] = 0;//发信
        $service_arr = $cmi->account->get_service_list($uid);//获取当前用户购买的所有服务
        if(isset($service_arr[2])){$user_info['is_vip'] = 1;}
        if(isset($service_arr[33])){$user_info['is_chat'] = 1;}
        if(isset($service_arr[38])){$user_info['is_readmsg'] = 1;}
        if(isset($service_arr[40])){$user_info['is_diamond'] = 1;}
        if(isset($service_arr[41])){$user_info['is_sendmsg'] = 1;}
        //拆分显示用UID的每一位
        $uid_disp_str = substr($user_info['uid_disp'], -6);//取显示用UID后六位用于条件操作
        $uid_last_six = array();
        for($i = 0; $i < strlen($uid_disp_str); $i++){
            $uid_last_six[] = $uid_disp_str[$i];
        }
		$user_info['is_new_user'] = $user_info['new_msg'] == 10 ? 1 : 0;	//判断会员类型1，新会员；0，旧会员
		$user_info_dict = $cmi->profile->get_userinfo($uid, array('status'), 1);
        $user_info['uid_last_six'] = $uid_last_six;
        $user_info['is_lctuan'] = $cmi->zhuanti->lctuan_user_is_buy($uid);
		$cache = array(
			'disp_id' => $user_info['uid_disp'],
			'avatar' => $avatar,
			'uid_hash' => $user_info['uid_hash'],
			'nickname' => $user_info['nickname'],
			'status_str' => $user_info_dict['status'],
			'complete' => $user_info['complete'],
			'photo' => $user_info['upload_count'],
			'gid' => $user_info['gid'],
			'work_location' => $user_info['work_location'],
			'work_sublocation' => $user_info['work_sublocation'],
			'sex' => $user_info['sex'],
			'name' => $user_info['name'],
			'register_time' => $user_info['register_time'],
			'service_str' => $service_str,
			'login_count' => $user_info['login_count'],
			'have_photo' => $have_photo,
			'new_reg' => $new_reg_no_avatar,
            'avatar_status' => $user_info['avatar'],
            'is_new_user' => $user_info['is_new_user'],
            'is_vip' => $user_info['is_vip'],
            'is_chat' => $user_info['is_chat'],
            'is_readmsg' => $user_info['is_readmsg'],
            'is_diamond' => $user_info['is_diamond'],
            'is_sendmsg' => $user_info['is_sendmsg'],
		);
		$level = $user_info['level'] > 5 ? 5 : $user_info['level'];
		$levelimg = '';
		for($i = 0; $i < $level; $i++){
			$levelimg .= '★';
		}
		$cache['level'] = $level;
		$cache['levelimg'] = $levelimg;

		//感兴趣的服务，看信、钻石过期提醒，新服务升级、续费提醒
		$use_service = $cmi->account->get_subscribe();
		$tip_arr = tip_service($use_service);
		if(!empty($tip_arr) && is_array($tip_arr)){
			$cache['intrest_service'] = ($now+7*24*3600 >= $tip_arr['expire_time'] && $tip_arr['expire_time'] >= $now-20*24*3600)?1:0;
		}

		if($cache['intrest_service']){
			$cache['is_expired'] = ($now-$tip_arr['expire_time'] >= 0)?'Y':'N';
			$cache['expire_time'] = $tip_arr['expire_time'];
			$cache['service_key'] = $tip_arr['id'];
		}else{
			require_once(WWW_ROOT_PATH . 'usercp/usercp.inc.php');
			$intrest_service = get_one_service();
			$cache['intrest_service'] = empty($intrest_service)?0:1;
		}

        $cache['menu_config'] = get_usercp_menu($menu_conf_arr, $user_info);	//我的佳缘左菜单处理

        //靠普度查询
        //$cmi->account->setHTTP('get_reliable_score', 1);
        $reliable_score = $cmi->account->get_reliable_score($uid);
        if($reliable_score > 5 || $reliable_score < 0) $reliable_score = 0;
        $cache['reliable'] = $reliable_score;
		$memc->set("{$uid}_usercpstats_new", $cache, 0, 60);
	}

	//实时更新部分
	//新信件数
	$msgcount_info = &$cmi->msg->jy_countmsg($uid);
	$cache['new_msg'] = intval($msgcount_info) > 999 ? '999+' : intval($msgcount_info);

	//头像审核倒计时、加催
    $avatar_up_time = -1;
	if(($now-$USER->register_time) < 3600*24*7){
		$avatar_up_time = $cmi->photo->get_reg_up_avatar($uid);	//是否是注册时上传头像的用户
		if($avatar_up_time){
			if(!$obj_checking) $obj_checking = $cmi->profile->get_checking($uid);
			if($obj_checking['avatar']){	//头像待审
				if(($now - $avatar_up_time) < 1200){
					$diff = $avatar_up_time + 1200 - $now;
					$min = intval($diff / 60);
					$sec = intval($diff % 60);
					$min = $min > 9 ? $min : '0'.$min;
					$sec = $sec > 9 ? $sec : '0'.$sec;
					$cache['check_avatar_min'] = $min;
					$cache['check_avatar_sec'] = $sec;
					$cache['is_show_djs'] = 1;
				}else{
					$mem_3 = $cmi->memc->loadMemc('servers3');
					$avatar_h = $mem_3->get('avatar_hurry_'.$uid);
					if($avatar_h){//用户加催过显示 头像加速审核中,否则显示加催提示层
						$cache['show_check_hurry'] = 1;
					}else{
						$cache['is_show_cui'] = 1;
					}
				}
				//头像待审
				$cache['avatar_is_checking'] = 1;
			}else{
				$avatar_status = $cmi->profile->get_userinfo($uid,array('avatar'));
				if(in_array($avatar_status['avatar'],array(0,2,4))){				//未通过
					$cache['avatar_no_pass'] = 1;
				}
			}
		}
	}
    $cache['avatar_up_time'] = $avatar_up_time;
	
	return $cache;
}

//处理我的佳缘左侧菜单的条件显示 by ljf
function get_usercp_menu($usercp_menu_array, $user_info=false)
{
	if(is_array($usercp_menu_array)){
		foreach($usercp_menu_array as $top_key=>$usercp_menu){
        	$display_array = explode(',', $usercp_menu['display']);						//处理显示标签
            if(in_array(SITE_LABEL, $display_array)){									//如果当前站点显示该项，则处理
            	$show_item	= 0;														//根据自定义条件字段，判断是否显示该菜单项，默认不显示
                $show_opt	= 'or';
                $i			= 0;
                $j			= 0;
                if($usercp_menu['conditions']){											//菜单项设置了自定义条件
                	if(is_array($user_info)){											//用户信息，用户未登录的情况下，所有有自定义条件的菜单项均不显示
                    	if(isset($usercp_menu['conditions']['opt'])){
                        	$show_opt = strtolower($usercp_menu['conditions']['opt']);	//获取设置的关系运算符
                        }
                        //循环便利每一个自定义条件，根据设置的值进行显示的判断，符合条件显示，不符合不显示。
                        //条件判断分两种：一、枚举条件判断；二、范围条件判断
                        foreach($usercp_menu['conditions'] as $cus_key=>$cus_value){
                            if($cus_key == 'opt'){										//越过关系条件
                                continue;
                            }
                            if(strpos($cus_key, 'uid_last_six') !== false){				//针对UID按位条件特殊处理
                            	$key_pos = str_replace('uid_last_six_', '', $cus_key);	//取出要选择UID的位数下标
                                $cus_key = 'uid_last_six';
                                $user_info[$cus_key] = $user_info[$cus_key][$key_pos];	//获取指定位数的值覆盖其父级信息，便于统一做条件判断
                           	}
                            $cust_val_array = explode(',', $cus_value);					//将限定的值拆分成数组
                            if($show_opt == 'or'){
                            	if($cus_key == 'age' || $cus_key == 'register_time'){	//针对年龄和注册时间单独处理
									if($user_info[$cus_key] >= $cust_val_array[0] && $user_info[$cus_key] <= $cust_val_array[1]){
                                        $show_item = 1;									//年龄和时间，取两个值之间的范围做比对
                                        break;
                                    }
                           		}else{
                                    if(in_array($user_info[$cus_key], $cust_val_array)){//当多个条件关系为或（OR）的时候，满足一个条件即可显示
                                        $show_item = 1;
                                        break;
                                    }
                                }
                            }else{
                            	if($cus_key == 'age' || $cus_key == 'register_time'){	//针对年龄和注册时间单独处理
									if($user_info[$cus_key] >= $cust_val_array[0] && $user_info[$cus_key] <= $cust_val_array[1]){
                                        $j++;											//统计自定义条件满足的数量
                                    }
                           		}else{
                                    if(in_array($user_info[$cus_key], $cust_val_array)){//当多个条件关系为和（AND）的时候，要同时满足所有条件才可显示
                                        $j++;											//统计自定义条件满足的数量
                                    }
                                }
                            }
                            $i++;														//统计有多少个自定义条件
                        }
                        if($i == $j){
                            $show_item = 1;												//所有自定义条件全都满足，则显示此菜单项
                        }
                    }
                }else{
                	$show_item = 1;
                }
                $usercp_menu_array[$top_key]['show_item'] = $show_item;					//增加自定义条件处理后的显示属性（1显示，0不显示）
                if($show_item == 1){
                    if(is_array($usercp_menu['name'])){									//如果是数组则做数据处理
                        $arr_menu_name = $usercp_menu['name'];
                        if(array_key_exists('field', $arr_menu_name)){					//有条件字段参考
                        	$usercp_menu_array[$top_key]['name'] = $arr_menu_name[$user_info[$arr_menu_name['field']]];
                        }else{
                            $usercp_menu_array[$top_key]['name'] = current($arr_menu_name);	//无条件参考，默认去第一个元素
                        }
                    }
                    if(is_array($usercp_menu['child'])){
                        $usercp_menu_array[$top_key]['child'] = get_usercp_menu($usercp_menu['child'], $user_info);	//处理下一级菜单
                    }
                }
            }
        }
        return $usercp_menu_array;														//返回处理后的菜单数组
    }else{
    	return false;
    }
}

function tip_service($service_arr){
	isset($service_arr['love']['header']['result']['value']) && $service_arr = $service_arr['love']['body']['subscribe'];
	if(!empty($service_arr) && is_array($service_arr)){
		if(isset($service_arr[0])){
			 foreach ($service_arr as $v){
				if($v['service_id']['value'] == '40') return array('id'=>1,'expire_time'=>strtotime($v['expire_time']['value']));

				if($v['service_id']['value'] == '38') $data = array('id'=>2,'expire_time'=>strtotime($v['expire_time']['value']));
			}
			return $data;
		}
		else {
			if($service_arr['service_id']['value'] == '40') return array('id'=>1,'expire_time'=>strtotime($service_arr['expire_time']['value']));
			else
			return $service_arr['service_id']['value'] == '38'?array('id'=>2,'expire_time'=>strtotime($service_arr['expire_time']['value'])):0;
		}
	}else{
		return 0;
	}
}

function get_complete($uid, $result_search=false, $result_detail=false)
{
	return get_complete_new($uid, $result_search=false, $result_detail=false);
}

function get_complete_new($uid, $result_search=false, $result_detail=false, $result_extended=false)
{
	global $DC, $SDB;
	if (!$uid)
	{
		return 0;
	}
	else
	{
		require_once(WWW_ROOT_PATH . 'includes/CMI/CMI.inc.php');
		$cmi = &$GLOBALS['CMI'];
		$cmi->mod('profile');
		if (empty($result_search))
		{
			$result_search = $DC->get_search($uid);
		}
		if (empty($result_detail))
		{
			$result_detail = $DC->get_detail($uid);
		}
		if(empty($result_extended)){
			$result_extended = $cmi->profile->get_extended($uid);
		}
		$result_user = $DC->get_user($uid);

		$complete_profile = 0.0;
		//择偶条件：15%
		//基本资料：20%
		//内心独白：15%
		//上面三个在注册中必填 直接给分 共50%
		$complete_profile += 50.0;
		//形象照片：20%
		$complete_profile += ($result_search['avatar'] == 1 || $result_search['avatar'] == 3) ? 20.0 : 0;
		//详细资料：30%，共六部分
		//第一部分，经济实力 3
		$complete_profile += $result_extended['investment'] ? 0.5 : 0;
		$complete_profile += $result_extended['loan'] ? 0.5 : 0;
		$complete_profile += $result_extended['consumption_concept'] ? 0.5 : 0;
		//第二部分：生活方式 13
		$complete_profile += $result_search['smoke_type'] ? 0.5 : 0;
		$complete_profile += $result_search['drink_type'] ? 0.5 : 0;
		$complete_profile += $result_extended['most_cost'] ? 0.5 : 0;
		$complete_profile += $result_extended['eating_habits'] ? 0.5 : 0;
		$complete_profile += $result_extended['shopping'] ? 0.5 : 0;
		$complete_profile += $result_extended['communication'] ? 0.5 : 0;
		$complete_profile += $result_search['belief'] ? 0.5 : 0;
		$complete_profile += $result_search['live_cust'] ? 0.5 : 0;
		$complete_profile += $result_search['sport_type'] ? 0.5 : 0;
		$complete_profile += $result_extended['undefined1'] > 1 ? 0.5 : 0;//家务水平等级
		$complete_profile += $result_extended['housework_distribution'] ? 0.5 : 0;
		$complete_profile += $result_extended['undefined2'] > 1 ? 0.5 : 0;//宠物喜欢程度
		$complete_profile += ($result_extended['about_pet'] || ($result_search['pet_like'] && $result_search['pet'])) ? 0.5 : 0;
		//第三部分：工作学习 11
		$complete_profile += $result_search['industry'] ? 0.5 : 0;
		$complete_profile += $result_search['rank_condition'] ? 0.5 : 0;
		$complete_profile += $result_search['company'] ? 0.5 : 0;
		$complete_profile += $result_search['income_desc'] ? 0.5 : 0;
		$complete_profile += $result_search['rank_desire'] ? 0.5 : 0;
		$complete_profile += $result_extended['work_change'] ? 0.5 : 0;
		$complete_profile += $result_extended['overseas_work'] ? 0.5 : 0;
		$complete_profile += $result_extended['workorfamily'] ? 0.5 : 0;
		$complete_profile += $result_search['university'] ? 0.5 : 0;
		$complete_profile += $result_search['speciality'] ? 0.5 : 0;
		$complete_profile += $result_search['language'] ? 0.5 : 0;
		//第四部分：外貌体型 10
		$complete_profile += $result_search['weight'] ? 0.4 : 0;
		$complete_profile += ($result_extended['shape'] || $result_search['shape']) ? 0.4 : 0;
		$complete_profile += $result_search['face_type'] ? 0.4 : 0;
		$complete_profile += ($result_extended['eye_type'] || $result_search['rank_consumption'] ) ? 0.4 : 0;
		$complete_profile += ($result_extended['hair_length'] || $result_search['hair_type'] || $result_search['hair_color'] || $result_search['hair_type']) ? 0.4 : 0;
		$complete_profile += $result_extended['skin'] ? 0.4 : 0;
		$complete_profile += $result_extended['skin_color'] ? 0.4 : 0;
		$complete_profile += $result_extended['cupormuscle'] ? 0.4 : 0;
		$complete_profile += $result_extended['dress_style'] ? 0.4 : 0;
		$complete_profile += $result_extended['health'] ? 0.4 : 0;
		//第五部分：婚姻观念 17
		$complete_profile += ($result_search['love_location'] && $result_search['love_sublocation']) ? 0.5 : 0;
		$complete_profile += $result_search['nationality'] ? 0.5 : 0;
		$complete_profile += $result_extended['personality'] ? 0.5 : 0;
		$complete_profile += $result_extended['humor'] ? 0.5 : 0;
		$complete_profile += $result_extended['temper'] ? 0.5 : 0;
		$complete_profile += $result_extended['emotional_attitude'] ? 0.5 : 0;
		$complete_profile += ($result_extended['child_want'] || $result_search['child_want']) ? 0.5 : 0;
		$complete_profile += $result_extended['marriage_time'] ? 0.5 : 0;
		$complete_profile += $result_extended['ld_relationship'] ? 0.5 : 0;
		$complete_profile += $result_extended['marriage'] ? 0.5 : 0.5;//无论是否传入都给分
		$complete_profile += $result_search['parent_together'] ? 0.5 : 0;
		$complete_profile += $result_search['paihang'] ? 0.5 : 0;
		$complete_profile += ($result_extended['have_o_brother'] || $result_extended['have_y_brother'] || $result_extended['have_o_sister'] || $result_extended['have_y_sister'] || $result_search['paihang'] == 1 )? 0.5 : 0;
		$complete_profile += $result_extended['parents'] ? 0.5 : 0;
		$complete_profile += ($result_extended['father_work'] || $result_extended['mother_work'] ) ? 0.5 : 0;
		$complete_profile += $result_extended['parents_economic'] ? 0.5 : 0;
		$complete_profile += $result_extended['parents_health_care'] ? 0.5 : 0;
		//第六部分：兴趣爱好 8
		$complete_profile += $result_search['sports'] ? 0.5 : 0;
		$complete_profile += $result_search['food'] ? 0.5 : 0;
		$complete_profile += $result_search['book'] ? 0.5 : 0;
		$complete_profile += $result_search['influential_movie'] ? 0.5 : 0;
		$complete_profile += $result_search['attention'] ? 0.5 : 0;
		$complete_profile += $result_search['interest'] ? 0.5 : 0;
		$complete_profile += $result_search['travel'] ? 0.5 : 0;
		$complete_profile += $result_search['space'] ? 0.5 : 0;

		return $complete_profile;
	}
}

function del_usercp_stats($uid)
{
	global $DC;
	if ($uid)
	{
		$DC->delete_cache("{$uid}_usercpstats");
	}
}

//取得用户所在的聊天服务器IP
function get_chat_server($uid)
{
	//20140331改为使用虚ip访问 10.0.5.10 10.0.5.11
	return 'http://10.0.0.54/';
}

//取得用户所在的聊天服务器域名
function get_chat_server_url($uid)
{
	$server_id	=	$uid%2;
	$server_id++;

	if(REG_HOST	==	'1')
	{
		switch($server_id)
		{
			case 1:
				$server	=	'http://chat1.jiayuan.msn.com.cn/';
				break;
			case 2:
				$server	=	'http://chat2.jiayuan.msn.com.cn/';
				break;
		}
	}
	else
	{
		switch($server_id)
		{
			case 1:
				$server	=	'http://chat1.jiayuan.com/';
				break;
			case 2:
				$server	=	'http://chat2.jiayuan.com/';
				break;
		}
	}

	return $server;
}

//邀请会员进入聊天
function invite_user($inv_uid,$code,$type='')
{
	global $USER,$SDB, $MDB,$DC;

	if(!($USER->uid	>	0))
	{
		return false;
	}
	if($_SESSION['self_photo']	==	'')
	{
		$ser_arr	=	$DC->get_search($USER->uid);
		$avatar	=	get_user_avatar($ser_arr);
		$_SESSION['self_photo']	=	$avatar;
	}

	$invite_arr	=	get_relation_array($inv_uid, 'inviteblackd');
	if(array_search($USER->uid,$invite_arr)	>	0)
	{
		return false;
	}

	$inv_user_obj	=	$DC->get_user($inv_uid);
	$pop_tag =	$inv_user_obj->pop_tag;
	if($pop_tag	!=	'1')
	{
		$pop_tag	=	'1';
		$inv_user_obj->pop_tag	=	$pop_tag;
		$arr_user['pop_tag']	=	$pop_tag;
		$DC->update_user($inv_user_obj->uid,$arr_user,$inv_user_obj);
	}


	//请求lava服务器，发出提醒

	require_once(WWW_ROOT_PATH . '/lava/lava_config.inc.php');

	$uid_lava	=	uid_to_lava_GID($inv_user_obj->uid);
	$chat_server_ip	=	$chat_server_ip_arr[array_rand($chat_server_ip_arr)];

	$url_str	=	'http://'.$chat_server_ip.'/lava_req.php?to_gid='.$uid_lava.'&to_uid='.get_disp_uid($inv_user_obj->uid).'&from_uid='.get_disp_uid($USER->uid).'&nick='.$USER->nickname;
	//echo $url_str."\n";
	$xml	=	file_get_contents($url_str);

	//echo $xml;
	//exit();
	//请求lava服务器，发出提醒

	$invite_arr	=	get_relation_array($inv_uid, 'invite');

	if($type	==	'done')
	{
		$uid_tmp	=	0-$USER->uid;
	}
	else
	{
		$uid_tmp	=	$USER->uid;
	}


	$invite_arr[]	=	$uid_tmp.':'.$code;
	$invite_arr	=	array_unique($invite_arr);
	update_relation_array($inv_uid, $invite_arr, 'invite');

	$server	=	get_chat_server($inv_uid);
	if($type	!=	'done')
	{
		$send_url	= $server.'/write_message.php?ava='.$_SESSION['self_photo'].'&code='.$code.'&selfuid='.$USER->uid.'&uid='.$inv_uid.'&nickname='.urlencode($USER->nickname).'&message='.urlencode(md5('love21cn'));
		//2010-1-19 sunliang 非聊友聊天
		sendKeyLog("chatlog","inv|".$USER->uid."|".$inv_uid."|".$USER->sex."|".$inv_user_obj->sex."|");
	}
	else
	{
		$send_url	= $server.'/write_message.php?ava='.$_SESSION['self_photo'].'&code='.$code.'&selfuid='.$USER->uid.'&uid='.$inv_uid.'&nickname='.urlencode($USER->nickname).'&message='.urlencode(md5('love21cn_done'));
		//2010-1-19 sunliang 聊友聊天
		sendKeyLog("chatlog","fri|".$USER->uid."|".$inv_uid."|".$USER->sex."|".$inv_user_obj->sex."|");
	}

	$str	=	file_get_contents($send_url);


	return true;
}


//接受邀请
function recv_invite($from_uid,$firend_type	=	'chat')
{
	global $USER,$SDB, $MDB,$DC;

	if($_SESSION['self_photo']	==	'')
	{
		$ser_arr	=	$DC->get_search($USER->uid);
		$avatar	=	get_user_avatar($ser_arr);
		$_SESSION['self_photo']	=	$avatar;
	}

	$chat_arr	=	get_relation_array($from_uid, $firend_type);
	if(array_search($USER->uid,$chat_arr)	=== false)
	{
		$chat_arr[]	=	$USER->uid;
		update_relation_array($from_uid, $chat_arr, $firend_type);
		$DC->add_contact($from_uid,$USER->uid,'2');
		$DC->add_contact($USER->uid,$from_uid,'2');
	}

	$beinv_arr	=	get_relation_array($USER->uid, 'adnvite');
	$beinv_arr[]	=	$from_uid;

	$beinv_arr	=	array_reverse($beinv_arr);
	$beinv_arr	=	array_unique($beinv_arr);
	$beinv_arr	=	array_reverse($beinv_arr);


	update_relation_array($USER->uid, $beinv_arr, 'adnvite');

	$invite_arr	=	get_relation_array($USER->uid, 'invite');


	while(list($key,$value)	=	each($invite_arr))
	{
		$arr_tmp	=	explode(':',$value);
		if(abs($arr_tmp[0])	==	$from_uid)
		{
			$unset_arr[]	=	$key;
		}
	}

	for($i=0;$i<count($unset_arr);$i++)
	{
		unset($invite_arr[$unset_arr[$i]]);
	}

	update_relation_array($USER->uid, $invite_arr, 'invite');

	if(!(count($invite_arr)	>	0))
	{
		$USER->pop_tag	=	' ';
		$arr_user['pop_tag']	=	$USER->pop_tag;
		$DC->update_user($USER->uid,$arr_user,$USER);
	}



	$myuid			= get_disp_uid($USER->uid);
	$fruid			= get_disp_uid($from_uid);

	require_once(WWW_ROOT_PATH . '/lava/lava_config.inc.php');
	$chat_server_ip	=	$chat_server_ip_arr[array_rand($chat_server_ip_arr)];

	$url_str	=	'http://'.$chat_server_ip.'/lava_add.php?myuid='.$myuid.'&fruid='.$fruid;
	//echo $url_str;
	$xml	=	file_get_contents($url_str);

	//echo $xml;


	$server	=	get_chat_server($from_uid);
	$send_url	= $server.'/write_message.php?ava='.$_SESSION['self_photo'].'&selfuid='.$USER->uid.'&uid='.$from_uid.'&nickname='.urlencode($USER->nickname).'&message='.urlencode(md5('love21cn_re'));

	$str	=	file_get_contents($send_url);

	//2010-1-19 sunliang
	sendKeyLog("chatlog","rec|".$from_uid."|".$USER->uid."||".$USER->sex."|");

	return true;

}

//不接受邀请
function unrecv_invite($from_uid)
{
	global $USER,$SDB, $MDB,$DC;

	$invite_arr	=	get_relation_array($USER->uid, 'invite');

	while(list($key,$value)	=	each($invite_arr))
	{
		$arr_tmp	=	explode(':',$value);
		if(abs($arr_tmp[0])	==	$from_uid)
		{
			$unset_arr[]	=	$key;
		}
	}

	for($i=0;$i<count($unset_arr);$i++)
	{
		unset($invite_arr[$unset_arr[$i]]);
	}

	update_relation_array($USER->uid, $invite_arr, 'invite');

	$server	=	get_chat_server($from_uid);
	$send_url	= $server.'/write_message.php?selfuid='.$USER->uid.'&uid='.$from_uid.'&nickname='.urlencode($USER->nickname).'&message='.urlencode(md5('love21cn_un'));

	$str	=	file_get_contents($send_url);

	if(!(count($invite_arr)	>	0))
	{
		$USER->pop_tag	=	' ';

		$arr_user['pop_tag']	=	$USER->pop_tag;
		$DC->update_user($USER->uid,$arr_user,$USER);
	}

	//2010-1-19 sunliang
	sendKeyLog("chatlog","ref|".$from_uid."|".$USER->uid."||".$USER->sex."|");

	return true;
}

/**
 * 内容发送到标示，记录关键日志
 * @param $key
 * @param $logstr
 * @return unknown_type
 */
function sendKeyLog($key,$logstr){
	/* 日志挪到往外，停记此日志
	$url = "http://10.0.0.222/any/k.gif?k=$key&log=|$logstr|";
	$r = new HTTP_Request($url);
	$response = $r->sendRequest();
	*/
}


function refresh_photo($url)
{
	$url = 'http://ccms.chinacache.com/index.jsp?user=love21cn&pswd=love21cn123&ok=ok&urls=' . $url;
	$head = "";
	$url_p = parse_url($url);
	$host = $url_p["host"];
	$port = intval($url_p["port"]);
	if(!$port) $port=80;
	$path = $url_p["path"];
	$fp = fsockopen($host, $port, $errno, $errstr, 20);
	if(!$fp)
	{
		return false;
	}
	else
	{
		fputs($fp, "HEAD "  . $url  . " HTTP/1.1\r\n");
		fputs($fp, "HOST: " . $host . "\r\n");
		fputs($fp, "User-Agent: www.love21cncn.com\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		$headers = "";
		$succeed = 0;
		while (!feof($fp))
		{
			$line = fgets($fp);
			if(strpos('aaa' . $line, 'whatsup') > 0)
			{
				if(strpos($line, 'succeed') > 0)
				{
					$succeed = 1;
				}
			}
		}
	}
	fclose ($fp);
	return $succeed;
}

function get_sina_ad($id)
{
	global $SDB,$DC;
	if($id ==0)
	{
		return '';
	}
	$cache_key = "sina_ad";
	$ad_array = $DC->get_cache($cache_key);
	if(empty($ad_array))
	{
		$ad_array = array();
		$sql = "select id,content from sina_ad";
		$result = $SDB->query($sql);
		while($dr = $SDB->fetch_object($result))
		{
			$ad_array[$dr->id] = $dr->content;
		}
		$DC->set_cache($cache_key, $ad_array, 300);
	}
	$content = $ad_array[$id];
	if(strlen($content) < 10)
	{
		$content = "";
	}
	$url = $_SERVER['HTTP_HOST'];
	if(strpos('aaa' . $url, 'msn') > 0)
	{
		$content = '';
	}
	return $content;
}
function get_sina_index_ad($id, $default='')
{
	$ad = get_sina_ad($id);
	$time = date("YmdH");
	$sina_ad = '<script type="text/javascript" src="http://images.jiayuan.com/w/index/j/sinaflash.js"></script>';
	if($ad == '')
	{
		return $default;
	}
	else
	{
		return $sina_ad . $ad;
	}
}


function get_sina_ad_v2($id)
{
	$sina_ad = get_sina_ad($id);
	$arr = array(2,3,5,8,10,11,13,16);
	if(in_array($id, $arr))
	{
		if($sina_ad != '' )
		{
			$output = <<<EOF
			<iframe id="s_ad_{$id}"
			frameborder="0"
			name="s_ad_{$id}"
			marginwidth="0"
			marginheight="0"
			src="http://images.jiayuan.com/w/sina/iframe/{$id}.html"
			width="100%"
			height="90px"
			scrolling="no"
			onload="javascript:SetWinHeight(this)" >
			</iframe>
			<script type="text/javascript">
			function SetWinHeight(obj)
			{
				var win=obj;
				if (win.contentDocument && win.contentDocument.body.offsetHeight)
				{
					win.height = win.contentDocument.body.offsetHeight;
				}
				else
				{
					if(win.Document && win.Document.body.scrollHeight)
					{
						win.height = win.Document.body.scrollHeight;
					}
				}
			}
			</script>
EOF;
		}
		else
		{
			$output = '<div id="ad2" name="ad2" style="float:left;"></div>';
		}
	}
	else
	{
		if($sina_ad != '')
		{
			$output = '<script type="text/javascript" src="http://images.jiayuan.com/w/index/j/sinaflash.js"></script>';
			$output .= $sina_ad;
		}

	}
	return $output;

}

function get_sina_ad_js($mod='index')
{
	$couplet_id = 1;
	$banner_id = 2;
	if($mod == 'search')
	{
		$couplet_id = 12;
		$banner_id = 10;
	}
	if($mod == 'profile')
	{
		$couplet_id = 9;
		$banner_id = 8;
	}
	if($mod == 'usercp')
	{
		$couplet_id = 14;
		$banner_id = 13;
		$mod = 'jiayuan';
	}
	$sina_ad2 = get_sina_ad($couplet_id); // 通栏
	$sina_ad1 = get_sina_ad($banner_id); // 对联
	if($sina_ad2 != "" && $sina_ad1 != "")
	{
		return '';
	}
	if($sina_ad1 == '' && $sina_ad2 == '')
	{
		return '<script type="text/javascript" src="http://images.jiayuan.com/w/global/j/www_'.$mod.'_.js?'. $time .'"></script>';
	}
	if($sina_ad2 != "")
	{
		$sina_ad = '<div id="ad2" name="ad2" style="float:left;" style="display:none"></div><script type="text/javascript" src="http://images.jiayuan.com/w/global/j/www_'.$mod.'_.js?'. $time .'"></script>';
		return $sina_ad;
	}
	if($sina_ad1 != "" )
	{
		$sina_ad .= $sina_ad2 . '<div id="ad2" name="ad2" style="float:left;"></div><script type="text/javascript" src="http://images.jiayuan.com/w/global/j/www_'.$mod.'_.js?'. $time .'"></script>';
		$sina_ad .= <<<EOF
			<script type="text/javascript">
			var sina_ad_c  =  document.getElementById('love21cnadl');
			if(sina_ad_c != undefined)
			{
				sina_ad_c.style.display = 'none';
			}
			sina_ad_c  =  document.getElementById('love21cnadr');
			if(sina_ad_c != undefined)
			{
				sina_ad_c.style.display = 'none';
			}

		</script>
EOF;
		return $sina_ad;
	}
	return '';
}


function alert_reloc($notice="", $url="")
{
	$notice = addslashes($notice);
	$output = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<script type="text/javascript" charset="utf-8">
alert("$notice");
window.location.href="$url";
</script>
</body>
</html>
EOF;
	exit($output);
}

function get_uid_from_email($email)
{
	require_once(WWW_ROOT_PATH . '/includes/mem_cache.class.php');
	global $DC,$memcache_servers42,$SDB;
	$mem42 = new Mem_Cache($memcache_servers42);
	$uid	=	$mem42->get($email);
	if($uid	>	0)
	{
		return $uid;
	}
	else
	{
		$uid	=	$SDB->result("select `uid` from `user` where `name`='".$email."'");
		if(!$mem42->set($email,$uid))
		{
			$mem42->update($email,$uid);
		}
		return $uid;
	}
}

function pop_set($type,$uid,$to_uid,$sex,$nickname	=	false,$to_user_obj	=	false)	//弹出设置
{
	require_once(WWW_ROOT_PATH . '/includes/mem_cache.class.php');
	global $DC,$memcache_servers42;
	$mem42 = new Mem_Cache($memcache_servers42);
	if(!$to_user_obj)
	{
		$to_user_obj	=	$DC->get_user($to_uid);
	}
	else
	{
		if($to_user_obj->uid	!=	$to_uid)
		{
			return false;
		}
	}
	if((time()	-	$to_user_obj->last_activity)	<	15*60)
	{
		if($sex	!=	$to_user_obj->sex)
		{
			$pop_mem['tag']		=	$type;
			$pop_mem['uid']		=	$uid;
			$pop_mem['nick']	=	$nickname;
			$pop_mem['time']	=	time();
			if(!$mem42->set('pop_'.$to_uid,$pop_mem))
			{
				$mem42->replace('pop_'.$to_uid,$pop_mem);
			}
		}
	}
	else
	{
		if($type != 11 && $type != 12 && $type != 13)
		{
			return false;
		}
		else
		{
			$pop_mem	=	$mem42->get('pop_'.$to_uid);
			$pop_mem['inv_'.$type]++;
			if(!$mem42->set('pop_'.$to_uid,$pop_mem))
			{
				$mem42->replace('pop_'.$to_uid,$pop_mem);
			}
		}
	}
	return true;
}

function pop_get($uid)	//获取弹出信息
{
	require_once(WWW_ROOT_PATH . '/includes/mem_cache.class.php');
	global $DC,$memcache_servers42;
	$mem42 = new Mem_Cache($memcache_servers42);
	$pop_mem	=	$mem42->get('pop_'.$uid);
	$mem42->delete('pop_'.$uid);
	return $pop_mem;
}

/**
 * 获取用户资料显示限制
 *
 * @param integer $uid 会员uid
 */
function get_contact_limit($contact_limit)
{
	$bin = decbin($contact_limit);
	$len = strlen($bin);
	$result = array();
	for ($i=1;$i<$len;$i++)
	{
		$result[$i] = $bin{$len-1-$i};
	}
	return $result;
}

/**
 * 设置浏览资料限制
 *
 * @param integer $uid
 * @param integer $limit_id
 * @param array $search_cache
 * @return boolean
 */
function set_contact_limit($uid, $limit_id, $value=1, $search_cache=false)
{
	if ($uid)
	{
		global $DC;
		if (empty($search_cache))
		{
			$search_cache = $DC->get_search($uid);
		}
		$dec = $search_cache['contact_limit'];
		if ($value)
		{
			$dec = $dec | pow(2, $limit_id);
		}
		else
		{
			$dec = $dec & ~pow(2, $limit_id);
		}
		return $DC->update_search($uid, array('contact_limit' => $dec), $search_cache);
	}
	else
	{
		return false;
	}
}




/**
 *  获取用户的头像
 *
 * @param array $arr_search  用户search表的memcache数组
 * @param int $photo_pwd_allow  是否验证了用户输入的爱情密码
 * @param int $thumb  是否显示小图，在线列表处用
 * @return string  头像url
 */
function get_user_avatar($arr_search, $photo_pwd_allow = 0 , $thumb = 0){
	global $USER;
	require_once(WWW_ROOT_PATH . 'includes/CMI/CMI.inc.php');
	$cmi = &$GLOBALS['CMI'];
	$cmi->mod('photo');
	$look_uid = false;
	if($USER) $look_uid = $USER->uid;
	$avatar = $cmi->photo->get_useravatar($arr_search['uid'], $look_uid, 'b', 1,'',$photo_pwd_allow);
	return $avatar;
}

/**
 *  获取用户的头像，利用第一个参数$user验证而不是用$SESSION验证查看者的身份
 *
 * @param array $user 用户的信息，可以用$DC->get_user获取，也可以自己制定，必须包含uid,gid
 * @param array $arr_search  用户search表的memcache数组
 * @param int $photo_pwd_allow  是否验证了用户输入的爱情密码
 * @param int $thumb  是否显示小图，在线列表处用
 * @return string  头像url
 */
function get_user_avatar_user($user, $arr_search, $photo_pwd_allow = 0 , $thumb = 0)
{
	global $img_base_url, $DC;
	$img_base_url = defined('IMG_BASE_URL') ? IMG_BASE_URL : 'http://images.jiayuan.com/w4';
	$thumb_string = '';
	if($thumb)
	{
		$thumb_string = '_t';
	}
	$pic_url = $img_base_url . '/global/i/';

	$avatar_url = "{$pic_url}nopic_{$arr_search['sex']}{$thumb_string}.jpg";
	if($arr_search['avatar'] == 0 || $arr_search['avatar'] == 2 || $arr_search['avatar'] == 4)
	{
		return $avatar_url;
	}
	if($arr_search['uid_hash'] == '')
	{
		$arr_search['uid_hash'] = md5($arr_search['uid']);
	}
	$avatar_url = get_disp_url($arr_search['uid_hash']) . '/avatar.jpg';

	//输入后爱情密码后
	if($photo_pwd_allow)
	{
		return $avatar_url;
	}

	$starless = 0;
	$photo_path = $avatar_url;
	switch ($arr_search['privacy'])
	{
		case 2:	//会员可见
			if (!is_register_seephoto($user->uid,$str_info))
			{
				$photo_path = $pic_url . 'hykj_' . $arr_search['sex'] . $thumb_string . '.jpg';
			}
			break;
		case 3:  //星级会员可见
			if ($user->gid != AUTHMEMBER_GID && $user->gid != AUTHVIP_GID)
			{
				$photo_path = $pic_url . 'xjhy_' . $arr_search['sex'] . $thumb_string . '.jpg';
				$starless = 1;
			}
			break;
		case 4:	//需要爱情密码
			$photo_path = $pic_url . 'aqmm_' . $arr_search['sex'] . $thumb_string . '.jpg';
			break;
		case 5: // 有照片会员可见
			$photo_path = $pic_url . 'yzpkj_' . $arr_search['sex'] . $thumb_string . '.jpg';
			if($user->uid > 0)
			{
				$my_search = $DC->get_search($user->uid);
				if($my_search['avatar'] == 1 || $my_search['avatar'] == 3)
				{
					$photo_path = $avatar_url;
				}
			}
			break;
		case 1: //所有人可见
			break;
		default:
			break;
	}

	//是否设置了照片给Ta看
	if($user->uid > 0 && ($starless == 1 || $arr_search['privacy'] == 4))
	{
		$photo_allowed = get_user_photo_allowed($arr_search['uid']);
		if($photo_allowed)
		{
			return $avatar_url;
		}
	}
	return $photo_path;
}

/**
 * 获取用户的头像
 *
 * @param int $uid    用户id
 * @param string $sex  性别
 * @param int $avatar  search表的avatar字段值
 * @param int $privacy  search表的privacy值
 * @param int $photo_pwd_allow 是否验证了用户输入的爱情密码
 * @param int $thumb 是否显示小图，在线列表处用
 * @return unknown
 */
function get_user_avatar_1($uid, $sex, $avatar, $privacy, $photo_pwd_allow =0 , $thumb = 0)
{
	$arr_search = array(
	'uid' => $uid,
	'uid_hash' => md5($uid),
	'avatar' => $avatar,
	'privacy' => $privacy
	);
	return get_user_avatar($arr_search, $photo_pwd_allow, $thumb);
}
/**
 * 获取用户设置的照片允许列表
 * @param int $uid
 * @return true or false
 */
function get_user_photo_allowed($uid)
{
	global $SESSION, $DC, $SDB;

	$allow_userlist = $DC->get_relation($uid, 'photoallow');
	if(empty($allow_userlist) || $allow_userlist == '')
	{
		return false;
	}
	if(in_array($SESSION->info->uid, $allow_userlist))
	{
		return true;
	}
	return false;
}


function get_my_certify()
{
	global $USER, $SDB, $DC, $certificate_array;
	$cert_checked = $cert_uncheck = array();
	$sql = "SELECT sql_no_cache cid,type,status FROM certificate WHERE status < 2 and  uid=" . $USER->uid;
	$result = $SDB->query($sql);
	while ($dr = $SDB->fetch_assoc($result))
	{
		$dr['type_string'] = $certificate_array[$dr['type']];
		if($dr['status'] == 1)
		{
			$cert_checked[] = $dr;
		}
		else
		{
			$cert_uncheck[] = $dr;
		}
	}
    require_once(WWW_ROOT_PATH . 'includes/CMI/CMI.inc.php');
	$cmi = &$GLOBALS['CMI'];//改为引用全局CMI
	$cmi->mod('zmxy');
	$zmxy_isauth=$cmi->zmxy->isauth_zmxy($USER->uid);
    $zmxy_isauth == false ? $obj->zmxy = 1:$obj->zmxy = 2;
    if($zmxy_isauth == true) $obj->select_zhima = 18;
	$obj->certify = $USER->certify;
	$obj->ms_mobile = $USER->ms_mobile; //add by ly for checked mobile  1:验证过；0：未验证
	$obj->validate_email = $USER->validate_email;
	$arr_checking = $DC->get_checking($USER->uid);
	$obj->video = $arr_checking['camera'];
	$obj->cert_checked = $cert_checked;
	$obj->cert_uncheck = $cert_uncheck;
	$obj->checked_count = count($cert_checked);
	$obj->uncheck_count = count($cert_uncheck);
	return $obj;
}

function cr_get($channel,$level=0,$zone_code=0)
{
	global $SDB;
	return array();
	$result_array=cr_get_ad($channel,$level,$zone_code);
	if(count($result_array)>0) return $result_array;

	$where=" where channel_code=$channel ";
	if($level>0)
	$where .= "and attrib=$level ";
	if($zone_code>0)
	$where .= "and zone_code=$zone_code ";

	$query_string = "select * from channel_recommend " . $where . " order by zone_code";

	$query = $SDB->query($query_string);
	while ($result = $SDB->fetch_assoc($query))
	{
		$one_result=array();
		$one_result = $result;
		$result_array[$result['id']]=$one_result;
	}

	return $result_array;
}

function cr_get_ad($channel,$level=0,$zone_code=0)
{
	global $SDB;
	$result_array=array();
	$where=" where channel_code=$channel ";
	if($level>0)
	$where .= "and attrib=$level ";
	if($zone_code>0)
	$where .= "and zone_code=$zone_code ";

	$query_string = "select * from ad_link " . $where . " order by zone_code";

	$query = $SDB->query($query_string);
	while ($result = $SDB->fetch_assoc($query))
	{
		$one_result=array();
		$one_result = $result;
		$result_array[$result['id']]=$one_result;
	}

	return $result_array;
}

function get_href_by_site($href,$site)
{
	$href_replace_array = array(
	"love21cn.msn.com.cn/profile" => "my.jiayuan.com",
	"love21cn.msn.com.cn/story" => "love.jiayuan.com",
	"love21cn.msn.com.cn/article" => "diary.jiayuan.com",
	"love21cn.msn.com.cn/student" => "student.jiayuan.com",
	"love21cn.msn.com.cn/photo" => "photo.jiayuan.com",
	"love21cn.msn.com.cn/search" => "search.jiayuan.com",
	"love21cn.msn.com.cn/online" => "online.jiayuan.com",
	"love21cn.msn.com.cn/msg" => "msg.jiayuan.com",
	"love21cn.msn.com.cn/party" => "party.jiayuan.com",

	"jiayuan.msn.com.cn/profile" => "my.jiayuan.com",
	"jiayuan.msn.com.cn/story" => "love.jiayuan.com",
	"jiayuan.msn.com.cn/article" => "diary.jiayuan.com",
	"jiayuan.msn.com.cn/student" => "student.jiayuan.com",
	"jiayuan.msn.com.cn/photo" => "photo.jiayuan.com",
	"jiayuan.msn.com.cn/search" => "search.jiayuan.com",
	"jiayuan.msn.com.cn/online" => "online.jiayuan.com",
	"jiayuan.msn.com.cn/msg" => "msg.jiayuan.com",
	"jiayuan.msn.com.cn/party" => "party.jiayuan.com",
	);

	$prefix="http://jiayuan.msn.com.cn";

	$r_prefix="http://www.jiayuan.com/search/link.php?type=1&id=$id" . "&url=";

	if(strncasecmp($href,$prefix,strlen($prefix)) != 0) return $r_prefix . urlencode($href);

	$result = strtolower($href);
	if($site == 1)
	{
		foreach($href_replace_array as $key=>$value)
		{
			$result = str_replace($key,$value,$href);
			if(strncasecmp($result,$href,strlen($result)) != 0) return $r_prefix . urlencode($result);
		}

		$result = str_replace("jiayuan.msn.com.cn","www.jiayuan.com",$result);

		return $r_prefix . urlencode($result);
	}
	elseif($site == 3)
	{
		$result = str_replace("jiayuan.msn.com.cn","sina.jiayuan.com",$result);
		return $r_prefix . urlencode($result);
	}
	return $r_prefix . urlencode($href);
}

function get_href_by_site_4msg($href,$site)
{
	$href_replace_array = array(
	"love21cn.msn.com.cn/profile" => "my.jiayuan.com",
	"love21cn.msn.com.cn/story" => "love.jiayuan.com",
	"love21cn.msn.com.cn/article" => "diary.jiayuan.com",
	"love21cn.msn.com.cn/student" => "student.jiayuan.com",
	"love21cn.msn.com.cn/photo" => "photo.jiayuan.com",
	"love21cn.msn.com.cn/search" => "search.jiayuan.com",
	"love21cn.msn.com.cn/online" => "online.jiayuan.com",
	"love21cn.msn.com.cn/msg" => "msg.jiayuan.com",
	"love21cn.msn.com.cn/party" => "party.jiayuan.com",

	"jiayuan.msn.com.cn/profile" => "my.jiayuan.com",
	"jiayuan.msn.com.cn/story" => "love.jiayuan.com",
	"jiayuan.msn.com.cn/article" => "diary.jiayuan.com",
	"jiayuan.msn.com.cn/student" => "student.jiayuan.com",
	"jiayuan.msn.com.cn/photo" => "photo.jiayuan.com",
	"jiayuan.msn.com.cn/search" => "search.jiayuan.com",
	"jiayuan.msn.com.cn/online" => "online.jiayuan.com",
	"jiayuan.msn.com.cn/msg" => "msg.jiayuan.com",
	"jiayuan.msn.com.cn/party" => "party.jiayuan.com",
	);

	$prefix="http://jiayuan.msn.com.cn";

	$r_prefix="http://www.jiayuan.com/search/link.php?type=1&id=$id" . "&url=";
	$r_prefix = '';

	//if(strncasecmp($href,$prefix,strlen($prefix)) != 0) return $r_prefix . urlencode($href);
	if(strncasecmp($href,$prefix,strlen($prefix)) != 0) return $r_prefix . $href;

	$result = strtolower($href);
	if($site == 1)
	{
		foreach($href_replace_array as $key=>$value)
		{
			$result = str_replace($key,$value,$href);
			if(strncasecmp($result,$href,strlen($result)) != 0) return $r_prefix . $result;
		}

		$result = str_replace("jiayuan.msn.com.cn","www.jiayuan.com",$result);

		//return $r_prefix . urlencode($result);
		return $r_prefix . $result;
	}
	elseif($site == 3)
	{
		$result = str_replace("jiayuan.msn.com.cn","sina.jiayuan.com",$result);
		return $r_prefix . $result;
	}
	return $r_prefix . $href;
}
/**
 * 会员修改邮箱地址
 *
 * 编写人：陈雷
 * 编写日期：2009-6-16
 * 修改日期：
 * @param int $uid 用户uid
 * @param string $oldname 老邮箱地址
 * @param string $newname 新邮箱地址
 * @param string $password 原密码
 * @param string $validate_code 验证码
 * @param string $errorinfo 错误信息，引用
 * @return 1表示失败，0表示成功
 */

function change_email($uid,$oldname,$newname,$password,$validate_code,&$errorinfo)
{
	global $SDB,$DC,$SESSION,$MDB,$MDB_LY;

	$obj_user = $DC->get_user($uid);

	if($obj_user -> validate_email == 1)
	{
		$errorinfo = '您的邮箱已经通过验证，不能再次修改';
		$result = 0;
		return $result;
	}

	if(!strstr($newname, '@'))
	{
		$errorinfo = '新邮箱格式错误';
		$result = 0;
		return $result;
	}

	$validate_code	 = addslashes($validate_code);
	$code_hash = $_COOKIE['GFCH'];
	if(!verify_antispam_v2($code_hash, $validate_code))
	{
		$errorinfo = '验证码错误';
		$result = 0;
		return $result;
	}

	$sql=  "select uid from user where name = '{$newname}'";
	$temp_uid = $SDB->result($sql);
	if($temp_uid > 0)
	{
		$errorinfo = '您要修改的新邮箱已经存在';
		$result = 0;
		return $result;
	}

	$raw_hash = 'Love21cn.com' . sha1(_strtolower($oldname)) . sha1($password);
	$login_hash = sha1($raw_hash);

	$user_obj	=	$DC->get_userlog_from_uidhash(md5($uid));

	if($user_obj->login_hash	==	$login_hash)
	{
		$new_raw_hash = 'Love21cn.com' . sha1(_strtolower($newname)) . sha1($password);
		$new_login_hash = sha1($new_raw_hash);

		$arr = array('name' => $newname, 'login_hash' => $new_login_hash);
		$blogin = $DC->update_userlogin($uid, $arr);

		$buser = $DC->update_user($uid, $arr);

		v_send_email($newname,1,$uid);

		$DC->set_cache('post_email_num_chenlei_'.$uid, 1, 3600*24);

		$errorinfo = '邮箱修改成功，验证邮件已发至新邮箱('.$newname.')请使用新邮箱地址重新登录世纪佳缘。';
		$result = 1;

		/**
		 * 更新直邮推荐表
		 */
		$sql="update subscribe set email='".$newname."' where uid=".$uid;
		$MDB_LY->query($sql);

		$USER = $SDB->result("SELECT * FROM user WHERE `uid`='".$uid."'");
		if (is_object($USER) && $USER->uid > 0)
		{
			write_session($USER,0,0,$new_login_hash);
		}
		$DC->set_cache('post_email_num_chenlei_'.$USER->uid, 0, 3600*24);
	}
	else
	{
		$errorinfo = '密码错误';
		$result = 0;
	}
	return $result;
}
/**
 * 限制登录频率
 *
 * @param string $login_email 登录的email
 * @param string $error 输出错误信息
 * @return true or false
 */
function restrict_login_times($login_email,&$error)
{
	global $DC;
	define('IP_RESTRICT_TIME',20);
	define('IP_RESTRICT_FREQUENCY',0.01);
	define('IP_EMAIL_RESTRICT_FREQUENCY',1);
	define('MEMCACHE_EXPIRE_TIME',1200);

	$ip = get_client_ip();
	$ip_key = 'restrict_login_times_'.$ip;//key以restrict_login_times_+客户端ip命名

	$array_login_total = $DC -> get_from_memcache10($ip_key);//首先去memcache里面找

	$time_float = microtime_float_now();//获得当前的时间，精确到0.01秒

	if($array_login_total == '')//取得的key为空，初始化
	{
		$array_login_email = array($login_email=>array(0=>$time_float,1=>0),'restrict_flag'=>true,'restrict_time'=>0);
		$DC -> set_to_memcache10($ip_key,$array_login_email,MEMCACHE_EXPIRE_TIME);
		return true;
	}
	else
	{
		if($array_login_total['restrict_flag'] == false)//ip访问标记位为false
		{
			if($time_float - $array_login_total['restrict_time'] < IP_RESTRICT_TIME)//若当前时间和被封相差20秒以内
			{
				$error = '访问ip被封20秒中……';
				return false;
			}
			else//否则改变标记位，并把当前时间设置为解封时间
			{
				$array_login_total['restrict_flag'] = true;
				$array_login_total['restrict_time'] = $time_float;
				if($array_login_total[$login_email] == '')//增加新邮箱，初始化
				{
					$array_login_total[$login_email] = array(0=>$time_float,1=>0);
				}
				else
				{
					for($j = 3;$j >= 0;$j --)//更新登录时间
					{
						$array_login_total[$login_email][$j+1] = $array_login_total[$login_email][$j];
					}
					$array_login_total[$login_email][0] = $time_float;
				}
				$DC -> set_to_memcache10($ip_key,$array_login_total,MEMCACHE_EXPIRE_TIME);
				return true;
			}
		}
		else//ip访问标记位为true
		{
			if($time_float - $array_login_total['restrict_time'] < IP_RESTRICT_FREQUENCY)//如果同一个ip访问间隔0.01秒以内，更改标志位为false，并把当前时间设置为封锁时间
			{
				$array_login_total['restrict_flag'] = false;
				$array_login_total['restrict_time'] = $time_float;

				$DC -> set_to_memcache10($ip_key,$array_login_total,MEMCACHE_EXPIRE_TIME);

				$forbidden_key = "forbidden_ip";
				if($DC->memcache10->increment($forbidden_key) === false) {
					$DC->memcache10->add($forbidden_key, 1, false, 0);
				}

				$error = 'ip登录被拒绝';
				return false;
			}
			if($array_login_total[$login_email] == '')//增加新邮箱，初始化
			{
				$array_login_total[$login_email] = array(0=>$time_float,1=>0);
				$DC -> set_to_memcache10($ip_key,$array_login_total,MEMCACHE_EXPIRE_TIME);
				return true;
			}

			if(($time_float - $array_login_total[$login_email][1]) < IP_EMAIL_RESTRICT_FREQUENCY)//如果当前时间和最后差去平均值小于1，邮箱登录被拒绝
			{
				$error = '邮箱登录被拒绝';

				$forbidden_key = "forbidden_email";
				if($DC->memcache10->increment($forbidden_key) === false) {
					$DC->memcache10->add($forbidden_key, 1, false, 0);
				}

				return false;
			}
			/*for($j = 3;$j >= 0;$j --)//更新登录时间
			 {
				$array_login_total[$login_email][$j+1] = $array_login_total[$login_email][$j];
				}*/
			$array_login_total[$login_email][1] = $array_login_total[$login_email][0];
			$array_login_total[$login_email][0] = $time_float;
			$array_login_total['restrict_time'] = $time_float;

			$DC -> set_to_memcache10($ip_key,$array_login_total,MEMCACHE_EXPIRE_TIME);
			return true;
		}
	}
	return true;
}
/**
 * 获得当前时间，精确到0.01秒
 *
 * @return 时间
 */
function microtime_float_now()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function dec2scale($num,$scale)
{
	$num_tmp	=	$num;
	$reslut	=	'';
	while(1)
	{
		$res_ari	=	ari($num_tmp,$scale);
		$num_tmp	=	($num_tmp-$res_ari)/$scale;

		$res_ari	=	chr(97+$res_ari);
		if($num_tmp	<	1)
		{
			$reslut	=	$res_ari.$reslut;
			return $reslut;
		}
		else
		{
			$reslut	=	$res_ari.$reslut;
		}
	}
}

function ari($dividend,$divisor)
{
	return $dividend-$divisor*floor($dividend/$divisor);
}

function encode($num)
{
	if($num	==	'')
	{
		return '';
	}

	if($num	==	0)
	{
		return '0';
	}

	$num	=	strval($num);

	$num	=	str_replace('０','0',$num);
	$num	=	str_replace('１','1',$num);
	$num	=	str_replace('２','2',$num);
	$num	=	str_replace('３','3',$num);
	$num	=	str_replace('４','4',$num);
	$num	=	str_replace('５','5',$num);
	$num	=	str_replace('６','6',$num);
	$num	=	str_replace('７','7',$num);
	$num	=	str_replace('８','8',$num);
	$num	=	str_replace('９','9',$num);

	$str_tmp	=	'';
	$sep_str	=	'';
	for($i=0;$i<strlen($num);$i++)
	{
		$ord_tmp	=	ord($num{$i});
		if($ord_tmp	<	48 || $ord_tmp>57)
		{
			$arr_tmp[]	=	$str_tmp;
			$str_tmp	=	'';
			$sep_str	.=	$num{$i};
		}
		else
		{
			$str_tmp	.=	$num{$i};
		}
	}
	$arr_tmp[]	=	$str_tmp;

	$res	=	'';
	$rand_str	=	'';
	$spe_pose	=	'';

	foreach ($arr_tmp as $num_part)
	{
		if($num_part !=	'')
		{
			$num_part	=	'1'.$num_part;
			$rand	=	rand(1000,9999);
			$rand_str	.=	dec2scale($rand,26);
			$num_tmp	=	dec2scale(sprintf ("%01.0f", ($num_part+	$rand)*$rand),26);
			$res_arr[]	=	$num_tmp;
		}
	}

	$res	=	implode('-',$res_arr);

	return $res.$rand_str;
}

/**
 * 将被过滤的关键字修改成**，不同的应用类型可能采用不同的策略
 * @param string $str 需要进行更改的内容
 * @param string $app 应用类型，有些类型需要特殊处理
 * @return string 过滤后的内容。
 */
function change_filted_keywords($str, $app='')
{
	//目前mail 和 ques_love两个应用类型的关键字不予屏蔽了
	$special_keywords = array();
	switch ($app) {
		case 'mail':
			return $str;
			break;
		case 'ques_love':
			return $str;
			break;
		case 'note':
		case 'article':
			break;
	}
	static $keywords = array('性交',
'性爱',
'性关系',
'性伴侣',
'作爱',
'做爱',
'一夜性',
'E夜情',
'一夜激情',
'一夜情',
'性服务',
'性虐待',
'for one night',
'one night',
'make love',
'419',
'坐台',
'恋足',
'卖淫',
'操你',
'操你妈',
'妈了个逼',
'妈了个B',
'傻逼',
'傻B',
'傻比',
'阴道',
'阴毛',
'干你',
'干你娘',
'他妈的',
'包养',
'鸡巴',
'骚货',
'骚逼',
'骚B',
'骚比',
'阳具',
'3P',
'性饥渴',
'婊子',
'妓女',
'性奴',
'强奸',
'口交',
'中奖',
'領獎',
'禮品',
'抽取',
'婚介',
'交友中心',
'交友群',
'交友会所',
'白领交友',
'百合网',
'嫁我网',
'高级club国际婚姻',
'婚姻猎头',
'亚洲交友中心',
'蜜糖网',
'珍爱网',
'迷药',
'三唑仑',
'时代',
'民运',
'李洪志',
'美国之音',
'阴茎',
'群发',
'假币',
'阴唇',
'乱伦',
'手淫',
'法轮大法',
'大法弟子',
'六合',
'共产主义',
'共产党',
'紫阳',
'六四事件',
'反革命',
'反华',
'公安',
'盗取',
'分裂国家',
'朱镕基',
'毛主席',
'买卖枪支',
'春药',
'高干子女',
'瓮安',
'黄色电影',
'手机游戏',
'胡耀',
'东突',
'推翻',
'第十六届',
'十六次',
'自杀手册',
'迷昏药',
'激情小电影',
'催情药',
'成人电影',
'色情服务',
'俯卧撑',
'胡锦涛',
'藏独',
'台独',
'法轮',
'毛泽东',
'李岚清',
'天安门事件',
'偷拍',
'江主席',
'国民党',
'十六大',
'官商勾结',
'戴海静',
'成人片',
'禁书',
'314事件',
'枪支',
'淫靡',
'三级片',
'阴蒂',
'做鸡',
'中共',
'反共',
'多党执政',
'自制手枪',
'摇头丸',
'西藏独立',
'暴动',
'大法',
'dafa',
'退党',
'淫水',
'小穴',
'达赖',
'政府',
'李鹏',
'迷奸药',
'反政府',
'帝国之梦',
'鬼村',
'新疆独立',
'换妻',
'麻醉药',
'色情小电影',
'六合采',
'和弦',
'代开',
'嫖',
'天安门',
'打倒',
'投毒杀人',
'出售假币',
'透视眼镜',
'自杀指南',
'办证',
'极景',
'窃听',
'阴户',
'游行',
'专制',
'新唐人',
'短信群发器',
'九评',
'蒙汗',
'党代',
'第十六屆',
'天鹅之旅',
'西藏天葬',
'高干子弟',
'无界',
'民运分子',
'特码',
'胡景涛',
'办理证件',
'弹药',
'看中国',
'大纪元',
'人民报',
'世界之门',
'阿波罗',
'法轮功',
'壹周刊',
'北京之春',
'中国之路',
'中文独立笔会',
'三退',
'退团',
'退队',
'九评共产党',
'宪章',
'讲真相',
'天灭中共',
'自由门',
'神韵艺术团',
'明慧',
'群发',
'假币',
'藏独',
'大法',
'小电影',
'透视',
'麻醉乙醚',
'学运',
'手机复制',
'侦探设备',
'绕过封锁',
'PK黑社会',
'大学骚乱',
'枪决现场',
'血腥图片',
'高校群体事件',
'自由亚州',
'强硬发言',
'肉棍',
'邓小平',
'胡锦涛',
'胡景涛',
'江泽民',
'毛泽东',
'毛主席',
'李鹏',
'习近平',
'吴邦国',
'温家宝',
'温总理',
'胡主席',
'李克强',
'回良玉',
'王岐山',
'刘少奇',
'朱德',
'周恩来',
'周总理',
'乔石',
'小平',
'李岚清',
'江主席',
'龚海燕',
	);
	static $traditional_keywords = array (
 '性愛',
 '性关係',
 '性關系',
 '性關係',
 '性伴侶',
 '作愛',
 '做愛',
 '性服務',
 '戀足',
 '賣淫',
 '操你媽',
 '妈了個逼',
 '媽了个逼',
 '媽了個逼',
 '妈了個B',
 '媽了个B',
 '媽了個B',
 '陰道',
 '陰毛',
 '幹你',
 '幹你娘',
 '他媽的',
 '包養',
 '雞巴',
 '骚貨',
 '騷货',
 '騷貨',
 '騷逼',
 '騷B',
 '騷比',
 '陽具',
 '性饑渴',
 '強奸',
 '中獎',
 '交友會所',
 '白領交友',
 '百合網',
 '嫁我網',
 '高级club国際婚姻',
 '高级club國际婚姻',
 '高级club國際婚姻',
 '高級club国际婚姻',
 '高級club国際婚姻',
 '高級club國际婚姻',
 '高級club國際婚姻',
 '婚姻猎頭',
 '婚姻獵头',
 '婚姻獵頭',
 '亞洲交友中心',
 '蜜糖網',
 '珍爱網',
 '珍愛网',
 '珍愛網',
 '迷藥',
 '三唑侖',
 '時代',
 '民運',
 '李洪誌',
 '美國之音',
 '阴莖',
 '陰茎',
 '陰莖',
 '群發',
 '假幣',
 '陰唇',
 '乱倫',
 '亂伦',
 '亂倫',
 '法輪大法',
 '共产主義',
 '共產主义',
 '共產主義',
 '共産主义',
 '共産主義',
 '共产黨',
 '共產党',
 '共產黨',
 '共産党',
 '共産黨',
 '紫陽',
 '反華',
 '盜取',
 '分裂國家',
 '朱鎔基',
 '买卖槍支',
 '买賣枪支',
 '买賣槍支',
 '買卖枪支',
 '買卖槍支',
 '買賣枪支',
 '買賣槍支',
 '春藥',
 '高幹子女',
 '甕安',
 '黄色電影',
 '黃色电影',
 '黃色電影',
 '手机游戲',
 '手机遊戏',
 '手机遊戲',
 '手機游戏',
 '手機游戲',
 '手機遊戏',
 '手機遊戲',
 '東突',
 '第十六屆',
 '自杀手冊',
 '自殺手册',
 '自殺手冊',
 '迷昏藥',
 '激情小電影',
 '催情藥',
 '成人電影',
 '色情服務',
 '俯卧撐',
 '俯臥撑',
 '俯臥撐',
 '胡锦濤',
 '胡錦涛',
 '胡錦濤',
 '藏獨',
 '台獨',
 '法輪',
 '毛泽東',
 '毛澤东',
 '毛澤東',
 '李嵐清',
 '天安門事件',
 '国民黨',
 '國民党',
 '國民黨',
 '官商勾結',
 '戴海靜',
 '禁書',
 '槍支',
 '三級片',
 '陰蒂',
 '做雞',
 '多党執政',
 '多黨执政',
 '多黨執政',
 '自制手槍',
 '自製手枪',
 '自製手槍',
 '摇頭丸',
 '搖头丸',
 '搖頭丸',
 '西藏獨立',
 '暴動',
 '退黨',
 '达賴',
 '達赖',
 '達賴',
 '李鵬',
 '迷奸藥',
 '帝国之夢',
 '帝國之梦',
 '帝國之夢',
 '新疆獨立',
 '換妻',
 '麻醉藥',
 '色情小電影',
 '代開',
 '天安門',
 '投毒殺人',
 '出售假幣',
 '透视眼鏡',
 '透視眼镜',
 '透視眼鏡',
 '自殺指南',
 '办證',
 '辦证',
 '辦證',
 '極景',
 '窃聽',
 '竊听',
 '竊聽',
 '阴戶',
 '陰户',
 '陰戶',
 '遊行',
 '专製',
 '專制',
 '專製',
 '短信群發器',
 '九評',
 '黨代',
 '天鵝之旅',
 '高幹子弟',
 '無界',
 '民運分子',
 '特碼',
 '胡景濤',
 '办理證件',
 '辦理证件',
 '辦理證件',
 '弹藥',
 '彈药',
 '彈藥',
 '看中國',
 '大紀元',
 '人民報',
 '世界之門',
 '阿波羅',
 '法輪功',
 '中國之路',
 '中文独立笔會',
 '中文独立筆会',
 '中文独立筆會',
 '中文獨立笔会',
 '中文獨立笔會',
 '中文獨立筆会',
 '中文獨立筆會',
 '退團',
 '退隊',
 '九评共产黨',
 '九评共產党',
 '九评共產黨',
 '九评共産党',
 '九评共産黨',
 '九評共产党',
 '九評共产黨',
 '九評共產党',
 '九評共產黨',
 '九評共産党',
 '九評共産黨',
 '憲章',
 '講真相',
 '天滅中共',
 '自由門',
 '神韵艺术團',
 '神韵艺術团',
 '神韵艺術團',
 '神韵藝术团',
 '神韵藝术團',
 '神韵藝術团',
 '神韵藝術團',
 '神韻艺术团',
 '神韻艺术團',
 '神韻艺術团',
 '神韻艺術團',
 '神韻藝术团',
 '神韻藝术團',
 '神韻藝術团',
 '神韻藝術團',
 '群發',
 '假幣',
 '藏獨',
 '小電影',
 '透視',
 '学運',
 '學运',
 '學運',
 '手机复製',
 '手机複制',
 '手机複製',
 '手機复制',
 '手機复製',
 '手機複制',
 '手機複製',
 '侦探设備',
 '侦探設备',
 '侦探設備',
 '偵探设备',
 '偵探设備',
 '偵探設备',
 '偵探設備',
 '绕过封鎖',
 '绕過封锁',
 '绕過封鎖',
 '繞过封锁',
 '繞过封鎖',
 '繞過封锁',
 '繞過封鎖',
 'PK黑社會',
 '大学骚亂',
 '大学騷乱',
 '大学騷亂',
 '大學骚乱',
 '大學骚亂',
 '大學騷乱',
 '大學騷亂',
 '枪决现場',
 '枪决現场',
 '枪决現場',
 '枪決现场',
 '枪決现場',
 '枪決現场',
 '枪決現場',
 '槍决现场',
 '槍决现場',
 '槍决現场',
 '槍决現場',
 '槍決现场',
 '槍決现場',
 '槍決現场',
 '槍決現場',
 '血腥圖片',
 '高校群體事件',
 '自由亞州',
 '强硬發言',
 '強硬发言',
 '強硬發言',
 '鄧小平',
 '胡锦濤',
 '胡錦涛',
 '胡錦濤',
 '胡景濤',
 '江澤民',
 '毛泽東',
 '毛澤东',
 '毛澤東',
 '李鵬',
 '習近平',
 '吴邦國',
 '吳邦国',
 '吳邦國',
 '温家寶',
 '溫家宝',
 '溫家寶',
 '温總理',
 '溫总理',
 '溫總理',
 '李克強',
 '劉少奇',
 '周恩來',
 '周總理',
 '喬石',
 '朱鎔基',
 '李嵐清',
 '龔海燕',
	);
	$newstr = str_replace($keywords, "**", $str);
	$newstr = str_replace($traditional_keywords, "**", $newstr);
	return $newstr;
}
//


/**
 * 从批量用户里找出准黑会员
 *
 * @param  array $uid_arr  由uid构成的数组
 * @return array $black_arr  由uid构成的数组
 */
function extract_black_users($uid_arr)
{
    global $SDB;

    if (!is_array($uid_arr) || empty($uid_arr)) {
        return array();
    }
    $arr = array_chunk($uid_arr, 100);
    $black_arr = array();
    foreach ($arr as $uid_list) {
        $uid_list_str = implode(',', $uid_list);
        $sql = "SELECT uid FROM blacklist WHERE status=0 AND uid in({$uid_list_str})";
        $query = $SDB->query($sql);
        $black_list = array();
        while ($result = $SDB->fetch_array($query)) {
            $black_list[] = $result['uid'];
        }
        $black_arr = array_merge($black_arr, $black_list);
    }

    return $black_arr;
}

/**
 * 用于从直邮点击过来默认填写 email
 */
function email_to_login_input()
{
	global $smarty;

	$email_code = (strval($_GET['email_code']));

	if(empty($email_code))
	{
		return false;
	}

	$array = array();
	$array = email_code_to_array($email_code);

	send_email_log($email_code, "login");

	$smarty->assign('login_email', $array['to_email_address']);
}

/**
 * 邮箱验证字串解密成数组
 */
function email_code_to_array($email_code)
{
	$str = email_authcode($email_code, 'DECODE', EMAIL_KEY);
	$info_array_tmp = explode('|', $str);

	if(empty($info_array_tmp[0]) || empty($info_array_tmp[5]))
	{
		return $info_array;
	}

	$info_array['log_id'] = $info_array_tmp[0];
	$info_array['task_id'] = $info_array_tmp[1];
	$info_array['uid'] = $info_array_tmp[2];
	$info_array['curr_task_time_stamp'] = $info_array_tmp[3];
	$info_array['send_time_stamp'] = $info_array_tmp[4];
	$info_array['to_email_address'] = $info_array_tmp[5];

	return $info_array;
}

/**
 * 以下是用于 email 推送的
 */
function send_email_log($email_code, $operation)
{
	global $SDB_LY, $MDB_LY;
	$EMAIL_LOG_MDB =  new Database(M_REG_TRACE_HOST, REG_TRACE_PORT, REG_TRACE_USER, REG_TRACE_PWD, REG_TRACE_DATABASE);
	$EMAIL_LOG_SDB =  new Database(S_REG_TRACE_HOST, REG_TRACE_PORT, REG_TRACE_USER, REG_TRACE_PWD, REG_TRACE_DATABASE);

	$info_array = array();
	$info_array_tmp = array();
	$email_code = strval($email_code);

	if(empty($email_code))
	{
		return $info_array;
	}

	// 解密
    $str = email_authcode($email_code, 'DECODE', EMAIL_KEY);

	$info_array_tmp = explode('|', $str);

	if(empty($info_array_tmp[0]) || empty($info_array_tmp[5]))
	{
		return $info_array;
	}

    $info_array['log_id'] = $info_array_tmp[0];
    $info_array['task_id'] = $info_array_tmp[1];
	$info_array['uid'] = $info_array_tmp[2];
	$info_array['curr_task_time_stamp'] = $info_array_tmp[3];
	$info_array['send_time_stamp'] = $info_array_tmp[4];
	$info_array['to_email_address'] = $info_array_tmp[5];

	$send_log_tablename = 'send_msg_logs_'.date('m_d', $info_array['curr_task_time_stamp']);
	$table_log_id = 'log_id';

	if(($info_array['send_time_stamp'] < mktime(0, 0, 0, 3, 18, 2010)))
	{
		$send_log_tablename =  'send_msg_logs_'.date('m_d');

		$table_log_id = 'source_log_id';
	}
	$oper_log_tablename = 'send_msg_logs_'.date('m_d');


    if($info_array['task_id'])
	{
		// 检测是否记录过了 2010-1-8
		switch($operation)
		{
			case 'open':
				$sql = "SELECT open_time AS myflag FROM $send_log_tablename WHERE uid = ".$info_array['uid']." LIMIT 1";
				break;
			case 'login':
				$sql = "SELECT login_time AS myflag FROM $send_log_tablename WHERE uid = ".$info_array['uid']." LIMIT 1";
				break;
		}

		$result = $EMAIL_LOG_SDB->query($sql);
		$arr = $EMAIL_LOG_SDB->fetch_array($result);

		if($arr['myflag'])
		{
			return true;
		}

		// 2010-1-8
		$time_stamp = time();
		switch($operation)
		{
			case 'open':
				if($table_log_id == 'log_id')
				{
					$sql_1 = "UPDATE $send_log_tablename SET open_time='".$time_stamp."' WHERE uid = ".$info_array['uid']." LIMIT 1";
					$EMAIL_LOG_MDB->query($sql_1);
				}

				$sql_2 = "INSERT INTO $oper_log_tablename(source_log_id, oper_flag, task_id, uid, user_email, curr_task_day, send_time, open_time) VALUES(".$info_array['log_id'].", 2, ".$info_array['task_id'].", ".$info_array['uid'].", '".$info_array['to_email_address']."', ".$info_array['curr_task_time_stamp'].", ".$info_array['send_time_stamp'].", ".$time_stamp.")";
				$EMAIL_LOG_MDB->query($sql_2);
				break;
			case 'login':
				if($table_log_id == 'log_id')
				{
					$sql_1 = "UPDATE $send_log_tablename SET login_time='".$time_stamp."' WHERE uid = ".$info_array['uid']." LIMIT 1";
					$EMAIL_LOG_MDB->query($sql_1);
				}

				$sql_2 = "INSERT INTO $oper_log_tablename(source_log_id, oper_flag, task_id, uid, user_email, curr_task_day, send_time, login_time) VALUES(".$info_array['log_id'].", 3, ".$info_array['task_id'].", ".$info_array['uid'].", '".$info_array['to_email_address']."', ".$info_array['curr_task_time_stamp'].", ".$info_array['send_time_stamp'].", ".$time_stamp.")";
				$EMAIL_LOG_MDB->query($sql_2);
				break;
		}
	}


	$send_day = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

	$sql = "SELECT * FROM send_email_tongji WHERE type_id = '".$info_array['task_id']."' AND send_day = '$send_day'";
	$result = $SDB_LY->query($sql);
	$array = $SDB_LY->fetch_array($result);

	if(empty($array))
	{
		$sql = "INSERT send_email_tongji(type_id, send_day, sends, opens, logins) VALUES('".$info_array['task_id']."', '$send_day', 0, 0, 0)";
		$MDB_LY->query($sql);
	}

    switch($operation)
	{
		case 'open':
		    $tongji_sql = "UPDATE send_email_tongji SET opens = opens + 1 WHERE type_id = '".$info_array['task_id']."' AND send_day='".mktime(0, 0, 0, date('m'), date('d'), date('Y'))."'";
			$MDB_LY->query($tongji_sql);
		    break;
		case 'login':
		    $tongji_sql = "UPDATE send_email_tongji SET logins = logins + 1 WHERE type_id = '".$info_array['task_id']."' AND send_day='".mktime(0, 0, 0, date('m'), date('d'), date('Y'))."'";
			$MDB_LY->query($tongji_sql);
			break;
	}

	return $info_array;
}

// $string： 明文 或 密文
// $operation：DECODE表示解密,其它表示加密
// $key： 密匙
// $expiry：密文有效期
function email_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++)
	{
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++)
	{
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++)
	{
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE')
	{
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
		{
			return substr($result, 26);
		}
		else
		{
			return '';
		}
	}
	else
	{
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

// 2009-12-22 by chenchuanwen
// $string： 明文 或 密文
// $operation：DECODE表示解密,其它表示加密
// $key： 密匙
function email_decode($string, $operation, $key = '')
{
	$key = md5($key);
	$key_length = strlen($key);
	$string = $operation == 'D'?base64_decode($string):substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);
	$rndkey = $box = array();
	$result = '';
	for($i=0; $i<=255; $i++)
	{
		$rndkey[$i] = ord($key[$i%$key_length]);
		$box[$i] = $i;
	}
	for($j=$i=0; $i<256; $i++)
	{
		$j = ($j+$box[$i]+$rndkey[$i])%256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a=$j=$i=0; $i<$string_length; $i++)
	{
		$a = ($a+1)%256;
		$j = ($j+$box[$a])%256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation=='D')
	{
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8))
		{
			return substr($result, 8);
		}
		else
		{
			return '';
		}
	}
	else
	{
		return str_replace('=', '', base64_encode($result));
	}
}

// 2010-1-22 by zhouqing
// $operation：刷新用户服务
function refresh_service_v22(){
	global $DC, $USER, $CC;

	//更新服务
	$service_cache_flag = 'profile_service_'.$USER->uid;
	$service_arr = $CC->get_profile_service($USER->uid);
	$DC->set_cache_16($service_cache_flag,$service_arr,3600*2);

	$CC->get_balance();
	unset($_SESSION['center']['subscribe']);
	$CC->get_subscribe();

	$service_arr = $DC->get_cache_16($service_cache_flag);
	// 高级会员
	if(isset($service_arr[1]) || isset($service_arr[2]) || isset($service_arr[40])){
		$_SESSION['info']->charged	=	1;
		$arr_tmp['vip']	=	1;
		setcookie("charged", "1", (time()+3600*24*30),'/','jiayuan.com');
		$DC->update_user($USER->uid,$arr_tmp);
	}
	// 聊天包月
	if(isset($service_arr[40]) || isset($service_arr[33])){
		$_SESSION['info']->chargechat	=	1;
		setcookie("chargechat", "1", (time()+3600*24*30),'/','jiayuan.com');
		$arr_tmp['chargechat']	=	1;
		$DC->update_user($USER->uid,$arr_tmp);
	}
	// 钻石会员自动送玫瑰情书
	if (isset($service_arr[40])){
		$_SESSION['info']->chargemsg	=	1;
		setcookie("chargemsg", "1", (time()+3600*24*30),'/','jiayuan.com');
		$arr_tmp['chargemsg']	=	1;
		$DC->update_user($USER->uid,$arr_tmp);
	}

	// 看信免费的产品
	$baoci_flag = $CC->get_msg_ser_3($USER->uid);
	if(isset($service_arr[1]) || ($baoci_flag != 0) || isset($service_arr[40])){
		$readfree_key = 'readfree_'.$USER->uid;
		$DC->set_cache($readfree_key,1,86400*2);

		$today_kxby_uid_arr = $DC->get_cache('today_kxby_uid_arr');
		if(!is_array($today_kxby_uid_arr) || empty($today_kxby_uid_arr)){
		$today_kxby_uid_arr = array();
		}
		$today_kxby_uid_arr[] = $USER->uid;
		$DC->set_cache('today_kxby_uid_arr',$today_kxby_uid_arr);
	}

	//排名提前
	if(isset($service_arr[5])){
		$priority_arr = array('priority' => 1);
		$DC->update_search($USER->uid, $priority_arr);
	}
}

/**
 * raw_hash 加密
 */
function jy_encode_raw_hash($raw_hash)
{
    $raw_hash = jy_encrypt_string(time().'|'.$raw_hash,'jy^$39(2REv');
    return $raw_hash;
}

/**
 * raw_hash 解密
 */
function jy_decode_raw_hash($raw_hash_encoded)
{
    $raw_hash = jy_decrypt_string($raw_hash_encoded, 'jy^$39(2REv');
    $raw_hash_arr = explode('|', $raw_hash);
    return isset($raw_hash_arr[1]) ? $raw_hash_arr[1] : '';
}

function jy_encrypt_string($str, $key = 'haseverylettercanbeanystring')
{
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_CFB, $iv);
	$en_text = base64_encode($iv.$encrypted);
	$en_text = strtr($en_text, '+/=','-*.');
	return $en_text;
}

function jy_decrypt_string($str, $key = 'haseverylettercanbeanystring')
{
	$str = strtr($str, '-*.', '+/=');
    $data = base64_decode($str);
	$ivlength = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB);
    $iv = substr($data, 0, $ivlength);
    $encrypted = substr($data, $ivlength);
    return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, MCRYPT_MODE_CFB, $iv);

}

function get_domain() {
	$domainName = $mydomain	=	$_SERVER['HTTP_HOST'];

	if(strpos($mydomain, "msn.com.cn") !== false)
	{
		$domainName = "msn.com.cn";
	}
	else
	{
		$dom_arr	=	explode('.',$mydomain);
		$dom_len	=	count($dom_arr);
		if($dom_len	<=	3)
		{
			$domainName = $dom_arr[($dom_len-2)].'.'.$dom_arr[($dom_len-1)];
		}
	}

	return $domainName;
}
/**
 * 通过客户端 ip 转化成佳缘地区系统变量-------------------- add by will
 * @param string $ip_temp
 */
function clientip_to_location($ip_temp = "") {
	@include_once (WWW_ROOT_PATH . '/includes/location.inc.php');
	global $DC, $location_array;
	if(empty($ip_temp))
	{
		$ip_temp = get_client_ip ();
	}
	$ip_str = sprintf ( "%u", ip2long ( trim ( $ip_temp ) ) );
	$str_cus = floor ( $ip_str / 100000 );
	$cache_key = 'c2_' . $str_cus;
	$retu = '';
	$shuchu = '';
	$loc_arr = $DC->get_cache ( $cache_key );
	if (is_array ( $loc_arr )) {
		foreach ( $loc_arr as $value ) {
			$new_arr = explode ( '_', $value );
			$min_ip = $new_arr [0];
			$max_ip = $new_arr [1];
			$loc_str = $new_arr [2];
			if ($ip_str <= $max_ip && $ip_str >= $min_ip) {
				$retu = $loc_str;
			}
		}
		if ($retu != '') {
			foreach ( $location_array as $key => $value ) {
				if ($value == $retu) {
					$shuchu = $key;
				}
			}
		}
	}
	if (! $shuchu)
		$shuchu = 99;
	return $shuchu;
}
/**
 *  测试 地区显示。-------------------- add by will
 */
function oauth_display() {
	@include_once (WWW_ROOT_PATH . '/includes/config/switch/oauth.inc.php');
	if (defined ( "TEST_OAUTH_LOCATION" )) {
		$locals = explode ( "|", TEST_OAUTH_LOCATION );
		$loc = clientip_to_location();
		if(in_array($loc,$locals))
		{
			return true;
		}
	}
	return false;
}
/**
 *
 * 广告点击记录 -------------------- add by will
 * @param string $from
 */
function save_jy_st_cookie($from = "search") {
	//
	$trace = array ("positionid" => 67, "content" => 0, "template" => $from, "value" => "0" );
	if (REG_HOST == 1) {
		$from_table = get_req_value ( 'from_table' );
		if ($from_table) {
			$trace ["content"] = 3000;
		}
		$from_msn = get_req_value ( 'from_msn' );
		if ($from_msn) {
			$trace ["content"] = 2009;
		}
	}
	$search_from = intval ( get_req_value ( 'search_from' ) );
	if ($search_from > 0) {
		$trace ["content"] = $search_from;
	}
	$search_child_from = get_req_value ( 'subfrom' );
	if ($search_child_from) {
		$trace ["value"] = $search_child_from;
		set_jy_cookie ( 'FROM_ST_SUB_ID', $search_child_from, time () + 3600, '/', true );
	}
	if (intval ( $trace ["content"] ) > 0) {
		set_jy_cookie ( 'FROM_ST_ID', $trace ["content"], time () + 3600, '/', true );
		@include_once (WWW_ROOT_PATH . '/includes/config/switch/oauth.inc.php');
		$commonTrace = new CommonTrace ( session_id (), false, false );
		$commonTrace->inserttrace ( $trace );
	}
}

/**
 *
 * 广告来源频道浏览纪录 -------------------- add by liyi
 * @param string $from
 */
function save_st_cookie_record($from = "search") {
	$from_st_id = intval($_COOKIE['FROM_ST_ID']);
	if ($from_st_id > 0) {
		$trace = array ("positionid" => 230, "content" => 0, "template" => $from, "value" => "0" );
		$trace ["content"] = $from;
		$commonTrace = new CommonTrace ( session_id (), false, false );
		$commonTrace->inserttrace ( $trace );
	}
}

//记录模块执行时间统计 $time 毫秒,$key 统计标识
function excute_time_log($key,$time){
	global $USER;
	if($USER->uid % 10 != 3) return ;
	$time = intval($time);
	$t = intval($time/10);
	if($t>100){//>1000ms
		$t = 5;
	}elseif($t>50){//500-1000ms
		$t = 4;
	}elseif($t>20){//200-500ms
		$t = 3;
	}elseif($t>15){//150-200ms
		$t = 2;
	}elseif($t>10){//100-150ms
		$t = 1;
	}else{//<100ms
		$t = 0;
	}
	require_once(WWW_ROOT_PATH . '/includes/write_pv_memcache.php');
	write_pv_memcache($key.'_'.$t);
}
//手机拦截测试的用户判断接口---xujiao 2015/4/7(测试专用)
function is_mobile_ljcs_user($uid){
	return 0;
}

//通用加解密函数

function jy_string_encode($string, $operation = 'DECODE', $key = 'jystpec', $expiry = 0) {   
	    $ckey_length = 4;   
	    $key = md5($key);   
	    $keya = md5(substr($key, 0, 16));   
	    $keyb = md5(substr($key, 16, 16));   
	    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';   
	    $cryptkey = $keya.md5($keya.$keyc);   
	    $key_length = strlen($cryptkey);     
	    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
	    $string_length = strlen($string);   
	    $result = '';   
	    $box = range(0, 255);   
	    $rndkey = array();    
	    for($i = 0; $i <= 255; $i++) {   
	        $rndkey[$i] = ord($cryptkey[$i % $key_length]);   
	    }   
	    for($j = $i = 0; $i < 256; $i++) {
	        $j = ($j + $box[$i] + $rndkey[$i]) % 256;   
	        $tmp = $box[$i];   
	        $box[$i] = $box[$j];   
	        $box[$j] = $tmp;   
	    }   
	    for($a = $j = $i = 0; $i < $string_length; $i++) {   
	        $a = ($a + 1) % 256;   
	        $j = ($j + $box[$a]) % 256;   
	        $tmp = $box[$a];   
	        $box[$a] = $box[$j];   
	        $box[$j] = $tmp;   
	        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
	    }   
	    if($operation == 'DECODE') {  
	        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
	            return substr($result, 26);   
	        } else {   
	            return '';   
	        }   
	    }else{ 
	        return $keyc.str_replace('=', '', base64_encode($result));   
	    }   
	} 
        
        /**
         * 接受超全局变量$_REQUEST,并方便处理
         * @return type
         */
        function &jy_request(){
            $request = &$_REQUEST;
            return $request;
        }
        /**
         * 接受超全局变量$_GET,并方便处理
         * @return type
         */
        function &jy_get(){
            $get = &$_GET;
            return $get;
        }
        /**
         * 接受超全局变量$_POST,并方便处理
         * @return type
         */
        function &jy_post(){
            $post = &$_POST;
            return $post;
        }
