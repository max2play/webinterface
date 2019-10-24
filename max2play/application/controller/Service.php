<?php

/**
 * Klasse für Services mit Methoden zur Steuerung
 * 
 * Alle Scripte müssen in die sudoers Datei mit Rechten für die Gruppe www-data - visudo oder nano /etc/sudoers.d/max2play
 * 
 */

/**
 * Services Administration Controller
 *
 * @copyright 2014 Stefan Rick
 * @author Stefan Rick
 *         Mail: stefan@rick-software.de
 *         Web: http://www.netzberater.de
 *        
 *         This program is free software; you can redistribute it and/or modify
 *         it under the terms of the GNU General Public License as published by
 *         the Free Software Foundation; either version 2 of the License, or
 *         (at your option) any later version.
 *        
 *         This program is distributed in the hope that it will be useful,
 *         but WITHOUT ANY WARRANTY; without even the implied warranty of
 *         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *         GNU General Public License for more details.
 *        
 *         You should have received a copy of the GNU General Public License along
 *         with this program; if not, write to the Free Software Foundation, Inc.,
 *         51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
include_once '../application/model/Info.php';

class Service
{

    public $view;

    public $viewname;
 // Name of Service in View
    public $info;

    public $plugins;

    public $debug = false;

    public $readonly_fs = 'unset';
 // Readonly Filesystem
    public $autostartconf = '/opt/max2play/autostart.conf';

    public $dynamicScript = '/opt/max2play/dynamic_script.sh';

    public $equal = array(
        '01. 31 Hz',
        '02. 63 Hz',
        '03. 125 Hz',
        '04. 250 Hz',
        '05. 500 Hz',
        '06. 1 kHz',
        '07. 2 kHz',
        '08. 4 kHz',
        '09. 8 kHz',
        '10. 16 kHz'
    );

    public $equalvalues = array();

    public function __construct()
    {
        $this->view = new stdClass();
        $this->view->message = array(); // Array of Messages for View
        $this->view->error = array();
        $this->info = new Info();
        if ($this->getConfigFileParameter('/opt/max2play/options.conf', 'DEBUG_WEBINTERFACE') == true || isset($_REQUEST['debug']) && $_REQUEST['debug'] == true) {
            $this->debug = true;
            // activate Error Reporting
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            ini_set("display_errors", "stdout");
        }
        $this->checkAccessRights();
    }

    /**
     * Function for Instance of Serviceclass to load Global Parameters (e.g.
     * for Header)
     */
    public function loadDefaults()
    {
        $this->getPlayername();
        if (isset($_REQUEST['refreshDeviceList']) && $_REQUEST['refreshDeviceList'] == 1) {
            $this->resetNetworkPlayerList();
        }
        $this->getAllNetworkPlayers();
        $this->getVersion();
        $this->getDonate();
        $this->getHardwareInfo();
        if (isset($_REQUEST['closeguide']) && $_REQUEST['closeguide'] == 1) {
            $this->setHelpOnSidebar(false);
        }
        $this->showLicenseMessage();
        return true;
    }

    /**
     * get Status of Process by name
     * 
     * @param string $name
     * @return boolean | int PID
     */
    public function status($name = '')
    {
        if ($name == '')
            return false;
        
        $shellanswer = $this->shell_exec("ps -ef | grep '" . $name . "' | grep -v grep");
        if ($shellanswer != '') {
            preg_match('=([ ]*)([0-9]*)=', substr($shellanswer, 9, 5), $match);
            if ($match[2]) {
                $process_id = $match[2];
                return $process_id;
            }
        }
        return false;
    }

    /*
     * Outdated - replaced by new status function
     * public function status($name = ''){
     * if($name == '')
     * return false;
     *
     * $shellanswer = $this->shell_exec("ps -Al | grep ".$name);
     * if($shellanswer != ''){
     * preg_match('=([ ]*)([0-9]*)=', substr($shellanswer, 10,5), $match);
     * if($match[2]){
     * $process_id = $match[2];
     * return $process_id;
     * }
     * }
     * return false;
     * }
     */
    public function start($name = '', $command = '', $statusname = '', $rootstart = false, $background = false)
    {
        if ($name == '')
            return false;
        if ($statusname != '')
            $pid = $this->status($statusname);
        else
            $pid = $this->status($name);
        
        if ($pid !== FALSE) {
            $shellanswer = _('Process is already running');
            return $shellanswer;
        }
        if ($command != '') {
            $startcom = $command;
        } else {
            $startcom = 'sudo /etc/init.d/' . $name . ' start 2>&1';
        }
        
        if (! $rootstart) {
            $answer = $this->shell_exec($startcom);
        } else {
            if ($background)
                $answer = $this->writeDynamicScript(array(
                    $startcom
                ), $background);
            else
                $answer = $this->writeDynamicScript(array(
                    $startcom
                ));
        }
        
        sleep(2);
        
        $shellanswer = _('Trying to launch');
        
        if ($statusname != '')
            $pid = $this->status($statusname);
        else
            $pid = $this->status($name);
        
        if ($pid != FALSE) {
            $shellanswer .= ' ... ' . _('successful with ID') . ' ' . $pid;
        } else {
            $shellanswer .= ' ... ' . _('NOT successful');
            if ($background)
                $shellanswer .= ' ' . $this->formatMessageOutput($this->shell_exec('cat ' . $background));
            else
                $shellanswer .= ' ' . $this->formatMessageOutput($answer);
        }
        return $shellanswer;
    }

    public function stop($name = '', $command = '', $statusname = '', $rootstop = false)
    {
        if ($name == '')
            return false;
        
        if ($statusname != '')
            $pid = $this->status($statusname);
        else
            $pid = $this->status($name);
        
        if ($pid === FALSE) {
            $shellanswer = _('Unable to stop process - it seems that it is not running');
            return $shellanswer;
        }
        
        if ($command != '') {
            $stopcom = str_replace('$PID', $pid, $command);
        } else {
            $stopcom = 'sudo /etc/init.d/' . $name . ' stop';
        }
        
        if (! $rootstop) {
            $this->shell_exec($stopcom);
        } else {
            $this->writeDynamicScript(array(
                $stopcom
            ));
        }
        
        sleep(3);
        
        $shellanswer = _('Trying to stop');
        
        if ($statusname != '')
            $pid = $this->status($statusname);
        else
            $pid = $this->status($name);
        
        if ($pid === FALSE) {
            $shellanswer .= ' ... ' . _('successfully stopped');
        } else {
            $shellanswer .= ' ... ' . _('could NOT stop process - try kill instead');
        }
        return $shellanswer;
    }

    /**
     * Funktion kill - kills by name in processlist
     * 
     * @param string $name
     * @return boolean|string
     */
    public function kill($name = '')
    {
        if ($name == '')
            return false;
        $pid = $this->status($name);
        if ($pid === FALSE) {
            $shellanswer = _('Unable to kill process - it seems that it is not running');
            return $shellanswer;
        }
        
        $this->shell_exec("sudo kill -9 " . $pid);
        
        sleep(3);
        
        $shellanswer = _('Trying to stop');
        $pid = $this->status($name);
        if ($pid === FALSE) {
            $shellanswer .= ' ... ' . _('successfully killed');
        } else {
            $shellanswer .= ' ... ' . _('could not kill process');
        }
        return $shellanswer;
    }

    /**
     * Use init.d Script to restart daemon
     * 
     * @param Script $name
     */
    public function restart($name = '', $command = '', $statusname = '')
    {
        if ($name == '')
            return false;
        
        if ($statusname != '')
            $pid = $this->status($statusname);
        else
            $pid = $this->status($name);
        
        if ($pid === FALSE) {
            $shellanswer = _('Unable to stop process - it seems that it is not running');
            return $shellanswer;
        }
        
        $startcom = 'sudo /etc/init.d/' . $name . ' restart';
        
        $this->writeDynamicScript(array(
            $startcom
        ));
        return _('restarted');
    }

    /**
     *
     * @param string $name
     *            Servicename - i.e. Scriptname in /etc/init.d
     * @param string $autostartconf
     *            Use /opt/max2play/autostart.conf for Autostart
     * @return boolean Important: XBMC uses differend autostart for XServer (defined in /controller/Xbmc.php)
     */
    public function checkAutostart($name = '', $autostartconf = false)
    {
        if ($name == '')
            return false;
        if (! $autostartconf) {
            $isactive = $this->shell_exec("ls /etc/rc2.d /etc/rc3.d | grep -e 'S.*" . $name . "'");
            if (strpos($isactive, $name) !== FALSE) {
                $isactive = true;
            } else {
                $isactive = false;
            }
            return $isactive;
        } elseif ($autostartconf === 'systemd') {
            $output = $this->shell_exec("systemctl status $name | grep '.service; enabled' | wc -l");
            if (trim($output) == 1) {
                $isactive = true;
            } else {
                $isactive = false;
            }
            return $isactive;
        } else {
            $output = $this->shell_exec('grep -i "^' . $name . '=1" ' . $this->autostartconf);
            if (strpos($output, $name . '=1') === 0) {
                $isactive = true;
            } else {
                $isactive = false;
            }
            return $isactive;
        }
    }

    /**
     * Configure Autostart
     * 
     * @param bool $autostart
     * @param bool $autostartconf
     *            defines wether update-rc.d or the autostartconfigfile in /opt/max2play should be used
     */
    public function selectAutostart($autostart = false, $autostartconf = true, $name = '')
    {
        if ($name == '')
            $name = $this->pname;
        $isactive = $this->checkAutostart($name, $autostartconf);
        if (! $autostart && $isactive) {
            $this->updateAutostart($name, false, $autostartconf);
            $this->view->message[] = $name . " " . _('successfully removed from autostart');
        } elseif ($autostart && ! $isactive) {
            $this->updateAutostart($name, true, $autostartconf);
            $this->view->message[] = $name . " " . _('successfully added to autostart');
        }
        return true;
    }

    /**
     * Update Autostart by Config-File OR update-rc.d
     * 
     * @param string $name
     * @param string $active
     * @return bool Value Changed
     */
    public function updateAutostart($name = '', $active = false, $autostartconf = true)
    {
        $output = preg_replace('=[\r\n ]*$=', '', $this->shell_exec('cat ' . $this->autostartconf));
        
        if ($name == '')
            return false;
        if ($active == false) {
            if (! $autostartconf) {
                $this->shell_exec("sudo update-rc.d -f " . $name . " remove");
            } elseif ($autostartconf === 'systemd') {
                $output = $this->writeDynamicScript(array("systemctl disable $name"));                
            } else {
                // Write Config-file
                return $this->saveConfigFileParameter($this->autostartconf, $name, 0);
            }        
        } else {
            if (! $autostartconf) {
                $this->shell_exec("sudo update-rc.d " . $name . " defaults");
            } elseif ($autostartconf === 'systemd') {
                $output = $this->writeDynamicScript(array("systemctl enable $name"));
            } else {
                // Write Config-file
                return $this->saveConfigFileParameter($this->autostartconf, $name, 1);
            }
        }
        return true;
    }

    /**
     * Name of Player - also used in /etc/hostname
     * 
     * @return string
     */
    public function getPlayername()
    {
        $output = $this->shell_exec('cat /opt/max2play/playername.txt');
        $this->info->playername = $output;
        return $output;
    }

    /**
     * get a list of all Max2Play-Devices in local network
     * Needs package nmap to list devices responding on port 5002
     * uses /opt/max2play/list_devices.sh
     */
    public function getAllNetworkPlayers()
    {
        // Only do network search if activated in basic-settings
        if (! $this->checkAutostart('Max2PlayNetworkLookup', true)) {
            return false;
        }
        // Caching in txt-file - reload every $reload
        $reload = 3600; // 1 hour
        
        $devices_txt = $this->shell_exec('cat /opt/max2play/cache/list_devices.txt');
        
        if (preg_match('=([0-9\-: ]{15,})=', $devices_txt, $matches)) {
            $lastcheck = strtotime($matches[0]);
        } else {
            $lastcheck = false;
        }
        
        if ((time() - $reload) > $lastcheck) {
            // Recheck available Devices
            $this->writeDynamicScript(array(
                '/opt/max2play/list_devices.sh'
            ), true, true);
        }
        
        // parse Nmap scan report
        $devices = explode("\n", $devices_txt);
        
        if (isset($devices[1])) {
            foreach ($devices as $tmp) {
                $dev_array = explode('|', $tmp);
                if (isset($dev_array[1])) {
                    $device = array(
                        'name' => $dev_array[0],
                        'ip' => $dev_array[1],
                        'current' => false
                    );
                    if (isset($dev_array[2]) && $dev_array[2] == 'current') {
                        $device['current'] = true;
                    }
                    $this->info->devices[] = $device;
                }
            }
        } else {
            $this->info->devices = false;
        }
        
        return true;
    }

    /**
     * get Current Version of Max2Play-Webinterface
     */
    public function getVersion()
    {
        $this->info->version = file_get_contents(APPLICATION_PATH . '/config/version.txt');
        return $this->info->version;
    }

    public function getDonate()
    {
        $this->info->removedonate = $this->getConfigFileParameter('/opt/max2play/options.conf', 'removedonate');
        return $this->info->removedonate;
    }

    /**
     * If an Addon needs a minimum Version of Max2Play
     * 
     * @param number $minversion
     * @return boolean
     */
    public function checkMinVersion($minversion = 0)
    {
        $this->getVersion();
        if ((float) $this->info->version < $minversion && strpos($this->info->version, 'Beta') === FALSE) {
            $this->view->message[] = str_replace('$MINVERSION', $minversion, _('You need at least Max2Play Version $MINVERSION to run this addon. Please update Max2Play on Settings tab!'));
            return false;
        }
        return true;
    }

    public function checkForUpdate()
    {
        $this->getVersion();
        // Check for Update
        $file = $this->getExternalFile('https://shop.max2play.com/media/downloadable/currentversion/version.txt', 3);
        if (strpos($this->info->version, 'Beta') !== FALSE) {
            $this->view->message[] = _('You are running a Beta-Version of Max2Play!');
        } elseif ((float) $this->info->version < (float) $file) {
            $this->view->message[] = _('Max2Play update is available - start update on tab Settings / Reboot');
        }
        if ($file === false) {
            $this->view->message[] = _('No internet connection available!');
            return false;
        }
        return true;
    }

    /**
     * Get external File with defined timeout
     * Do it with Curl (if installed) or by file_get_contents
     */
    public function getExternalFile($file = '', $timeout = 5, $curl = false)
    {
        if ($file != '') {
            if ($curl === true) {
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $file);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    if ($response) {
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $content = substr($response, $header_size);
                        return $content;
                    } else
                        return false;
                } catch (Exception $e) {
                    $this->view->message[] = _('No Curl for HTTP-Request available!');
                }
            } else {
                $ctx = stream_context_create(array(
                    'http' => array(
                        'timeout' => $timeout
                    )
                ));
                $content = @file_get_contents($file, false, $ctx);
                return $content;
            }
        }
        return false;
    }

    /**
     * Parse Plugin Configuration
     */
    public function getActivePlugins($basedir = '')
    {
        $xml = simplexml_load_file($basedir . APPLICATION_PATH . '/config/plugins.xml');
        $json = json_encode($xml);
        $this->plugins = json_decode($json, TRUE);
        return $this->plugins;
    }

    public function getReadOnlyFS()
    {
        if ($this->readonly_fs === 'unset') {
            if ($this->getConfigFileParameter('/opt/max2play/options.conf', 'readonly') == '1') {
                $this->readonly_fs = true;
                $this->dynamicScript = '/tmp/dynamic_script.sh';
                if (! file_exists('/tmp/writemode.txt'))
                    $this->view->message[] = _('SD-Card is Read-Only! Changes on settings will not be saved!');
            } else {
                $this->readonly_fs = false;
            }
        }
        return $this->readonly_fs;
    }

    /**
     * Write to File that has Root Rights to launch specific installations and configs
     * $script is an array separated by lines for each task
     * $background to run script in background
     * $daemon to run as real daemon - survives even a apache restart.
     * e.g. for update and upgrade
     */
    public function writeDynamicScript($script = '', $background = false, $daemon = false)
    {
        $this->getReadOnlyFS();
        if ($this->debug) {
            $time_start = microtime(true);
        }
        
        $fp = fopen($this->dynamicScript, 'w+');
        
        fwrite($fp, "#!/bin/bash\n");
        
        if(is_array($script)){
            foreach ($script as $s)
                fwrite($fp, "\n" . str_ireplace("\x0D", "", $s));
        }
        fclose($fp);
        
        if ($this->readonly_fs)
            shell_exec('chmod 777 ' . $this->dynamicScript);
        
        $backgroundfile = '/dev/null';
        if (strpos($background, '/tmp') !== FALSE) {
            $backgroundfile = $background;
        }
        
        if ($daemon) {
            $command = escapeshellarg('sudo ' . $this->dynamicScript);
            exec("php /opt/max2play/dynamicscriptdaemon.php {$command} >> /dev/null 2>&1 &");
            // To prevent other actions to overwrite dynamic_script file
            sleep(1);
        } else {
            $output = shell_exec('sudo './*((true == $background) ? 'nohup ' : ' ').*/$this->dynamicScript . ((true == $background) ? ' > ' . $backgroundfile . ' &' : ''));
            if (true == $background) {
                sleep(1);
            }
        }
        
        if ($this->debug) {
            $time = microtime(true) - $time_start;
            global $debuglog;
            $debuglog[] = get_class($this) . '_dynamic_script (' . $time . ' sec): ' . shell_exec('cat ' . $this->dynamicScript);
        }
        
        return $output;
    }

    /**
     *
     * @param string $directory
     * @param string $domain
     */
    public function registerLocale($directory = '', $domain = '')
    {
        // Add Plugin Translation
        bindtextdomain($domain, $directory);
        bind_textdomain_codeset($domain, 'UTF-8');
        define('newLocale', $domain);
    }

    /**
     * Function to save specific Parameter to specified Configfile
     * 
     * @param string $configfile
     * @param string $parameter
     * @param string $value
     */
    public function saveConfigFileParameter($configfile = '', $parameter = '', $value = '', $separator = '=')
    {
        if (file_exists($configfile)) {
            $old_parameter = trim($this->getConfigFileParameter($configfile, $parameter));
            
            if ($old_parameter == $value) {
                // No changes
                return false;
            }
            // Check for empty entry
            $param_exists = $this->shell_exec('grep -aP "^[ \t]*' . $parameter . '[ \t]*' . $seperator . '" ' . $configfile . ' | wc -l');
            if ($old_parameter != '' || $param_exists > 0) {
                $this->writeDynamicScript(array(
                    'sed -i "s/^[ \t]*' . $parameter . '[ \t]*' . $separator . '.*$/' . $parameter . $separator . str_replace(array(
                        '/'
                    ), array(
                        '\/'
                    ), $value) . '/g" ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - existing Entry changed");
            } else {
                // check for Newline in Last Line in config file
                if (strpos($this->shell_exec('xxd -p ' . $configfile . ' | tail -c 3'), '0a') === FALSE) {
                    // Newline missing -> add one
                    $parameter = "\n" . $parameter;
                }
                $this->writeDynamicScript(array(
                    'echo "' . $parameter . $separator . $value . '" >> ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - new Entry created");
            }
        } else {
            $this->writeDynamicScript(array(
                'echo "' . $parameter . $separator . $value . '" > ' . $configfile
            ));
            $this->view->message[] = _("Update Configfile - new Configfile created");
        }
        return true;
    }

    /**
     * Function to delete specific Parameter from specified Configfile
     * 
     * @param string $configfile
     * @param string $parameter
     */
    public function deleteConfigFileParameter($configfile = '', $parameter = '', $separator = '\=')
    {
        if (! file_exists($configfile))
            return false;
        $param_exists = $this->shell_exec('grep -aP "^[ \t]*' . $parameter . '[ \t]*' . $seperator . '" ' . $configfile . ' | wc -l');
        if ($param_exists > 0) {
            $this->writeDynamicScript(array(
                'sed -i "s/^[ \t]*' . $parameter . '[ \t]*' . $separator . '.*$//g" ' . $configfile
            ));
        }
        return true;
    }

    /**
     * Function to get specific Parameter from specified Configfile
     * 
     * @return boolean
     */
    public function getConfigFileParameter($configfile = '', $parameter = '', $separator = '\=')
    {
        if (! file_exists($configfile))
            return false;
        $output = trim($this->shell_exec('grep -aP "^[ \t]*' . $parameter . '[ \t]*' . $separator . '" ' . $configfile . ' | sed -n -e "s/^[ \t]*[A-Za-z_0-9\.]*' . $separator . '//p"'));
        return $output;
    }

    /**
     * Longer Tasks need some progress shown to the user while completing
     * Call this function first and than put all Output into the progressfile
     * 1.
     * Ending when Progressfile is deleted by calling Script at the end
     * 2. Ending when Progressfile has output "finished" somewhere
     * 
     * @param $progressfile File
     *            that as long as it exists shows current status of install
     * @param $create First
     *            call creates Outputfile and Message
     * @param $reloadWhenFinished Reload
     *            Window when everything is finished
     * @param $lastlines Show
     *            X lastlines of Progressfile
     * @param $message Show
     *            this Message instead of "Installation started"
     * @param $url Redirect
     *            URL after finished
     * @return Message for Ajax-Output
     */
    public function getProgressWithAjax($progressfile = '', $create = 0, $reloadWhenFinished = 0, $lastlines = 0, $message = false, $url = false, $failmessage = false, $finishedText = 'finished|Finished')
    {
        if (! file_exists($progressfile) && $create == 1) {
            // Create File and set Message Output for Ajax-Call
            $this->shell_exec('echo `date +"%Y-%m-%d %H:%M:%S|"` > ' . $progressfile);
            if ($message)
                $this->view->message[] = $message;
            else
                $this->view->message[] = _('Installation startet - This Messages refreshes every 3 seconds to show current status of installation. If finished this message disappears.');
            // Separate Parameters from current Filename
            if (! $url)
                $url = preg_replace('=\?.*$=', '', $_SERVER['REQUEST_URI']);
                $this->view->message[] = '<div id="msgprogress"></div><script type="text/javascript">setTimeout(function(){reloadprogress("msgprogress", "' . $url . '", ' . $reloadWhenFinished . ', "'.$finishedText.'")}, 3000);</script>';
            return true;
        } elseif (file_exists($progressfile) && $create == 0) {
            // Check for Status finished and return current status and progressfile and reload if neccesary
            // Set Header Files to NO CACHE
            header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', FALSE);
            header('Pragma: no-cache');
            if ($lastlines > 0) {
                $shellanswer = $this->shell_exec("tail -$lastlines $progressfile");
            } else
                $shellanswer = $this->shell_exec("cat $progressfile");
            return $shellanswer;
        } elseif (file_exists($progressfile) && $create == 1) {
            // File should not be existing - show error and delete file!
            $shellanswer = $this->shell_exec("cat $progressfile");
            preg_match('=[0-9\: -]*=', $shellanswer, $started);
            if (! $failmessage)
                $this->view->message[] = _('Something went wrong in last Install Attempt - Deleting Progressfile');
            else
                $this->view->message[] = $failmessage;
            $this->writeDynamicScript(array(
                "rm $progressfile"
            ));
            return false;
        } else {
            // !file_exists($progressfile) && $create == 0 --> Finished
            $this->view->message[] = '<!-- finished -->';
            return true;
        }
    }

    /**
     * Format Message-Output for view
     * 
     * @param string $string
     *            content
     * @param boolean $hide
     *            generate link to display content
     */
    public function formatMessageOutput($string, $hide = false)
    {
        if ($hide)
            return nl2br($string);
        else {
            $id = rand();
            $out = '<a style="color: #21759b;" href="#javascript" onclick="document.getElementById(\'' . $id . '\').style.display=\'\';return false;">' . _("Click here to show detailed information") . '</a>';
            $out .= '<span style="display:none;" id="' . $id . '"><br />' . nl2br($string) . '</span>';
            return $out;
        }
    }

    /**
     * Function to render View Header
     * 
     * @param
     *            reload Set to reload Header after Changes (if global settings changed)
     */
    public function loadViewHeader($reload = false)
    {
        global $service;
        
        if (true == $reload) {
            // Clear Output
            ob_clean();
            $service->loadDefaults(); // Global Scope
        }
        if (file_exists(APPLICATION_PATH . '/view/header_custom.php'))
            include (APPLICATION_PATH . '/view/header_custom.php');
        else
            include (APPLICATION_PATH . '/view/header.php');
        
        return true;
    }

    public function loadViewFooter()
    {
        global $service;
        $helpOnSidebar = $this->getHelpOnSidebar();
        if (file_exists(APPLICATION_PATH . '/view/footer_custom.php'))
            include_once (APPLICATION_PATH . '/view/footer_custom.php');
        else
            include_once (APPLICATION_PATH . '/view/footer.php');
        
        return true;
    }

    public function setHelpOnSidebar($active = 0)
    {
        $this->saveConfigFileParameter('/opt/max2play/options.conf', 'showHelpOnSidebar', $active);
        return true;
    }

    public function getHelpOnSidebar()
    {
        if ($this->getConfigFileParameter('/opt/max2play/options.conf', 'showHelpOnSidebar') == "1") {
            global $helpSidebar;
            if (isset($helpSidebar) && $helpSidebar != false) {
                return $helpSidebar;
            }
            return false;
        }
        return false;
    }

    /**
     * Get Hardware Information
     */
    public function getHardwareInfo()
    {
        $hwByRevisionRegex = array(
            'a.208[23]' => 'Raspberry PI 3',
            'a.[12]04[12]' => 'Raspberry PI 2',
            '^9000[923c1]+' => 'Raspberry PI Zero',
            '000[23456def]' => 'Raspberry PI B',
            '001[03]' => 'Raspberry PI B+',
            '000[789]' => 'Raspberry PI A',
            '0012' => 'Raspberry PI A+',
            'a03111' => 'Raspberry PI 4B 1GB',
            'b03111' => 'Raspberry PI 4B 2GB',
            'c03111' => 'Raspberry PI 4B 4GB'
        );
        
        if (! $this->info->hardware) {
            $output = $this->shell_exec("cat /proc/cpuinfo | grep 'Hardware\|Revision'");
            $this->info->hardware = '';
            if (preg_match('=Hardware.*: ([^ \n]*)=', $output, $matches)) {
                if (strpos($output, 'BCM2708') || strpos($output, 'BCM2709') || strpos($output, 'BCM2837') || strpos($output, 'BCM2835') || strpos($output, 'BCM2836')) {
                    $this->info->hardware = 'Raspberry PI';
                    $this->info->chipset = trim($matches[1]);
                    // Pi Version? Check Revision
                    if (preg_match('=Revision.*: ([^ ]*)=', $output, $matches)) {
                        $revision = trim($matches[1]);
                        foreach ($hwByRevisionRegex as $key => $value) {
                            if (preg_match('=' . $key . '=', $revision, $test)) {
                                $this->info->boardname = $value;
                                break;
                            }
                        }
                    }
                } else {
                    $this->info->hardware = trim($matches[1]);
                    $this->info->chipset = trim($matches[1]);
                    
                    // Custom Names for special Chipsets
                    if ($this->info->chipset == 'gs705a') {
                        $this->info->hardware = 'Allo Sparky';
                    }
                }
            }
        }
        return $this->info->hardware;
    }

    public function getFreeDiskSpace()
    {
        $this->getSystemUser();
        if ($this->info->system_user == 'pi') {
            $this->info->freespace = $this->shell_exec("df -km / | tail -1 | awk '{print $4}'");
        }
        if ($this->info->system_user == 'odroid') {
            $this->info->freespace = $this->shell_exec("df -km /dev/mmcblk0p2 | tail -1 | awk '{print $4}'");
        }
        return $this->info->freespace;
    }

    public function getSystemUser()
    {
        if (! $this->info->system_user) {
            $system_user = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SYSTEM_USER');
            if ($system_user)
                $this->info->system_user = $system_user;
            else
                $this->info->system_user = 'odroid';
        }
        return $this->info->system_user;
    }

    /**
     * get Linux Version of System
     * e.g.
     * Ubuntu
     * 
     * @return array 0=> Ubuntu 1=> trusty
     *         array 0=> Ubuntu 1=> xenial
     *         array 0=> Raspbian 1=> stretch
     */
    public function getLinuxVersion()
    {
        if ($this->info->linux)
            return $this->info->linux;
        $output = trim($this->shell_exec('lsb_release -a 2>/dev/null | grep "Distributor\|Codename" | sed "s/Distributor ID:\t//;s/Codename:\t//"'), "\n");
        if ($output) {
            $this->info->linux = explode("\n", $output);
        }
        if (! isset($this->info->linux[0]) || $this->info->linux[0] == '') {
            $this->info->linux[0] = 'Unknown';
        }
        return $this->info->linux;
    }

    /**
     *
     * @param string $local
     *            do only local check (on update always check online)
     * @param boolean $silent
     *            do not print messages
     * @return boolean
     */
    public function checkLicense($local = false, $silent = false)
    {
        if ($local == true) {
            $license = $this->getConfigFileParameter('/opt/max2play/options.conf', 'license');
            $this->view->licenseDate = $license;
            if ($license == 1 || ($license != 0 && strtotime($license) > time())) {
                $this->view->license = true;
                return true;
            } else {
                $this->view->license = false;
                if (! $silent)
                    $this->view->message[] = _('No valid Max2Play-License for additional plugins and features found. Please enter a valid eMail-address on the Settings-page to verify that you are a customer of Max2Play and to access all features.');
                return false;
            }
        }
        $email = $this->getConfigFileParameter('/opt/max2play/options.conf', 'email');
        
        include_once '../application/model/CheckLicense.php';
        $this->view->licenseDate = $license;
        if ($license == 1 || ($license != 0 && strtotime($license) > time())) {
            $this->view->license = true;
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'license', $license);
            return true;
        } else {
            $this->view->license = false;
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'license', '0');
            return false;
        }
    }

    /**
     * Install a new Plugin from HTTP-Resource
     * 
     * @param string $pathToPlugin
     * @param
     *            autoenable add to navigation
     * @param
     *            position at position X
     * @param
     *            default Set this Plugin as new Default
     * @param
     *            basedir Install Plugin to different Folder (for Imageburner Setup)
     */
    public function installPlugin($pathToPlugin = '', $autoenable = false, $position = false, $default = false, $basedir = '')
    {
        $uploadsuccess = false;
        if ($pathToPlugin == '' && isset($_FILES['uploadedfile']) && $_FILES['uploadedfile']['tmp_name']) {
        	// remove unwanted characters
        	$basename = preg_replace('=[^a-zA-Z0-9\.]=', '', basename($_FILES['uploadedfile']['name']));
        	$uploaddir = '/var/www/max2play/public/addons/';
        	$uploadfile = $uploaddir . $basename;
            $this->view->message[] = _("File Uploaded");
            // make sure File write access for www-data: save in Addon Folder
            $this->writeDynamicScript(array(
                'chmod 777 ' . $uploaddir
            ));
            if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
            	$pathToPlugin = "http://{$_SERVER['HTTP_HOST']}" . '/addons/' . $basename;
                $uploadsuccess = true;
                $this->view->message[] = _("File moved successful");
            }
        }
        $this->getEmail();
        if (! $uploadsuccess && ! preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $pathToPlugin)) {
            $this->view->message[] = _("Invalid Plugin-URL");
            return false;
        } else {
            $linux = $this->getLinuxVersion();
            $add_params = '"?email=' . $this->view->email . '&premium=' . $this->checkLicense(true, true) . '&hardware=' . urlencode($this->getHardwareInfo()) . '&linux=' . urlencode($linux[0]) . '"';
            $output = nl2br($this->writeDynamicScript(array(
                '/opt/max2play/install_plugin.sh ' . $pathToPlugin . ' ' . $add_params . ' ' . $basedir
            )));
            $this->view->message[] = $output;
            
            if (strpos($output, 'Install successful') === FALSE) {
                $this->view->message[] = _("Plugin Installer ERROR! Maybe missing internet connection or write error on filesystem.");
                return false;
            }
            
            if ($autoenable == true) {
                if (preg_match("=Installing Plugin ([a-zA-Z0-9 _-]*)=", $output, $match)) {
                    if (preg_match('=\$this-\>pluginname \= \_\(\'(.*)\'=', $this->shell_exec('grep -i \'$this->pluginname\' /opt/max2play/cache/newplugin/' . $match[1] . '/controller/Setup.php'), $namematch)) {
                        $pluginname = $namematch[1];
                        
                        $this->enablePlugin($pluginname, $position, $default, $basedir);
                        
                        $this->view->message[] = str_replace('$PLUGINNAME', _($pluginname), _('Plugin $PLUGINNAME activated and added to main navigation. You may change the position and visibility in the addon configuration on the <a href="/plugins/max2play_settings/controller/Basic.php#pluginconfigblock">settings page</a>.'));
                        if ($basedir == '')
                            $this->loadViewHeader(true);
                    }
                }
            }
        }
        return true;
    }

    /**
     *
     * @param array $pluginnames
     */
    public function enablePlugin($pluginname = '', $position = false, $default = false, $basedir = '')
    {
        $this->parsePlugins($basedir);
        foreach ($this->view->pluginselect as $key => $value) {
            if ($value['active'] == 1) {
                if ($position !== false && $key >= $position)
                    $key ++;
                $activeplugins[$key] = $value['name'];
            }
            if ($value['default'] == 1)
                $defaultplugin = $value['name'];
        }
        if ($position !== false)
            $activeplugins[$position] = $pluginname;
        else
            $activeplugins[] = $pluginname;
        if ($default != false)
            $defaultplugin = $pluginname;
        $this->pluginConfig($activeplugins, $defaultplugin, $basedir);
        return true;
    }

    /**
     * get all plugins
     * user may activate plugins and add them to the navigation
     * 
     * @param $basedir Set
     *            Alternative Install Folder
     */
    public function parsePlugins($basedir = '')
    {
        $plugins_avail = array();
        // Parse Folder
        $handle = opendir($basedir . APPLICATION_PATH . '/plugins');
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                try {
                    $handle_controller = opendir($basedir . APPLICATION_PATH . '/plugins/' . $file . '/controller');
                    if ($handle_controller) {
                        while (false !== ($action = readdir($handle_controller))) {
                            if ($action != "." && $action != "..") {
                                $path = '/plugins/' . $file . '/controller/' . $action;
                                // Parse Pluginname
                                $output = $this->shell_exec('cat ' . $basedir . APPLICATION_PATH . $path . ' | grep "this->pluginname"');
                                
                                if (preg_match('=\$this-\>pluginname \= \_\(\'(.*)\'=', $output, $match)) {
                                    // only activate plugin if name is set (class of plugin may just be part of another class)
                                    // add version / timestamp to plugin to get later updates from config.txt if existing
                                    $updateURL = $lastUpdate = '';
                                    if (file_exists($basedir . APPLICATION_PATH . '/plugins/' . $file . '/config.txt')) {
                                        $updateURL = $this->getConfigFileParameter($basedir . APPLICATION_PATH . '/plugins/' . $file . '/config.txt', 'UPDATEURL');
                                        $lastUpdate = $this->getConfigFileParameter($basedir . APPLICATION_PATH . '/plugins/' . $file . '/config.txt', 'LASTUPDATE');
                                    }
                                    if (file_exists($basedir . APPLICATION_PATH . '/plugins/' . $file . '/custom.txt')) {
                                        $customPlugin = true;
                                    } else
                                        $customPlugin = false;
                                    
                                    $plugins_avail[$match[1]] = array(
                                        'name' => $match[1],
                                        'navigation' => array(
                                            'translate' => $match[1]
                                        ),
                                        'path' => $path,
                                        'folder' => $file,
                                        'updateurl' => $updateURL,
                                        'lastupdate' => $lastUpdate,
                                        'custom' => $customPlugin
                                    );
                                }
                            }
                        }
                        closedir($handle_controller);
                    }
                } catch (Exception $e) {
                    $this->view->message[] = _('Plugin Error');
                }
            }
        }
        closedir($handle);
        
        // get current Configuration from XML
        $pluginConf = $this->getActivePlugins($basedir);
        
        $plugins['configuration'] = $pluginConf['plugin'];
        $plugins['available'] = $plugins_avail;
        // Prepare Output for Choosing plugins in Multi SELECT
        $pos = 100;
        $position = 0;
        foreach ($plugins['available'] as $pa) {
            $active = $default = $position = false;
            foreach ($plugins['configuration'] as $key => $pc) {
                if ($pa['name'] == $pc['name'] && isset($pc['active']) && $pc['active'] == 1) {
                    $active = true;
                    $position = $key;
                    if (isset($pc['default']) && $pc['default'] == 1) {
                        $default = 1;
                    }
                }
            }
            if ($active)
                $pluginselect[$position] = array(
                    'name' => $pa['name'],
                    'active' => $active,
                    'default' => $default,
                    'custom' => $pa['custom'],
                    'folder' => $pa['folder']
                );
            else
                $pluginselect[$pos ++] = array(
                    'name' => $pa['name'],
                    'active' => $active,
                    'default' => $default,
                    'custom' => $pa['custom'],
                    'folder' => $pa['folder']
                );
        }
        ksort($pluginselect);
        $this->view->pluginselect = $pluginselect;
        return $plugins;
    }

    /**
     * Save Plugin Configuration to XML
     * 
     * @param string $plugins
     */
    public function pluginConfig($pluginchoose = false, $defaultplugin = false, $basedir = '')
    {
        // Make config Folder and Plugin Config File writeable
        $this->writeDynamicScript(array(
            'chmod -R 777 ' . $basedir . APPLICATION_PATH . '/config'
        ));
        
        // Check active Plugins
        $plugins = $this->parsePlugins($basedir);
        $pos = 100;
        foreach ($plugins['available'] as $pa) {
            $pa['active'] = 0;
            $pa['pos'] = $pos ++;
            foreach ($pluginchoose as $key => $pc) {
                if ($pc == $pa['name']) {
                    $pa['active'] = 1;
                    $pa['pos'] = $key;
                }
            }
            if ($defaultplugin == $pa['name']) {
                $pa['default'] = 1;
            }
            $newconfig['plugin'][$pa['pos']] = $pa;
        }
        ksort($newconfig['plugin']);
        
        include_once (APPLICATION_PATH . '/library/array2xml.php');
        $xml = Array2XML::createXML('config', $newconfig);
        
        $xml->save($basedir . APPLICATION_PATH . '/config/plugins.xml');
        
        // Reload Plugins
        global $service;
        $service->plugins = $this->getActivePlugins($basedir);
        
        return _('Plugin configuration updated');
    }

    public function getEmail()
    {
        $this->view->email = $this->getConfigFileParameter('/opt/max2play/options.conf', 'email');
        return $this->view->email;
    }

    public function removePlugins($plugins = array(), $basedir = '')
    {
        $this->parsePlugins($basedir);
        foreach ($this->view->pluginselect as $key => $value) {
            if ($value['active'] == 1 && ! in_array($value['name'], $plugins))
                $activeplugins[$key] = $value['name'];
            if ($value['default'] == 1)
                $defaultplugin = $value['name'];
        }
        $this->pluginConfig($activeplugins, $defaultplugin, $basedir);
        if ($basedir == '')
            $this->loadViewHeader(true);
        return true;
    }

    /**
     * use Alsaequal
     */
    public function updateEqualizer($equalvalue, $user = false, $card = 'equal')
    {
        if (! $user)
            $user = $this->getSystemUser();
        
        $this->getEqualizer($user, $card);
        
        // if($_GET['use_equalizer'] == 1 && $this->checkLicense(true) == false)
        // return true;
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER', ($_REQUEST['use_equalizer'] == 1) ? 1 : 0)) {
            // Changes successful
        }
        
        foreach ($this->equal as $key) {
            $value = (isset($equalvalue[$key])) ? (int) $equalvalue[$key] : 66;
            // Only update changes values
            if (str_replace('%', '', $this->equalvalues[$key]) != $value)
                $script[] = 'su - ' . $user . ' -c \'amixer -D ' . $card . ' -q set "' . $key . '" ' . $value . '\'';
        }
        
        $this->view->message[] = $this->writeDynamicScript($script);
        $this->view->message[] = _("Updated Equalizer Settings");
    }

    /**
     * get Alsaequal Settings
     */
    public function getEqualizer($user = false, $card = 'equal')
    {
        $this->view->use_equalizer = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER');
        if ($this->view->use_equalizer || $_REQUEST['use_equalizer']) {
            if (! $user)
                $user = $this->getSystemUser();
            $script = array(
                'su - ' . $user . ' -c \'amixer -D ' . $card . '\''
            );
            $output = $this->writeDynamicScript($script);
            preg_match_all('=\'([0-9\. kHz]+)\'.*?\[([0-9%]+)\]=is', $output, $matches);
            // $matches: 1 => Keys, 2 => Values
            for ($i = 0; $i < count($matches[1]); $i ++) {
                $tmpequal[$matches[1][$i]] = $matches[2][$i];
            }
            // DISABLED NOT USEFUL... Use Mapping only for Pi as Odroid has other Frequencies
            /*if ($user == pi) {
                foreach ($this->equal as $key) {
                    $this->equalvalues[$key] = $tmpequal[$key];
                }
            } else {*/
            $this->equal = array_keys($tmpequal);
            $this->equalvalues = $tmpequal;
            
        }
        return true;
    }

    /**
     * Enable / Disable Audio Interface on Raspberry Pi
     * 
     * @param unknown $status
     *            TODO: save dtparm Parameter like saveDTOverlayConfig in RaspberrySettings
     */
    public function setBuildInAudio($status = true)
    {
        // Replace commented dtparm - make sure only one line is active
        $this->writeDynamicScript(array(
            'if [ $(grep "^dtparam=audio=" /boot/config.txt | wc -l) -lt 1 ]; then sed -i "s@.*dtparam=audio=@dtparam=audio=@" /boot/config.txt; fi'
        ));
        if ($status == true) {
            $this->writeDynamicScript(array(
                'sed -i "s@dtparam=audio=off@dtparam=audio=on@" /boot/config.txt'
            ));
        } else {
            $this->writeDynamicScript(array(
                'sed -i "s@dtparam=audio=on@dtparam=audio=off@" /boot/config.txt'
            ));
        }
        return true;
    }

    /**
     * Show License Message every X requests if not activated
     * 
     * @param $every X
     *            Requests show Message
     */
    public function showLicenseMessage($every = 3)
    {
        if ($this->checkLicense(true, true) == true)
            return false;
        $name = 'Max2Play-Interface-Requests';
        if (isset($_COOKIE[$name])) {
            $req = intval($_COOKIE[$name]);
            if ($req >= $every) {
                setcookie($name, 1, 0, '/');
                if (! isset($this->view->error[0]))
                    $this->view->error[] = _("Max2Play-License <a href='/plugins/max2play_settings/controller/Basic.php'>is not activated</a>. Not all functions available!");
            } else {
                setcookie($name, $req + 1, 0, '/');
            }
        } else
            // Set Cookie to save Requests
            setcookie($name, 2, 0, '/');
        return false;
    }

    /**
     * Needed for IPv6 to get correct URL for Redirects
     */
    public function getServerUrl($forceIPv4 = false)
    {
        // is IPv6? Look for at least 2 ":"
        if (preg_match('=.*:.*:.*=', $_SERVER['SERVER_ADDR'], $match) != 0) {
            if (TRUE == $forceIPv4) {
                if ($this->info->ipv4)
                    return $this->info->ipv4;
                // return Name OR get IPv4 Address if possible
                $output = $this->shell_exec("LANG=C && /sbin/ip addr | grep -o 'inet [0-9\.]\+' | grep -v '127.0.0.1' | head -1");
                if (strpos($output, 'inet ') !== FALSE) {
                    $address = explode("\n", trim(mb_substr($output, 5)));
                    $this->info->ipv4 = $address[0];
                    return $this->info->ipv4;
                } else {
                    return $this->getPlayername();
                }
            }
            return '[' . $_SERVER['SERVER_ADDR'] . ']';
        } else
            return $_SERVER['SERVER_ADDR'];
    }

    /**
     * Ajax Message output
     * 
     * @param sting $action
     *            Function to call
     * @return boolean
     */
    public function checkAjaxCall($action)
    {
        // Check for Ajax-Variable in HTTP-GET and custom action
        if ($_REQUEST['ajax'] == 1 && $_REQUEST['action'] == $action) {
            // Function to get Progress of Installation
            if (method_exists($this, $action)) {
                // Call Action with Ajax Parameter 1
                $this->$action(1);
            } else {
                $this->view->message[] = str_replace('$action', $action, _('Function $action not found for Ajax-Call'));
            }
            ob_end_clean();
            echo implode('<br />', $this->view->message);
            ob_flush();
            die();
        }
        return true;
    }

    /**
     * API Function to print JSON Encoded Output if Parameter "apijson=1" is set
     * Load this at the End of the Construct Function
     * Optional: Use API-DOC to specify POST / GET Parameters for each function
     * 
     * @return boolean
     */
    public function loadAPIHandler()
    {
        if ($_REQUEST['apijson'] == 1) {
            // API for JSON formatted Output
            ob_end_clean();
            header('API-Type: Max2Play API');
            header('Content-Type: application/json; charset=utf-8', false);
            echo json_encode(get_object_vars($this));
            ob_flush();
            die();
        }
        return true;
    }

    /**
     * Shell Exec Handler with Logging and Time measurement
     * 
     * @param string $code
     * @return string output
     */
    public function shell_exec($code = '')
    {
        if ($this->debug) {
            $time_start = microtime(true);
        }
        $output = shell_exec($code);
        if ($this->debug) {
            $time = microtime(true) - $time_start;
            global $debuglog;
            $debuglog[] = get_class($this) . '_shell_exec (' . $time . ' sec): ' . $code;
        }
        return $output;
    }

    /**
     * get Instance of RPI_Functions
     * 
     * @param class $Rpi_functions
     */
    public function Rpi_functions()
    {
        if (! $this->Rpi_functions) {
            include_once 'Rpi_functions.php';
            $this->Rpi_functions = new Rpi_functions();
        }
        return $this->Rpi_functions;
    }

    /**
     * Load new DeviceList
     */
    public function resetNetworkPlayerList()
    {
        $this->view->message[] = _('Network Player List is currently generated in background and will be available in ~10 seconds.');
        $this->shell_exec('rm /opt/max2play/cache/list_devices.txt');
        return true;
    }

    /**
     * Function to Allow / Disallow specific functions
     */
    public function checkAccessRights()
    {
        if (file_exists('/tmp/automatic_accesspoint_mode')) {
            // Disallow all but WiFi Configuration            
            if (! in_array(get_called_class(), array(
                'Service',
                'Wlan'
            ))) {
                // Allow Reboot and Shutdown Basic.php?action=reboot
                if(get_called_class() == 'Basic' && ($_REQUEST['action'] == 'reboot' || $_REQUEST['action'] == 'shutdown'))
                    return true;
                $this->view->message[] = _('Device is started in Automatic Accesspoint Mode. For security, only WiFi configuration is possible to connect Max2Play to your local network.');
                unset($_REQUEST);
                unset($_GET);
                unset($_POST);
            }
        }
        return true;
    }
}

// Create Instance of Service Class
$service = new Service();
$service->loadDefaults();

