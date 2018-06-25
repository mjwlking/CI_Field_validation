####作用####
用于快速校验字段的值是否符合要求，适合于注册表单等等提交过来的值进行校验。
####程序中使用示例：####
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
    //返回值为数组 是否验证通过：$return_data['result']; //值为truhe或者false 
    //如果false，错误信息为$return_data['message']
####使用说明：####
1. 本类库中的自定义函数，用户可以放在core_helper.php中。。同样，core_helper.php中自定义函数，只要返回值是true或者false，都可以加到验证规则里。
2. 使用field_validation->valid_rule（规则数组，要校验的字段数组）时候。规则数组里的字段名，一定要在post字段中有，否则会出错。
3. is_unique 是验证数据库表是否有重复字段，格式是 表名.字段名。
4. 参数要放在[]中，目前没有设计多参数。所以使用一个参数。
5. 可以在rules字符串里使用callback_方法名，则本方法会使用 $this->方法名(值,[参数])的方式回调本方法。
6. field_validation.php放在application/libraries目录下。加载时候用：$this->load->library('field_validation');