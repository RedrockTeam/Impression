<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, target-densitydpi=200">
    <title>重邮印象墙</title>
    <link rel="stylesheet" href="/Impression/Public/dist/assets/css/main.css">
</head>
<body>
    <div class="mine">
        <div class="minePic" style="background-image:url(/Impression/Public/dist/assets/images/<?php echo $user['avatar']?$user['avatar']:'bg.png' ?>)"></div>
        <div class="mineName"><?php echo ($user["nickname"]); ?></div>
        <div class="mineIntro"><?php echo ($user["signature"]); ?></div>
    </div>
    <ul class="others">
        <?php if($num > 0 ): ?><li class="list">我共收到<span>$num</span>个印象</li>
            <?php else: ?>
                <li class="none">我还未收到任何印象</li><?php endif; ?>
        <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="other box" data-id="<?php echo ($vo["impression_id"]); ?>">
                <div class="otherPic iconfont" style="background-image:url(/Impression/Public/dist/assets/images/<?php echo $vo['avatar']?$vo['avatar']:'bg.png' ?>)"></div>
                <div class="otherName"><?php echo ($vo["nickname"]); ?></div>
                <div class="othercom"><?php echo ($vo["content"]); ?></div>
                <div class="tools">
                    <div class="tool toolDel iconfont">&#xe609;</div>
                    <div class="tool toolDown iconfont">&#xe606;<span><?php echo ($vo["praise"]); ?></span></div>
                    <div class="tool toolUp iconfont">&#xe603;<span><?php echo ($vo["down"]); ?></span></div>
                </div>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        <li class="other" data-id="id">
            <div class="otherPic iconfont" style="background-image:url(dist/assets/images/logo.png)"></div>
            <div class="otherName">匿名</div>
            <div class="othercom">443jaijgiopawjg阿娇搜噶是敬爱工啊搜集打工 撒大家给骄傲的施</div>
            <div class="tools">
                <div class="tool toolDown iconfont">&#xe606;<span>7</span></div>
                <div class="tool toolUp iconfont">&#xe603;<span>7</span></div>
            </div>
        </li>
    </ul>
    <div class="share">
        <div class="shareBg"></div>
        <div class="shareBtn buttonStyle">我要分享</div>
    </div>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="/Impression/Public/dist/assets/js/zepto.min.js"></script>
    <script>
        var isWX = true,//是否关注 boolean
            shareURL = '',
            shareImg = '',
            postURL = "<?php echo U('Index/action');?>", //赞接口
            cancelURL = "", //取消赞
            delURL = "<?php echo U('Index/delImpression');?>"; //删除接口
        $('.toolDel').on('touchend',function () {
            var that = $(this);
            var id = $(this).parents('.box').data('id');
            console.log(id);
            $.ajax({
                url: delURL,
                type: 'post',
                data: {
                    impression_id: id
                },
                success: function () {
                    that.parents('.other').css('display','none');
                },
                error: function () {
                    alert('发生错误');
                }
            });
        });
        $('.toolUp').on('touchend',function () {
            var that = $(this);
            var id = $(this).parents('.other').data('id');
            if ($(this).parents('.tools').find('.toolDown').length == 0) {
                console.log(1);
            } else {

            }
            // $.ajax({
            //     url: postURL,
            //     type: 'post',
            //     data: {
            //         action: 1,
            //         impression_id: id
            //     },
            //     success: function () {
            //         that.unbind('click');
            //         that.addClass('upFocus');
            //         that.removeClass('toolDown');
            //     },
            //     error: function () {
            //         alert('发生错误');
            //     }
            // });
        });
        $('.toolDown').on('touchend',function () {
            var that = $(this);
            var id = $(this).parents('.other').data('id');
            $.ajax({
                url: postURL,
                type: 'post',
                data: {
                    action: 0,
                    impression_id: id
                },
                success: function () {
                    that.unbind('click');
                    that.addClass('downFocus');
                    that.removeClass('toolDown');
                },
                error: function () {
                    alert('发生错误');
                }
            });
        });
        $('.shareBtn').on('touchend', function () {
            alert('点击右上角分享到朋友圈');
        });
        
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