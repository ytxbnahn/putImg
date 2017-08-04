<?php
/**
 * Created by PhpStorm.
 * User: slyvanas_mhb
 * Date: 2017-5-5
 * Time: 15:59
 *
 *        $_SESSION['guest'] = [id,name,phone,portrait];序列化
 *        $_SESSION['validateCode'] = {phone1:[phone1,code1,expiretime1,id1,status1],phone2:[phone2,code2,expiretime2,id2,status1]...};//序列化
 *        COOKIE(id@phone@name)
 *
 */

include_once "../lib/commonstr.php";
include_once "../lib/commonImg.php";


class test
{
    var $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
        if (!isset($_SESSION)) {
            session_start();
        }

    }

    function __destruct()
    {
    }

    public function outstream($name, $data)
    {
    }


    public function main()
    {

        echo "<pre>";
        echo 'test' . "\r\n";

        session_start();
//        var_dump($result, $_SESSION['testID']);


//        $_SESSION['testID'] = date("Y-m-d H:i:s") . 'mhbtest';
//        $_SESSION['gusetID'] = date("Y-m-d H:i:s") . 'mhbguest';

        setcookie('guest', "@mhb@12121992", time() + 3677 * 24 * 365, '/');
        var_dump($_SESSION['testID']);
        var_dump($_SESSION['guestID']);
        var_dump($_COOKIE);


    }

    public function checkUserPhoneAndName($phone, $name)
    {
        $sql = sprintf("SELECT tlu_id,tlu_nickname,tlu_portrait,tlu_phone  
                    FROM t_luke_users WHERE tlu_nickname = '%s' AND tlu_phone = '%s'  LIMIT 1", $name, $phone);
        $res = $this->registry->dao->dbobject->queryExcute($sql);
        if ($res < 0) {
            return false;
        } else {
            return $res;
        }
    }

    public function checkPhoneRegisted($phone)//检测手机号是否已经被注册
    {
        $sql = sprintf("SELECT COUNT(1) total FROM t_luke_users WHERE tlu_phone = '%s' ", $phone);
        $res = $this->registry->dao->dbobject->selectExcute($sql);
        if ($res < 0) {
            return false;
        } else {
            return (int)$res[0]['total'];
        }
    }

    public function checkNameRegisted($name)//检测名称是否未被占用
    {
        $sql = "SELECT COUNT(1) total FROM t_luke_users WHERE  tlu_nickname = '$name' ";
        $res = $this->registry->dao->dbobject->selectExcute($sql);
        if ($res < 0) {
            return false;
        } else {
            return (int)$res[0]['total'];
        }
    }

    public function checkGuestInfo()
    {
        if (empty($_SESSION['guest'])) {//session未创建,则 初始化 会话
            if (empty($_COOKIE['guestInfo'])) {//cookie未创建,用户需登录
                header("location:../route/ddl_ctrl..php?A&B&C&redirect_url=" . $_SERVER['REQUEST_URI']);
                return true;
            } else {//用户保留cookie,免登录
                $guest_info = explode("@", $_COOKIE['guest']);
                $guest_phone = $guest_info[1];
                $guest_name = $guest_info[0];
                $checkUserIdAndName = $this->checkUserPhoneAndName($guest_phone, $guest_name);
                if ($checkUserIdAndName === false || empty($checkUserIdAndName)) {//身份信息错误
                    header("location:../route/ddl_ctrl..php?A&B&C&redirect_url=" . $_SERVER['REQUEST_URI']);
                    return true;
                } else {//身份信息审核正确，创建session
                    $_SESSION['guset'] = "name@phone@portrait";

                }
            }
        }
        return true;
    }

    public function sendCode($txt, $phone)
    {
        //todo 短信服务
        return true;
    }

    function create_guid($namespace = null)
    {
        static $guid = '';
        $uid = uniqid("", true);

        $data = $namespace;
        $data .= $_SERVER ['REQUEST_TIME'];     // 请求那一刻的时间戳
        $data .= $_SERVER ['HTTP_USER_AGENT'];  // 获取访问者在用什么操作系统
        $data .= $_SERVER ['SERVER_ADDR'];      // 服务器IP
        $data .= $_SERVER ['SERVER_PORT'];      // 端口号
        $data .= $_SERVER ['REMOTE_ADDR'];      // 远程IP
        $data .= $_SERVER ['REMOTE_PORT'];      // 端口信息

        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = '{' . substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12) . '}';

        return $guid;
    }

    function create_short_guid($len)
    {

        $chars_array = array(
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z",
        );
        $charsLen = count($chars_array) - 1;

        $outputstr = "";
        for ($i = 0; $i < $len; $i++) {
            $outputstr .= $chars_array[mt_rand(0, $charsLen)];
        }
        return $outputstr;
    }

}

class init extends test
{

    public function main()
    {

        if (empty($_SESSION['guest'])) {//session未创建,则 初始化 会话
            if (empty($_COOKIE['guestInfo'])) {//cookie未创建,用户需自主登录
                header("location:../route/ddl_ctrl.php?luke&log&loginGuest&redirect_url=.." . $_SERVER['REQUEST_URI']);
                return true;
            } else {//用户保留cookie,免登录
                $guest_info = explode("@", $_COOKIE['guest']);
                $guest_phone = $guest_info[1];
                $guest_name = $guest_info[0];
                $checkUserIdAndName = $this->checkUserPhoneAndName($guest_phone, $guest_name);
                if (!$checkUserIdAndName) {//身份信息错误
                    header("location:../route/ddl_ctrl.php?luke&log&loginGuest&redirect_url=.." . $_SERVER['REQUEST_URI']);
                    return true;
                } else {//身份信息审核正确，创建session
                    $data = array((int)$checkUserIdAndName[0]['tlu_id'], $guest_name, $guest_phone, $checkUserIdAndName[0]['tlu_portrait']);
                    $_SESSION['guest'] = serialize($data);
                }
            }
        }
        $user_info = unserialize($_SESSION['guest']);

        $inject_js =
            'var m_guest_id = ' . $user_info[0] . ';' . "\r\n" .
            'var m_guest_name = "' . $user_info[1] . '";' . "\r\n" .
            'var m_guest_phone = "' . $user_info[2] . '";' . "\r\n" .
            'var m_guest_portrait = "' . $user_info[3] . '";' . "\r\n";;
        ddl_output_view($inject_js, '');
    }
}


//登录
class loginGuest extends test
{

    public function main()
    {
        if (empty($_REQUEST['name'])) {
            return jsonout(false, 400, '请提供登录名');
        }
        if (empty($_REQUEST['pwd'])) {
            return jsonout(false, 400, '请提供密码');
        }
        $name = trimall($_REQUEST['name']);
        $pwd = trimall($_REQUEST['pwd']);
        $sql = sprintf("SELECT tlu_id,tlu_nickname,tlu_portrait,tlu_phone FROM  t_luke_users WHERE
                tlu_nickname  = '%s' AND  tlu_pwd = '%s' LIMIT 1", $name, $pwd);
        $getUserInfo = $this->registry->dao->dbobject->selectExcute($sql);
        if ($getUserInfo < 0) {
            return jsonout(false, 500, '服务器异常');
        }
        if (empty($getUserInfo)) {
            $phone = $name;
            $checkPhoneFormat = pregPN($phone);
            if ($checkPhoneFormat) {
                $sql = sprintf("SELECT tlu_id,tlu_nickname,tlu_portrait,tlu_phone FROM  t_luke_users WHERE
                tlu_phone  = '%s' AND  tlu_pwd = '%s' LIMIT 1", $name, $pwd);
                $getUserInfo = $this->registry->dao->dbobject->selectExcute($sql);
                if ($getUserInfo < 0) {
                    return jsonout(false, 500, '服务器异常');
                }
                if (empty($getUserInfo)) {
                    return jsonout(false, 40001, '错误的账号或密码');
                }
            }

        }
        $id = $getUserInfo[0]['tlu_id'];
        $portrait = $getUserInfo[0]['tlu_portrait'];
        $phone = $getUserInfo[0]['tlu_phone'];
        //创建cookie
        setcookie('guestInfo', $name . "@" . $phone . "@" . $portrait, time() + 365 * 24 * 3600, "/");
        //创建session
        $data = array($id, $name, $phone, $portrait);
        $_SESSION['guest'] = serialize($data);


        return jsonout(true, 200, 'OK');
    }
}


//注册
class registGuest extends test
{
    public function getValidateStatus($id)
    {
        $sql = "SELECT t_lpv_result FROM t_luke_phone_validate WHERE t_lpv_id = $id";
        $res = $this->registry->dao->dbobject->selectExcute($sql);
        if ($res < 0) {
            return false;
        } else {
            return $res;
        }
    }

    public function updatePhcodeStatus($phcode_id, $status)
    {
        //更新验证结果
        $sql = "UPDATE t_luke_phone_validate SET t_lpv_result = $status,t_lpv_validate_time = now() 
                WHERE t_lpv_id =  " . $phcode_id;
        $updateResult = $this->registry->dao->dbobject->queryExcute($sql);
        if ($updateResult < 0 || empty($updateResult)) {
            return false;
        }
        return true;
    }

    public function main()
    {
//        if (empty($_REQUEST['name'])) {
//            return jsonout(false, 400, '请提供登录名');
//        }
        if (empty($_REQUEST['pwd'])) {
            return jsonout(false, 400, '请提供密码');
        }
        if (empty($_REQUEST['phone'])) {
            return jsonout(false, 400, '请提供手机号');
        }
        if (empty($_REQUEST['phcode'])) {
            return jsonout(false, 400, '请提供短信验证码');
        }
        $phone = trimall($_REQUEST['phone']);//手机号
        $name = !empty($_REQUEST['name']) ? trimall($_REQUEST['name']) : "";//登陆名
        $pwd = trimall($_REQUEST['pwd']);//密码
        $phcode = trimall($_REQUEST['phcode']);//手机验证码


        //todo 检查 手机 唯一
        $checkPhoneFormat = pregPN($phone);
        if (!$checkPhoneFormat) {
            return jsonout(false, 40001, '手机号格式错误');
        }
        //检查手机是否已经被注册
        $checkPhoneRegisted = $this->checkPhoneRegisted($phone);
        if ($checkPhoneRegisted === false) {
            return jsonout(false, 50001, '检查手机号注册唯一性失败');
        }
        if ($checkPhoneRegisted > 0) {
            return jsonout(false, 40002, '该手机号已被使用');
        }
        //todo 检查 名称 唯一
        if (!empty($name)) {
            $checkNameRegisted = $this->checkNameRegisted($name);
            if ($checkNameRegisted === false) {
                return jsonout(false, 50002, '检查名称注册唯一性失败');
            }
            if ($checkNameRegisted > 0) {
                return jsonout(false, 40003, '该名称已被使用');
            }
        }


        if (!isset($_SESSION)) {
            session_start();
        }
        if (empty($_SESSION['validateCode'])) {
            return jsonout(false, 40004, '未获取到预设验证请求');
        }
        $vali_array = unserialize($_SESSION['validateCode']);
        error_log(print_r($vali_array, 1));
        if (!isset($vali_array[$phone]) || empty($vali_array[$phone])) {
            return jsonout(false, 40005, '请先验证手机号');
        }

        $session_phone = $vali_array[$phone][0];//session正确手机号
        $session_code = $vali_array[$phone][1];//session正确手机验证文本
        $session_expire = (int)$vali_array[$phone][2];//session正确验证截止时间
        $session_result_id = (int)$vali_array[$phone][3];//日志id
        $session_result_status = (int)$vali_array[$phone][4];//状态，0未验证,1验证成功

//        if ($session_result_status != 1) {
//            return jsonout(false, 40006, '请先验证手机号');
//        }
        if ($session_code != $phcode) {
            $this->updatePhcodeStatus($session_result_id, -1);
            return jsonout(false, 40007, '验证码错误');
        }
//        if (time() > $session_expire) {
//            $this->updatePhcodeStatus($session_result_id, -1);
//            return jsonout(false, 40008, '验证码已过期,请重新获取验证码');
//        }
        //更新session结果
        $vali_array[$phone][4] = 1;
        $_SESSION['validateCode'] = serialize($vali_array);
        $this->updatePhcodeStatus($session_result_id, 1);


        //手机号,名称唯一,创建用户记录
        $sql = sprintf("INSERT INTO  t_luke_users SET 
                tlu_nickname = '%s', tlu_pwd = '%s',tlu_phone = '%s'", $name, $pwd, $phone);
        $insertNewUser = $this->registry->dao->dbobject->queryExcute($sql);
        if ($insertNewUser < 0) {
            return jsonout(false, 50003, '创建用户记录失败');
        }
        //清空 验证 session
        $_SESSION['validateCode'] = array();


        //创建cookie
        setcookie('guestInfo', $name . "@" . $phone . "@" . " ", time() + 365 * 24 * 3600, "/");
        //创建session
        $data = array($insertNewUser, $name, $phone, " ");
        $_SESSION['guest'] = serialize($data);

        return jsonout(true, 200, 'OK');

    }
}

//发送手机验证码
class sendValidateCode extends test
{
    public function main()
    {
        if (empty($_REQUEST['phone'])) {
            return jsonout(false, 40001, '请提供手机号');
        }
        $phone = trimall($_REQUEST['phone']);
        $checkPhoneFormat = pregPN($phone);
        if (!$checkPhoneFormat) {
            return jsonout(false, 40002, '手机号格式错误');
        }
        //检查手机是否已经被注册
        $checkPhoneRegisted = $this->checkPhoneRegisted($phone);
        if ($checkPhoneRegisted === false) {
            return jsonout(false, 50001, '检查手机号注册唯一性失败');
        }
        if ($checkPhoneRegisted > 0) {
            return jsonout(false, 40003, '该手机号已被使用');
        }

        $code = $this->create_short_guid(6);
        $code = "abcd12";

        //写入日志表
        $sql = sprintf("INSERT INTO t_luke_phone_validate SET t_lpv_phone = '%s',t_lpv_code = '$code',
                            t_lpv_create_time = NOW(),t_lpv_result = 0 ", $phone);
        $insertRec = $this->registry->dao->dbobject->queryExcute($sql);
        if ($insertRec < 0) {
            return jsonout(false, 500, '创建日志记录失败');
        }
        //发送短信
        $send_code = $this->sendCode($code, $phone);
        if ($send_code) {//发送成功,创建session存储

            if (!isset($_SESSION)) {
                session_start();
            }
            $current_validateCode = array();
            if (!empty($_SESSION['validateCode'])) {
                $current_validateCode = unserialize($_SESSION['validateCode']);
            }
            $info = array($phone, $code, time() + 600, $insertRec, 0);
            $current_validateCode[$phone] = $info;
            $_SESSION['validateCode'] = serialize($current_validateCode);
            return jsonout(true, 200, 'OK');
        } else {
            return jsonout(false, 500, 'Fail to send note,hint:' . $send_code);
        }
    }
}

//验证手机号
class validatePhone extends test
{
    public function main()
    {
        if (empty($_REQUEST['phone'])) {
            return jsonout(false, 40001, '请提供手机号');
        }
        if (empty($_REQUEST['phcode'])) {
            return jsonout(false, 40002, '请提供手机验证码');
        }
        $phone = trimall($_REQUEST['phone']);
        $code = trimall($_REQUEST['phcode']);
        $checkPhoneFormat = pregPN($phone);//验证手机格式
        if (!$checkPhoneFormat) {
            return jsonout(false, 40003, '手机号格式错误');
        }
        //检查手机是否已经被注册
        $checkPhoneRegisted = $this->checkPhoneRegisted($phone);
        if ($checkPhoneRegisted === false) {
            return jsonout(false, 50001, '检查手机号注册唯一性失败');
        }
        if ($checkPhoneRegisted > 0) {
            return jsonout(false, 40004, '该手机号已被使用');
        }


        if (!isset($_SESSION)) {
            session_start();
        }
        if (empty($_SESSION['validateCode'])) {
            return jsonout(false, 400005, '未获取到预设验证请求');
        }
        $vali_array = unserialize($_SESSION['validateCode']);
        error_log(print_r($vali_array, 1));
        if (!isset($vali_array[$phone]) || empty($vali_array[$phone])) {
            return jsonout(false, 40006, '手机号已变更,请重新获取验证码');
        }

        $session_phone = $vali_array[$phone][0];//session正确手机号
        $session_code = $vali_array[$phone][1];//session正确手机验证文本
        $session_expire = (int)$vali_array[$phone][2];//session正确验证截止时间
        $session_result_id = (int)$vali_array[$phone][3];//日志id
        $session_result_status = (int)$vali_array[$phone][4];//状态，0未验证,1验证成功

        if ($session_code != $code) {
            return jsonout(false, 40007, '验证码错误');
        }
        if (time() > $session_expire) {
            return jsonout(false, 40008, '验证码已过期,请重新获取验证码');
        }
        //更新session结果
        $vali_array[$phone][4] = 1;
        $_SESSION['validateCode'] = serialize($vali_array);
        //更新验证结果
        $sql = "UPDATE t_luke_phone_validate SET t_lpv_result = 1,t_lpv_validate_time = now() 
                WHERE t_lpv_id =  " . $session_result_id;
        $updateResult = $this->registry->dao->dbobject->queryExcute($sql);

        return jsonout(true, 200, "OK");
    }
}

//补全信息
class completeGuest extends test
{
    public function main()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (empty($_SESSION['guest'])) {
            return jsonout(false, 400, '请先登录');
        }
        $guestInfo = unserialize($_SESSION['guest']);
        $guest_id = $guestInfo[0];//路客用户id

        $nickname = isset($_REQUEST['nickname']) && !empty($_REQUEST['nickname']) ? trimall($_REQUEST['nickname']) : "";
        $province = isset($_REQUEST['province']) && !empty($_REQUEST['province']) ? trimall($_REQUEST['province']) : "";
        $city = isset($_REQUEST['city']) && !empty($_REQUEST['city']) ? trimall($_REQUEST['city']) : "";
        $district = isset($_REQUEST['district']) && !empty($_REQUEST['district']) ? trimall($_REQUEST['district']) : "";
        $address = isset($_REQUEST['address']) && !empty($_REQUEST['address']) ? trimall($_REQUEST['address']) : "";
        $birthdate = isset($_REQUEST['birthdate']) && !empty($_REQUEST['birthdate']) ? date("Y-m-d", strtotime($_REQUEST['birthdate'])) : "";
        $motto = isset($_REQUEST['motto']) && !empty($_REQUEST['motto']) ? trimall($_REQUEST['motto']) : "";

        $sql_extra = "";
        if (!empty($nickname)) {
            $sql_extra .= sprintf(",tlu_nickname = '%s'", $nickname);
        }
        if (!empty($province)) {
            $sql_extra .= sprintf(",tlu_province = '%s'", $province);
        }
        if (!empty($city)) {
            $sql_extra .= sprintf(",tlu_city = '%s'", $city);
        }
        if (!empty($district)) {
            $sql_extra .= sprintf(",tlu_district = '%s'", $district);
        }
        if (!empty($address)) {
            $sql_extra .= sprintf(",tlu_address = '%s'", $address);
        }
        if (!empty($birthdate)) {
            $sql_extra .= sprintf(",tlu_birthdate = '%s'", $birthdate);
        }
        if (!empty($motto)) {
            $sql_extra .= sprintf(",tlu_motto = '%s'", $motto);
        }

        $tag_dir = "../app_data/" . $this->registry->CustomID;
        if (!is_dir($tag_dir)) {
            mkdir($tag_dir);
        }
        $tag_dir .= "/luke-user";
        if (!is_dir($tag_dir)) {
            mkdir($tag_dir);
        }
        $tag_dir .= "/user" . $guest_id;
        $tag_file = "";
        if (!empty($_FILES['portrait']['tmp_name'])) {
            $tag_file = $tag_dir . "/" . time() . "_portrait_" . '.jpg';
            $uploadImg = uploadImg($_FILES['portrait']['tmp_name'], $tag_dir, $tag_file, 90, true);
            $sql_extra .= sprintf(",tlu_portrait = '%s'", $tag_file);
        }
        $sql = "UPDATE t_luke_users SET  tlu_update_time =NOW()" . $sql_extra . " WHERE  tlu_id = $guest_id";
        $res = $this->registry->dao->dbobject->queryExcute($sql);
        if ($res < 0) {
            return jsonout(false, 500, '更新出错');
        }

        return jsonout(true, 200, "OK");
    }
}



