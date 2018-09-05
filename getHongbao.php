<?php 
	/**
	 * 这是饿了么红包机器人的后端抢红包部分的php源码。
	 * php7.0环境下可用，其他版本请自测。
	 * 
	 * 阅读源码，请结合Readme.md一起理解。
	 * @author	AlphaNut<119879622@qq.com>
	 */
 ?>
<?php header("Content-type: text/plain; charset=utf-8"); ?>
<?php set_time_limit(0); ?>
<?php
	class Xiaohao {
		private $_eleme_key; //小写
		private $_openid; //大写
		private $_ip; //模拟ip
		function __construct($eleme_key, $openid) {
			$this -> _eleme_key = $eleme_key;
			$this -> _openid = $openid;
			$this -> _ip = '10.'.rand(10,200).'.'.rand(10,200).'.'.rand(10,200);
		}
		public function getPhone() {
			$url = 'https://h5.ele.me/restapi/v1/weixin/'.$this -> _openid.'/phone?sign='.$this -> _eleme_key;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			$res = curl_exec($curl);
			curl_close($curl);
			$res = json_decode($res, true);
			return isset($res['phone'])? $res['phone']: 0;
		}
		public function changePhone($phone) {
			$phone = strval($phone);
			$url = 'https://h5.ele.me/restapi/v1/weixin/'.$this -> _openid.'/phone';
			$postData = array(
				"sign"  => $this -> _eleme_key,
				"phone" => $phone
			);
			$header = array(
				'Content-Type: text/plain;charset=UTF-8',
				'Origin: https://h5.ele.me',
				'User-Agent: Mozilla/5.0 (Linux; Android 8.0; SM-G9550 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044203 Mobile Safari/537.36 V1_AND_SQ_7.1.0_0_TIM_D PA TIM2.0/2.2.7.1810 QQ/6.5.5  NetType/WIFI WebP/0.3.0 Pixel/1080',
				'Referer: https://h5.ele.me/hongbao/',
				'CLIENT-IP: '.$this -> _ip,
				'X-FORWARDED-FOR: '.$this -> _ip
			);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
			curl_exec($curl);
			curl_close($curl);
			
			if ($phone === $this -> getPhone()) {
				return true;
			} else {
				return false;
			}
		}
		public function getHongbao(&$hb) {
			$url = 'https://h5.ele.me/restapi/marketing/promotion/weixin/'.$this -> _openid;
			$img = "https://eleme-avatar-1251241442.cos.ap-shanghai.myqcloud.com/eleme-avatar/eleme".rand(0,5).".png";
			$postData = array(
				"method" => "phone",
				"group_sn" => $hb -> getSN(),
				"sign" => $this -> _eleme_key,
				"phone" => "",
				"device_id" => "",
				"hardware_id" => "",
				"platform" => 0,
				"track_id" => "undefined",
				"weixin_avatar" => $img,
				"weixin_username" => "[ 饿了么 ]",
				"unionid" => "fuck",
				"latitude" => -180,
				"longitude" => -180
			);
			$postData = json_encode($postData, JSON_UNESCAPED_SLASHES);
			$header = array(
				'Content-Type: text/plain;charset=UTF-8',
				'Origin: https://h5.ele.me',
				'X-Shard: eosid='.$hb -> getEosid(), //这个header一定要加上，非常重要！
				'User-Agent: Mozilla/5.0 (Linux; Android 8.0; SM-G9550 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044203 Mobile Safari/537.36 V1_AND_SQ_7.1.0_0_TIM_D PA TIM2.0/2.2.7.1810 QQ/6.5.5  NetType/WIFI WebP/0.3.0 Pixel/1080',
				'Referer: https://h5.ele.me/hongbao/',
				'CLIENT-IP: '.$this -> _ip,
				'X-FORWARDED-FOR: '.$this -> _ip
			);
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			$res = curl_exec($curl);
			curl_close($curl);
			$res = json_decode($res, true);
			# print_r($res); //调试用

			# ret_code的值：
			# [1]领完了
			# [2]这是一个已经抢过的包
			# [4]正常领取
			# [5]今日领取上限了（可能是QQ小号领取上限了，也有可能是手机号领取上限了）
			# [6]红包被取消了
			# [10]要求短信验证手机号

			if (isset($res['name'])) {
				// 一般来说是不会返回带有name的值的，如果有的话，可能是“手机号未填”或是“繁忙”等情况，反正就是没领到红包
				return array(
					'ret_code' => 0,
					'account' => 0,
					'is_lucky' => 0,
					'amount' => 0,
					'records' => 0
				);
			}
			return array(
				'ret_code' => $res['ret_code'],
				'account' => $res['account'], //领取的手机号账户
				'is_lucky' => isset($res['is_lucky'])?$res['is_lucky']:false, //是否是最大包
				'amount' => isset($res['promotion_items'][0]['amount'])?$res['promotion_items'][0]['amount']:0, //领取红包金额
				'records' => count($res['promotion_records']) //当前总共多少个人领取了
			);
		}
	}
	class Hongbao {
		private $_sn;
		private $_lucky_number;
		function __construct($sn) {
			$this -> _sn = $sn;
			if (isset($this -> getDetail()['lucky_number'])) {
				$this -> _lucky_number = $this -> getDetail()['lucky_number'];
			} else {
				$this -> _lucky_number = 0;
			}
		}
		public function getDetail() {
			$url = 'https://h5.ele.me/restapi//marketing/themes/0/group_sns/'.$this -> _sn;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			$res = curl_exec($curl);
			curl_close($curl);
			return json_decode($res, true);
		}
		public function getLuckyNumber() {
			return $this -> _lucky_number;
		}
		public function getSN() {
			return $this -> _sn;
		}
		public function getEosid() {
			//这个要在抢红包post过程中放在header中
			return substr(hexdec($this -> _sn), 0, -2) . '00';
		}
	}

	if (!isset($_GET['phone']) || !isset($_GET['sn'])) {
		die('please give me phone and sn with get method.');
	}

	// 连接数据库
	$db = new PDO(
		"mysql:host=sqld-gz.bcehost.com;port=3306;dbname=UtWeviJWQuWvDtcwVkHk",
		'fbf3f9b8b9df40a49a0851dcaa44a2e4',
		'547f03df35d04f48bf9cc0813f74f33c',
		array(PDO::ATTR_PERSISTENT => true)
	);

	$phone = $_GET['phone'];
	$sn = $_GET['sn'];

	$h = new Hongbao($sn); //构造红包对象
	$lucky_number = $h -> getLuckyNumber(); //取出红包的最大包是在第几个，下称幸运数

	if ($lucky_number === 0) {
		die("这个红包可能过期了或失效了！");
	}

	// 抢小红包，把小红包垫刀垫完再进入大红包模式
	while ($row = $result -> Fetch()) {
		$result = $db -> query("
			SELECT
				* 
			FROM
				`eleme_qq` 
			WHERE
				`left` BETWEEN 1 AND 5 
				AND `phone` IS NOT NULL 
			ORDER BY
				`left` DESC 
				LIMIT 1;
		");
		$row = $result -> Fetch(); //每次取出一个小号出来
		$s = new Xiaohao($row['eleme_key'], $row['openid']);
		$res = $s -> getHongbao($h);

		switch ($res['ret_code']) {
			case 0: # 繁忙，那就再来一遍
				continue;
			case 4: # 正常领取，更新数据库把这个小号的可用次数减1
				$db -> exec("UPDATE `eleme_qq` SET `left` = `left` - 1 WHERE `qq` = '".$row['qq']."';");
				break;
			case 5: # 领取上限了，更新数据库把这个小号的可用次数置0
				$db -> exec("UPDATE `eleme_qq` SET `left` = '0' WHERE `qq` = '".$row['qq']."';");
				break;
			case 6: # 红包被饿了么取消了。
				die("红包被饿了么取消了。");
				break;
			case 10: # 手机号失效了，更新数据库把这个小号的手机号置空
				$db -> exec("UPDATE `eleme_qq` SET `phone` = NULL WHERE `qq` = '".$row['qq']."';");
				break;
			default: break;
		}

		if ($res['records'] > $lucky_number) {
			# 红包领取人数大于幸运数，说明大红包已经领走了
			die("来晚了，大红包已经被别人领走了。");
		} elseif ($res['records'] == $lucky_number) {
			# 红包领取人数等于幸运数，说明大红包领到了
			if ($res['is_lucky']) {
				die("早就说过了，下一个就是大红包的时候不要用机器人。现在好了吧，大红包被小号吃了。");
			} else {
				die("来晚了，大红包已经被别人领走了。");
			}
		} elseif ($res['records'] + 1 === $lucky_number) {
			# 下一个就是最大包了
			# die("下一个该是最大的了，请手动点进去领领吧。"); //调试用
			break; # 退出抢小红包，进入抢大红包
		}
		
		unset($s); //释放内存，这句也可以不用写
	}

	// 抢大红包
	while ($row = $result -> Fetch()) {
		$result = $db -> query("
			SELECT
				* 
			FROM
				`eleme_qq` 
			WHERE
				`left` BETWEEN 1 AND 5 
				AND `phone` IS NOT NULL 
			ORDER BY
				`left` DESC 
				LIMIT 1;
		");
		$row = $result -> Fetch(); //每次取出一个小号出来
		$s = new Xiaohao($row['eleme_key'], $row['openid']);
		if ($s -> changePhone($phone) === false) {
			// 修改成目标手机号失败了，直接退出，让用户手动点链接领。
			die('无法使用你的手机号进行自动领取。下一个就是最大包了，请自己手动点进去领取一下吧。');
		}
		$res = $s -> getHongbao($h);		// 抢大红包
		$s -> changePhone($row['phone']);	// 把手机号改回原来的

		switch ($res['ret_code']) {
			case 0: # 繁忙，那就再来一遍
				continue;
			case 2: # 该手机号领取过小红包
				$db -> exec("UPDATE `eleme_qq` SET `left` = `left` - 1 WHERE `qq` = '".$row['qq']."';");
				die("你的手机号领取过小包，无法领取大红包。下一个就是最大包了，你可以送给你的好友。");
				break;
			case 4: # 正常领取，更新数据库把这个小号的可用次数减1
				$db -> exec("UPDATE `eleme_qq` SET `left` = `left` - 1 WHERE `qq` = '".$row['qq']."';");
				break;
			case 5: # 领取上限了，不知道是用户手机号的问题还是小号的问题，为了确保稳定，要更新数据库把这个小号的可用次数置0
				$db -> exec("UPDATE `eleme_qq` SET `left` = '0' WHERE `qq` = '".$row['qq']."';");
				die('你的手机号领取上限了，大红包没领到。下一个就是最大包了，你可以试试自己点进去领取。');
				break;
			case 6: # 红包被饿了么取消了。
				die("红包被饿了么取消了。");
				break;
			case 10: # 手机号失效了，更新数据库把这个小号的手机号置空
				$db -> exec("UPDATE `eleme_qq` SET `phone` = NULL WHERE `qq` = '".$row['qq']."';");
				die('无法使用你的手机号进行自动领取。下一个就是最大包了，请自己手动点进去领取一下吧。');
				break;
			default: break;
		}

		if ($res['records'] > $lucky_number) { # 领大红包的过程中被人劫胡，会出现这个情况。
			die("惊呆了，大红包被截胡了！你只领到了".$res['amount']."元的小红包。");
		} elseif ($res['records'] === $lucky_number) { # 红包领取人数等于幸运数，说明大红包领到了
			if ($res['account'] === $phone) {
				if ($res['is_lucky']) {
					die($res['amount']."元红包到手！");
				} else {
					die("说起来你可能不相信，这个链接里没有大红包，你在第".$lucky_number."个红包中领到了".$res['amount']."元。");
				}
			} else {
				die("切换到你的手机号领取时似乎出了点问题，大红包没有领取到。（这个问题目前无解=_=只能跟你说声抱歉了）");
			}
		} elseif ($res['records'] + 1 === $lucky_number) {
			# 如果能进入这个分支，那应该是出现未知问题了。
			die('发生了一些未知错误，下一个就是最大包了，请自己手动点进红包链接去领取一下吧。错误码：'.$res['ret_code']);
		}
		unset($s);
	}

	unset($h);
	// 下面这句是个坑，为啥是坑呢，我不解释了。
	// 因为目前小号库很充足，不会出现这个，懒得改了=_=
	die("服务器内置的小号已经消耗殆尽了，你的红包没有领完，可以明天再来。");
