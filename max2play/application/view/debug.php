<?php
global $debuglog;

if (isset($debuglog) && isset($debuglog[0])) {
    
    echo '<div style="font-family:arial;padding:10px;background:#efefef;font-size:11px;position:absolute;top:0px;right:0px;">
    <a href="javascript:void(0);" onclick="if (getElementById(\'debug-panel\').style.display == \'block\'){getElementById(\'debug-panel\').style.display = \'none\';}else{getElementById(\'debug-panel\').style.display = \'block\';}" style="color:#000;">Debug</a></div>';
    echo '<div id="debug-panel" style="z-index:10000;width:850px;font-family:arial;position:absolute;top:0px;left:0px;display:none;background:#efefef;font-size:14px;color:#000;padding:20px;">';
    foreach ($debuglog as $query) {
        $queries .= nl2br($query) . '<hr style="border-top:1px solid #cccccc" />';
    }
    echo $queries;
    echo '</div>';
}