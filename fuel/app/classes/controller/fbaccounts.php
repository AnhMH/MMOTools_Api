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
    
    /**
     * FBAccount get token url
     */
    public function action_gettokenurl() {
        return \Bus\FBAccounts_GetTokenUrl::getInstance()->execute();
    }
    
    /**
     * FBAccount add from token
     */
    public function action_addtoken() {
        return \Bus\FBAccounts_AddToken::getInstance()->execute();
    }
    
    /**
     * FBAccount get uid from url
     */
    public function action_getuidfromurl() {
        return \Bus\FBAccounts_GetUIDFromUrl::getInstance()->execute();
    }
    
    /**
     * FBAccount check is live
     */
    public function action_checklive() {
        return \Bus\FBAccounts_CheckLive::getInstance()->execute();
    }
    
    /**
     * FBAccount update page
     */
    public function action_updatepage() {
        return \Bus\FBAccounts_UpdatePage::getInstance()->execute();
    }
    
    /**
     * FBAccount reup search
     */
    public function action_reupsearch() {
        return \Bus\FBAccounts_ReupSearch::getInstance()->execute();
    }
}