<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller_Test extends \Controller_App {

    /**
     * The basic welcome message
     *
     * @access  public
     * @return  Response
     */
    public function action_index() {
        $url = 'https://www.facebook.com/mai.hoanganh.16#_';
        $url = 'https://www.facebook.com/TIN-NHANH-365-291823958346497/?modal=admin_todo_tour';
        $url = 'https://www.facebook.com/groups/HocVienYT/';
        $a = Lib\AutoFB::getUIDfromUrl($url);
        echo '<pre>';
        print_r($a);
        die();
        // Youtube video url
        $youtubeURL = 'https://www.youtube.com/watch?v=LFFibPk6f2Q';
        $youtubeURL = 'http://clips.vorwaerts-gmbh.de/VfE_html5.mp4';
//        $youtubeURL = 'https://r5---sn-uvu-c336.googlevideo.com/videoplayback?source=youtube&key=yt6&mime=video%2Fmp4&requiressl=yes&txp=5432432&initcwndbps=852500&ratebypass=yes&signature=82CCCF1CE990E87AC2A84EAC2316205452DF5803.7A066EAAA346A5A4EE81E0C6C2057C0159C2DE1F&ei=GBg0XPG6EOrRz7sPtqCmqAI&fvip=5&pl=24&sparams=dur%2Cei%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Cratebypass%2Crequiressl%2Csource%2Cexpire&mv=m&mt=1546917828&ms=au%2Conr&ip=1.10.186.157&lmt=1546867812552601&c=WEB&expire=1546939512&id=o-AOmnp7fwVm8spLd3F1WwXnnV9ETfGZ-LU5II3GufIdzr&dur=167.090&mn=sn-uvu-c336%2Csn-npoe7ned&mm=31%2C26&ipbits=0&itag=22&video_id=LFFibPk6f2Q&title=One+Piece+Chapter+930+Predictions+and+Release+Date%21';
//        $source = Lib\YtbDownloader::downloader($youtubeURL);
//        echo '<pre>';
//        print_r($source);
//        die();
        
        $pageId = '291823958346497';
        $title = 'aaa';
        $tokenPage = 'EAAAAUaZA8jlABAPHWc24KnaUpmGQlmvTBJXZCocMQHZCbRD5vmq3Q9JZCuVhYHEKjfemicXPfFcDiDxGdgPXaFjmTm4ZBHiYT8PV9VlhMfSuxVsfCBlYfSRuQtaQ5rJnWFVPhVHTZCxRyyQDzrZB5k5HASfGntnn7iGGzvHxrPbvAZDZD';
        $token = 'EAAAAUaZA8jlABAGNA7wUR0nMxRDAcLAhC2lhRCW6mVJWxuYW2WfXVawNuAjoqFufW6nIPVZCYOKk2sVIhUvOvJo5ZCrISRXKF2pX4Qa8LhKhkZBJj13NBCKNZBgyIYREgzKXbBXu3JRBZB1tsUOxFEQWIBPuY8G89Cf6ZB7AsnDfwZDZD';
//        $data = Lib\AutoFB::autoPostPageVideo($pageId, $youtubeURL, $title, $token);
//        $data = Lib\AutoFB::getPageVideos($pageId, $token, 5000);
        
        $post_url = "https://graph-video.facebook.com/{$pageId}/videos?"
 . "title=" . $title. "&description=" . $title
 . "&access_token=". $token;
        $ch = curl_init();
        $data = array('name' => 'file', 'file' => '@'.realpath(APPPATH."logs/video.mp4"));// use realpath
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        curl_close($ch);
        echo '<pre>';
//        print_r($source);
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
