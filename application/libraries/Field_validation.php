<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by FrogCI.
 * User: 292885666@qq.com
 * Date: 2018/6/25
 * Time: 18:45
 * 程序中使用示例：
 *
    $this->load->library('field_validation');
    $post_data['data'] = array(
    'username' => 'admin',
    'email' => 'admin@admin.com',
    'password' => '123456',
    'repeat' => '123456',
    'nickname' => '管理员'
    );
    $rules_array = array(
    'username' => 'required|max_length[15]|min_length[4]|is_unique[users.username]|alpha_numeric',
    'email' =>'required|valid_email|is_unique[users.email]|is_card',
    'password' => 'required|max_length[25]|min_length[6]|alpha_numeric',
    'repeat' => 'required|matches[password]'
    );
    $return_data = $this->field_validation->valid_rule($rules_array,$post_data['data']);
 * 返回值为数组 是否验证通过：$return_data['result']; //值为truhe或者false
 * 如果false，错误信息为$return_data['message']
 *
 * 使用说明：
    A、本类库中的自定义函数，用户可以放在core_helper.php中。。同样，core_helper.php中自定义函数，只要返回值是true或者false，都可以加到验证规则里。
    B、使用field_validation->valid_rule（规则数组，要校验的字段数组）时候。
    规则数组里的字段名，一定要在post字段中有，否则会出错。
    C、is_unique 是验证数据库表是否有重复字段，格式是 表名.字段名。
    D、参数要放在[]中，目前没有设计多参数。所以使用一个参数。
    E、可以在rules字符串里使用callback_方法名，则本方法会使用 $this->方法名(值,[参数])的方式回调本方法。
    F、field_validation.php放在application/libraries目录下。加载时候用：$this->load->library('field_validation');
 */
class Field_validation
{
    function __construct()
    {
    }

    /**
     * 字段规则校验
     * 条件：rules和post两个数组，post的规则必须在rules里体现，校验函数必须返回true 或者 false
     * @param $rules_array
     * @return array 返回值为数组，bool result 是否正确 和 string message 错误信息
     */
    public function valid_rule($rules_array,$post_data)
    {
        // CI instance
        $CI =& get_instance();
        $CI->load->library('form_validation');
        //特定的函数错误反馈语句
        $error_message = array(
            'required' => '%filed 的值不能为空',
            'matches' => '%filed 的值和 %param 的字段值不一致', //matches[$post_data另一个KEY]
            'differs' => '%filed 的值和 %param 的字段值一致', //matches[$post_data另一个KEY]
            'regex_match' => '%filed 的值不符合指定正则要求', //regex_match[/regex/]
            'is_unique' => '%filed 的值已经存在，该值必须唯一',
            'alpha_numeric' => '只能是数字和字母',
            'min_length' => '长度不能小于 %param 位',
            'max_length' => '长度不能大于 %param 位',
            'exact_length' => '长度必须等于 %param 位',
            'greater_than' => '%filed 的值不能小于等于 %param,并且必须是数字',
            'greater_than_equal_to' => '%filed 的值不能小于 %param,并且必须是数字',
            'less_than' => '%filed 的值不能大于等于 %param,并且必须是数字',
            'less_than_equal_to'  => '%filed 的值不能大于 %param,并且必须是数字',
            'in_list' => '%filed 的值必须在 %param 列表中',//in_list[red,blue,green]
            'valid_ip' => '%filed 的值不是有效的IP地址', //可选参数valid_ip(ip_address, 'ipv6'|'ipv4')
            'alpha' => '%filed 的值只能是字母',
            'alpha_numeric' => '%filed 的值只能是字母和数字',
            'alpha_numeric_spaces' => '%filed 的值只能包含字母、数字和空格',
            'alpha_dash' => '%filed 的值只能包含字母/数字/下划线/破折号',
            'numeric' => '%filed 的值只能是数字', //+-.都包含 十六进制 8进制也包含
            'integer' => '%filed 的值只能是正整数',
            'decimal' => '%filed 的值只能是十进制数字',
            'is_natural' => '%filed 的值只能是不包括0的自然数', //如果元素值包含了非自然数的其他数值 （不包括零），返回 FALSE 自然数形如：0、1、2、3 .... 等等。
            'is_natural_no_zero' => '%filed 的值只能是自然数', //如果元素值包含了非自然数的其他数值 （包括零），返回 FALSE 自然数形如：1、2、3 .... 等等。
            'valid_url' => '%filed 的值不是正确的url',
            'valid_email' => '%filed 的值不是正确的Email地址',
            'valid_emails' => '%filed 的值不是,号分割的Email地址', //如果元素值包含不合法的 email 地址（地址之间用逗号分割），返回 FALSE
            'valid_base64' => '%filed 的值不是正确的base64值',
            //以下是自定义的验证函数
            'is_date' => '%filed 的值不是正确的日期值', //可选参数，规定日期/时间的格式is_date(date_value, $format='Y-m-d')
            'is_datetime' => '%filed 的值不是正确的日期时间格式', //上述函数的固定格式验证 只验证Y-m-d H:i:s这种格式
            'is_time' => '%filed 的值不是正确的时间格式', //验证 00:00:00 这种格式
            'is_image' => '%filed 不是本地图片', //逻辑：file_exists() 函数检查文件或目录是否存在。如果存在，然后用getimagesize来判断是否是图片
            'is_chinese' => '%filed 的值必须是中文',
            'is_english' => '%filed 的值必须是英文',
            'is_card'  => '%filed 不是合法的身份证号码', //是否为合法的身份证(支持15位和18位)
            'is_price' => '%filed 不是合法的价格数字',
            'is_mobile' => '%filed 不是正确的手机号'
        );
        //必须要参数的验证规则
        $rule_need_param = array(
            'regex_match',
            'is_unique',
            'min_length',
            'max_length',
            'exact_length',
            'greater_than',
            'greater_than_equal_to',
            'less_than',
            'less_than_equal_to',
            'in_list'
        );
        $return_data = array(
            'result' => FALSE,
            'message' => ''
        );
        if(is_array($rules_array) && is_array($post_data))
        {
            foreach ($rules_array as $filed => $rules)
            {
                //要校验的值
                $valid_value = $post_data[$filed];
                //验证是否通过标识
                $callable = FALSE;
                //规则拆分成数组
                $rules = preg_split('/\|(?![^\[]*\])/', $rules);
                //$r = $this->form_validation->is_unique($post_data['data']['username'],'users.username');
                foreach ($rules as $rule)
                {
                    if (is_string($rule))
                    {
                        //判断是否带参数的，需要回调的方法
                        $param = FALSE;
                        if (preg_match('/(.*?)\[(.*)\]/', $rule, $match))
                        {
                            $rule = $match[1];
                            $param = $match[2];
                        }
                        //var_dump($param.' '.$rule);
                        //这里做个hook，跳过$this->form_validation->matches和differs方法，因为我们是用数组判断，对方是用form的input name来判断
                        if($rule == 'matches')
                        {
                            //必须要有参数
                            if($param)
                            {
                                $callable = ($valid_value == $post_data[$param])? TRUE : FALSE;
                            }
                            else
                            {
                                $callable = FALSE;
                                $return_data['message'] = '对比的字段必须填写';
                            }
                        }
                        else if($rule == 'differs')
                        {
                            //必须要有参数
                            if($param)
                            {
                                $callable = ($valid_value !== $post_data[$param])? TRUE : FALSE;
                            }
                            else
                            {
                                $callable = FALSE;
                                $return_data['message'] = '对比的字段必须填写';
                            }
                        }
                        //必须要填写参数的规则
                        else if (in_array($rule,$rule_need_param) && !$param)
                        {
                            $callable = FALSE;
                        }
                        //是否是内置函数
                        else if (function_exists($rule))
                        {
                            if($param)
                            {
                                $callable = $rule($valid_value,$param);
                            }
                            else
                            {
                                $callable = $rule($valid_value);
                            }
                        }
                        //因为我们自动加载了form_validation，所以用method_exists来判断，速度比较快。
                        else if(method_exists($CI->form_validation,$rule))
                        {
                            if($param)
                            {
                                $callable = $CI->form_validation->$rule($valid_value,$param);
                            }
                            else
                            {
                                $callable = $CI->form_validation->$rule($valid_value);
                            }
                        }
                        else if (strpos($rule, 'callback_') === 0)
                        {
                            $rule = substr($rule, 9);
                            //看这个回调函数是否可以用
                            if (is_callable(array($this,$rule)))
                            {
                                if($param)
                                {
                                    $callable = $this->$rule($valid_value,$param);
                                }
                                else
                                {
                                    $callable = $this->$rule($valid_value);
                                }
                            }
                        }
                        else
                        {
                            $return_data['message'] = "规则{$rule}不存在";
                        }
                        //如果可以校验，则进行校验
                        if(!$callable)
                        {
                            $message = $error_message[$rule];
                            $message = str_replace("%filed", $filed, $message);
                            $message = str_replace("%param", $param, $message);
                            $return_data['message'] = $message;
                            break;
                        }
                    }
                    else
                    {
                        $return_data['message'] = '规则必须是字符串';
                        break;
                    } //规则是否是字符串判断
                } //规则字符串解析 /foreach
                //验证错误退出循环
                if(!$callable)
                {
                    //break;
                }
                else
                {
                    $return_data['result'] = TRUE;
                }
            } //要验证的字符数组循环 /foreach
        }
        else
        {
            $return_data['message'] = '校验的字段必须是数组格式';
        } //参数数组判断 /if
        return $return_data;
    }

    /**
     * 是否是手机号
     * @param string $mobile
     * @return bool
     */
    function is_mobile($mobile=''){

        if (strlen ( $mobile ) != 11 || ! preg_match ( '/^1[2|3|4|5|8|6|7|8|9][0-9]\d{4,8}$/', $mobile )) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * 是否为价格
     * @param float $number
     * @return boolean
     */
    function is_price($number = 0){
        if(preg_match('/^[-\+]?\d+(\.\d+)?$/',$number)){
            return true;
        }else{
            return false;
        }

    }

    /**
     * 是否为合法的身份证(支持15位和18位)
     * @param string $card
     * @return boolean
     */
    function is_card($card){
        if(preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/',$card)||preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/',$card))
            return true;
        else
            return false;
    }

    /**
     * 是否为英文
     * @param string $str
     * @return boolean
     */
    function is_english($str){
        return ctype_alpha($str);
    }

    /**
     * 是否为中文
     * @param string $str
     * @return boolean
     */
    function is_chinese($str){
        if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$str))
            return true;
        else
            return false;
    }

    /**
     * 判断是否为图片
     * @param string $file  图片文件路径
     * @return boolean
     */
    function is_image($file){
        if(file_exists($file)&&getimagesize($file===false)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 是否为时间格式
     * @param time $value
     * @return boolean
     */
    function is_time($value=''){
        if(preg_match('/^\d{2}[:]\s*\d{2}[:]\s*\d{2}$/',$value)){
            return true;
        }else{
            return false;
        }

    }

    /**
     * 是否是日期时间格式
     * @param $date
     * @return bool
     */
    function is_datetime($date)
    {

        if ($date == date('Y-m-d H:i:s', strtotime($date))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证日期格式是否正确
     * @param string $date
     * @param string $format
     * @return boolean
     */
    function is_date($date,$format='Y-m-d'){
        $t = date_parse_from_format($format,$date);
        if(empty($t['errors'])){
            return true;
        }else{
            return false;
        }
    }
}