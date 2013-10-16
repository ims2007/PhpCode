<?php
function getHtml($url,$urlfile) {
	global $cookie_file;
	if (file_exists($urlfile)) {
		$fp   = fopen($urlfile,"rb") or die('err');
		$html = '';
		while (!feof($fp)) {
		  $html .= fread($fp, 8192);
		}
		fclose($fp);
	} else {
		sleep(SLEEP_TIME);
		$html = simulateGet($url);
		$fp = fopen($urlfile, 'w') or die("can't open file");
		fwrite($fp, $html);
		fclose($fp);
	}
	return $html;
}

/**
 * 模拟GET获取数据
 * @param $url string
 * @return string
 */

function simulateGet($url) {
	global $cookie_file;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
	$response = curl_exec($ch);
	curl_close($ch);
	//echo $result;die;
	return $response;
}

function get_multi_html($datas){
	global $cookie_file;
	$new_datas = array();
	foreach($datas as $i=>$data){
		$urlfile = $data['urlfile'];
		if (file_exists($urlfile)) {
			$fp   = fopen($urlfile,"rb") or die('err');
			$html = '';
			while (!feof($fp)) {
			  $html .= fread($fp, 8192);
			}
			fclose($fp);
			$data['content'] = $html;
			$new_datas[$i] = $data;
			unset($datas[$i]);
		}
	}
	vdump($datas);
	$results = get_multi_contents($datas);
	if(!empty($result)){
		$new_datas = array_merge($new_datas, $resutls);
	}
	return $new_datas;
}

function get_multi_contents($datas){
	 global $cookie_file;
	 $master = curl_multi_init();

	 $curl_arr = array();
	 foreach($datas as $i=>$data){
		  $url = $data['url'];
          //批量初始化curl资源
          $curl_arr[$i] = curl_init($url);
		  //设置curl参数
		  curl_setopt($curl_arr[$i], CURLOPT_HEADER, 0);
          curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
          //批量增加句柄
		  curl_setopt($curl_arr[$i], CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
		  curl_multi_add_handle($master, $curl_arr[$i]);
     }

     $running = null;
     do {
          //执行
          curl_multi_exec($master, $running);
     } while($running > 0);

	 foreach($datas as $i=>$data){
		 $result = curl_multi_getcontent($curl_arr[$i]);
		 $datas[$i]['content'] = $result;
		 if($results){
			$urlfile = $data['urlfile'];
			$fp = fopen($urlfile, 'w') or die("can't open file");
			fwrite($fp, $results);
			fclose($fp);
		 }
		 curl_multi_remove_handle($master, $curl_arr[$i]);
	 }
     //务必要关闭资源
     curl_multi_close($master);
     return $datas;
}

function simulatePost($url) {
	global $cookie_file;
	$ch = curl_init($url); //初始化
	curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	curl_exec($ch);
	curl_close($ch);
}

function vdump($arr, $debug = true){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';

	if($debug){
		exit;
	}
}

function cmkdir($dir, $mod = 777){
	if(!file_exists($dir)){
		mkdir($dir, $mod);
	}
}

function debug($msg){
	global $debug;
	if($debug){
		echo $msg . '<br>';
	}
}

function ctrim($str){
	$str = trim($str);
	$str = trim($str,'&nbsp;');
	$str = trim($str);
	return $str;
}

function caddslashes($arr) {
	foreach ($arr as $key=>$value) {
		$arr[$key] = addslashes($value);
	}
	return $arr;
}

function clog($file, $data){
	$content = date('Y-m-d H:i:s') . "\r\r" . $data . "\r\n";
	file_put_contents($file, $content, FILE_APPEND);
}

function wcsv($data, $file){
	$data = file_get_contents($file);
	$arr = explode('\r\n', $data);
	$uniqueArr = array_unique($arr);
	array_walk($uniqueArr, function (&$val) { $val = $val . "\r\n";});
	file_put_contents($file, $uniqueArr);
}
