<?php
return array(
	//'配置项'=>'配置值'
	'DB_TYPE'               =>  'mysql', // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'ryks', // 数据库名
    'DB_USER'               =>  'root', // 用户名
    'DB_PWD'                =>  'root', // 密码
//    'DB_PWD'                =>  '1216568dc847d210', // 密码
    'DB_PORT'               =>  '3306', // 端口
    'DB_PREFIX'             =>  '', // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    =>  false, // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       =>  true, // 启用字段缓存
    'DB_CHARSET'            =>  'utf8', // 数据库编码默认采用utf8
    'SESSION_AUTO_START'	=>  true, //是否开启session
    'URL_MODEL'				=>  1, // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_CASE_INSENSITIVE'  =>  true,
	'DEFAULT_MODULE'        =>  'Home',  // 默认模块
	'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	'LOAD_EXT_FILE'			=>	'pay', //自动加载Common目录下载PHP文件
	//'TMPL_EXCEPTION_FILE'	=>  APP_PATH.'/Home/index.html', // 上线开启
	
	'phone_account'			=>	'C52116529', // 互亿短信账号
	'phone_psd'				=>  'fbcfd1ee0dcce48ad9b861d682e1cdcd', // 互亿短信密码
	'phone_time'			=>  '60', // 验证码发送时效
	'phone_time_verify'		=>  '300', // 验证码验证时效
	
	'push_AppKey'			=>  'd87df067d7b549fa95e94ea4', // 如邮快送 AppKey
	'push_Secret'			=>  '53088a93b090dee85197fa80', // 如邮快送 Master Secret
	'sj_push_AppKey'		=>  '44e22c56a8b3dfb80ee68196', // 如邮快送司机 AppKey
	'sj_push_Secret'		=>  '9a14b558c96d199907fbf55f', // 如邮快送司机 Master Secret

//	'web_address'			=>	'http://192.168.110.83/',
	'web_address'			=>	'http://www.rysas.com/',
	'admin_address'			=>	'http://www.rysas.com/index.php/Admin/',
//	'admin_address'			=>	'http://rysy.demo.dlshangcai.com/index.php/Admin/',
//	'admin_address'			=>	'http://192.168.110.83/index.php/Admin/',
	'upload_path'			=>	'Public/images',
);