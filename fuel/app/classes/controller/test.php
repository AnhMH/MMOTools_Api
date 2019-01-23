<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Facebook\Facebook;

class Controller_Test extends \Controller_App {

    /**
     * The basic welcome message
     *
     * @access  public
     * @return  Response
     */
    public function action_index() {
        $param = array(
            'id' => '1,2,9'
        );
        $data = Model_Fb_Account::check_live($param);
        echo '<pre>';
        print_r($data);
        die();
        $channelId = 'UCZ0zEZe8pWhmNpPdzwfxx4A';
        $key = 'AIzaSyCMCc_4fUlOGvY1PeP9Rw-TFs4qFDJJ1yE';
        $data = Lib\YtbDownloader::ytbSearch($key, '', 'Tháng Tư Là Lời Nói Dối Của Anh');
        echo '<pre>';
        print_r($data);
        die();
        $url = 'https://www.youtube.com/watch?v=6aFwjjIyfD8';
        $appId = '100274197065981';
        $appSecret = 'ceb5eaddbebb4590a991cf32a956f2f6';
        $accessToken = 'EAAAAUaZA8jlABAFwybRQpCffXgZCL5BRoI8KIa1uwu3KB8q5BKZA5VFFZCcZAvvz74yCQ7m9LIssFoI1ZCjX3ZByyIoGqvCut4tlzVwO6r3vOTlvtlpZB2hCffiFf1YNvgRRt2pnmaCTnU7bSNp2J5WdddvFRR2xIyMHBEAOhn3hKf2TQWFbfUoR';
        $config = array(
            'appId' => $appId,
            'secret' => $appSecret,
        );
        $fb = new Lib\Facebook($config);
        $feed = '/v2.8/165729847563083/videos';
        $params = array(
            'access_token' => $accessToken,
            'title' => 'aa',
            'description' => 'bb',
            'file_url' => Lib\YtbDownloader::downloader($url),
//            'URL' => 'https://www.youtube.com/watch?v=CcCihBrD2UA',
//            'file_url' => 'https://r3---sn-npoeened.googlevideo.com/videoplayback?txp=5531432&url=https%3A%2F%2Fr3---sn-npoeened.googlevideo.com%2Fvideoplayback%3Ftxp%3D5531432&lmt=1542114690650439&key=yt6&id=o-AEhIjWadaAiyt1EFYcTxdGOCD3bxaunTfgj3Pd_zwQTH&mn=sn-npoeened,sn-i3b7kn7k&c=WEB&ipbits=0&mm=31,26&ms=au,onr&mv=u&dur=443.501&source=youtube&pl=22&mime=video%2Fmp4&mt=1548089399&ip=45.252.248.10&fvip=1&ratebypass=yes&signature=CC9D2E36CC0BCF48A64BBB6A15FBA66C9F83FD93.05D51D060B2B443A3847DF933D4D172ED4C6152D&requiressl=yes&itag=22&ei=LfpFXO3AOceK1AbErry4Dw&expire=1548111502&sparams=dur,ei,id,ip,ipbits,itag,lmt,mime,mm,mn,ms,mv,pl,ratebypass,requiressl,source,expire&type=video%2Fmp4%3B+codecs%3D%22avc1.64001F,+mp4a.40.2%22&quality=hd720&signature=CC9D2E36CC0BCF48A64BBB6A15FBA66C9F83FD93.05D51D060B2B443A3847DF933D4D172ED4C6152D'
        );
        $data = $fb->api( $feed, 'POST', $params );
        echo '<pre>';
        print_r($data);
        die();
    }

    /**
     * Generate pass
     *
     * @access  public
     * @return  Response
     */
    public function action_pass() {
        include_once APPPATH . "/config/auth.php";
        $account = $_GET['acc'];
        $pass = $_GET['pw'];
        echo \Lib\Util::encodePassword($pass, $account);
    }

    /**
     * import coupon from attvn
     *
     * @access  public
     * @return  Response
     */
    public function action_attvnimportcoupon() {
        Model_Atvn_Coupon::import();
    }

    /**
     * import top product from attvn
     *
     * @access  public
     * @return  Response
     */
    public function action_attvnimporttopproduct() {
        Model_Atvn_Product::import();
    }

    /**
     * Get home post
     *
     * @access  public
     * @return  Response
     */
    public function action_fbgethomeposts() {
        Model_Fb_Auto_Like_Feed::get_posts();
    }

    /**
     * Auto like feed
     *
     * @access  public
     * @return  Response
     */
    public function action_fbautolikefeed() {
        Model_Fb_Auto_Like_Feed::auto_like();
    }

    /**
     * Add fb account
     *
     * @access  public
     * @return  Response
     */
    public function action_addfbaccount() {
        include_once APPPATH . "/config/auth.php";
        $account = $_GET['acc'];
        $pass = $_GET['pw'];
        $tokeninfo = Lib\AutoFB::getToken($account, $pass);
        $token = !empty($tokeninfo['access_token']) ? $tokeninfo['access_token'] : '';
        if (!empty($token)) {
            $profile = Lib\AutoFB::getProfile($token);
            $parram = array(
                'email' => $account,
                'password' => $pass,
                'token' => $token,
                'name' => $profile['name'],
                'fb_id' => $profile['id']
            );
            echo Model_Fb_Account::add_update($parram);
        } elseif (!empty($tokeninfo['error_data'])) {
            $err = json_decode($tokeninfo['error_data'], true);
            echo $err['error_message'];
        }
    }

    /**
     * Auto comment
     *
     * @access  public
     * @return  Response
     */
    public function action_fbautocomment() {
        ini_set('memory_limit', -1);
        Model_Fb_Auto_Comment::auto_comment();
    }

}
