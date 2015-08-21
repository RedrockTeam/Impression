<?php

namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller{

    private $verify_url = 'http://hongyan.cqupt.edu.cn/MagicLoop/index.php?s=/addon/Api/Api/openidVerify';
    private $info_url = 'http://hongyan.cqupt.edu.cn/MagicLoop/index.php?s=/addon/Api/Api/userInfo';
    private $appid = 'wx81a4a4b77ec98ff4';
    private $acess_token = 'gh_68f0a1ffc303';
    public function index() {
        $openid = I('get.openid', session('openid'));
        if(!$openid && !session('openid')) {
            $this->error('请从小帮手进入');
        }
        $timestamp = time();
        $string = 'sadfsadfdsfa';
        $access = array(
                'token' => 'gh_68f0a1ffc303',
                'timestamp' => $timestamp,
                'string' => $string,
                'secret' => sha1(sha1($timestamp) . md5($string) . "redrock"),
                'openid' => $openid
        );
        if (!$this->checkAttention($access)) {
            $data = array(
                    'care' => false
            );
            $this->assign('data', $data);
            $this->display(); //todo
        }
        session('openid', $openid);
        $map = array(
                'openid' => $openid
        );
        $data = M('users')->where($map)->find();
        if ($data == null) {
            $data = $this->curl_api($this->info_url, $access);
            $save = array(
                    'openid' => $data['data']['openid'],
                    'avatar' => $data['data']['headimgurl'],
                    'signature' => '',
                    'nickname' => $data['data']['nickname'],
            );
            $data = M('users')->add($save);
        }
        session('uid', $data['id']);
        $this->assign('data', $data);
        $this->display(); //todo
    }



    //查看印象
    public function viewImpression() {
        $uid = I('get.uid');
        if(!$uid) {
            $this->error('非法链接');
        }
        $impression = M('impression_user');
        $openid = M('users')->where(array('id' => $uid))->getField('openid');
        $map = array(
                'to_openid' => $openid,
                'status' => 1
        );
        $data = $impression->where($map)
                ->join('join users on impression_user.from_openid = users.openid')
                ->field('impression_user.id as impression_id, content, praise, down, nickname, avatar')
                ->select();
        foreach($data as &$value) {
           $search = array(
               'impression_id' =>  $value['impression_id'],
               'openid' => session('openid')
           );
            if(M('user_praise')->where($search)->count()) {
                $value['action'] = M('user_praise')->where($search)->getField('action');
            } else {
                $value['action'] = 2;
            }
        }
        $num = $impression->where($map)->count();
        $user = M('users')->where(array('id' => $uid))->find();
        $this->assign('data', $data);
        $this->assign('num', $num);
        $this->assign('user', $user);
        $this->display('personal'); //todo
    }

    //发表印象页面
    public function createImpressionView() {
        $uid = I('get.uid');
        if($uid < 1) {
            $this->error('参数错误');
        }
        //获取openid
        $code = I('get.code');
        if($code == null){
            $re_url = urlencode(U('Index/createImpressionView').'?uid='.$uid);
            return redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=http%3a%2f%2fhongyan.cqupt.edu.cn%2f$re_url&response_type=code&scope=snsapi_userinfo&state=sfasdfasdfefvee#wechat_redirect");//todo 回调域名
        }else{
            session('code', $code);
            $return =  json_decode($this->getOpenId());
            $openid = $return['data']['openid'];
            $timestamp = time();
            $string = 'sadfsadfdsfa';
            $access = array(
                    'token' => 'gh_68f0a1ffc303',
                    'timestamp' => $timestamp,
                    'string' => $string,
                    'secret' => sha1(sha1($timestamp).md5($string)."redrock"),
                    'openid' => $openid
            );
            if(!$this->checkAttention($access)) {
                $this->error('请先关注小帮手~');
            }
            session('openid', $openid);
        }
        session('to_id', $uid);
    }

    //发表印象页面
    public function createImpressionPage() {
        $this->display('comment');
    }
    //发表印象
    public function createImpression() {
        if(!session('openid')) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '非法登录!'
            ));
        }
        $data = I('post.');
        $content = trim($data['content']);
        if(!$content) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '内容不能为空'
            ));
        }
        if(mb_strlen($content, 'utf8') > 60) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '内容过长'
            ));
        }
        if($data['hide']>0) {
            $to_openid = 'niming';
        } else {
            $to_openid = M('users')->where(array('id' => $data['uid']))->getField('openid');
        }
        $save = array(
                'from_openid' => session('openid'),
                'to_openid' => $to_openid,
                'content' => $content,
                'praise' => 0,
                'down' => 0,
                'status' => 1
        );
        M('impression_user')->add($save);
        $this->ajaxReturn(array(
                'status' => 200,
                'info' => '成功'
        ));
    }

    //点赞/踩
    public function action() {
        $input = I('post.');
        if(!is_numeric($input['action']) || !is_numeric($input['impression_id'])) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '非法数据!'
            ));
        }
        $openid = session('openid');
        if(!M('impression_user')->where(array('id' => $input['impression_id']))->count()) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '不存在此印象!'
            ));
        }
        $search = array(
            'openid' => $openid,
            'impression_id' => $input['impression_id'],
        );
        if(M('user_praise')->where($search)->count()) {
            $this->ajaxReturn(array(
                'status' => 403,
                'info' => '你已经评价过了'
            ));
        }
        $data = array(
                'openid' => $openid,
                'impression_id' => $input['impression_id'],
                'action' => $input['action']
        );
        M('user_praise')->add($data);
        if($input['action'] > 0) {
            M('impression_user')->where(array('id' => $input['impression_id']))->setInc('praise');
        } else {
            M('impression_user')->where(array('id' => $input['impression_id']))->setInc('down');
        }
        $action = $input['action'] > 0 ? '点赞' : '踩';
        $this->ajaxReturn(array(
                'status' => 200,
                'info' => $action.'成功'
        ));
    }

    //编辑个性签名页面
    public function edtiSignaturePage() {
        $this->display('card');
    }

    //编辑个性签名
    public function edtiSignature() {
        $openid = session('openid');
        if(!$openid) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '非法登录!'
            ));
        }
        $signature = I('post.signature');
        if(mb_strlen($signature, 'utf8') > 60) {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '内容过长'
            ));
        }
        M('users')->where(array('openid' => $openid))->save(array('signature' => $signature));
        $this->ajaxReturn(array(
                'status' => 200,
                'info' => '成功'
        ));

    }

    //删除印象
    public function delImpression() {
        $openid = session('openid');
        $impression_id = I('post.impression_id');
        $map = array(
                'to_openid' => $openid,
                'id' => $impression_id
        );
        if(M('impression_user')->where($map)->save(array('status' => 0))) {
            $this->ajaxReturn(array(
                    'status' => 200,
                    'info' => '成功'
            ));
        } else {
            $this->ajaxReturn(array(
                    'status' => 403,
                    'info' => '非法操作'
            ));
        }

    }

    //分享页面
    public function share(){
        $code = I('get.code');
        $uid = I('get.uid');
        if($code == null){
            $re_url = urlencode(U('Index/share').'?uid='.$uid);
            return redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=http%3a%2f%2fhongyan.cqupt.edu.cn%2f$re_url&response_type=code&scope=snsapi_userinfo&state=sfasdfasdfefvee#wechat_redirect");//todo 回调域名
        }else{
            session('code', $code);
            $return =  json_decode($this->getOpenId());
            $openid = $return['data']['openid'];
            if(!$openid) {
                $this->error('身份认证失败!');
            }
            session('openid', $openid);
        }

        if(!$uid) {
            $this->error('非法链接');
        }
        $impression = M('impression_user');
        $openid = M('users')->where(array('id' => $uid))->getField('openid');
        $map = array(
            'to_openid' => $openid,
            'status' => 1
        );
        $data = $impression->where($map)
            ->join('join users on impression_user.from_openid = users.openid')
            ->field('impression_user.id as impression_id, content, praise, down, nickname, avatar')
            ->select();
        foreach($data as &$value) {
            $search = array(
                'impression_id' =>  $value['impression_id'],
                'openid' => session('openid')
            );
            if(M('user_praise')->where($search)->count()) {
                $value['action'] = M('user_praise')->where($search)->buildSql();
            } else {
                $value['action'] = '';
            }
        }
        $num = $impression->where($map)->count();
        $user = M('users')->where(array('id' => $uid))->find();
        $this->assign('data', $data);
        $this->assign('num', $num);
        $this->assign('user', $user);
        $this->display('personal'); //todo
        $this->display('share');
    }

    //检测关注小帮手
    private function checkAttention($access) {
        $result = $this->curl_api($this->verify_url, $access);
        if($result['status'] != 200) {
            return false;
        }
        return true;
    }

    /*curl通用函数*/
    private function curl_api($url, $data){

        // 初始化一个curl对象
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        // 运行curl，获取网页。
        $contents = json_decode(curl_exec($ch),true);
        // 关闭请求
        curl_close($ch);
        return $contents;
    }

    //获取用户openid
    private function getOpenId () {
        $code = session('code');
        $time=time();
        $str = 'abcdefghijklnmopqrstwvuxyz1234567890ABCDEFGHIJKLNMOPQRSTWVUXYZ';
        $string='';
        for($i=0;$i<16;$i++){
            $num = mt_rand(0,61);
            $string .= $str[$num];
        }
        $secret =sha1(sha1($time).md5($string)."redrock");
        $t2 = array(
                'timestamp'=>$time,
                'string'=>$string,
                'secret'=>$secret,
                'token'=>$this->acess_token,
                'code' => $code,
        );
        $url = "http://hongyan.cqupt.edu.cn/MagicLoop/index.php?s=/addon/Api/Api/webOauth";
        return json_encode($this->curl_api($url, $t2), true);
    }
}
