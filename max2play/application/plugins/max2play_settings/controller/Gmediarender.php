<?php

/**
 Gmediarenderer Administration Controller
 Currently only working on Raspberry
 
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
class Gmediarender extends Service
{

    protected $pname = 'gmediarender';

    public $viewname = 'DLNA-Client Audio (gmediarender)';

    public function __construct()
    {
        parent::__construct();
        // Only Available for Debian - Ubuntu 14.04 has no gmediarenderer
        $version = $this->getLinuxVersion();
        if ($version[0] != 'Debian' && $version[0] != 'Raspbian') {
            $this->view->isAvailable = false;
        } else {
            $this->view->isAvailable = true;
        }
        if ($this->checkInstalled()) {
            if (isset($_GET['action'])) {
                if ($_GET['action'] == 'startgm') {
                    $this->view->message[] = $this->start($this->pname);
                }
                
                if ($_GET['action'] == 'stopgm') {
                    $this->view->message[] = $this->stop($this->pname);
                }
                
                if ($_GET['action'] == 'killgm') {
                    $this->view->message[] = $this->kill($this->pname);
                }
                
                if ($_GET['action'] == 'save') {
                    $this->selectAutostart(isset($_GET['autostartgm']) ? 1 : 0);
                    $this->saveAudioDevice();
                }
            }
            
            $this->view->pid = $this->status($this->pname);
            
            $this->view->autostart = $this->checkAutostart($this->pname, true);
            
            $this->getAudioDevice();
        } else {
            if (isset($_GET['action']) && $_GET['action'] == 'installgm') {
                $this->installGmediarender();
            }
        }
    }

    /**
     * Save Command-Line Options from
     * gmediarenderer_soundcard
     */
    public function saveAudioDevice()
    {
        $setsoundcard = $_GET['gmediarender_soundcard'];
        // Sounddevices werden in Squeezeplayer geladen!
        global $sp;
        $soundoutputs = array();
        foreach ($sp->view->soundDevices as $soundkeys => $sounddevice) {
            $soundoutputs[] = $soundkeys;
        }
        if (in_array($setsoundcard, $soundoutputs)) {
            $value = $setsoundcard;
        } else {
            $value = 'sysdefault';
        }
        
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'GMEDIARENDER_ALSA_DEVICE', $value)) {
            $this->view->message[] = str_replace('$SERVICE', $this->viewname, _('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
            
            if ($this->status($this->pname) !== FALSE) {
                // Restart Service
                $this->view->message[] = $this->stop($this->pname);
                $this->view->message[] = $this->start($this->pname);
            }
        }
        
        return true;
    }

    /**
     * Get Commandline Options for Audioplayer by Config File
     * 
     * @return boolean
     */
    public function getAudioDevice()
    {
        $output = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'GMEDIARENDER_ALSA_DEVICE');
        if ($output) {
            $this->view->gmediarender_soundcard = $output;
        } else {
            $this->view->gmediarender_soundcard = "sysdefault";
        }
        return $this->view->gmediarender_soundcard;
    }

    public function checkInstalled()
    {
        if (file_exists('/usr/local/bin/gmediarender'))
            $this->view->installed = true;
        else
            $this->view->installed = false;
        return $this->view->installed;
    }

    /**
     * install Script Gmediarender
     */
    public function installGmediarender()
    {
        $script[] = 'apt-get update
                    pushd /tmp
					git clone https://github.com/hzeller/gmrender-resurrect.git
					cd gmrender-resurrect
					echo "Y" | sudo apt-get install autoconf automake libtool
					echo "Y" | sudo apt-get install libupnp-dev libgstreamer0.10-dev \
					    gstreamer0.10-plugins-base gstreamer0.10-plugins-good \
					    gstreamer0.10-plugins-ugly \
					    gstreamer0.10-pulseaudio gstreamer0.10-alsa -y
					sudo apt-get install gstreamer0.10-plugins-bad -y
                    sudo ./autogen.sh
					sudo ./configure
					sudo make
					sudo make install';
        $this->view->message[] = nl2br($this->writeDynamicScript($script));
        $this->view->message[] = "Installation finished - Reload this page to see changes...";
        return true;
    }
}

$gm = new Gmediarender();
