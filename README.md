# 介绍

这是饿了么自动领取大红包的领取端。

运行前，请自行安置好数据库。

运行后，以`GET`方法传入`phone`和`sn`两个参数即可。

源码删去了红包领取记录等语句，以方便供大家二次开发。

# 环境

PHP 7.0（需要启用CURL）

MYSQL 5.5.35

# 数据库设计

**eleme_qq**（存放QQ小号）

|  #  | 字段          |  类型       | NULL | 备注 |
| --- | :------------ | :---------- | :--: | ---- |
|  1  | **qq**        | varchar(10) |      | QQ号 |
|  2  | **pwd**       | varchar(30) |      | QQ密码 |
|  3  | **eleme_key** | char(32)    |      | 从饿了么cookie中提取出的eleme_key |
|  4  | **openid**    | char(32)    |      | 从饿了么cookie中提取出的openid |
|  5  | **left**      | tinyint(3)  |      | 小号今日剩余可领取次数，0-5之间的整数 |
|  6  | **phone**     | char(11)    | √    | 该小号绑定的手机号，默认为NULL，需要后期绑定，绑定手机号去看绑定端 |

# 饿了么红包接口

## 获取红包属性

`GET` https://h5.ele.me/restapi//marketing/themes/ **[theme_id]** /group_sns/ **[sn]**

## 修改手机号

`PUT` https://h5.ele.me/restapi//v1/weixin/ **[openid]** /phone

`DATA`

```
{
"sign": "[eleme_key]",
"phone": "[修改的手机号]"
}
```

## 领取红包

`POST` https://h5.ele.me/restapi//marketing/promotion/weixin/ **[openid]**

`DATA`

```
{
"method": "phone",
"group_sn": "[sn]",
"sign": "[eleme_key]",
"phone": "",
"device_id": "",
"hardware_id": "",
"platform": 0,
"track_id": "[track_id*]",
"weixin_avatar": "[显示的头像]",
"weixin_username": "[显示的名字]",
"unionid": "fuck"
}
```
> track_id 可以空

# 饿了么红包机器人

* QQ机器人①号：**2871630096**
* QQ机器人②号：**1158060451**
* QQ机器人③号：**946084155**  [测试期，会有不稳定]
* 微信机器人：**eleme-bot**  [测试期，会有不稳定]
