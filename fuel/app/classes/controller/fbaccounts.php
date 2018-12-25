<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller_FBAccounts extends \Controller_App {
    /**
     * FBAccount login
     */
    public function action_all() {
        return \Bus\FBAccounts_All::getInstance()->execute();
    }
    
    /**
     * FBAccount login
     */
    public function action_addupdate() {
        return \Bus\FBAccounts_AddUpdate::getInstance()->execute();
    }
    
    /**
     * FBAccount list
     */
    public function action_list() {
        return \Bus\FBAccounts_List::getInstance()->execute();
    }
    
    /**
     * FBAccount disable
     */
    public function action_disable() {
        return \Bus\FBAccounts_Disable::getInstance()->execute();
    }
    
    /**
     * FBAccount delete
     */
    public function action_delete() {
        return \Bus\FBAccounts_Delete::getInstance()->execute();
    }
}