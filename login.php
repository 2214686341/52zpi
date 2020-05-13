<?php
//error_reporting(0);
define('QQ_QRCODE', 1);

header('Content-type: application/json');
class qq_qrlogin{
	public function login_sig(){
		if (QQ_QRCODE == 1) {
			$url = 'https://xui.ptlogin2.qq.com/cgi-bin/x';
		}else{
			$url = 'http://ui.ptlogin2.qq.com/cgi-bin/';
		}
		$url .='login?proxy_url=http%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&pt_qzone_sig=1&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=http%3A//qzs.qq.com/qzone/v5/loginsucc.html?para=izone&pt_qr_app=手机QQ空间&pt_qr_link=http%3A//z.qzone.com/download.html&self_regurl=http%3A//qzs.qq.com/qzone/v6/reg/index.html&pt_qr_help_link=http%3A//z.qzone.com/download.html';
		$ret = $this->get_curl($url,0,0,0,1);
		preg_match('/pt_login_sig=(.*?);/',$ret,$match);
		return $match[1];
	}
	public function getqrpic(){
		if (QQ_QRCODE == 1) {
			$url = 'https://ssl.ptlogin2.qq.com';
		}else{
			$url = 'http://ptlogin.qq.com';
		}
		$url .='/ptqrshow?appid=549000912&e=2&l=M&s=3&d=72&v=4&t=0.5409099'.time().'&daid=5&pt_3rd_aid=0';
		$arr=$this->get_curl($url,0,0,0,1,0,0,1);
		preg_match('/qrsig=(.*?);/',$arr['header'],$match);
		if($qrsig=$match[1])
			exit('{"saveOK":0,"qrsig":"'.$qrsig.'","data":"'.base64_encode($arr['body']).'"}');
		else
			exit('{"saveOK":1,"msg":"二维码获取失败"}');
	}
	public function qqlogin(){
		$qrsig=empty($_GET['qrsig'])?exit('{"saveOK":-1,"msg":"qrsig不能为空"}'):$_GET['qrsig'];
		$sig=$this->login_sig();
		if (QQ_QRCODE == 1) {
			$url = 'https://ssl.ptlogin2.qq.com';
		}else{
			$url = 'http://ptlogin.qq.com';
		}
		$url .='/ptqrlogin?u1=http%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'0000&js_ver=10194&js_type=1&login_sig='.$sig.'&pt_uistyle=40&aid=549000912&daid=5&';
		$ret = $this->get_curl($url,0,$url,'qrsig='.$qrsig.'; ',1);
		if(preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)){
			$r=explode("','",str_replace("', '","','",$arr[1]));
			if($r[0] == 0){
				preg_match('/superuin=(.*?);/',$ret,$uin);
				preg_match('/skey=@(.{9});/',$ret,$skey);
				$data = $this->get_curl($r[2],0,0,0,1);
				if($data) {
					preg_match("/p_skey=(.*?);/", $data, $pskey);
					preg_match('/pt4_token=(.*?);/',$data,$pt4_token);
				}
				if($pskey){
					$json = array(
						'saveOK'=>0
						,'msg'=>'登录成功！'
						,'desc'=>'登录成功！'
						,'qq'=>$this->getuin($uin[1])
						,'skey'=>'@'.$skey[1]
						,'nick'=>$r[5]
						,'p_skey'=>$pskey[1]
						,'pt4_token'=>$pt4_token[1]
					);
					exit(json_encode($json,320));
				}else{
					$json = array('saveOK'=>4,'msg'=>'登录成功，获取相关信息失败！','desc'=>'登录成功，获取相关信息失败！'.$r[2]);
					exit(json_encode($json,320));
				}
			}elseif($r[0]==65){
				$json = array('saveOK'=>1,'msg'=>'二维码已失效。','desc'=>'请重新扫描二维码');
				exit(json_encode($json,320));
			}elseif($r[0]==66){
				$json = array('saveOK'=>2,'msg'=>'二维码未失效。','desc'=>'使用QQ手机版扫描二维码');
				exit(json_encode($json,320));
			}elseif($r[0]==67){
				$json = array('saveOK'=>3,'msg'=>'正在验证二维码。','desc'=>'扫码成功，请在手机上确认授权登录');
				exit(json_encode($json,320));
			}else{
				$json = array('saveOK'=>4,'msg'=>$r[4],'desc'=>$r[4]);
				exit(json_encode($json,320));
			}
		}else{
			exit('{"saveOK":6,"msg":"'.$ret.'"}');
		}
	}
	private function getuin($uin){
        for($i = 0; $i < strlen($uin); $i++){
			if($uin[$i]=='o'||$uin[$i]=='0')continue;
			else break;
        }
        return substr($uin,$i);
    }
	private function getqrtoken($qrsig){
        $len = strlen($qrsig);
        $hash = 0;
        for($i = 0; $i < $len; $i++){
            $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
			$hash &= 2147483647;
        }
        return $hash & 2147483647;
    }
	private function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$split=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept:*/*";
		$httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
		$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
		$httpheader[] = "Connection:close";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if($header){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
		}
		if($cookie){
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		if($referer){
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		if($ua){
			curl_setopt($ch, CURLOPT_USERAGENT,$ua);
		}else{
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36');
		}
		if($nobaody){
			curl_setopt($ch, CURLOPT_NOBODY,1);

		}
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$ret = curl_exec($ch);
		if ($split) {
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($ret, 0, $headerSize);
			$body = substr($ret, $headerSize);
			$ret=array();
			$ret['header']=$header;
			$ret['body']=$body;
		}
		curl_close($ch);
		return $ret;
	}
}

$login=new qq_qrlogin();
if($_GET['do']=='qqlogin'){
	$login->qqlogin();
}
if($_GET['do']=='getqrpic'){
	$login->getqrpic();
}
