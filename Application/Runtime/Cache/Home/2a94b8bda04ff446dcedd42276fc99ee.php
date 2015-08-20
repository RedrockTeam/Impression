<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, target-densitydpi=200">
    <title>重邮印象墙</title>
    <link rel="stylesheet" href="/Impression/Public/dist/assets/css/main.css">
</head>
<body>
    <img class="logo" src="/Impression/Public/dist/assets/images/logo.png"></img>
    <div class="pic" style="background-image:url(<?php echo $data['avatar'] ? $data['avatar'] : '/Impression/Public/dist/assets/images/bg.png' ?>)"></div><!-- tp 的模板就是垃圾-->
    <h1 class="wxname"><?php echo ($data["nickname"]); ?></h1>
    <p class="selfIntro"><span>简介：</span><?php echo ($data['signature']?$data['signature']:'暂无'); ?></p>
    <div class="button buttonStyle card">完善我的卡片</div>
    <div class="button buttonStyle personal">查看他人对我的印象</div>
    <span class="copyright">©红岩网校工作站</span>
    <div id="shade"></div>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
        var isWX = <?php echo ($data['care']?'true':'false'); ?>,//是否关注 boolean
            shareURL = "<?php echo U('Index/share');?>?uid=<?php echo session('uid');?>",
            shareImg = "<?php echo $data['avatar'] ? $data['avatar'] : '/Impression/Public/dist/assets/images/bg.png' ?>";
        document.getElementsByClassName('card').addEventListener('click', function () {
            window.location = "<?php echo U('Index/edtiSignaturePage');?>?uid=<?php echo session('uid');?>"; //跳转链接
        });
        document.getElementsByClassName('personal').addEventListener('click', function () {
            window.location = "<?php echo U('Index/viewImpression');?>"; //跳转链接
        });
        function Show () {
            this.smile = {
                str: '&#xe77d;',
                color: '#008e06'
            },
            this.sad = {
                str: '&#xf0067;',
                color: '#ff8d27'
            }
        }
        Show.prototype.display = function (status) {
            var string = '<div class="bc"></div><div class="shadeMeg">';
            if (status == 'success') {
                string += '<div class="iconfont" id="close">&#xe636;</div><div class="face iconfont" style="color:' + this.smile.color + '">' + this.smile.str + '</div>';
                string += '<h2>提交成功~</h2>';
                string += '<a class="button buttonStyle" id="_close">确定</a></div>';
            } else if (status == 'error') {
                string += '<div class="iconfont" id="close">&#xe636;</div><div class="face iconfont" style="color:' + this.sad.color + '">' + this.sad.str + '</div>';
                string += '<h2>出错了</h2>';
                string += '<a class="button buttonStyle" id="_close">确定</a></div>';
            } else if (status == 'focus') {
                string += '<div class="face iconfont"  style="color:' + this.smile.color + '";padding-top:1rem;>' + this.sad.str + '</div>';
                string += '<h2>sorry，你还没有关注小帮手</h2>';
                string += '<a href="http://mp.weixin.qq.com/s?__biz=MjM5NDAzNDM2MQ==&mid=213491480&idx=1&sn=ac22f1c7690da2cd95e480fcaa99022a&scene=1&from=singlemessage&isappinstalled=0#rd" class="button buttonStyle">关注</a></div>';
            }
            document.getElementById('shade').style.display = 'block';
            document.getElementById('shade').innerHTML = string;
            if (document.getElementById('close')) {
                document.getElementById('close').addEventListener('click',function () {
                    document.getElementById('shade').style.display = 'none';
                    document.getElementById('shade').innerHTML = '';
                });
            }
            if (document.getElementById('_close')) {
                document.getElementById('_close').addEventListener('click',function () {
                    document.getElementById('shade').style.display = 'none';
                    document.getElementById('shade').innerHTML = '';
                });
            }
        };
        var show = new Show(); 
        if (!isWX) {
            show('focus');
        }

        //wx
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: '', // 必填，公众号的唯一标识
            timestamp: <?php echo time();?>, // 必填，生成签名的时间戳
            nonceStr: '', // 必填，生成签名的随机串
            signature: '',// 必填，签名，见附录1
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage'
            ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
        });

        wx.ready(function(){
            wx.onMenuShareTimeline({
                title: '', // 分享标题
                link: shareURL, // 分享链接
                imgUrl: shareImg, // 分享图标
                success: function () { 
                    alert('分享成功。');
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () { 
                    // 用户取消分享后执行的回调函数
                }
            });
            wx.onMenuShareAppMessage({
                title: '', // 分享标题
                desc: '', // 分享描述
                link: shareURL, // 分享链接
                imgUrl: shareImg, // 分享图标
                success: function () { 
                    alert('分享成功。');
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () { 
                    // 用户取消分享后执行的回调函数
                }
            });
        });
    </script>
</body>
</html>