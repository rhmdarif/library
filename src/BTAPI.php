<?php

namespace rhmdarif\Library;

/**
 * 宝塔API接口示例Demo
 * 仅供参考，请根据实际项目需求开发，并做好安全处理
 * date 2018/12/12
 * author 阿良
 */
class BTAPI
{
	private $BT_KEY = "thVLXFtUCCNzBShBweKTPBmw8296q8R8";  //接口密钥
	private $BT_PANEL = "http://192.168.1.245:8888";	   //面板地址

	//如果希望多台面板，可以在实例化对象时，将面板地址与密钥传入
	public function __construct($bt_panel = null, $bt_key = null)
	{
		if ($bt_panel) $this->BT_PANEL = $bt_panel;
		if ($bt_key) $this->BT_KEY = $bt_key;
	}


	public function AddSite($domain, $port = '80', $path, $php_version, $ftp = false, $sql = 'MySQL', $sql_datauser, $sql_datapassword, $set_ssl = 0, $force_ssl = 0)
	{
		$url = $this->BT_PANEL . '/site?action=AddSite';

		$p_data_1 = $this->GetKeyData();
		$p_data_2 = [
			'webname'	=>	'{"domain":"' . $domain . '","domainlist":[],"count":0}',
			'type'		=> 'PHP',
			'port'		=> $port,
			'ps'		=> str_replace('.', '_', $domain),
			'path'		=> $path,
			'type_id' => 0,
			'version' => $php_version,
			'ftp'		=> $ftp,
			'sql'		=> $sql,
			'datauser'	=> $sql_datauser,
			'datapassword'	=> $sql_datapassword,
			'codeing'		=> 'utf8',
			'set_ssl'		=> $set_ssl,
			'force_ssl'	=> $force_ssl,
		];
		$p_data = array_merge($p_data_1, $p_data_2);

		//请求面板接口
		$result = $this->HttpPostCookie($url, $p_data);

		//解析JSON数据
		$data = json_decode($result, true);
		return $data;
	}

	public function searchSite($domain, $key = null)
	{
		$url = $this->BT_PANEL . '/data?action=getData';

		$p_data_1 = $this->GetKeyData();
		$p_data_2 = [
			"table" => 'sites',
			'limit' => 20,
			'p' => 1,
			'search' => $domain,
			'type' => -1
		];
		$p_data = array_merge($p_data_1, $p_data_2);

		$result = $this->HttpPostCookie($url, $p_data);
		$data = json_decode($result, true);
		return (is_numeric($key)) ? $data['data'][$key] : $data['data'];
	}

	public function SiteStatus($domain)
	{
		$search = $this->searchSite($domain, 0);
        if(isset($search['status'])) {
            $type = ($search['status'] == "0") ? "SiteStart" : "SiteStop";
            $url = $this->BT_PANEL . '/site?action=' . $type;

            $p_data_1 = $this->GetKeyData();
            $p_data_2 = [
                'id' => $search['id'],
                'name' => $search['name'],
            ];
            $p_data = array_merge($p_data_1, $p_data_2);

            $result = $this->HttpPostCookie($url, $p_data);
            $data = json_decode($result, true);
        } else {
            $data = [];
        }
		return $data;
	}

	//示例取面板日志	
	public function GetLogs()
	{
		//拼接URL地址
		$url = $this->BT_PANEL . '/data?action=getData';

		//准备POST数据
		$p_data = $this->GetKeyData();		//取签名
		$p_data['table'] = 'logs';
		$p_data['limit'] = 10;
		$p_data['tojs'] = 'test';

		//请求面板接口
		$result = $this->HttpPostCookie($url, $p_data);

		//解析JSON数据
		$data = json_decode($result, true);
		return $data;
	}


	/**
	 * 构造带有签名的关联数组
	 */
	private function GetKeyData()
	{
		$now_time = time();
		$p_data = array(
			'request_token'	=>	md5($now_time . '' . md5($this->BT_KEY)),
			'request_time'	=>	$now_time
		);
		return $p_data;
	}


	/**
	 * 发起POST请求
	 * @param String $url 目标网填，带http://
	 * @param Array|String $data 欲提交的数据
	 * @return string
	 */
	private function HttpPostCookie($url, $data, $timeout = 60)
	{
		//定义cookie保存位置
		$cookie_file = './' . md5($this->BT_PANEL) . '.cookie';
		if (!file_exists($cookie_file)) {
			$fp = fopen($cookie_file, 'w+');
			fclose($fp);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
}
