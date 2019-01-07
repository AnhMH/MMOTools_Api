<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller_AutoComments extends \Controller_App {
    /**
     * AutoComment login
     */
    public function action_all() {
        return \Bus\AutoComments_All::getInstance()->execute();
    }
    
    /**
     * AutoComment login
     */
    public function action_addupdate() {
        return \Bus\AutoComments_AddUpdate::getInstance()->execute();
    }
    
    /**
     * AutoComment addupdate multi
     */
    public function action_addupdatemulti() {
        return \Bus\AutoComments_AddUpdateMulti::getInstance()->execute();
    }
    
    /**
     * AutoComment list
     */
    public function action_list() {
        return \Bus\AutoComments_List::getInstance()->execute();
    }
    
    /**
     * AutoComment disable
     */
    public function action_disable() {
        return \Bus\AutoComments_Disable::getInstance()->execute();
    }
    
    /**
     * AutoComment detail
     */
    public function action_detail() {
        return \Bus\AutoComments_Detail::getInstance()->execute();
    }
    
    /**
     * AutoComment delete
     */
    public function action_delete() {
        return \Bus\AutoComments_Delete::getInstance()->execute();
    }
}