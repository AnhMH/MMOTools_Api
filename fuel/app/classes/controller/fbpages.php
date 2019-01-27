<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller_FbPages extends \Controller_App {
    /**
     * FbPage login
     */
    public function action_all() {
        return \Bus\FbPages_All::getInstance()->execute();
    }
    
    /**
     * FbPage login
     */
    public function action_addupdate() {
        return \Bus\FbPages_AddUpdate::getInstance()->execute();
    }
    
    /**
     * FbPage list
     */
    public function action_list() {
        return \Bus\FbPages_List::getInstance()->execute();
    }
    
    /**
     * FbPage disable
     */
    public function action_disable() {
        return \Bus\FbPages_Disable::getInstance()->execute();
    }
    
    /**
     * FbPage delete
     */
    public function action_delete() {
        return \Bus\FbPages_Delete::getInstance()->execute();
    }
}