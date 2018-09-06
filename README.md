# 免责声明

此源码仅供学习，请勿用于商业及非法用途，如产生法律纠纷与本人无关。

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
|  6  | **phone**     | char(11)    | √    | 该小号绑定的手机号，默认为NULL，需要后期绑定，绑定手机号去看绑定端（改漂亮了再发上来） |

> 以下提供一些测试数据。eleme_key和openid真实有效。
> |qq			|pwd		|eleme_key							|openid								|left|phone		|
> | --------- | --------- | --------------------------------- | --------------------------------- | - | --------- |
> |1551127252	|aaaa2766	|308bbb50188ecde7be310890ec402096	|09B1B2D594D0548F82B2943356A8604F	|5	|17080631064|
> |1926088025	|aafxa27587	|1e286cb24c0f98e3ed1d9c2cc184deba	|101AFA3901329478F1C85281D4EA43AC	|5	|17080631169|
> |1945345145	|1qaz2wsx	|3976eb0964df8ab5f46e4bc7af00a0fb	|ACA18890EE1F19B463769D8D9E21674B	|5	|17080631174|
> |1948620738	|jvhl9636	|25ac746c1ca1385b17360e8d97828179	|4E70F87473FBBF1AD640F5AB92DDF268	|5	|17080631175|
> |2052079681	|wan920143	|6ac020d2beb7b45058e8fec0c8be16fb	|8AE4AF545687848EF4CDDB6B8B0321CF	|5	|17080631256|
> |3328360480	|mmmcpfe5	|4b2f9e0b39b6f71926bc3562ffe856df	|1AA3806296FD43CB6604EC99D9A26967	|5	|17080632981|
> |3351469928	|ge184989	|5816b86ba82e239a56afe2e11bd38d3d	|CF18B78F0822E32506447372F75CB533	|5	|17080633097|
> |3356484679	|ncpxc451	|990526b5fcf1f2d0c63622470db8b3a5	|7531C22753371B32AD05C400381A8F52	|5	|17080633100|
> |3601992072	|eb182482	|7a8542979e088fa6a8f4eac1056cd17f	|5847E7C05DA8120BB540B77DEB50897B	|5	|17080633576|
> |2057204184	|ydftf5952	|f281a1919637a8a7dc42bacf5bf75f8c	|1C3C370245C7928BB2CBCBA67BE9ADC7	|5	|17080633691|
> |2269769459	|y6346353	|f8d9688f98cd551e549afe1aab2ea5f7	|A261DC6CB65FD763F486267405BD0AE2	|5	|17080633767|
> |3514905069	|xdfc9059	|5dbefa556761a15d50b0330e6b94ea51	|6C700A7DF5713BFC65D336F1301E111E	|5	|17080633866|
> |926386830	|t8674831	|e0ffe0abb1c37877da62501573c60075	|6F4515F7FB37BCA9D920F4A6EA98E3A3	|5	|17080634209|
> |3282639178	|duu888888	|f1ea3c867e263d228af37ca8bfcf6336	|0648D54165AB8A511AAB6361BC422E36	|5	|17080629724|
> |3279449199	|c7nvwrrsut	|3a27b61ce2359b247ba9bcf12383286a	|E25D7BBA23194A2502DFC7661602087A	|5	|17080634327|

# 饿了么的红包接口

之所以能写成程序，是因为饿了么领红包的接口很容易扒下来。以下是领红包过程中会用到的一些接口。

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
