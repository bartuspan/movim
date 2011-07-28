<?php

/**
 * @package Widgets
 *
 * @file Notifs.php
 * This file is part of MOVIM.
 *
 * @brief The notification widget
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 16 juin 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Notifs extends WidgetBase
{
    function WidgetLoad()
    {
    	$this->addcss('notifs.css');
    	$this->addjs('notifs.js');
   	    /*$notifs = Cache::c('activenotifs');
   	    $notifs = array();
	    Cache::c('activenotifs', $notifs);*/
    	//$this->addjs('friends.js');
		$this->registerEvent('incomesubscribe', 'onSubscribe');
    }
    
    function onSubscribe($payload) {
   	    $notifs = Cache::c('activenotifs');
   	    
   	    $html = '
            <li>
                '.$payload['from'].' '.t('wants to talk with you'). ' <br />
   	            <input id="notifsalias" class="tiny" value="'.$payload['from'].'" onfocus="myFocus(this);" onblur="myBlur(this);"/>
   	            <a class="button tiny" href="#" onclick="'.$this->genCallAjax("ajaxSubscribed", "'".$payload['from']."'").' showAlias(this);">'.t("Accept").'</a>
   	            <a class="button tiny" href="#" id="notifsvalidate" onclick="'.$this->genCallAjax("ajaxAccept", "'".$payload['from']."'", "getAlias()").' hideNotification(this);">'.t("Validate").'</a>
   	            <a class="button tiny" href="#" onclick="'.$this->genCallAjax("ajaxRefuse", "'".$payload['from']."'").' hideNotification(this);">'.t("Decline").'</a>
   	        </li>';
   	    $notifs['sub'.$payload['from']] = $html;
   	    
        RPC::call('movim_prepend', 'notifslist', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxSubscribed($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->subscribedContact($jid);
    }
    
    function ajaxRefuse($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->unsubscribed($jid);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxAccept($jid, $alias) {
		$xmpp = Jabber::getInstance();
        $xmpp->acceptContact($jid, false, $alias);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxAddContact($jid, $alias) {
		$xmpp = Jabber::getInstance();
        $xmpp->addContact($jid, false, $alias);
    }
    
    function build() {  
    $notifs = Cache::c('activenotifs');
    if($notifs == false)
        $notifs = array();
    ?>
    <div id="notifs">
        <ul id="notifslist">
            <?php
            ksort($notifs);
            foreach($notifs as $key => $value) {
                    echo $value;
            }
            ?>
            <li>
                <input id="addjid" class="tiny" value="user@server.tld" onfocus="myFocus(this);" onblur="myBlur(this);"/>
                <input id="addalias" class="tiny" value="<?php echo t('Alias'); ?>" onfocus="myFocus(this);" onblur="myBlur(this);"/>
                <a class="button tiny" href="#" id="addvalidate" onclick="<?php $this->callAjax("ajaxAddContact", "getAddJid()", "getAddAlias()"); ?>"><?php echo t('Validate'); ?></a>
                <a class="button tiny" href="#" onclick="addJid(this);"><?php echo t('Add a contact'); ?></a>
            </li>
        </ul>
    </div>
    <?php    
    }
}