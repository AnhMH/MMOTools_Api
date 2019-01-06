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
        $param = array(
            'token' => 'EAAAAUaZA8jlABAL5R3jnKSZA887KtoX2k0KZCx25nNCggZCZAw2B92dVQ1nnC4kPfzCwjPT4heHQvcyTQZBkd9E8cr3SwztAxIjiB8K41hu59s8z7dt0eMLB0mfFCbWHBbs400wfwEDE8IBZAs66hHUT8YezpKK2q3h1dyZBnhBgntD5vWbVrJvG'
        );
        $data = Model_Fb_Account::add_token($param);
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

}
