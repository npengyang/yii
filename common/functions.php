<?php

use yii\data\Pagination;
use yii\widgets\LinkPager;


function ajaxReturn($flag = 1,$msg = '',$url = null){
    $data = array();
    $data['flag'] = $flag;
    $data['msg'] = $msg;
    $data['data'] = $url;
    exit(json_encode($data));
}


function curl_file_get_contents($url, $postFields = null,$timeout = 100)
{
    set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($postFields) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}

// 格式输出
function dump($data)
{
    header("Content-Type:text/html;   charset=utf-8");
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    //exit();
}


/**
 * 格式化分类
 * @param $category
 * @param int $fid
 * @param int $level
 * @param int $selectedId
 */
function category_format($category,$fid =0,$level=1,$selectedId=0){
    $str = '';
    foreach ($category as $c){
        if($c['parent_id'] == $fid){
            $leaf = str_repeat('&nbsp;&nbsp;&nbsp;',$level - 1) ;
            if($fid != 0){
                $leaf .=  '├';
            }
            $str .= '<option value="'.$c['cat_id'] .'"';
            if($c['cat_id'] == $selectedId){
                $str .= ' selected ';
            }
            $str .= '>';
            $str .= $leaf . ' '. $c['cat_name'].'</option>';
            $str .= category_format($category,$c['cat_id'],$level+1,$selectedId);
        }
    }
    return $str;
}

/**
 *根据选择的品类属性值生成N个SKU供选择 
 * */
function buildsku($arr,$spuid){
    if(count($arr) >= 2){
        $tmparr = array();
        $arr1 = array_shift($arr);
        $arr2 = array_shift($arr);
        foreach($arr1 as $k1 => $v1){
            foreach($arr2 as $k2 => $v2){
                if(is_array($v1)){
                    $tmparr[] = [['spu'=>$spuid,'str'=>$v1[0]['str'].','.$v2],[$v1[0]['str'],$v2]];
                }else{
                    $tmparr[] = [['spu'=>$spuid,'str'=>$v1.','.$v2],[$v1,$v2]];
                }
            }
        }
        array_unshift($arr, $tmparr);
        $arr = buildsku($arr,$spuid);
    }else{
        return $arr;
    }
    return $arr;
}


/**
 * 分页公用方法
 * */
function pages($data,$pagesize,$type=false){
 $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $pagesize]);
 if($type){
     $model = $data->offset($pages->offset)->limit($pages->limit)->all();
 }else{
     $model = $data->offset($pages->offset)->limit($pages->limit)->asArray()->all();
 }

 return [
     'model' => $model,
     'no'   => ($pages->offset+1),
     'pages' => LinkPager::widget(['pagination' => $pages]),
 ];
}



/**
 * @param $path
 */
function create_folder($path)
{
    if (!file_exists($path)) {
        create_folder(dirname($path));
        mkdir($path, 0755);
    }
}


/**
 * 循环删除目录和文件函数
 *
 * @param $path
 * @return bool
 */
function deldir($path)
{
    //给定的目录不是一个文件夹
    if (!is_dir($path)) {
        return false;
    }

    $fh = opendir($path);
    while (($row = readdir($fh)) !== false) {
        //过滤掉虚拟目录
        if ($row == '.' || $row == '..') {
            continue;
        }
        if (!is_dir($path . '/' . $row)) {
            unlink($path . '/' . $row);
        }
        deldir($path . '/' . $row);
    }
    //关闭目录句柄，否则出Permission denied
    closedir($fh);
    //删除文件之后再删除自身
    if (!rmdir($path)) {
        return false;
    }
    return true;
}


/**
 * 判断数组是否有该键值
 *
 * @param array $where 检查的数组
 * @param string $key 键值
 * @param string $default_val 默认值
 * @return string
 */
function check_data($where = array(), $key = '', $default_val = '0')
{
    if (empty($where) || !$key)
        return $default_val;
    return isset($where[$key]) && $where[$key] ? clean_data($where[$key]) : $default_val;
}


/**
 * 判断字符是否在一定范围内，不是则返回第一个
 *
 * @param $input
 * @param $arr
 * @return mixed
 */
function str_range($input, $arr)
{
    if (!is_array($arr)) {
        $arr = explode(',', $arr);
    }
    foreach ($arr as $key => $value) {
        if ($input == $value) {
            return $input;
        }
    }

    return $arr[0];

}

/**
 * 把二维数组重组
 * 例 array('123','456','789') 变成 array(0=>array(0=>'123',1=>'456'),1=>array('0'=>'789'))
 *
 * @param $arr
 * @param int $num
 * @return string
 */
function array_combination($arr, $num = 2)
{
    $i = 1;
    $new_arr = '';
    $new_key = 1;
    foreach ($arr as $key => $value) {
        if ($i % 3 == 0) {
            $new_arr[$new_key][] = $value;
            $new_key++;
        } else {
            $new_arr[$new_key][] = $value;
        }
        $i++;
    }
    return $new_arr;
}


/**
 * 验证用户名
 *
 * @param $value
 * @param int $minLen
 * @param int $maxLen
 * @param string $charset
 * @return bool|int
 */
function is_name($value, $minLen = 2, $maxLen = 20, $charset = 'ALL')
{
    if (empty($value))
        return false;
    switch ($charset) {
        case 'EN':
            $match = '/^[_\w\d]{' . $minLen . ',' . $maxLen . '}$/iu';
            break;
        case 'CN':
            $match = '/^[_\x{4e00}-\x{9fa5}\d]{' . $minLen . ',' . $maxLen . '}$/iu';
            break;
        default:
            $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
    }
    return preg_match($match, $value);
}

/**
 * 验证手机
 */
function is_mobile($mobile)
{
    if (preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|18[0-9]{9}$|14[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/", $mobile)) {
        //验证通过
        return true;
    } else {
        //手机号码格式不对
        return false;
    }
}


/**
 * 只提取汉字
 *
 * @param $chars
 * @param string $encoding
 * @return string
 */
function match_chinese($chars, $encoding = 'utf8')
{
    $pattern = ($encoding == 'utf8') ? '/[\x{4e00}-\x{9fa5}]/u' : '/[\x80-\xFF]/';
    preg_match_all($pattern, $chars, $result);
    $temp = join('', $result[0]);
    return $temp;
}

/**
 * PHP实现中文字串截取无乱码的方法
 *
 * @param $str
 * @param $mylen
 * @return mixed
 */
function substr_cn($str, $mylen)
{
    $from = 0;
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' .
        '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $mylen . '}).*#s',
        '$1', $str);
}

/*
 * serialize代替函数
 */
function mb_serialize($serial_str)
{
    $serial_str = serialize($serial_str);
    return base64_encode($serial_str);
}

/*
 * unserialize 代替函数
 */
function mb_unserialize($serial_str)
{
    if (empty($serial_str) == true) {
        return false;
    }
    $serial_str = base64_decode($serial_str);
    return unserialize($serial_str);
}

/**
 * @param $mobile
 * @return mixed
 */
function hidden_mobile($mobile)
{
    return substr_replace($mobile, '*****', 3, 5);
}


/**
 * @param $email
 * @return bool
 */
function validate_email($email)
{
    if (!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i', $email)) {
        return false;
    } else {
        return true;
    }
}


/**
 * stdClass转数组
 *
 * @param $array
 * @return array
 */
function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $value = (array)$value;
            }
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

/**
 * stdClass转数组
 *
 * @param $array
 * @return array
 */
function object_array2($d)
{
    if (is_object($d)) {
        $d = get_object_vars($d);
    }
    if (is_array($d)) {
        return array_map(__FUNCTION__, $d); // recursive
    } else {
        return $d;
    }
}


/**
 * 截取 2位价格
 * @param $price
 * @return string
 */
function format_price($price)
{
    return substr(sprintf("%.3f", $price), 0, -1);
}



/**
 * strval增强版，
 * @param $str
 * @return string
 */
function strval2($str)
{
    if (!isset($str) || is_array($str) || is_object($str)) {
        return '';
    }
    return strval($str);
}

/**
 * 检查字符串里（每个字）是否都在指定字符集内的字.
 *
 * @param $str
 * @param $chars                    字符集
 * @param bool|true $is_ignore 是否区分大小写,true=不区分,false=区分大小写
 * @return bool                     true=在字符集内,false=存在非字符集的字符
 */
function is_existvalue($str, $chars, $is_ignore = TRUE)
{
    $str = strval2($str);
    if (strlen($str) == 0 || strlen($chars) == 0) {
        return FALSE;
    }

    if ($is_ignore) {
        $str = strtolower($str);
        $chars = strtolower($chars);
    }

    $ti = strlen($str);
    for ($i = 0; $i < $ti; $i++) {
        if (strpos($chars, $str[$i]) === FALSE) {
            return FALSE;
        }
    }
    return TRUE;
}

/**
 * 检查输入字符串,是否为纯数字组成
 *
 * usage: web_helper::is_numberchar($str);
 * @param $str
 * @return bool         true=是，false=不是
 */
function is_numberchar($str)
{
    $str = strval2($str);
    return strlen($str) > 0 && is_existvalue($str, '0123456789', FALSE);
}

/**
 * 检查输入字符串,是否为纯字母组成（不区分大小写）
 *
 * usage: web_helper::is_english($str);
 * @param $str
 * @return bool         true=是，false=不是
 */
function is_english($str)
{
    $str = strval2($str);
    return strlen($str) > 0 && is_existvalue($str, 'abcdefghijklmnopqrstuvwxyz');
}

/**
 * 检查输入字符串,是否全由字母和数字组成（不区分大小写）
 *
 * usage: web_helper::is_rndkey($str);
 * @param $str
 * @return bool         true=是，false=不是
 */
function is_rndkey($str)
{
    $str = strval2($str);
    return strlen($str) > 0 && is_existvalue($str, 'abcdefghijklmnopqrstuvwxyz0123456789');
}

/**
 * 取得服务器ip地址
 *
 * usage: web_helper::get_server_ip();
 * @return string
 */
function get_server_ip()
{
    if (isset($_SERVER)) {
        if ($_SERVER['SERVER_ADDR']) {
            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {
            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }
    return $server_ip;
}

/**
 * 转为 int型 ，并 如果值小于0，即返回 0
 * @param $str
 * @return int
 */
function to_numberchar($str)
{
    $k = 0;
    if (isset($str)) {
        if(is_numberchar($str)){
            return $str;
        }
    }
    return $k;
}

/**
 * 转为 int型 ，并 如果值小于0，即返回 0
 * @param $str
 * @return int
 */
function to_int0($str)
{
    $k = 0;
    if (isset($str)) {
        $k = intval($str);
        if ($k < 0) {
            return 0;
        }
    }
    return $k;
}

/**
 * 转为 float 型 ，并 如果值小于0，即返回 0
 * @param $str
 * @return float
 */
function to_float0($str)
{
    $k = 0;
    if (isset($str)) {
        $k = floatval($str);
        if ($k < 0) {
            return 0;
        }
    }
    return $k;
}


/**
 * 格式化 id_list
 * @param $id_list
 * @return string
 */
function fmt_id_list($id_list)
{
    $sb = [];
    $arr = explode(',', strval2($id_list));
    foreach ($arr as $v) {
        $k = to_int0($v);
        if ($k > 0) {
            $sb[] = $k;
        }
    }

    return implode(',', $sb);
}

/**
 * 转为 str型 ，并, 如果值小于0，即返回 0
 * @param $str
 * @param int $length 0 = 不切取，大于0，即限制输入的长度,切取长度
 * @param bool|TRUE $is_trim true = 调用 trim()
 * @param bool|TRUE $is_checksql true = 过滤sql 注入关键字
 * @return string
 */
function to_str($str, $length = 0, $is_trim = TRUE, $is_checksql = TRUE)
{
    if (!isset($str)) {
        return '';
    }

    if (empty($str)) {
        return '';
    }

    if ($is_trim) {
        $str = trim($str);
    }

    if ($is_checksql) {
        $str = check_sql($str);
    }

    if ($length > 0) {
        if (mb_strlen($str) > $length) {
            $str = mb_substr($str, 0, $length);
        }
    }
    return $str;
}

/**
 * 格式化时间，
 * @param int $date
 * @return bool|string
 */
function to_date($date = 0)
{
    if ($date > 0) {
        return date("Y-m-d", $date);
    }
    return date("Y-m-d", time());
}

/**
 * 格式化时间，
 * @param int $time
 * @return bool|string
 */
function to_datetime($time = 0)
{
    if ($time > 0) {
        return date("Y-m-d H:i:s", $time);
    }
    return date("Y-m-d H:i:s", time());
}

/**
 * 字符串时间转为unix时间秒
 * 即 "Y-m-d H:i:s" to time
 * @param int $time
 * @param int $def 如果非法时，使用的默认值
 * @return bool|string
 */
function to_timesecond($time, $def = 0)
{
    $tmp = strtotime($time);
    if ($tmp) {
        return $tmp;
    }
    return $def;
}

/**
 * 格式化输出金额
 *
 * @param $price
 * @param string $prefix
 * @return string
 */
function fmt_price($price, $prefix = '¥ ')
{
    return $prefix . number_format(round($price, 2), 2);
}

/**
 * 格式化输出金额
 * @param $price
 * @return string
 */
function to_price($price)
{
    return number_format(round($price, 2), 2);
}

/**
 * 格式化输出金额
 *
 * @param $price
 * @param string $prefix
 * @return string
 */
function fmt_price2($price, $prefix = '')
{
    $p = 2;
    $tmp = explode('.', number_format(round($price, $p), $p));

    return $prefix . '<em>' . $tmp[0] . '</em>.' . $tmp[1];
}


/**
 * 过滤sql,防止注入
 *
 * @param $str
 * @return mixed|string
 */
function check_sql($str)
{
    if (empty($str)) {
        return '';
    }
    $ret = $str;
    $ret = str_replace("\\'", '', $ret);
    $ret = str_replace(chr(0), '', $ret);
    $ret = str_replace("'", '', $ret);
    return $ret;
}

/**
 * implode用法一样，只是把里边的值，转为int型
 *
 * @param $glue 规定数组元素之间放置的内容。默认是 ""（空字符串）。 &arr 数组
 * @param $arr
 * @return string
 */
function implode_int($glue, $arr)
{
    $new_arr = array();
    foreach ($arr as $item) {
        $new_arr[] = intval($item);
    }

    return implode($glue, $new_arr);

}


/**
 * 把价格分为指定份数，并返回数组
 *
 * @param $
 * @param $arr
 * @return string
 */

function cut_price_by_number($price, $num)
{
    $part = floor($price / $num);
    $price_arr = array();
    for ($i = 1; $i <= $num; $i++) {
        if ($i == $num) {
            $price_arr[] = $price - $part * ($num - 1);
        } else {
            $price_arr[] = $part;
        }
    }
    return $price_arr;
}

/**
 * 用户登录密码的加密
 */
function member_password_md5($source_str, $username, $createtime)
{
    //return md5($password . $createtime);
    $string_md5 = md5(md5($source_str) . $username . $createtime);
    $front_string = substr($string_md5, 0, 31);
    $end_string = 's' . $front_string;
    return $end_string;
}

/**
 * 输出广告
 *
 * @param $pic
 * @param string $url
 * @param string $alt
 * @return string
 */
function get_advert_html($pic, $url = '', $alt = '')
{

    $ss = '';
    if (!empty($pic) && strlen($pic) > 5) {
        $CI =& get_instance();
        $pic = $CI->config->item('hosts_img') . $pic;
        $ss = '<img src="' . $pic . '" alt="' . $alt . '"  />';
    }

    if (!empty($ss)) {
        if (!empty($url) && strlen($url) > 5) {
            $ss = '<a href="' . $url . '" target="_blank" title="' . $alt . '">' . $ss . '</a>';
        }
    }

    return $ss;
}

/**
 * 输出图片
 * @param $pic
 * @param string $url
 * @param bool|true $is_lazy
 * @param bool|true $is_blank
 * @param string $alt
 * @return string
 */
function get_img_html($pic, $url = '', $is_lazy = false, $is_blank = false, $alt = '')
{
    $ss = '';
    if (!empty($pic) && strlen($pic) > 5) {
        if ($is_lazy) {
            $ss = '<img class="lazy" data-original="' . $pic . '" src="/public/wechat/img/bg/grey.gif">';
        } else {
            $ss = '<img src="' . $pic . '" alt="' . $alt . '"  />';
        }

    }

    if (!empty($ss)) {
        if (!empty($url) && strlen($url) > 5) {
            $tt = $is_blank ? ' target="_blank" ' : '';
            $ss = '<a href="' . $url . '" ' . $tt . ' title="' . $alt . '">' . $ss . '</a>';
        }
    }
    return $ss;
}


/**
 * 输出html
 *
 * @param $html
 */
function echo_html($html)
{
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.
    header('Content-type: text/html; charset=UTF-8');
    echo $html;
    exit();
}

/**
 * 输出json
 *
 * @param $data
 */
function echo_json($data)
{
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit();
}

/**
 * 根据时间类型获得 秒数
 * @param int $type months|week|day|hours|minute 默认minute
 * @param int $num
 */
function get_second($type, $num)
{
    switch ($type) {
        case 'months':
            $second = $num * (60 * 60 * 24 * 30);     //算30日一个月
            break;
        case 'weeks':
            $second = $num * (60 * 60 * 24 * 7);
            break;
        case 'days':
            $second = $num * (60 * 60 * 24);
            break;
        case 'hours':
            $second = $num * (60 * 60);
            break;
        default :
            $second = $num * 60;
            break;
    }

    return $second;
}

/**
 * 获取间隔类型
 */
function get_interval_type($type =null){
    $type_arr = [
        'seconds'=>'秒',
        'minutes'=>'分',
        'hours'=>'时',
        'days'=>'天',
    ];
    if(isset($type_arr[$type])){
        return $type_arr[$type];
    }
    return $type_arr;
}

/**
 * 判断字符串 是否时间
 * @param unknown $str
 * @param string $format
 */
function checkDatetime($str, $format = "Y-m-d H:i:s")
{
    $unixTime = strtotime($str);
    $checkDate = date($format, $unixTime);
    if ($checkDate == $str)
        return 1;
    else
        return 0;
}

//随机取6位字符数
function randomkeys($length)
{
    $key = '';
    $pattern = '1234567890';    //字符池
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{mt_rand(0, 9)};    //生成php随机数
    }
    return $key;
}

//二维数组去重复
function unique_arr($array2D, $stkeep = false, $ndformat = true)
{
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if ($stkeep) $stArr = array_keys($array2D);

    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if ($ndformat) $ndArr = array_keys(end($array2D));

    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v) {
        $v = join(",", $v);
        $temp[] = $v;
    }

    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);

    //再将拆开的数组重新组装
    foreach ($temp as $k => $v) {
        if ($stkeep) $k = $stArr[$k];
        if ($ndformat) {
            $tempArr = explode(",", $v);
            foreach ($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        } else $output[$k] = explode(",", $v);
    }

    return $output;
}

//修改二维数组以某个子数组的值为键值
function key_arr($arr, $key)
{
    $newArray = array();
    foreach ($arr as $k => $v) {
        $newArray[$v[$key]] = $v;
    }
    return $newArray;
}

/**
 * 取得一维数据中的一个值
 *
 * @param $arr
 * @param $val
 * @return string
 */
function get_array_value($arr, $val)
{
    return isset($arr[$val]) ? $arr[$val] : '';
}

/**
 * 将id list 换成 数据，并根据id做下标
 * @param $str
 * @param string $flag
 * @return array
 */
function explode2($str, $flag = ',')
{
    $sb = [];
    if (empty($str)) {
        return $sb;
    }

    $arr = explode($flag, $str);
    foreach ($arr as $k => $v) {
        $sb[$v] = $v;
    }
    return $sb;
}

/**
 * 取得二维数据中的一个值
 *
 * @param $arr
 * @param $val
 * @param $str_id
 * @param $str_name
 * @return string
 */
function get_array2_value($arr, $val, $str_id = 'id', $str_name = 'name')
{
    foreach ($arr as $key => $item) {
        if ($val == $item[$str_id]) {
            return $item[$str_name];
        }
    }
    return '';
}


/**
 * 秒转为小时格式
 *
 */
function sec2time($sec)
{

    $sec = round($sec / 60);
    if ($sec >= 60) {
        $hour = floor($sec / 60);
        $min = $sec % 60;
        $res = $hour . ' : ';
        $min != 0 && $res .= $min . ' :';
    } else {
        $res = $sec . ' 分钟';
    }
    return $res;
}


/**
 * 格式化输出日期
 * @param $str
 * @return bool|string
 */
function fmt_datetime($str)
{
    if (empty($str)) {
        return '';
    }
    return date('Y-m-d H:i:s', $str);
}

/**
 * 生成唯一字符串 token
 */
function set_token()
{
    return md5(microtime(true));
}

/**
 *
 */
function txt_is_show($str = null){
    $arr = [
        0=> '否',
        1=> '是',
    ];
    if($str !== null){
       switch($str){
           case 0:
               return $arr[0];
           case 1:
               return $arr[1];
       }
    }
    return $arr;
}

/**
 * 生成指定长度字符串
 * @param int $length
 * @return null|string
 */
function create_char( $length = 7 ){
    $str = null;
    $strPol = "345678abcdefghjklmnpqrtuvwxy";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}

/**
 * 数组进行json_encode处理,保持其中的多字节字符不变,并实体化
 *
 * @param array $arr
 *            传递进来一个需要json_encode的数组
 * @return string 返回经过json_encode处理之后的字符串,其中中文保持不变
 */
function json_encode_zh($arr)
{
    return json_encode($arr,JSON_UNESCAPED_UNICODE);
}

/*
 * 手机号码隐蔽
 */
function hide_mobile($mobile){
    return  substr($mobile,0,3)."*****".substr($mobile,8,3);
}

//素材
function materialNumber_format($type){
    if($type == 1){
        $msgtype = 'text';
    }elseif($type == 2){
        $msgtype = 'news';
    }elseif($type == 3){
        $msgtype = 'image';
    }elseif($type == 4){
        $msgtype = 'voice';
    }elseif($type == 5){
        $msgtype = 'video';
    }
    return $msgtype;
}

/**
 * 前端调用生成url
 * /index.php?r=site/index&src=ref1#name
 * echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);
 * @return string
 */
function htmlUrl($params){
    return \yii\helpers\Url::toRoute($params);
}

