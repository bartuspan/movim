<?php

/**
 * @file Log.php
 * This file is part of MOVIM.
 * 
 * @brief The log widget.
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Log extends Widget
{
    function widgetLoad()
    {
        $this->addcss('log.css');
        $this->registerEvent('allEvents', 'onEvent');
    }

	function build()
	{
		?>
		<h3>Debug console </h3>
		<div id="log">

       	</div>
		<?php
	}

    function onEvent($data)
    {
        $this->sendto('log', 'PREPEND', "<span>" . date('H:i:s> ') . "data :</span> " . var_export($data, true) ."<br />");
    }
}

?>