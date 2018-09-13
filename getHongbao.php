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
		private $_sid; //放cookie里
		private $_ip; //模拟ip
		function __construct($eleme_key, $openid, $sid) {
			$this -> _eleme_key = $eleme_key;
			$this -> _openid = $openid;
			$this -> _sid = $sid;
			$this -> _ip = '10.'.rand(10,200).'.'.rand(10,200).'.'.rand(10,200);
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
				"track_id" => "",
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
			$cookie = 'SID=' . $this -> _sid; //09-06饿了么更新后需要提供这个cookie
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
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
			$this -> _eosid = substr(hexdec($sn), 0, -2) . '00';
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
			return $this -> _eosid;
		}
	}

	if (!isset($_GET['sn'])) {
		die('please give me phone and sn with get method.');
	}
	
	$sn = $_GET['sn'];

	// 连接数据库
	$db = new PDO(
		"mysql:host=sqld-gz.bcehost.com;port=3306;dbname=UtWeviJWQuWvDtcwVkHk",
		'fbf3f9b8b9df40a49a0851dcaa44a2e4',
		'547f03df35d04f48bf9cc0813f74f33c',
		array(PDO::ATTR_PERSISTENT => true)
	);

	$h = new Hongbao($sn); //构造红包对象
	$lucky_number = $h -> getLuckyNumber(); //取出红包的最大包是在第几个，下称幸运数
	$record_number = 0; //记录已经抢了多少个

	if ($lucky_number === 0) {
		die("这个红包可能过期了或失效了！");
	}

	// 抢小红包，把小红包垫刀垫完剩下大红包
	while (true) {
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
		if($row === false) {
			die('服务器小号用完了，你的红包已经领了' . $record_number . '个，第' . $lucky_number .'个是大红包。');
		}
		$s = new Xiaohao($row['eleme_key'], $row['openid'], $row['sid']);
		$res = $s -> getHongbao($h);
		
		if($res['records'] > 0) {
			$record_number = $res['records'];
		}
		
		switch ($res['ret_code']) {
			case 0: # 繁忙或者是手机号失效绑定，那就再来一遍
				$db -> exec("UPDATE `eleme_qq` SET `left` = '11' WHERE `qq` = '".$row['qq']."';");
				break;
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
				$db -> exec("UPDATE `eleme_qq` SET `left` = '10' WHERE `qq` = '".$row['qq']."';");
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
			die("下一个该是最大的了，请手动点进去领领吧。");
		}
		
		unset($s); //释放内存，这句也可以不用写
	}
