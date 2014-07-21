<?php

namespace ZhouYL\Api;

/**
 * 公共 Api 客户端
 */
class Client
{

    const GET  = 'GET';
    const POST = 'POST';

    /**
     * API 网关
     *
     * @var string
     */
    protected $gateway = null;

    /**
     * 访问端口
     *
     * @var integer
     */
    protected $port = null;

    /**
     * 默认请求 header 信息
     *
     * @var string
     */
    protected $headers = array();

    /**
     * 超时时间
     *
     * @var integer
     */
    protected $timeout = 0;

    /**
     * 最后一次请求的响应数据
     *
     * @var string
     */
    protected $response = null;

    /**
     * 构造方法
     *
     * @param array $options
     */
    public function __construct(array $config = null)
    {
        if (! is_null($config)) {
            $this->config($config);
        }
    }

    /**
     * 设置 API 参数
     *
     * @param  array $config
     * @return \ZhouYL\Api\Client
     */
    public function config(array $config)
    {
        foreach ($config as $name => $value) {
            $method = 'set' . ucfirst($name);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * 设置 Api 网关
     *
     * @param  string $gateway
     * @return \ZhouYL\Api\Client
     * @throws \ZhouYL\Api\Exception
     */
    public function setGateway($gateway)
    {
        if (! preg_match('/^https?:\/\//i', filter_var($gateway, FILTER_VALIDATE_URL)))
            throw new Exception('Invalid gateway parameter: ' . $gateway);

        if (stripos('https', $gateway) !== false && is_null($this->port))
            $this->setPort(443);

        $this->gateway = rtrim($gateway, '/');

        return $this;
    }

    /**
     * 设置默认 header 信息
     *
     * @param  string $headers
     * @return \ZhouYL\Api\Client
     */
    public function setHeaders($headers)
    {
        $this->headers = is_array($headers) ? $headers : [$headers];

        return $this;
    }

    /**
     * 设置端口
     *
     * @param  integer $port
     * @return \ZhouYL\Api\Client
     */
    public function setPort($port)
    {
        $this->port = (int) $port;

        return $this;
    }

    /**
     * 设置默认超时时间
     *
     * @param  integer $timeout
     * @return \ZhouYL\Api\Client
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;

        return $this;
    }

    /**
     * 生成 API URL
     *
     * @param  string $api
     * @return string
     * @throws \ZhouYL\Api\Exception
     */
    public function getRequestUri($api, array $data = null, $method = self::GET)
    {
        if (! preg_match('/^https?:\/\//i', filter_var($this->gateway, FILTER_VALIDATE_URL)))
            throw new Exception('Missing parameter: gateway');

        $uri = $this->gateway;

        if (! is_null($this->port)) {
            $uri .= ':' . $this->port;
        }

        $uri .= '/' . ltrim($api, '/');

        if (strtoupper($method) === self::GET && $data) {
            $uri .= '?' . http_build_query($data);
        }

        return $uri;
    }

    /**
     * 执行 HTTP 请求
     *
     * @param  string $api
     * @param  array  $data
     * @param  string $method
     * @param  array  $options
     * @return \stdClass|false
     */
    public function request($api, array $data = null, $method = self::GET,
        array $options = array())
    {
        $uri = $this->getRequestUri($api, $data, $method);

        if ($this->timeout) $options[CURLOPT_TIMEOUT]    = $this->timeout;
        if ($this->headers) $options[CURLOPT_HTTPHEADER] = $this->headers;

        if (strtoupper($method) === self::GET) {
            $data = null;
        }

        // 执行 curl 请求
        $this->response = $this->curl($uri, $data, $options);

        return $this->response ? json_decode($this->response) : false;
    }

    /**
     * 执行 HTTP GET 请求
     *
     * @param  string $api
     * @param  array  $data
     * @return \ZhouYL\Api\Client
     */
    public function get($api, array $data = null)
    {
        return $this->request($api, $data, self::GET);
    }

    /**
     * 执行 HTTP POST 请求
     *
     * @param  string $api
     * @param  array  $data
     * @return \ZhouYL\Api\Client
     */
    public function post($api, array $data = null)
    {
        return $this->request($api, $data, self::POST);
    }

    /**
     * 获取响应数据并转换为 json 数据
     *
     * @return \stdClass|false
     */
    public function response()
    {
        return $this->response ? json_decode($this->response) : false;
    }

    /**
     * 获取原始响应数据
     *
     * @return string
     */
    public function rawResponse()
    {
        return $this->response;
    }

    /**
     * 执行 curl 请求，并返回响应内容
     *
     * @param  string $url
     * @param  array  $data
     * @param  array  $options
     * @return string
     */
    protected function curl($url, array $data = null, array $options = null)
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
        ));

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if ($options) {
            curl_setopt_array($ch, $options);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
