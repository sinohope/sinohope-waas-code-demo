# 关于 Sinohope WaaS Code Demo
对于Sinohope WaaS API，Sinohope 提供了多种语言的 SDK 方便开发者开发集成。
但是依然存在大量的开发语言当前尚未提供 SDK，开发者使用这类开发语言时，需要自行根据文档 [API Authentication](https://docs.sinohope.com/docs/develop/get-started/general#api-authentication) 的要求，实现接口请求的组装及签名。

同时，当提供交易回调服务时，开发者要实现对回调请求的签名验证。

本项目提供了部分开发语言的请求签名、验签相关的代码片段（示例）可供参考。