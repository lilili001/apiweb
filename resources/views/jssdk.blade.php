<h1>这个是jssdk页面!</h1>
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo app('wechat.official_account')->jssdk->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo'), true) ?>);
    wx.ready(function () {
        setShare();  //定义函数
    });

    var shareTitle = "分享测试",
        shareLink = "http://www.baidu.com",
        shareDesc= "这个是我的分享测试",
        shareImgUrl = "https://ss2.bdstatic.com/70cFvnSh_Q1YnxGkpoWK1HF6hhy/it/u=2890253114,12112746&fm=26&gp=0.jpg";


    function setShare(){
        // 在这里调用 API，分享朋友圈  
        wx.onMenuShareTimeline({
            title: shareTitle, // 分享标题  
            link: shareLink, // 分享链接  
            desc:shareDesc,
            imgUrl: shareImgUrl, // 分享显示的缩略图  
            success: function () {
                // 用户确认分享后执行的回调函数  
                // alert('分享完成');  
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数  
                // alert('淘气了哦，你取消分享');  
            },fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
        //分享给朋友  
        wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题  
            link: shareLink, // 分享链接  
            desc:shareDesc,
            imgUrl: shareImgUrl, // 分享显示的缩略图  
            success: function () {
                // 用户确认分享后执行的回调函数  
                // alert('分享完成');  
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数  
                // alert('淘气了哦，你取消分享');  
            },fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    }

</script>