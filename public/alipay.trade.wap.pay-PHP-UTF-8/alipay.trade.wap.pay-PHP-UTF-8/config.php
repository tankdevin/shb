<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2019112269308851",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEowIBAAKCAQEAguxt9hqxtTYZ5wbKx9NL8PTFtDcQ9opApFWBdIK6yMFri8Hb4iMOzeRLCJjJO24JlgwTpavEWCXuZI1s2U5pd87U83d2fXLw7/72J546SgbhNFQuZ95/AAREI98CEOmR83+ckw73eAllSbnmHchMy9Ld6TfOF568V0UlUmvsRuCp6U6CBgdTSnUwzo/MI+Fql9u45a+7wf/UI3wxqQ9O6SiahAbilzfx38Wu3xAh1PBQbDC3c/d0RX5meGgRGv9e214KP6XM6AMI76Eo9b8Seva5Nk9/5fBDha16NqjZq5QPVbbMC49Jv4HmsmUPhecXqs4LADpKup2xb21SzcU80wIDAQABAoIBAGc+TOSTXmoZVMnTmFuGK8/DJpkcB5tzoNA2wZSAdw1TQhz+7gfhP45rpiQMYDwmZRbXRTMTRg2BNAJFaH3hENiy2Ul5fo4k8w0/ERxi8XJpBHUAoWu1kI8Rzi4/cWCm4DqV+oIZfltBhYdjOq1nw+/DxM1h/Xqw6bMkSu85PiURaw2407HdBDKjMTcd1RkJKRL+NxFTG29bDDQ13Jo+/EKtlnQGUEPAy2NgXv0lT3XAVi0/r/Jy9MR+VWkerYABcojXCMOFvLSowcd/kN7zxggYvRk31CczDF1O8KAHso2+me6BKFri2ChlEMRI7V0BX0iHd4sP1FAyrwlCywzio0ECgYEAt7YrGajzN7R04XVJZYiHSEXSDw4emVD9A1kYXXJfN5qLFPk2BTgnAXK60RbCMZoagsJzUj9wajRc6z5hGxNsn6LBL2QhiGRM9C5NT2bvXTqfbCKBwFWTZiGfHHXkOOBVSArJsq8nVDL95n5F8sz0ycnmpNFWbQZWz4JQxHDsRvMCgYEAtnDCj3e/2p42Ta7OZalQiyoru6q6MKaQwmEypuJJrjxh4uCZ48aeOJ8xSJnxiJGAgpDMwAa6bjGran8fZLdgfIa53UbnwcBADX39Cd3OV0eg8r8GiHEOTNE4FGW0+irG5//4S2pecX+NIlneUpd6LSL5dYlJ+tchJVfYPcgEaqECgYBI1yj1rqBo6Nsi/b8RS/XfuRdVstJ9FbiMGEpp2sxYHqWgtkMuBJqqn1PlTXHH+wMh73mMFe07nvFssLoN40DBduXEJZ6KFsLQWn96ySSFQZYuaOwrV4LvDsuonDHWP1RxER3yBDLC1skHF/AiGhPA4pXZqcrrxYSsGE5v5Ro58wKBgFP8kPSu8flYgDM+1/FhAp0uKrfYHqKq61UwnbrJzZWSYpWpQ+7hzpDOaEe95r0yDnEM69Uyw7xazUjXgMJC0P5Cn2nIbIR7VoFfkI9sNsNjTCqz2fQR571hS2nIcIoCC1NeORWuwA+L4pL/wCNRbIM+6Iw9SXtacf3Y9MlsPOxBAoGBAIQOFIZ54rtQvgZgKEuz6mIBPE3FFcZ3L1lWRwHBq08zHpAsDYxfZ2a+uo48spOSnWvYtymjFZ8gGJ7QLpPAmwlGCAIxeOO41XC8neo9lyLUfry4IatUmEfq/AyOZ2SUKzgQsySRBtxsKLB9nXIOBD9i2uYGbI7QYb0YqIEJVnTy",
		
		//异步通知地址
		'notify_url' => "http://www.shenghb.com/index.php/api/alipay/notifyurl",
		
		//同步跳转
		'return_url' => "https://app.shenghb.com/",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjRE/EHff3Akme7tdhOM9qwgigS1PATrw1Y0MwCtCcnmU+gayWjGLxeMCjxjh3TCHPkRM4DuFDcmRebUxrhgcvhGHLIYSdtPxZpNlz3vPTk03HUrFfoDisH/1/D4QS054Dv5+1wfn0K66mgtrlnq70Z9qgQ12etLWg2MHyGbS4FeR8eKbHZuVp6SHKvrKYdui3sroc75rPGDBiWFuiXaDddR/1D+FLbDAQbEDS7i5Tej6UxSveKoLgrBbG2OGol/a0k426iZhwQBdvb7YTgfe4zRU/Kusw940T5mdIs4OgiQujtBVk8FJDktn3TQ0FSDnE3DQZtGrw9L567gK/UYj5wIDAQAB",
		
	
);