<?php
/**
 Index Page
 
 @Copyright 2014 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
?>

<?php
ob_start();

include_once ('../application/bootstrap.php');

$service->loadViewHeader();

// search for Controller and View by Name in URL
if (! isset($_GET['page']) || strpos($_GET['page'], 'plugin') === FALSE) {
    $pagename = preg_replace('=[^0-9a-zA-Z\.]=', '', isset($_GET['page']) ? $_GET['page'] : '');
    if ($pagename == '') {
        // get Default Plugin for first Page
        foreach ($service->plugins['plugin'] as $plugin) {
            if (isset($plugin['default']) && $plugin['default'] == 1) {
                include_once (APPLICATION_PATH . $plugin['path']);
                break;
            }
        }
    }
} else {
    // get Plugin by Config... check for Images or other Content 
    if(preg_match('=\.(png|jpg|gif|pdf)$=', $_GET['page'])){
        // just Output Content                
        $file = APPLICATION_PATH . '/' . $_GET['page'];        
        ob_clean();
        $size = getimagesize($file);
        header('Content-Type: '.$size['mime']);
        header('Content-Length: '.filesize($file));
        readfile($file);
        exit;
    }else{
        include_once (APPLICATION_PATH . '/' . $_GET['page']);
    }
}

$service->loadViewFooter();

ob_flush();