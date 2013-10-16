<?php
/**
 * 
 * @param unknown_type $data
 */
function vdump($arr,$debug = true){
	if(!is_array($arr)){
		echo $arr;echo '<br>';
	} else {
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	}
	if($debug)
		exit;	
}

function product_get_product_image_path($name, $pictime, $type = 'small'){
	global $config;
	$nopic_url = $config['weburl']."/image/default/nopic.gif";
	if($pictime){
		$pic_path = $config['webroot']."/uploadfile/comimg/$type/$pictime/".$name.".jpg";
		$pic_url = $config['weburl']."/uploadfile/comimg/$type/$pictime/".$name.".jpg";
		if(!file_exists($pic_path)) {
			$pic_url = $nopic_url;
		}
	}else{
		$pic_path = $config['webroot']."/uploadfile/comimg/$type/".$name.".jpg";
		$pic_url = $config['weburl']."/uploadfile/comimg/$type/".$name.".jpg";
		if(!file_exists($pic_path)){
			$pic_url = $nopic_url;
		}
	}
	return $pic_url;
}

function product_get_product_max_img_edge($name, $pictime, $type = 'small'){
	global $config;
	$max_edge = 'width';
	if($pictime){
		$pic_path = $config['webroot']."/uploadfile/comimg/$type/$pictime/".$name.".jpg";
	}else{
		$pic_path = $config['webroot']."/uploadfile/comimg/$type/".$name.".jpg";
	}
	if(file_exists($pic_path)) {
		$pic = getimagesize($pic_path);
		if($pic[0] < $pic[1]){
			$max_edge = 'height';
		}
	}
	return $max_edge;
}

function en_get_mail_content($tpl){
	global $config;
	$tpl_path = $config['webroot'] . '/templates/cpbay/mail/';
	$header = file_get_contents($tpl_path . 'en_mail_header.htm');
	$footer = file_get_contents($tpl_path . 'en_mail_footer.htm');
	$content = file_get_contents($tpl_path . $tpl);
	return $header . $content . $footer;
}

function en_cache($file, $data = array(), $expire = 3600){
	global $config;
	$file_path = $config['webroot'] . '/cache/' . $file;
	if(false !== strpos($file, '/')){
		$dir = $config['webroot'] . '/cache/' . substr($file, 0, strpos($file, '/'));
		mkdir($dir, 0777);
	}
	if(!file_exists($file_path)){
		touch($file_path);
	}
	$cdata['data'] = $data;
	$cdata['expire'] = time() + $expire;
	$sdata = serialize($cdata);
	file_put_contents($file_path, $sdata);
	return $return;
}

function en_read_cache($file){
	global $config;
	$file_path = $config['webroot'] . '/cache/' . $file;
	$data = file_get_contents($file_path);
	return unserialize($data);
}

function en_cached($file){
	global $config;
	$file_path = $config['webroot'] . '/cache/' . $file;
	$file_exists = file_exists($file_path);
	$cdata = en_read_cache($file);
	$expired = $cdata['expire'] > time();
	return $file_exists && $expired;
}

function en_truncate($str, $len, $suf = ''){
	$strlen = strlen($str);
	if($strlen <= $len){
		return $str;
	}

	$r = substr($str, 0, $len);
	$next = substr($str, $len, 1);
	if($next != ' '){
		$return = substr($r, 0, strrpos($r, ' '));
	} else {
		$return = $r;
	}
	return $return . $suf;
}


function mkdirs($dir)
{    
	return is_dir($dir) or (mkdirs(dirname($dir)) and mkdir($dir, 0777));
}


function resizeImg($srcName, $newName='', $newWidth, $newHeight, $cut = false, $cutpos = 'middle') {
	
	//图片名称
	if ($newName == '') {
		$nameArr = explode('.', $srcName);
		$expName = array_pop($nameArr);
		$expName = 'thumbnail.'.$expName;
		array_push($nameArr, $expName);
		$newName = implode('.', $nameArr);
	}
	
	//读取图片信息
	$info = '';
	$data = getimagesize($srcName, $info);
	
	switch ($data[2]) {
		case 1:
			if (function_exists("imagecreatefromgif")) {
				$im = imagecreatefromgif($srcName);
			} else {
				die('你的GD库不能使用GIF格式的图片');
			}
			break;
		case 2:
			if (function_exists("imagecreatefromjpeg")) {
				$im = imagecreatefromjpeg($srcName);
			} else {
				die('你的GD库不能使用jpeg格式的图片');
			}
			break;
		case 3:
			$im = imagecreatefrompng($srcName);
			break;
		default:
			return false;
		break;
	}	
	$srcW = imagesx($im);#宽度
	$srcH = imagesy($im);#高度
	
	
	//裁剪
	if ($cut) {
		//缩放后的尺寸
		$newRate = $newWidth / $newHeight;
		$srcRate = $srcW / $srcH;
		if ($newRate <= $srcRate) {
			$toH = $newHeight;
			$toW = $toH * ($srcW / $srcH);
		} else {
			$toW = $newWidth;
			$toH = $toW * ($srcH / $srcW);
		}
	} else {
		//缩放后的尺寸
		$newRate = $newWidth / $newHeight;
		$srcRate = $srcW / $srcH;
/*
		if ($newRate <= $srcRate) {
			$toW = $newWidth;
			$toH = $toW * ($srcH / $srcW);
		} else {
			$toH = $newHeight;
			$toW = $toH * ($srcW / $srcH);
		}
 */
		$toW = $newWidth;
		$toH = $toW * ($srcH / $srcW);
	}
	
	
	//开始缩放
	if ($srcW > $newWidth || $srcH > $newHeight) {
		if (function_exists("imagecreatetruecolor")) {
			@$ni = imagecreatetruecolor($toW, $toH);
			if ($ni) {
				imagecopyresampled($ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH);
			} else {
				$ni = imagecreate($toW, $toH);
				imagecopyresampled($ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH);
			}
		} else {
			$ni = imagecreate($toW, $toH);
			imagecopyresampled($ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH);			
		}
		
		
		//图片裁剪
		if ($cut) {
			
			//裁剪位置
			if ($cutpos == 'middle') {
				if ($toW > $newWidth) {#图片太宽
					$img_x = floor(($toW - $newWidth) / 2);
					$img_y = 0;
				} elseif ($toH > $newHeight) {#图片太高
					$img_x = 0;
					$img_y = floor(($toH - $newHeight) / 2);
				}
				$toW = $newWidth;
				$toH = $newHeight;
			} else {
				$img_x = 0;
				$img_y = 0;
				$toW = min($newWidth, $toW);
				$toH = min($newHeight, $toH);
			}
			
			@$ni2 = imagecreatetruecolor($newWidth, $toH);
			imagecopy ( $ni2, $ni, 0, 0, $img_x, $img_y, $toW, $toH);
			
			if (function_exists("imagejpeg")) {
				imagejpeg($ni2, $newName);
			} else {
				imagepng($ni2, $newName);
			}
			
			imagedestroy($ni);
			imagedestroy($ni2);
		} else {			
			if (function_exists("imagejpeg")) {
				imagejpeg($ni, $newName);
			} else {
				imagepng($ni, $newName);
			}
			
			imagedestroy($ni);
		}		
	}
	
	imagedestroy($im);
}


function makethumb($srcFile,$dstFile,$dstW,$dstH)
{ 
  global $wmark_config,$config;
  $quality=90; 
  $data = @GetImageSize($srcFile); 
  switch ($data['2']) { 
    case 1: 
      $im = ImageCreateFromGIF($srcFile); 
   break; 
    case 2: 
      $im = imagecreatefromjpeg($srcFile); 
      break; 
    case 3: 
      $im = ImageCreateFromPNG($srcFile); 
      break; 
  } 
  $srcW=@ImageSX($im); 
  $srcH=@ImageSY($im); 
  if(($srcW<=$dstW)&&($srcH<=$dstH)){
    $dstX=$srcW;
 $dstY=$srcH;
  }
  if(($srcW>=$dstW)&&($srcH<=$dstH)){
    $dstX=$dstW;
 $dstY=floor($srcH/($srcW/$dstW));
  }
  if(($srcW<=$dstW)&&($srcH>=$dstH)){
 $dstY=$dstH;
 $dstX=floor($srcW/($srcH/$dstH));
  }
  if(($srcW>$dstW)&&($srcH>$dstH)){
   if(($srcW/$dstW)>($srcH/$dstH)){
   $dstX=$dstW;
      $dstY=floor($srcH/($srcW/$dstW)); 
     
   }else{
      $dstY=$dstH;
      $dstX=floor($srcW/($srcH/$dstH));
   }
  }
     $ni=@imageCreateTrueColor($dstX,$dstY); 
	 $white = imagecolorallocate($ni, 255, 255, 255);
	 imagefill($ni, 0, 0, $white);
     @ImageCopyResampled($ni,$im,0,0,0,0,$dstX,$dstY,$srcW,$srcH); 

	if($dstW>400 && (isset($wmark_config['wmark_type']) && $wmark_config['wmark_type']!=0)){
		if($wmark_config['wmark_type']==1){
			imageWaterMark($ni,$wmark_config['wmark_locaction'],$config['webroot']."/image/default/".$wmark_config['wmark_image'],$data); 
		}else if($wmark_config['wmark_type']==2){
			imageWaterMark($ni,$wmark_config['wmark_locaction'],"",$data,$wmark_config['wmark_words'],14,$wmark_config['wmark_words_color']); 
		}
	}

     @ImageJpeg($ni,$dstFile,$quality); 
     @imagedestroy($im); 
     @imagedestroy($ni); 
}

function en_read_all_category(){
	global $db;
	
	//cache
	if(en_cached('en_pcat_cache.php')){
		$data = en_read_cache('en_pcat_cache.php');
		return $data['data'];
	}

	$sql = "SELECT catid, cat, nums, info_num, isindex, rec_nums, comnum, append_cat FROM b2bbuilder_pcat WHERE combine_to IS NULL ";
	$db->query($sql);

	$cats = array();
	
	$rows = $db->getRows();
	foreach($rows as $row){
		$len = strlen($row['catid']);
		$row['cat2'] = en_format_catname($row['cat']);
		if($len == 4){
			if(!isset($cats[$row['catid']])){
				$cats[$row['catid']] = $row;
			} else {
				$temp = $cats[$row['catid']]['subsv'];
				$cats[$row['catid']] = $row;
				$cats[$row['catid']]['subsv'] = $temp;
			}
		} elseif ($len == 6){
			$k1 = substr($row['catid'], 0, 4);
			if(!isset($cats[$k1]['subsv'][$row['catid']])){
				$cats[$k1]['subsv'][$row['catid']] = $row;
			} else {
				$temp = $cats[$k1]['subsv'][$row['catid']]['subsv'];
				$cats[$k1]['subsv'][$row['catid']] = $row;
				$cats[$k1]['subsv'][$row['catid']]['subsv'] = $temp;
			}
		} elseif ($len == 8){
			$k1 = substr($row['catid'], 0, 4);
			$k2 = substr($row['catid'], 0, 6);
			if(!isset($cats[$k1]['subsv'][$k2]['subsv'][$row['catid']])){
				$cats[$k1]['subsv'][$k2]['subsv'][$row['catid']] = $row;
			} else {
				$temp = $cats[$k1]['subsv'][$k2]['subsv'][$row['catid']]['subsv'];
				$cats[$k1]['subsv'][$k2]['subsv'][$row['catid']] = $row;
				$cats[$k1]['subsv'][$k2]['subsv'][$row['catid']]['subsv'] = $temp;
			}
		} elseif ($len == 10){
			$k1 = substr($row['catid'], 0, 4);
			$k2 = substr($row['catid'], 0, 6);
			$k3 = substr($row['catid'], 0, 8);
			$cats[$k1]['subsv'][$k2]['subsv'][$k3]['subsv'][$row['catid']] = $row;
		}
	}
	//clean
	foreach($cats as $k1=>$cat1){

		if(!isset($cat1['catid'])){
			unset($cats[$k1]);
			continue;
		}
		if(!isset($cat1['subsv'])){
			continue;
		}

		foreach($cat1['subsv'] as $k2=>$cat2){
			if(!isset($cat2['catid'])){
				unset($cats[$k1]['subsv'][$k2]);
				continue;
			}
		
			if(!isset($cat2['subsv'])){
				continue;
			}
			foreach($cat2['subsv'] as $k3=>$cat3){
				if(!isset($cat3['catid'])){
					unset($cats[$k1]['subsv'][$k2]['subsv'][$k3]);
					continue;
				}
				if(!isset($cat3['subsv'])){
					continue;
				}
				foreach($cat3['subsv'] as $k4=>$cat4){
					if(!isset($cat4['catid'])){
						unset($cats[$k1]['subsv'][$k2]['subsv'][$k3]['subsv'][$k4]);
						continue;
					}
				}
			 }
		}
	}
	//end clean 

	en_cache('en_pcat_cache.php', $cats, 3600);
	return $cats;
}

function en_read_all_cate_including_append_cat(){
	if(en_cached('en_pcat_with_append_cat_cache.php')){
		$data = en_read_cache('en_pcat_with_append_cat_cache.php');
		return $data['data'];
	}
	$cats = en_read_all_category();
    foreach($cats as $k1=>$cat1){
        if(isset($cat1['subsv'])) {
            foreach($cat1['subsv'] as $k2=>$cat2){
                if(isset($cat2['subsv'])) {
                    foreach($cat2['subsv'] as $k3=>$cat3){
                        $append = get_append_cats($cat3['append_cat']);
                        if(!empty($append)){
                            foreach($append as $tk=>$temp){
                                $cats[$k1]['subsv'][$k2]['subsv'][$k3]['subsv'][$tk] = $temp;
                            }
                        }
                    }
                }
                $append = get_append_cats($cat2['append_cat']);
                if(!empty($append)){
                    foreach($append as $tk=>$temp){
                        $cats[$k1]['subsv'][$k2]['subsv'][$tk] = $temp;
                    }
                }
            }
        }
        $append = get_append_cats($cat1['append_cat']);
        if(!empty($append)){
            foreach($append as $tk=>$temp){
                $cats[$k1]['subsv'][$tk] = $temp;
            }
        }
	}
	en_cache('en_pcat_with_append_cat_cache.php', $cats, 3600);
    return $cats; 
} 

function en_get_append($id, $append_cat){
    $append_cat = trim($append_cat, ',');
    if($append_cat == ''){
        return array();
    }
    $appends = explode(',', $append_cat);
    $len = strlen($id);
    $cates = array();
    foreach($appends as $append){
//        if($len > strlen($append)){
//            continue;
//        }
        $cates[$append] = en_read_subcate($append);
    }
    return $cates;
}


function en_read_subcat($id, $isindex = true, $has_product = false, $type = 'get_append', $order = 'alpha'){
	if($type == 'get_append'){
		$cats = en_read_all_cate_including_append_cat();
	} else {
		$cats = en_read_all_category();
	}
    $len = strlen($id);
    $subcat = array();
    if($len == 4){
		if(isset($cats[$id]['subsv'])){
            $subcat = $cats[$id]['subsv'];
		}
    } elseif ($len == 6){
        $k1 = substr($id, 0, 4);
        if(isset($cats[$k1]['subsv'][$id]['subsv'])){
            $subcat = $cats[$k1]['subsv'][$id]['subsv'];
        }
    } elseif ($len == 8){
        $k1 = substr($id, 0, 4);
        $k2 = substr($id, 0, 6);
        if(isset($cats[$k1]['subsv'][$k2]['subsv'][$id]['subsv'])){
            $subcat = $cats[$k1]['subsv'][$k2]['subsv'][$id]['subsv'];
        }
    } 
	$format_cat =  en_read_subcat_format($id, $subcat, $isindex, $has_product);

	$format_cat = array_values($format_cat);
	//order
	if(in_array($order, array('rec_nums', 'info_num', 'alpha'))){
		$format_cat = en_read_subcat_sort($format_cat, $order);
	} 
	return $format_cat;
}

function en_read_subcat_sort($cats, $order){
	usort($cats, "en_read_subcat_" . $order . "_sort_function");
	foreach($cats as $k=>$cat){
		if(isset($cat['subsv'])){
			$cats[$k]['subsv'] = en_read_subcat_sort($cat['subsv'], $order);
		}
	}
	return $cats;
}

function en_read_subcat_rec_nums_sort_function($cat1, $cat2){
    if ($cat1['rec_nums'] == $cat2['rec_nums']) {
        return 0;
    }
    return ($cat1['rec_nums'] > $cat2['rec_nums']) ? -1 : 1;
}

function en_read_subcat_info_num_sort_function($cat1, $cat2){
    if ($cat1['info_num'] == $cat2['info_num']) {
        return 0;
    }
    return ($cat1['info_num'] > $cat2['info_num']) ? -1 : 1;
}

function en_read_subcat_alpha_sort_function($cat1, $cat2){
	$a1 = strtolower($cat1['cat']);
	$a2 = strtolower($cat2['cat']);
    if ($a1 == $a2) {
        return 0;
    }
    return ($a1 > $a2) ? 1 : -1;
}

function en_read_subcat_format($id, $cats, $isindex = true, $has_product = false){
	foreach($cats as $key=>$subcat){
		if($isindex && !$subcat['isindex']){
			unset($cats[$key]);
			continue;
		}
		if($has_product && $subcat['rec_nums'] <= 0){
			unset($cats[$key]);
			continue;
		}
		if(isset($subcat['subsv'])){
			$cats[$key]['subsv'] = en_read_subcat_format($subcat['catid'], $subcat['subsv']);
		}
	}
	return $cats;
}	

function en_format_catname($cat){
	$cat=str_replace(array("'","\"","#","%","&"," ","/",",","(",")"), "-", $cat);
	$cat=str_replace(array("---","--"), "-", $cat);
	return $cat;
}

function readsubcat($id, $cattype = null, $isall = null, $get_append = true, $level = 0, $cond = '')
{	
	global $db;
	if(!is_numeric($id)){
		return array();
	}

	$s=$id."00";
	$b=$id."99";
	$ssql = '';
	if(empty($isall)){
		$ssql=" and isindex='1' ";
	}
	if(empty($cattype))	{
		$sql="select * from ".PCAT." 
		where 1 $ssql and catid>$s and catid<$b AND combine_to IS NULL $cond order by nums asc,char_index asc";
		$db->query($sql);
		$scat=array();
		while($k=$db->fetchRow()){
			$k['cat2']=str_replace(array("&"," ","/",",","(",")"),"-",$k['cat']);
			$k['cat2']=str_replace(array("---","--"),"-",$k['cat2']);
			$scat[]=$k;
		}
		//append cat
		if($get_append){
			$append_cats = get_append_cats_by_id($id);
			$scat = array_merge($scat, $append_cats);
		}
		if($level == 1){
			return $scat;	
		}

		foreach($scat as $key=>$sv)
		{
			$s=$sv['catid'].'00';
			$b=$sv['catid'].'99';
			$sql="select * from ".PCAT." where catid<=$b and catid>=$s AND combine_to IS NULL $cond order by nums asc";
			$db->query($sql);
			while($kk=$db->fetchRow()){
				$kk['cat2']=str_replace(array("&"," ","/",",","(",")"),"-",$kk['cat']);
				$kk['cat2']=str_replace(array("---","--"),"-",$kk['cat2']);
				$scat[$key]['subsv'][]=$kk;
			}
			//append cat
			if($get_append){
				$append_cats = get_append_cats_by_id($sv['catid']);
				$scat[$key]['subsv'] = array_merge($scat[$key]['subsv'], $append_cats);
			}

			if($level == 2)	continue;

			//$scat[$key]['subsv']=$db->getRows();
			foreach($scat[$key]['subsv'] as $key2=>$sv2){
				$s=$sv2['catid'].'00';
				$b=$sv2['catid'].'99';
				$sql="select * from ".PCAT." where catid<=$b and catid>=$s AND combine_to IS NULL $cond order by nums asc";
				$db->query($sql);
				while($kkk=$db->fetchRow()){
					$kkk['cat2']=str_replace(array("&"," ","/",",","(",")"),"-",$kkk['cat']);
					$kkk['cat2']=str_replace(array("---","--"),"-",$kkk['cat2']);
					$scat[$key]['subsv'][$key2]['sunsv2'][]=$kkk;
				}
				//$scat[$key]['subsv'][$key2]['sunsv2']=$db->getRows();
				//append cat
				if($get_append){
					$append_cats = get_append_cats_by_id($sv2['catid']);
					$scat[$key]['subsv'][$key2]['sunsv2'] = array_merge($scat[$key]['subsv'][$key2]['sunsv2'], $append_cats);
				}
			}
		}
	} else {
		if ($cattype == 'album') {
			$sql="select * from ".ALBUMCAT." 
			where 1 $ssql and catid>$s and catid<$b order by nums asc,char_index asc";
		} else {
			$sql="select * from ".OCAT." 
			where 1 $ssql and catid>$s and catid<$b AND combine_to IS NULL order by nums asc,char_index asc";
		}
		$db->query($sql);
		while($k=$db->fetchRow()){
			$k['cat2']=str_replace(array("&"," ","/",",","(",")"),"-",$k['cat']);
			$k['cat2']=str_replace(array("---","--"),"-",$k['cat2']);
			$scat[]=$k;
		}
		//append cat
		$append_cats = get_append_cats_by_id($id);
		$scat = array_merge($scat, $append_cats);
	}
	return $scat;
}


?>
