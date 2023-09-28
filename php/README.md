# About PHP Demo

## Generate Keys

使用PHP生成一对全新的，符合 Sinohope WaaS 格式要求的 `prime256v1`曲线（别名 `P256`, `secp256r1`）的 ECDSA密钥对，参考代码 `gen_key_pair.php`。
运行该脚本将创建一对新密钥对，输出 PEM 格式的私钥，以及 HEX 格式的 私钥 和 公钥，其中 HEX 格式的公钥也就是Sinohope 配置中所需的 API Key。

```php
php gen_key_pair.php
```

## Make API Request

可参考 `api_request_demo.php` 完成Sinohope WaaS API的请求。这个示例的重点是实现及展示请求签名的构造。

在执行该脚本之前，需要通过环境变量设置您的API 密钥对信息，运行示例如下：

```bash
export APIKEY_HEX="<your hex api key string>"
export APISECRET_HEX="<your hex secret key string>"
php api_request_demo.php
```

## Callback Demo

Sinohope WaaS 提供了中心化回调功能，可供用户集成风控流程。Sinohope 请求用户的回调接口时，将使用相同的 签名验证机制。因此用户实现回调服务时，一个基础的工作是实现对请求签名的验证。

`callback_demo.php` 实现了一个简单的回调服务示例，重点是实现及展示 如何对请求签名进行验证。

运行服务：

```bash
export APIKEY_HEX="< callback API Key provided by Sinohope >"
php -S localhost:8000 callback_demo.php
```

