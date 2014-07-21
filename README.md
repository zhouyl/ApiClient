# 通用 API 客户端

## Composer 加载说明

```json
{
    "require": {
        "zhouyl/apiclient": "dev-master"
    }
}
```

## 使用说明

### 示例代码

```php
$api = new \ZhouYL\Api\Client([
    'gateway' => 'http://127.0.0.1',
    'headers' => array(
        'Host: api.fanqie88.com',
    ),
    'port' => 9433,
    'timeout' => 10,
]);
```

### config: 配置参数

参数说明：

    gateway:    URL请求网关
    port:       端口，默认自动识别
    headers:    CURL请求附加头信息
    timeout:    超时时间，默认不限制

### request: 执行请求

```php
$json = $api->request('Api/xxx', array('key' => 'xxx', 'uid' => 12345), 'POST');
```

### get: GET 请求

```php
$json = $api->request('Api/xxx');
// or
$json = $api->get('Api/xxx');
```

### post: POST 请求

```php
$data = array(
    'key' => 'xxx',
    'uid' => 12345,
);

$json = $api->request('Api/xxx', $data, 'POST');
// or
$json = $api->post('Api/xxx', $data);
```

### response: 获取响应数据

Client 将尝试将响应数据转换进行 json_decode 转换，如果失败则返回 false

```
$api->request('Api/xxx');
$json = $api->response();
```

### rawResponse: 获取原始响应数据

```php
$raw = $api->rawResponse();
```
