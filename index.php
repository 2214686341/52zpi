<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>自定义修改QQ在线状态</title>
  <link href="//lib.baomitu.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="//lib.baomitu.com/jquery/1.12.4/jquery.min.js"></script>
  <script src="//lib.baomitu.com/layer/3.1.1/layer.js"></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
  <!--[if lt IE 9]>
    <script src="//lib.baomitu.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//lib.baomitu.com/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="container">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
<br>
<div class="panel panel-primary">
	<div class="panel-heading" style="background: linear-gradient(to right,#b221ff,#14b7ff,#8ae68a);"><h3 class="panel-title">
		自定义修改QQ在线状态
	</div>
	<div class="panel-body" style="text-align: center;">
		<div class="list-group">
			<div class="list-group-item list-group-item-info" style="font-weight: bold;" id="login">
				<span id="loginmsg">使用QQ手机版扫描二维码</span><span id="loginload" style="padding-left: 10px;color: #790909;"></span>
			</div>
			<div class="list-group-item" id="qrimg"></div>
			<div class="list-group-item" id="qrLogin"><a href="#" onclick="qrLogin()" class="btn btn-block btn-primary" style="background: linear-gradient(to right,#b221ff,#14b7ff);">点此验证</a></div>
		</div>
		<div class="list-group" id="PhoneInfo" style="display: none;">
			<div class="list-group-item">
			 	<img src="https://blog.toojk.com/wp-content/uploads/2020/01/1580225238-8c396ff06c26990.jpg" width="80" style="border-radius: 50%;opacity: 0.80;" id="avatar">
			</div>
			<div class="list-group-item">
				<div class="input-group">
					<div class="input-group-addon">QQ</div>
					<input type="text" id="qq" class="form-control" required="">
				</div>
			</div>
			<div class="list-group-item">
				<div class="input-group">
					<div class="input-group-addon" >skey</div>
					<input type="text" id="skey" class="form-control" required="">
				</div>
			</div>
			<div class="list-group-item">
				<div class="input-group">
					<div class="input-group-addon" >pt4_token</div>
					<input type="text" id="pt4_token" class="form-control" required="">
				</div>
			</div>
			<div class="list-group-item">
				<div class="input-group">
					<div class="input-group-addon" >自定义文本</div>
					<input type="text" id="desc" class="form-control" required="" value="Toojk">
				</div>
			</div>
			<div class="list-group-item">
				<div class="input-group">
					<div class="input-group-addon" >手机IMEI码</div>
					<input type="text" id="imei" class="form-control" required="" placeholder="安卓手机*#06#查看">
				</div>
			</div>
			<div class="list-group-item" id="Post"><a href="#" onclick="Post()" class="btn btn-block btn-primary" style="background: linear-gradient(to right,#b221ff,#14b7ff);">开始修改</a></div>
		</div>
	</div>
</div>
<div class="panel panel-default text-center">
	<div class="panel-body">
		©&nbsp;2017-2020
		<a href="https://www.toojk.com" title="CNXS" rel="link" target="_blank">Toojk</a>&
		<a href="#" title="源码下载" rel="link" target="_blank">源码下载</a>
    </div>
</div>
</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		GetQR();
	});
	var interval1,interval2;

	function GetQR(force){
		force = force || false;
		cleartime();
		var qrsig = getCookie('qrsig');
		var qrimg = getCookie('qrimg');
		if(qrsig != null && qrimg != null && force == false){
			$('#qrimg').attr('qrsig',qrsig);
			$('#qrimg').html('<img id="qrcodeimg" onclick="GetQR(true)" src="data:image/png;base64,'+qrimg+'" title="点击刷新">');
		}else{
			$.getJSON('login.php',{do:'getqrpic',r:Math.random(1)}, function(d) {
				if(d.saveOK ==0){
					setCookie('qrsig',d.qrsig);
					setCookie('qrimg',d.data);
					$('#qrimg').attr('qrsig',d.qrsig);
					$('#qrimg').html('<img id="qrcodeimg" onclick="GetQR(true)" src="data:image/png;base64,'+d.data+'" title="点击刷新">');
				}else{
					layer.alert(d.msg);
				}
			});

		}
	}
	function qrLogin(){
		if ($('#login').attr("data-lock") === "true") return;
		$.getJSON('login.php',{
			do:'qqlogin'
			,qrsig:decodeURIComponent($('#qrimg').attr('qrsig'))
			,r:Math.random(1)
		}, function(d, textStatus) {
			if(d.saveOK == 0){
				$('#login').html('<div class="alert alert-success">QQ验证成功！'+d.nick+'</div><br/><p>↓继续操作↓</p>');
				$('#qrimg').hide();
				$('#qrLogin').hide();
				$('#PhoneInfo').show();
				$('#login').attr("data-lock", "true");
				$('#avatar').attr('src','https://q4.qlogo.cn/headimg_dl?spec=100&dst_uin='+d.qq);
				$('#qq').val(d.qq);
				$('#skey').val(d.skey);
				$('#pt4_token').val(d.pt4_token);
			}else{
				cleartime();
				$('#loginmsg').html(d.msg);
				layer.msg(d.desc);
			}
		});
	}
	function Post() {
		$.getJSON('https://api.uomg.com/api/qq.oem', {
			qq: $('#qq').val()
			,skey: $('#skey').val()
			,imei: $('#imei').val()
			,desc: $('#desc').val()
			,pt4_token: $('#pt4_token').val()
		}, function(json, textStatus) {
			layer.alert(json.msg);
		});
	}
	function cleartime(){
		clearInterval(interval1);
		clearInterval(interval2);
	}
	function setCookie(name,value)
	{
		var exp = new Date();
		exp.setTime(exp.getTime() + 30*1000);
		document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	}
	function getCookie(name)
	{
		var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr=document.cookie.match(reg))
			return unescape(arr[2]);
		else
			return null;
	}
</script>
</body>
</html>