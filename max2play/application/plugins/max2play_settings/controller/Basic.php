<?php

/**
 Basic Administration Controller
 
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
class Basic extends Service
{

    public $timezonesfile = '/opt/max2play/timezones.txt';

    public $plugins;

    public function __construct()
    {
        parent::__construct();
        $this->pluginname = _('Settings / Reboot');
        $reboot = false;
        
        $this->view->locales = array(
            'deutsch / Deutschland' => 'de_DE.UTF-8',
            'deutsch / Schweiz' => 'de_CH.UTF-8',
            'english' => 'en_GB.UTF-8',
            'italiano' => 'it_IT.UTF-8',
            'français' => 'fr_FR.UTF-8',
            'türkçe' => 'tr_TR.UTF-8',
            'nederlands' => 'nl_NL.UTF-8',
            'español' => 'es_ES.UTF-8',
            'português' => 'pt_PT.UTF-8',
            'english / US' => 'en_US.UTF-8',
            'chinese' => 'zh_CN.UTF-8'
        );
        
        if (isset($_REQUEST['action'])) {
            if ($_REQUEST['action'] == 'reboot') {
                $reboot = $this->reboot();
            }
            
            if ($_REQUEST['action'] == 'reset') {
                $this->view->message[] = $this->resetFactoryDefaults();
            }
            
            if ($_REQUEST['action'] == 'shutdown') {
                $this->view->message[] = _("Shutdown initiated - press the power button on device to start it again");
                $this->view->message[] = $this->writeDynamicScript(array(
                    'poweroff'
                ));
            }
            
            if ($_REQUEST['action'] == 'expandfs') {
                $this->resizeFS();
            }
            
            if ($_REQUEST['action'] == 'fixusbmount') {
                $this->fixUsbMount();
            }
            
            if ($_REQUEST['action'] == 'reloadLicense') {
                if (! $this->updateEmail($_REQUEST['email'])) {
                    $this->checkLicense();
                }
            }
            
            if ($_REQUEST['action'] == 'switchDEBUG_WEBINTERFACE') {
                $this->switchDebugWebinterface();
            }
            
            if ($_REQUEST['action'] == 'checkMax2PlayUpdate') {
                $this->checkMax2PlayUpdate();
            }
            if ($_REQUEST['action'] == 'checkMax2PlayBetaUpdate') {
                $this->checkMax2PlayUpdate('beta');
            }
            
            if ($_REQUEST['action'] == 'pluginconfig') {
                $this->view->message[] = $this->pluginConfig($_REQUEST['activeplugin'], $_REQUEST['defaultplugin']);
                $this->loadViewHeader(true);
            }
            if ($_REQUEST['action'] == 'installplugin') {
                $this->installPlugin($_REQUEST['installplugin'], true);
            }
            
            if ($_REQUEST['action'] == 'save') {
                if (isset($_REQUEST['playername']))
                    $this->updatePlayername($_REQUEST['playername']);
                if (isset($_REQUEST['locale']))
                    $this->updateLocale($_REQUEST['timezone'], $_REQUEST['locale']);
                
                $this->setDonateButton((isset($_REQUEST['removedonate']) ? 1 : 0));
                $this->updateDisplayResolution($_REQUEST['displayResolution']);
                $this->updateEmail($_REQUEST['email']);
                $this->updateMax2playNetworkLookup();
                $this->setHelpOnSidebar($_REQUEST['showhelponsidebar']);
                if ($this->getHardwareInfo() == 'Raspberry PI') {
                    $this->updateAutostartX();
                }
            }
        }
        $this->getLocale();
        $this->getMax2playNetworkLookup();
        $this->getPlayername();
        $this->getDisplayResolutions();
        $this->parsePlugins();
        $this->getDebug();
        $this->getEmail();
        $this->enableBetaUpdates();
        $this->view->removedonate = $this->getDonate();
        $this->getHelpOnSidebar();
        $this->showHelpSidebar();
        
        if ($this->getHardwareInfo() == 'Raspberry PI') {
            $this->getAutostartX();
        }
        
        if ($reboot) {
            $this->writeDynamicScript(array(
                'sleep 1s;sudo /sbin/reboot -n;'
            ), false, true);
        }
        return true;
    }

    /**
     * Check for Reboot
     * 
     * @return boolean
     */
    public function reboot()
    {
        if ($this->getReadOnlyFS() != FALSE) {
            $path = '/tmp/';
        } else
            $path = '/opt/max2play/cache/';
        
        if ($_REQUEST['ajax'] == 1) {
            ob_end_clean();
            // Check for time difference in reboot.txt and set finished only if more than 15 seconds passed since reboot
            $rebootTime = strtotime(trim(shell_exec('grep -io "[^|]*" ' . $path . 'reboot.txt')));
            if ($rebootTime < (time() - 10)) {
                echo _("REBOOT successful") . " - finished";
                shell_exec('rm ' . $path . 'reboot.txt');
            } else {
                echo _("Please wait...");
            }
            ob_flush();
            die();
            return false;
        } else {
            if (isset($_REQUEST['redirecturl'])) {
                $url = $_REQUEST['redirecturl'];
                $reload = 1;
            } else {
                $url = false;
                $reload = 1;
            }
            if ($this->getProgressWithAjax($path . 'reboot.txt', 1, $reload, 0, _("REBOOT started"), $url, $failmessage = _('Previous Reboot status unknown - click Reboot again to force Reboot.')))
                return true;
            else
                return false;
        }
    }

    public function getDisplayResolutions()
    {
        $resolutions = array(
            '1024x768' => '1024x768-noedid',
            '1920x1080@60' => '1080p-edid',
            '1080p' => '1080p-noedid',
            '1280x720M@60' => '720p-edid',
            '720p' => '720p-noedid'
        );
        $this->view->currentResolution = 'Autodetect - NONE SET';
        
        if ($this->getHardwareInfo() == 'ODROID-XU3') {
            preg_match_all('=# ([^\n]*)\n(# )?setenv videoconfig "([^"]*)"=ism', shell_exec('cat /media/boot/boot.ini'), $matches);
            $this->view->displayResolutions = $matches[1];
            $this->view->displayResolutions[] = 'Autodetect - NONE SET';
            $this->view->displayResolutionValues = $matches[3];
            foreach ($matches[2] as $key => $selector) {
                if ($selector == '') {
                    $this->view->currentResolution = $matches[1][$key];
                }
            }
        } else {
            
            $output = shell_exec('ls /boot/ | grep boot-');
            preg_match_all("=boot-(.*?).scr=", $output, $matches);
            if ($matches[1])
                $this->view->displayResolutions = $matches[1];
            else
                return false;
            $output = shell_exec('cat /boot/boot.scr');
            preg_match_all("=HDMI-A-1:([0-9xM@]*)=", $output, $matches);
            
            if ($matches[1][0])
                // EDID Options
                $this->view->currentResolution = $resolutions[$matches[1][0]];
            else {
                // NOEDID Options
                preg_match_all("=-([0-9px]{4,})-(edid|noedid)=", $output, $matches);
                if ($matches[1][0]) {
                    $this->view->currentResolution = $resolutions[$matches[1][0]];
                } else
                    $this->view->currentResolution = 'auto_edid';
            }
        }
        return true;
    }

    public function updateDisplayResolution($newResolution = '')
    {
        if (! $this->getDisplayResolutions())
            return false;
        if ($this->view->currentResolution != $newResolution && in_array($newResolution, $this->view->displayResolutions)) {
            if ($this->getHardwareInfo() == 'ODROID-XU3') {
                // get Position and change boot.ini file
                foreach ($this->view->displayResolutions as $key => $value) {
                    if ($value == $this->view->currentResolution && isset($this->view->displayResolutionValues[$key])) {
                        // Disable previous one
                        $script[] = 'sed -i "s~.*' . $this->view->displayResolutionValues[$key] . '~# setenv videoconfig \"' . $this->view->displayResolutionValues[$key] . '~" /media/boot/boot.ini';
                    }
                    if ($value == $newResolution && isset($this->view->displayResolutionValues[$key])) {
                        // enable new one
                        $script[] = 'sed -i "s~.*' . $this->view->displayResolutionValues[$key] . '~setenv videoconfig \"' . $this->view->displayResolutionValues[$key] . '~" /media/boot/boot.ini';
                    }
                }
                $this->writeDynamicScript($script);
            } else {
                $output = shell_exec('sudo cp /boot/boot-' . $newResolution . '.scr /boot/boot.scr');
            }
            $this->view->message[] = _('Changed display resolution - Reboot needed');
        } else {
            // $this->view->message[] = _('no valid resolution choosen');
        }
        
        return true;
    }

    public function getPlayername()
    {
        $output = trim(shell_exec('cat /opt/max2play/playername.txt'), "\n");
        $this->view->playername = $output;
        $output = trim(shell_exec('cat /etc/hostname'), "\n");
        $this->view->hostname = $output;
        return $output;
    }

    /**
     * get settings for timezone
     */
    public function getLocale()
    {
        $this->view->currentTimezone = trim(shell_exec('cat /etc/timezone'), "\n");
        if (! file_exists($this->timezonesfile)) {
            $script[] = "find /usr/share/zoneinfo/right/ -type f | sed 's/\/usr\/share\/zoneinfo\/right\///' > /opt/max2play/timezones.txt";
            $this->writeDynamicScript($script);
            $this->view->message[] = _('Timezone file created');
        }
        $this->view->timezones = explode("\n", shell_exec('cat ' . $this->timezonesfile));
        
        $output = shell_exec('cat /etc/default/keyboard');
        preg_match('=XKBLAYOUT\="([a-z]{2})"=', $output, $match);
        $this->view->currentKeyboard = $match[1];
        
        // Sprache setzen
        $output = shell_exec('cat /etc/default/locale');
        preg_match('=LANG\=["]?([a-zA-Z0-9\.\-\_]*)=', $output, $match);
        $this->view->currentLocale = $match[1];
        
        return true;
    }

    public function updateLocale($timezone = '', $locale = '')
    {
        $this->getLocale();
        $script = array();
        if (in_array($timezone, $this->view->timezones)) {
            if ($this->view->currentTimezone == $timezone) {
                // No changes!
            } else {
                // Timezone setzen
            	$script[] = 'echo "'.$timezone.'" > /etc/timezone && ln -fs /usr/share/zoneinfo/`cat /etc/timezone` /etc/localtime && dpkg-reconfigure -f noninteractive tzdata';            	
                $linux = $this->getLinuxVersion();
                if (isset($linux[1]) && $linux[1] == 'xenial') {
                    // Ubuntu 16.04: timedatectl set-timezone $timezone
                    $script[] = 'timedatectl set-timezone "' . $timezone . '"';
                }
            }
            
            if ($this->view->currentLocale == $locale) {
                // No changes!
            } else {
                // Keyboard Layout setzen
                $script[] = "sed -i 's/XKBLAYOUT=.*/XKBLAYOUT=\"" . substr($locale, 0, 2) . "\"/' /etc/default/keyboard";
                
                // Ist die neue Sprache verfügbar?
                if (trim(shell_exec('locale -a | grep ' . str_replace('UTF-8', 'utf8', $locale)), "\n") == '') {
                    // Unterscheidung PI / ODROID
                    if ($this->getSystemUser() == 'pi') {
                        // Datei anpassen
                        $script[] = "sed -i 's/# " . $locale . " UTF-8/" . $locale . " UTF-8/' /etc/locale.gen";
                        $script[] = "locale-gen";
                    } else {
                        $script[] = 'locale-gen ' . $locale;
                    }
                }
                $script[] = "update-locale LANG=" . $locale;
            }
            if (isset($script[0])) {
                $output = $this->writeDynamicScript($script);
                $this->view->message[] = _("Changes successful - Reboot needed");
            }
            return true;
        } else {
            $this->view->message[] = _("Value for Timezone/Language not found.");
            return false;
        }
    }

    /**
     * Activate / Deactivate Debug
     */
    public function switchDebugWebinterface()
    {
        $this->saveConfigFileParameter('/opt/max2play/options.conf', 'DEBUG_WEBINTERFACE', isset($_REQUEST['DEBUG_WEBINTERFACE']));
        $this->debug = isset($_REQUEST['DEBUG_WEBINTERFACE']);
        $this->view->message[] = _("The Debug Output (console commands) is shown in the top right corner on each page.");
        return true;
    }

    /**
     * Change Player Name
     */
    public function updatePlayername($name = '')
    {
        $name = preg_replace('=[^a-zA-Z0-9\.\-]=i', '', $name);
        if ($name != '') {
            $this->getPlayername();
            if ($name != $this->view->playername) {
                // Playername anpassen
                $output = shell_exec('echo ' . $name . ' > /opt/max2play/playername.txt');
                
                // Hostname anpassen
                $output = shell_exec('echo ' . $name . ' > /etc/hostname');
                
                // Hosts anpassen
                $output = $this->writeDynamicScript(array(
                    'sed -i \'s/' . $this->getHostname($this->view->playername) . ' ' . $this->view->playername . '/' . $this->getHostname($name) . ' ' . $name . '/\' /etc/hosts'
                ));
                $output = $this->writeDynamicScript(array(
                    'sed -i \'s/' . $this->view->playername . '/' . $name . '/\' /etc/hosts'
                ));
                
                $this->view->message[] = _("Changes successful - Reboot needed");
                return true;
            }
        } else {
            $this->view->message[] = _("Name not possible! Please do not use special characters.");
            return false;
        }
    }

    /**
     * Alle Grundeinstellungen wiederherstellen: Autostart, XBMC, PlayerName, Wlan, etc.
     */
    public function resetFactoryDefaults()
    {
        // Ursprungsfiles sind als Sicherungen in den jeweiligen Ordnern enthalten
        $files = array(
            '/opt/max2play/playername.txt',
            '/etc/hostname',
            '/etc/network/interfaces',
            '/etc/fstab',
            '/boot/boot.scr',
            '/opt/max2play/wpa_supplicant.conf',
            '/opt/max2play/samba.conf',
            '/opt/max2play/options.conf', // TODO: .sav
            '/opt/max2play/audioplayer.conf' // TODO: .sav
        );
        
        foreach ($files as $filename) {
            if (file_exists($filename) && file_exists($filename . '.sav'))
                $output = shell_exec('cat ' . $filename . '.sav > ' . $filename);
        }
        // Correct install problems with dpkg
        $this->writeDynamicScript(array(
            'dpkg --configure -a'
        ));
        return _("Config Files restored for ") . ": <br />" . implode('<br />', $files);
    }

    public function getMax2playNetworkLookup()
    {
        return $this->view->Max2PlayNetworkLookup = $this->checkAutostart('Max2PlayNetworkLookup', true);
    }

    public function updateMax2playNetworkLookup()
    {
        if ($this->updateAutostart('Max2PlayNetworkLookup', (bool) $_REQUEST['Max2PlayNetworkLookup'], true)) {
            $this->view->message[] = _('Max2Play Network Player Lookup saved');
            $this->view->message[] = _('Network Player List is currently generated in background and will be available in ~10 seconds.');
            $this->loadViewHeader(true);
        }
        return true;
    }

    public function setDonateButton($removedonate = 0)
    {
        if ($this->saveConfigFileParameter('/opt/max2play/options.conf', 'removedonate', $removedonate)) {
            $this->loadViewHeader(true);
            $this->view->message[] = _('Thank you so much for your donation! We will keep up the work on the project to make it even better!');
        }
        return true;
    }

    public function updateEMail($email = '')
    {
        if ($this->saveConfigFileParameter('/opt/max2play/options.conf', 'email', $email)) {
            $this->view->message[] = _('Your eMail-address / activation code is saved.');
            if ($this->checkLicense() != false) {
                $this->view->message[] = _('Your license is validated. Now you have access to all features and plugins.');
                // Remove any Messages from Service Class (e.g. not activated License)
                global $service;
                if (is_object($service) && isset($service->view->error))
                    $service->view->error = array();
                
                // Rasbperry PI Settings load Plugin and activate
                if ($this->getHardwareInfo() == 'Raspberry PI') {
                    // Not Needed anymore
                    // $this->installPlugin('http://shop.max2play.com/media/downloadable/currentversion/raspberrysettings.tar');
                    // $this->parsePlugins();
                    // foreach($this->view->pluginselect as $key => $value){
                    // if($value['active'] == 1)
                    // $activeplugins[$key] = $value['name'];
                    // if($value['default'] == 1)
                    // $defaultplugin = $value['name'];
                    // }
                    // $activeplugins[] = 'Raspberry Settings';
                    // $this->pluginConfig($activeplugins, $defaultplugin);
                    if (strpos($email, '@') === FALSE)
                        $this->view->message[] = _('<b>For premium indication in the Max2Play Forum</b>, you may <a href="https://www.max2play.com/en/getting-started/connect-your-license-code-to-your-email-address/" target="_blank">connect your premium-code with your email-address</a>. This is also useful if you lose your code or want to receive the Max2Play Magazine with tips on Setup and Hardware.');
                    $this->loadViewHeader(true);
                }
            } else {
                $this->view->message[] = _('Your license could not be validated. Did you choose the right eMail-Address that is registered as a customer at www.max2play.com? If you have a key for activation please recheck, that the key is entered correctly.');
            }
            return true;
        }
        return false;
    }

    /**
     * check for available Updates and do it
     */
    public function checkMax2PlayUpdate($version = '')
    {
        ignore_user_abort(true);
        $this->getVersion();
        if ($version == 'beta') {
            $this->info->version = 0; // Reset
        }
        
        // Always Check for Plugin Updates
        // Do this first to make sure add_scripts sees changes in custom header / css files
        $this->_checkPluginUpdates($version);
        
        // Check auf Update
        $file = file_get_contents('http://shop.max2play.com/media/downloadable/currentversion/version.txt');
        if ((float) $this->info->version < (float) $file) {
            $this->view->message[] = _('Max2Play update started');
            // Start Script -> Download Files for Webserver and /opt/max2play
            $shellanswer = shell_exec('sudo /opt/max2play/update_max2play.sh ' . $version);
            $this->view->message[] = $this->formatMessageOutput($shellanswer);
            if (strpos($shellanswer, 'inflating: /opt/max2play/list_devices.sh') !== FALSE && strpos($shellanswer, 'extracting: /var/www/max2play/application/config/version.txt') !== FALSE) {
                $this->view->message[] = _('UPDATE SUCCESSFUL');
                $this->view->message[] = _('Max2Play-Webinterface Restarted - this takes up to 15 seconds - Wait for 15 seconds and Reload Page to see Changes');
                // Reload apache as Deamon in Background!
                $this->view->message[] = '<a href="http://www.max2play.com/changelog-max2play/" target="blank"><b>' . _('Click here to see what is new (Changelog)!') . '</b></a>';
                $this->view->message[] = $this->writeDynamicScript(array(
                    'sleep 15;/etc/init.d/apache2 restart'
                ), false, true);
            } else
                $this->view->message[] = _('UPDATE NOT SUCCESSFUL');
            
            $this->checkLicense();
            
            $this->loadViewHeader(true);
        } elseif ($file === false) {
            $this->view->message[] = _('Max2Play Website not available - maybe no internet connection? Please try a reboot to fix the internet connection.');
        } else {
            $this->view->message[] = _('Max2Play is up to date - no update required');
        }
        
        return true;
    }

    /**
     * Function to parse all installed Plugins and check for Updates
     */
    private function _checkPluginUpdates($version = 'currentversion')
    {
        $this->view->message[] = _('Running Check for Plugin Updates and install if available');
        $plugins = $this->getActivePlugins();
        $updatePlugins = false;
        for ($i = 0; $i < count($plugins['plugin']); $i ++) {
            $plugin = &$plugins['plugin'][$i];
            // check for updates
            if (isset($plugin['updateurl']) && $plugin['updateurl'] != '' && ! is_array($plugin['updateurl'])) {
                $lastupdate = new DateTime();
                // Check for missing complete Path in UpdateURL (e.g. coming from other sources / Preinstalled) and add path to Max2Play Server
                // https://shop.max2play.com/media/downloadable/currentversion/$1.tar
                if(strpos($plugin['updateurl'], 'http') === FALSE){
                    $plugin['updateurl'] = 'https://shop.max2play.com/media/downloadable/currentversion/'. $plugin['updateurl'] .'.tar';
                }
                
                // Switch Version of Plugin to beta / currentversion and change URL and force Update if path Changed
                if ($version == 'beta') {
                    // PATH: http://shop.max2play.com/media/downloadable/beta/accesspoint.tar
                    if (strpos($plugin['updateurl'], 'downloadable/currentversion')) {
                        $plugin['updateurl'] = str_replace('downloadable/currentversion', 'downloadable/beta', $plugin['updateurl']);
                        $plugin['lastupdate'] = '';
                    }
                } else {
                    if (strpos($plugin['updateurl'], 'downloadable/beta')) {
                        $plugin['updateurl'] = str_replace('downloadable/beta', 'downloadable/currentversion', $plugin['updateurl']);
                        $plugin['lastupdate'] = '';
                    }
                }
                try {
                    if (isset($plugin['lastupdate']) && $plugin['lastupdate'] != '') {
                        $lastupdate = new DateTime($plugin['lastupdate']);
                    } else {
                        $lastupdate = new DateTime('2000-01-01T00:00:00');
                    }
                    
                    $h = get_headers($plugin['updateurl'], 1);
                    
                    if (strpos($h[0], '200') !== FALSE) {
                        $filetime = new DateTime($h['Last-Modified']); // php 5.3
                        $diff = $lastupdate->diff($filetime);
                        
                        if ($diff->m != 0 || $diff->d != 0 || $diff->h != 0 || $diff->i != 0) {
                            $output = $this->writeDynamicScript(array(
                                '/opt/max2play/install_plugin.sh ' . $plugin['updateurl']
                            ));
                            $this->view->message[] = _('Update') . ' ' . $plugin['name'] . ' (' . $plugin['updateurl'] . '): ' . $this->formatMessageOutput('Serverversion: ' . $filetime->format(DateTime::ISO8601) . '<br />lokale Version:' . $lastupdate->format(DateTime::ISO8601) . '<br />' . $output);
                            $plugin['lastupdate'] = $filetime->format(DateTime::ISO8601);
                            $updatePlugins = true;
                        }
                    }
                } catch (Exception $e) {
                    $this->view->message[] = _('Plugin update time could not be validated:') . ' ' . $plugin['name'];
                }
            }
        }
        // Zeitstempel speichern
        if (true === $updatePlugins) {
            include_once (APPLICATION_PATH . '/library/array2xml.php');
            $xml = Array2XML::createXML('config', $plugins);
            
            $xml->save(APPLICATION_PATH . '/config/plugins.xml');
        }
        return true;
    }

    /**
     * Expandiere Root-FS auf ODROID auf Max.
     * Größe
     */
    private function resizeFS()
    {
        // Get root Partition for Resize
        $script = array(
            'blkid'
        );
        $output = $this->writeDynamicScript($script);
        $partitions = explode("\n", trim($output, "\n"));
        $resizePart = substr($partitions[count($partitions) - 1], 5, strpos($partitions[count($partitions) - 1], ':') - 5);
        if ($resizePart == 'mmcblk0')
            $resizePart = 'mmcblk0p2';
        $this->view->message[] = _('Resize Filesystem') . ': ' . $resizePart;
        
        // Do the Resize for typical odroid / raspberry Partitioning (normal image) | problem with noobs image!
        if (in_array($resizePart, array(
            'mmcblk0p2'
        ))) { // ,'mmcblk0p6'
            $script = array(
                '/opt/max2play/expandfs.sh ' . $resizePart . ' > /opt/max2play/cache/resize-max2play-log.txt'
            );
            $this->view->message[] = nl2br($this->writeDynamicScript($script));
            $this->view->message[] = nl2br(shell_exec('cat /opt/max2play/cache/resize-max2play-log.txt'));
            if ($this->getHardwareInfo() == 'ODROID-XU3') {
                // Only Ubuntu 15.04 on odroid XU3
                $this->writeDynamicScript(array(
                    'echo -e "[Unit]\nDescription=Resize FS\n[Service]\nType=simple\nExecStart=/etc/init.d/resize2fs_once start\n[Install]\nWantedBy=multi-user.target" >> /etc/systemd/system/multi-user.target.wants/fsresize.service'
                ));
            }
            $this->view->message[] = "<b><a href='/plugins/max2play_settings/controller/Basic.php?action=reboot'>" . _("Please Click here to Reboot Now") . "!</a></b>";
        } elseif (in_array($resizePart, array(
            'mmcblk0p3'
        ))) {
            $this->view->message[] = _('Filesystem already expanded - Nothing to do!');
        } else {
            $this->view->message[] = _('No Resize possible - no valid partition found to expand. Contact Max2Play-Support to add support for further file-systems.');
            $this->view->message[] = _('Make sure to disconnect all USB-drives and Memory-Cards and do a reboot before expanding the filesystem!');
        }
        return true;
    }

    /**
     * get Debuginformation
     */
    private function getDebug()
    {
        $out['FILESYSTEM'] = $this->shell_exec('df');
        $out['LOAD AVERAGE'] = $this->shell_exec('cat /proc/loadavg');
        $out['KERNEL'] = $this->shell_exec('uname -a');
        $out['LINUX-VERSION'] = $this->shell_exec('lsb_release -a');
        
        if ($this->getHardwareInfo() == 'Raspberry PI') {
            $this->hints = array(
                'Internet' => array(
                    'name' => 'Internet',
                    'min' => 1,
                    'error' => _('NO Connection'),
                    'message' => _('Connected'),
                    'shellcommand' => 'export LANG=C && ping -W 1 -c 1 www.max2play.com | grep "1 received" | wc -l'
                ),
                'CPU Load' => array(
                    'name' => 'CPU Load',
                    'max' => 2,
                    'variable' => 'LOAD AVERAGE',
                    'regex' => '=[^ ]+='
                ),
                'SD Card Usage' => array(
                    'name' => 'SD Card Usage',
                    'max' => '90%',
                    'variable' => 'FILESYSTEM',
                    'regex' => '=/dev/root.*?([0-9]+[%]+)='
                ),
                'Temp CPU' => array(
                    'name' => 'Temp CPU',
                    'max' => 80,
                    'superuser_shellcommand' => 'vcgencmd measure_temp',
                    'regex' => '@temp=([0-9\.]+)@'
                ),
                'Power Supply' => array(
                    'name' => 'Power Supply',
                    'min' => 1 /* means higher than 4.65V */, 'validvalues' => array(
                        "0",
                        "1"
                    ),
                    'error' => _('Low Voltage (< 4.65V)'),
                    'message' => _('OK'),
                    'superuser_shellcommand' => 'if [ ! -e /sys/class/gpio/gpio35/value ]; then echo 35 > /sys/class/gpio/export;fi; cat /sys/class/gpio/gpio35/value'
                )
            );
            
            foreach ($this->hints as $hint) {
                $hintalert = false;
                if (isset($hint['shellcommand'])) {
                    $var = trim($this->shell_exec($hint['shellcommand']));
                } elseif (isset($hint['superuser_shellcommand'])) {
                    $var = trim($this->writedynamicscript(array(
                        $hint['superuser_shellcommand']
                    )));
                } elseif (isset($hint['variable'])) {
                    $var = $out[$hint['variable']];
                }
                if (isset($hint['regex'])) {
                    if (preg_match($hint['regex'], $var, $match))
                        if (isset($match[1]))
                            $var = $match[1];
                        else
                            $var = $match[0];
                }
                
                if (! isset($hint['validvalues']) || isset($hint['validvalues']) && in_array($var, $hint['validvalues'])) {
                    if (isset($hint['min'])) {
                        if ($var < $hint['min'])
                            $hintalert = true;
                    }
                    if (isset($hint['max'])) {
                        if ($var > $hint['max'])
                            $hintalert = true;
                    }
                    if ($hintalert && isset($hint['error']))
                        $this->view->hints[$hint['name']] = $hint['error'];
                    elseif (! $hintalert && isset($hint['message']))
                        $this->view->hints[$hint['name']] = $hint['message'];
                    else
                        $this->view->hints[$hint['name']] = $var;
                }
                if ($hintalert) {
                    $this->view->hints[$hint['name']] .= '<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;margin-top:2px;"></span>';
                    $this->view->hintalert = true;
                }
            }
        }
        $this->view->debug = $out;
    }

    /**
     * Install ntfs-3g update Udev-Rules for Udisks2 and configure usbmount
     */
    private function fixUsbMount()
    {
        $script[] = 'apt-get install ntfs-3g';
        $script[] = 'echo SUBSYSTEMS==\"usb\",ENV{UDISKS_AUTO}=\"0\" > /etc/udev/rules.d/99-udisks2.rules';
        $script[] = 'udevadm control --reload-rules';
        $this->view->message[] = nl2br($this->writeDynamicScript($script));
        $this->view->message[] = _('udev-Rules added and reloaded... Completed');
        return true;
    }

    /**
     * Difference between FQDN and Hostname -> divided by fist "."
     */
    public function getHostname($name)
    {
        if (strpos($name, '.') !== FALSE) {
            $parts = explode('.', $name);
            return $parts[0];
        } else
            return $name;
    }

    /**
     * For Development and easy Beta-Updates
     * 
     * @return boolean
     */
    public function enableBetaUpdates()
    {
        if ($this->checkLicense(true, true) == false)
            return true;
        $this->view->betaEnabled = true;
    }

    public function getHelpOnSidebar()
    {
        if ($this->getConfigFileParameter('/opt/max2play/options.conf', 'showHelpOnSidebar') == "1")
            $this->view->showHelpOnSidebar = true;
        else
            $this->view->showHelpOnSidebar = false;
        return true;
    }

    /**
     * Autostart X-Server on Boot
     * Set Parameter in Autostart File
     */
    public function updateAutostartX()
    {
        $this->selectAutostart(isset($_REQUEST['autostartx']) ? 1 : 0, true, 'autostartxserver');
        return true;
    }

    /**
     * Autostart X-Server on Boot
     */
    public function getAutostartX()
    {
        $this->view->autostartxserver = $this->checkAutostart('autostartxserver', true);
        return true;
    }

    public function showHelpSidebar()
    {
        global $helpSidebar;
        $helpSidebar['title'] = _('Help - Basic Settings');
        $helpSidebar['content'] = _('<ul><li>Use this page to change the devicename, update Max2Play to the latest version, expand the filesystem on a new installation and install new addons.</li><li>To install and activate a new addon on the buttom of this page, you have to take 2 steps: first install the addon and second enable it.</li></ul>');
        if ($this->view->hints) {
            $helpSidebar['content'] .= '<div class="ui-widget"><div class="ui-state-' . (($this->view->hintalert) ? 'error' : 'highlight') . ' ui-corner-all infobox ' . (($this->view->hintalert) ? 'error' : 'noerror') . '" style="' . (($this->view->hintalert) ? '' : 'background:#98ff90;') . '">';
            $helpSidebar['content'] .= '<span class="headline ' . (($this->view->hintalert) ? 'error' : '') . '">' . _('Health Checker') . '</span><ul style="clear:left;list-style:none;" class="description ' . (($this->view->hintalert) ? 'error' : '') . '">';
            foreach ($this->view->hints as $name => $hint)
                $helpSidebar['content'] .= "<li>" . $name . ': ' . $hint . '</li>';
            $helpSidebar['content'] .= '</ul></div></div>';
        }
        return true;
    }
}

// Create Instance for view
$basic = new Basic();
include_once (dirname(__FILE__) . '/../view/basic.php');