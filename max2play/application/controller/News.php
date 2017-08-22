<?php

/**
 News Controller
 
 Get News and Cache it, save last timestamp for attempt to get new news
 
 Set Parameters for Newslocation, cachetime
 
 @Copyright 2015 Stefan Rick
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
class News extends Service
{

    private $cachepath = '/tmp/';

    private $newslocation;

    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /**
     * get News for position in template
     * 
     * @param number $position
     *            0 = sidebar
     */
    public function getNews($position = 0)
    {
        // TODO: cachetimeout static
        if (file_exists($this->cachepath . $position . '.html') && (filemtime($this->cachepath . $position . '.html') > (time() - 86000))) {
            return file_get_contents($this->cachepath . $position . '.html');
        } else {
            $this->getHardwareInfo();
            $image = $this->getConfigFileParameter('/opt/max2play/options.conf', 'specialimage');
            $this->newslocation = $this->getConfigFileParameter('/opt/max2play/options.conf', 'newslocation');
            if (! $this->newslocation) {
                // Set to Default
                $this->newslocation = 'https://www.max2play.com/wp-content/uploads/max2play/news/news.php';
                $this->saveConfigFileParameter('/opt/max2play/options.conf', 'newslocation', $this->newslocation);
            }
            $lang = substr(setlocale(LC_MESSAGES, 0), 0, 2);
            if ($this->newslocation !== '' && strpos($this->newslocation, 'http') !== FALSE) {
                $new_content = $this->getExternalFile($this->newslocation . '?position=' . $position . '&device=' . urlencode($this->info->hardware) . '&lang=' . $lang . '&image=' . urlencode($image), 2);
            }
            if (! $new_content) {
                $new_content = 'No News';
            }
            if (strlen($new_content) > 2000) {
                // Too much Content - something went wrong! E.g. when used as Access Point
                $new_content = 'No News';
            }
            if (! file_exists($this->cachepath)) {
                mkdir($this->cachepath);
            }
            file_put_contents($this->cachepath . $position . '.html', $new_content);
            return $new_content;
        }
    }
}

$news = new News();

