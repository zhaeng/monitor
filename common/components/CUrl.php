<?php
namespace common\components;

/**
 * CUrl助手模型
 * ----------------
 * @version 1.0.0
 * @author yuan
 */
class CUrl
{
	public $data = [];
	public $timeout = null;
	public $referer = null;
	public $agent = null;
	public $header = null;
	public $proxy = null;

	public $httpType = 'http';
	public $httpInfo = null;

	/**
	 * get请求
	 * @param string $url 请求地址
	 * @param string $httpType 访问类型 http/https
	 * @param string $dataType 返回类型 null/json
	 * @return string $result 返回内容
	 */
	public function get($url, $httpType = 'http', $dataType = null)
	{
		$this->httpType = $httpType;
		return $this->_httpRequest('GET', $url, $dataType);
	}

	/**
	 * post请求
	 * @param string $url 请求地址
	 * @param string $httpType 访问类型 http/https
	 * @param string $dataType 返回类型 null/json
	 * @return string | array $result 返回内容
	 */
	public function post($url, $httpType = 'http', $dataType = null)
	{
		$this->httpType = $httpType;
		return $this->_httpRequest('POST', $url, $dataType);
	}

	/**
	 * head请求
	 * @param string $url 请求地址
	 * @param string $httpType 访问类型 http/https
	 * @return string $result 返回内容
	 */
	public function head($url, $httpType = 'http')
	{
		$this->httpType = $httpType;
		return $this->_httpRequest('HEAD', $url);
	}

	/**
	 * put请求
	 * @param string $url 请求地址
	 * @param string $httpType 访问类型 http/https
	 * @param string $dataType 返回类型 null/json
	 * @return string $result 返回内容
	 */
	public function put($url, $httpType = 'http', $dataType = null)
	{
		$this->httpType = $httpType;
		return $this->_httpRequest('PUT', $url, $dataType);
	}

	/**
	 * delete请求
	 * @param string $url 请求地址
	 * @param string $httpType 访问类型 http/https
	 * @param string $dataType 返回类型 null/json
	 * @return string $result 返回内容
	 */
	public function delete($url, $httpType = 'http', $dataType = null)
	{
		$this->httpType = $httpType;
		return $this->_httpRequest('DELETE', $url, $dataType);
	}

	/**
	 * curl 请求实例
	 * @param string $method 请求方式
	 * @param string $url 请求地址
	 * @param string $dataType 返回类型
	 * @return string $result 返回内容
	 */
	private function _httpRequest($method, $url, $dataType = null)
	{
		$timeout = isset($this->timeout) ? $this->timeout : 15;
		$referer = isset($this->referer) ? $this->referer : '';
		$header = isset($this->header) ? $this->header : ['Connection: Keep-Alive'];
		$agent = isset($this->agent) ? $this->agent : 'Curl_Service_Requester';
		if ($method == 'GET' && count($this->data)) {
			$add = '?';
			foreach ($this->data as $key => $value) {
				$add .= '&' . $key . '=' . $value;
			}
			$url .= $add;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		switch (strtolower($method)) {
			case 'get':
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				break;
			case 'post':
				$this->data = is_string($this->data) ? $this->data : http_build_query($this->data);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
				break;
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
				break;
			case 'delete':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
				break;
			case 'head':
				curl_setopt($ch, CURLOPT_NOBODY, TRUE);
				break;
			default:
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				break;
		}		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		if (strtolower($this->httpType) == 'https') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		if (isset($this->proxy)) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy['host']);
			curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxy['port']);
			curl_setopt($ch, CURLOPT_PROXYPASSWORD, $this->proxy['pwd']);
		}
		$result = curl_exec($ch);
		$this->httpInfo = curl_getinfo($ch);
		curl_close($ch);
		if (strtolower($dataType) == 'json') {
			$result = ($arr = json_decode($result, 1)) ? $arr : $result;
		}
		return $result;
	}

	/**
	 * 获取请求头信息
	 */
	public function getInfo($opt = null)
	{
		return $this->httpInfo;
	}

	/**
	 * 设置发送数据
	 */
	public function setData($data = [])
	{
		$this->data = $data;
	}

	/**
	 * 设置超时时间
	 */
	public function setTimeout($timeout = '10')
	{
		$this->method = $timeout;
	}

	/**
	 * 设置来源信息referer
	 */
	public function setReferer($referer = '')
	{
		$this->referer = $referer;
	}

	/**
	 * 设置agent
	 */
	public function setAgent($agent = 'Service_Requester')
	{
		$this->agent = $agent;
	}

	/**
	 * 设置header
	 */
	public function setHeader($header = ['Connection: Keep-Alive'])
	{
		$this->header = $header;
	}

	/**
	 * 设置代理
	 */
	public function setProxy($proxy = null)
	{
		$this->proxy = $proxy;
	}
}
