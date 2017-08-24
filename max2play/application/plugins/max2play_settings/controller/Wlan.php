<?php

/**
 Lan / Wlan Administration
 
 @Copyright 2017 Stefan Rick
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
class Wlan extends Service
{

    private $wpa_config = '/opt/max2play/wpa_supplicant.conf';

    private $networkinterfaces = '/etc/network/interfaces';

    private $mac_address = '/etc/smsc95xx_mac_addr';

    public function __construct()
    {
        parent::__construct();
        $this->pluginname = _('WiFi / LAN');
        $this->scriptPath = dirname(__FILE__) . '/../scripts/';
        $this->_getWirelessConfig();
        
        if ($_REQUEST['ajax'] == 1)
            $_REQUEST['action'] = 'setupAjaxWifi'; // TODO: change this Hack later...
        $this->checkAjaxCall('setupAjaxWifi');
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'save') {
                $this->_saveWirelessConfig();
                $this->_getWirelessConfig();
            }
            if ($_GET['action'] == 'start_wps') {
                $this->_startWPS();
            }
            if ($_GET['action'] == 'scanWlanNetworks') {
                $this->_showWlanNetworks();
            }
        }
        $this->showHelpSidebar();
    }

    /**
     * Complex installer with Ajax Calls to get updates from running script
     * 
     * @param int $ajax
     * @return boolean
     */
    public function setupAjaxWifi($ajax = 0, $ssid, $psk)
    {
        $progressfile = '/tmp/wifi_install_progress.txt';
        if ($ajax == 0) {
            // First run - start Script and create temporary file for Output
            if ($this->getProgressWithAjax($progressfile, $create = 1, $reloadWhenFinished = 1, $lastlines = 0, $message = _("Setup WiFi Started"))) {
                $shellanswer = $this->writeDynamicScript(array(
                    $this->scriptPath . "setup_wifi_ajax.sh \"" . $ssid . "\" \"" . $psk . "\" >> " . $progressfile . " 2>&1 &"
                ), $background = false, $daemon = true);
            }
        } else {
            // Ajax Call every 3 seconds to get progression
            $status = $this->getProgressWithAjax($progressfile, $create = 0, $reloadWhenFinished = 1, $lastlines = 30);
            $this->view->message[] = nl2br($status);
            // Parse Output for String "finished"
            if (strpos($status, 'Finished') !== FALSE) {
                // Finished Progress - did not delete progressfile yet
                // Delete Progressfile
                shell_exec('rm ' . $progressfile);
            }
        }
        return true;
    }

    private function _startWPS()
    {
        $this->view->message[] = nl2br($this->writeDynamicScript(array(
            "/opt/max2play/wps_config.sh"
        )));
        $this->_getWirelessConfig();
        return true;
    }

    private function _showWlanNetworks()
    {
        // try 2 Attempts - in case Network Card is busy / not Network found... iwlist might take a while on first run
        $searchSuccess = false;
        $attempts = 0;
        while (! $searchSuccess && $attempts < 3) {
            $message = array();
            if ($attempts > 0)
                sleep(4);
            $shellanswer = shell_exec("LANG=C && sudo iwlist scan 2>&1");
            if (preg_match('=wlan0.*Network is down=', $shellanswer) == 1) {
                $message[] = _("Restarting Interface WLAN0...");
                $this->writeDynamicScript(array(
                    "wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf; sleep 3;"
                ));
                $shellanswer = shell_exec("LANG=C && sudo iwlist scan 2>&1");
            }
            if (strpos($shellanswer, 'Device or resource busy') !== false) {
                $message[] = _("WLAN Device not responding - Reboot and try again.");
                return false;
            }
            
            preg_match_all('=ESSID:"(.{1,50}?)".*?Group Cipher : (TKIP|CCMP|CCMP TKIP).*?Pairwise Ciphers \([0-2]\) : (TKIP|CCMP|CCMP TKIP).*?Authentication Suites \([0-2]\) : (PSK)=is', $shellanswer, $matches);
            if (count($matches[1]) > 0) {
                $message[] = _("Networks found and added to dropdown list");
                for ($i = 0; $i < count($matches[1]); $i ++) {
                    $this->view->wlanNetworks[] = array(
                        'ESSID' => $matches[1][$i],
                        'GCIPHER' => $matches[2][$i],
                        'PCIPHER' => $matches[3][$i],
                        'AUTH' => $matches[4][$i]
                    );
                }
                $searchSuccess = true;
            } else {
                $message[] = _("No networks found");
            }
            $attempts ++;
        }
        $this->view->message = array_merge($this->view->message, $message);
        return true;
    }

    /**
     * New Way to save Network
     * 
     * @param
     *            $ssid
     * @param
     *            $psk
     */
    private function _saveWiFiNetworkSettings($ssid, $psk)
    {
        if (1 == 1) {
            
            // 1. Delete empty Network with ssid="" from $this->wpa_config
            $save = false;
            file_put_contents($this->wpa_config, preg_replace('@network={.*ssid="".*}@s', '', file_get_contents($this->wpa_config)));
            if (trim(shell_exec('cat ' . $this->wpa_config . ' | grep "update_config" | wc -l')) == 0) {
                $script[] = 'echo "update_config=1" >> ' . $this->wpa_config;
            }
            // 2. Start WPA_Supplicant
            // stop hostapd if running! DNSMASQ ??
            $hostapd_running = trim($this->shell_exec('ps -Al | grep hostapd | wc -l'));
            if ($hostapd_running > 0 && ! isset($this->view->eth0)) {
                $script[] = "/etc/init.d/hostapd stop;";
            }
            // move everything to Background with AJAX Call -> show loading -> prevent connection error -> setupAjaxWifi doing that
            $script[] = "ifdown wlan0; killall -q wpa_supplicant && sleep 3;wpa_supplicant -B w -D wext -i wlan0 -c " . $this->wpa_config . ";";
            // Change connection settings to wpa_cli OR use Default Config File if no ssid is set
            if (strlen(str_replace('*', '', $psk)) > 0 && strlen($ssid) > 0) {
                $script[] = "wpa_cli -iwlan0 add_network";
                $script[] = "wpa_cli -iwlan0 set_network 0 key_mgmt WPA-PSK";
                $script[] = "wpa_cli -iwlan0 set_network 0 mode 0";
                $script[] = "wpa_cli -iwlan0 set_network 0 psk '\"" . $psk . "\"'";
                $script[] = "wpa_cli -iwlan0 set_network 0 ssid '\"" . $ssid . "\"'";
                $script[] = "wpa_cli -iwlan0 enable_network 0";
                $save = true;
            } elseif (strlen($ssid) == 0 || strlen($psk) == 0) {
                $script[] = "wpa_cli -iwlan0 remove_network 0";
                $save = true;
            }
            if ($save) {
                ignore_user_abort(true);
                set_time_limit(400);
                $script[] = "wpa_cli -iwlan0 save_config";
                $script[] = "killall -q wpa_supplicant && sleep 3"; // moved ifdown wlan0 to start of script
                                                                    
                // new: Disable Accesspoint when hostapd is running and no eth0 is connected to allows WiFi Connection
                if ($hostapd_running > 0 && ! isset($this->view->eth0)) {
                    $this->setupAjaxWifi($ajax = 0, $ssid, $psk);
                    return 9;
                }
                
                $outputSave = $this->writeDynamicScript($script);
                $this->view->message[] = 'Debug: Saving WPA-Supplicant (' . $outputSave . ')';
                if (strlen($ssid) > 0 && strpos($outputSave, 'FAIL') !== FALSE) {
                    $this->view->message[] = _('NetworkID or Passphrase are WRONG - WiFi Login not saved!');
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            $shellanswer = shell_exec("cat " . $this->wpa_config);
            
            $shellanswer = str_replace('ssid="' . $this->view->ssid . '"', 'ssid="' . $ssid . '"', $shellanswer);
            
            if (strlen(str_replace('*', '', $psk)) > 0 || $psk == '') {
                $shellanswer = str_replace('psk="' . $this->view->psk . '"', 'psk="' . $psk . '"', $shellanswer);
            }
            shell_exec("echo '" . $shellanswer . "' > " . $this->wpa_config);
        }
        return true;
    }

    private function _saveWirelessConfig()
    {
        $ssid = $_REQUEST['ssid'];
        $psk = $_REQUEST['psk'];
        if (in_array($_REQUEST['groupcipher'], array(
            'TKIP',
            'CCMP'
        ))) {
            $gcipher = $_REQUEST['groupcipher'];
        } else {
            $gcipher = $this->view->groupcipher;
        }
        
        $saveWiFi = $this->_saveWiFiNetworkSettings($ssid, $psk);
        if (intval($saveWiFi) == 9) // Save with Ajax!
            return true;
        
        $shellanswer_eth = $this->writeDynamicScript(array(
            "cat " . $this->networkinterfaces
        ));
        
        // TODO: Deactivate on RPI
        if ($_GET['lanmac'] != '' && $this->view->lanmac != $_GET['lanmac'] && preg_match("=([0-9abcdefABCDEF]{2}:){5}[0-9abcdefABCDEF]{2}=", $_GET['lanmac'], $matches) == true) {
            if ($this->view->bootconfig) {
                // XU3
                $this->writeDynamicScript(array(
                    'sed -i \'s@setenv macaddr .*@setenv macaddr "' . $matches[0] . '"@\' /media/boot/boot.ini'
                ));
            } else {
                // Set rights to update Mac-Address-File
                shell_exec("sudo /opt/max2play/change_mac_address.sh");
                shell_exec("echo '" . $matches[0] . "' > " . $this->mac_address);
                if ($this->getSystemUser() == 'pi') {
                    $this->view->message[] = _('IMPORTANT: do not use the MAC-Address Setup on Raspberry Pi as each device has its own MAC-Address! Leave the MAC-Address empty!');
                }
            }
            $this->view->message[] = _('MAC-Address changed - please reboot');
        }
        
        if ($_REQUEST['lanipfix'] == '1' && preg_match("=([0-9]{1,3}\.){3}[0-9]{1,3}=", $_REQUEST['lanip'], $match) == true) {
            if ($match[0] != $this->view->lanip || $this->view->fixedip == false) {
                $fixedIP = $match[0];
                $output_route = shell_exec("ip route show");
                if (preg_match("=default via (([0-9]{1,3}\.){3}[0-9]{1,3})=", $output_route, $route)) {
                    $gateway = $route[1];
                    $this->view->networkmask;
                    $this->view->lanip = $fixedIP;
                    // set fixed IP
                    // $this->view->fixedinterface is set to eth0 / wlan0
                    // Set dnsserver to fixed Google DNS
                    $shellanswer_eth = str_replace('iface ' . $this->view->fixedinterface . ' inet dhcp', "iface " . $this->view->fixedinterface . " inet static\n  address " . $fixedIP . "\n  gateway " . $gateway . "\n  dns-nameservers 8.8.8.8\n  netmask " . $this->view->networkmask, $shellanswer_eth);
                    $this->writeDynamicScript(array(
                        "sleep 3;echo '" . $shellanswer_eth . "' > " . $this->networkinterfaces . "; ifdown " . $this->view->fixedinterface . "; ifup " . $this->view->fixedinterface . ";"
                    ), $background = false, $deamon = true);
                    $this->view->message[] = str_replace('$FIXEDIP', $fixedIP, _('IP-Address set to $FIXEDIP'));
                    $this->view->message[] = _('Restarting Interface (especially WLAN0) might take some time (up to 1 minute)!');
                } else {
                    $this->view->message[] = _('No network route found.');
                }
            }
        } elseif (! isset($_REQUEST['lanipfix']) && $this->view->fixedip) {
            // Remove fixed IP
            $shellanswer_eth = preg_replace('=iface ' . $this->view->fixedinterface . ' inet static[^#]*netmask .*$=m', 'iface ' . $this->view->fixedinterface . ' inet dhcp', $shellanswer_eth);
            $this->writeDynamicScript(array(
                "echo '" . $shellanswer_eth . "' > " . $this->networkinterfaces . "; ifdown " . $this->view->fixedinterface . "; ifup " . $this->view->fixedinterface . ";"
            ));
            $this->view->message[] = _('IP-Address set to dynamic DHCP');
        }
        
        // Wenn Netzwerk gesetzt muss dieses in der etc/network/interfaces geladen werden
        if (strpos($shellanswer_eth, '#pre-up wpa_supplicant') !== FALSE) {
            // WLAN ist deaktiviert
            $wlanstatus = false;
        } else {
            $wlanstatus = true;
        }
        
        if ($ssid != '' && ($wlanstatus == false || $saveWiFi == true) && $_GET['wlan_configured'] != false) {
            // WLAN aktivieren
            $this->view->message[] = _('WLAN activated - please reboot device');
            $shellanswer_eth = str_replace(array(
                '#pre-up wpa_supplicant',
                '#allow-hotplug wlan0',
                '#auto wlan0',
                '#iface wlan0 inet dhcp',
                '#post-down killall'
            ), array(
                'pre-up wpa_supplicant',
                'allow-hotplug wlan0',
                'auto wlan0',
                'iface wlan0 inet dhcp',
                'post-down killall'
            ), $shellanswer_eth);
            $this->writeDynamicScript(array(
                "echo '" . $shellanswer_eth . "' > " . $this->networkinterfaces . ";sudo ifup wlan0"
            ));
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'autoreconnect_wifi', '1');
        } elseif (($ssid == '' || $_GET['wlan_configured'] == false) && $wlanstatus == true) {
            $this->view->message[] = _('WLAN deactivated - no network choosen - please reboot');
            $shellanswer_eth = str_replace(array(
                'pre-up wpa_supplicant',
                'allow-hotplug wlan0',
                'auto wlan0',
                'iface wlan0 inet dhcp',
                'post-down killall'
            ), array(
                '#pre-up wpa_supplicant',
                '#allow-hotplug wlan0',
                '#auto wlan0',
                '#iface wlan0 inet dhcp',
                '#post-down killall'
            ), $shellanswer_eth);
            $this->writeDynamicScript(array(
                "sleep 3; echo '" . $shellanswer_eth . "' > " . $this->networkinterfaces . ";sudo ifdown wlan0"
            ), false, true);
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'autoreconnect_wifi', '0');
            
            // optional reenable Accesspoint if no ETH0 connected and Accesspoint enabled as config option
            if (! isset($this->view->eth0) && ($this->view->auto_accesspoint_mode == 1 || $_REQUEST['auto_accesspoint_mode'] == 1)) {
                $this->view->message[] = _('No connection available anymore - Starting Accesspoint mode!');
                // ifdown wlan0 - start wpa_supplicant - restart hostapd
                $this->writeDynamicScript(array(
                    "sleep 3;/var/www/max2play/application/plugins/accesspoint/scripts/install_accesspoint.sh /var/www/max2play/application/plugins/accesspoint/scripts/ 1"
                ), false, true);
            }
        }
        
        $wpsenabled = trim(shell_exec('cat /etc/rc.local | grep wps_config | wc -l')) > 0 ? true : false;
        if (isset($_REQUEST['wpsenabled']) && $wpsenabled == FALSE) {
            $this->writeDynamicScript(array(
                'sed -i "s@^exit 0@if [ \"\$(LANG=C \&\& /sbin/ip addr show eth0 | grep \'inet \' | wc -l)\" -lt \"1\" ]; then sudo /opt/max2play/wps_config.sh; fi\nexit 0@" /etc/rc.local'
            ));
        } elseif (! isset($_REQUEST['wpsenabled']) && $wpsenabled == TRUE) {
            $this->writeDynamicScript(array(
                'sed -i "s@.*sudo /opt/max2play/wps_config.sh; fi@@" /etc/rc.local'
            ));
        }
        
        // Automatic Accesspoint Mode
        if (isset($_REQUEST['auto_accesspoint_mode']) && $this->view->auto_accesspoint_mode != $_REQUEST['auto_accesspoint_mode']) {
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'auto_accesspoint_mode', 1);
            // Check and Install hostapd and dnsmasq
            $this->view->message[] = nl2br($this->writeDynamicScript(array(
                "/var/www/max2play/application/plugins/accesspoint/scripts/install_accesspoint.sh /var/www/max2play/application/plugins/accesspoint/scripts/ 1 onlyinstall"
            )));
        } elseif (! isset($_REQUEST['auto_accesspoint_mode']) && $this->view->auto_accesspoint_mode == 1) {
            $this->saveConfigFileParameter('/opt/max2play/options.conf', 'auto_accesspoint_mode', 0);
        }
        
        $this->view->message[] = _("Data saved");
        return true;
    }

    private function _getWirelessConfig()
    {
        
        // Allgemeine Interface Config
        $shellanswer_if = shell_exec("LANG=C && /sbin/ifconfig");
        preg_match('=wlan0=', $shellanswer_if, $match);
        $this->view->ifconfig_txt = $shellanswer_if;
        
        // Get Current IP-address from first interface OR any other interface but not lo
        // Also check if more than one Interface is configured and set eth0_active and wlan0_active
        if (preg_match_all('/(?=(eth0|wlan0).*?)((?!packets).)+(inet addr:|inet )(([0-9]{1,3}\.){3}[0-9]{1,3})(?<!127\.0\.0\.1).*?(Mask:|netmask )(([0-9]{1,3}\.){3}[0-9]{1,3})/si', $shellanswer_if, $currip)) {
            for ($i = 0; $i < count($currip[0]); $i ++) {
                $this->view->$currip[1][$i] = $currip[4][$i];
                $this->view->fixedinterface = $currip[1][$i]; // this interface will get a fixed IP if set
                $this->view->lanip = $currip[4][$i];
                $this->view->networkmask = $currip[7][$i];
            }
        }
        
        $shellanswer_eth = $this->writeDynamicScript(array(
            "cat " . $this->networkinterfaces
        ));
        // Wenn Netzwerk gesetzt muss dieses in der etc/network/interfaces geladen werden
        if (strpos($shellanswer_eth, '#pre-up wpa_supplicant') !== FALSE) {
            // WLAN ist deaktiviert
            $this->view->wlan_configured = false;
        } else {
            $this->view->wlan_configured = true;
            $this->view->wlan_ip = trim(shell_exec("LANG=C && /sbin/ip addr show wlan0 | grep -o 'inet [0-9.]\+' | grep -o '[0-9.]\+'"));
            if ($this->view->wlan_ip == '') {
                $this->view->wlan_ip = _('NO IP! No connection!');
            }
        }
        
        // Get fixed IP-address from network config, if set
        if (preg_match('=iface (eth0|wlan0) inet static\s*address (([0-9]{1,3}\.){3}[0-9]{1,3})\s*gateway (([0-9]{1,3}\.){3}[0-9]{1,3}).*?\s*.*?netmask (([0-9]{1,3}\.){3}[0-9]{1,3})=ims', $shellanswer_eth, $fixedip)) {
            $this->view->fixedinterface = $fixedip[1];
            $this->view->fixedip = $fixedip[2];
            $this->view->fixedgateway = $fixedip[4];
            $this->view->fixednetmask = $fixedip[6];
        } else {
            $this->view->fixedip = false;
        }
        
        // iwconfig zur Erkennung des WLAN-Sticks
        $shellanswer_iwconfig = shell_exec("/sbin/iwconfig | grep wlan0 | wc -l");
        if ($shellanswer_iwconfig == 1) {
            // WLAN-Device verfügbar
            $this->view->wlan_active = true;
        }
        
        // WPA Konfiguration
        $shellanswer = shell_exec("cat " . $this->wpa_config);
        preg_match('=ssid\="(.*?)"=', $shellanswer, $match);
        if ($match[1]) {
            $this->view->ssid = $match[1];
        } else {
            $this->view->ssid = '';
        }
        
        preg_match('=group\=(.*)=', $shellanswer, $match);
        if ($match[1]) {
            $this->view->groupcipher = $match[1];
        }
        
        preg_match('=psk\="(.*?)"=', $shellanswer, $match);
        if ($match[1]) {
            $this->view->psk = $match[1];
            // Original durch Sterne ersetzen
            $star = '';
            for ($i = 0; $i < strlen($match[1]); $i ++)
                $star .= '*';
            $this->view->webpsk = $star;
        } else {
            $this->view->webpsk = '';
            $this->view->psk = '';
        }
        
        // Netzwerk Konfiguration ODROID ETH0 / /etc/smsc95xx_mac_addr löschen -> wird neu zugewiesen
        $shellanswer = shell_exec("cat /etc/smsc95xx_mac_addr 2>/dev/null");
        if (preg_match('=([0-9a-zA-Z:]*)=', $shellanswer, $match)) {
            if ($match[1]) {
                $this->view->lanmac = $match[1];
            }
        }
        
        // Netzwerk MAC ODROID XU3
        $shellanswer = shell_exec('cat /media/boot/boot.ini | grep "setenv macaddr"');
        if (preg_match('="([0-9a-zA-Z:]*)"=', $shellanswer, $match)) {
            if ($match[1]) {
                $this->view->lanmac = $match[1];
                $this->view->bootconfig = true;
            }
        }
        
        // WPS-Config
        $this->view->wpsenabled = trim(shell_exec('cat /etc/rc.local | grep wps_config | wc -l')) > 0 ? true : false;
        
        // Auto Accesspoint
        $this->view->auto_accesspoint_mode = $this->getConfigFileParameter('/opt/max2play/options.conf', 'auto_accesspoint_mode');
        return true;
    }

    public function showHelpSidebar()
    {
        global $helpSidebar;
        $helpSidebar['title'] = _('Help - WiFi & LAN');
        $helpSidebar['content'] = _('<ul><li>On this page you may set up and configure your WiFi and see details on the connected interfaces and their IP-Address.</li><li><a href="https://youtu.be/HsEBb_CIJ5A" target="blank">Watch a video showing WiFi setup with Max2Play</a></li><li>Step by Step: search for networks - choose you prefered network, activate the enable WiFi checkbox, enter the password and click save.</li></ul>');
        return true;
    }
}

$wlan = new Wlan();
include_once (dirname(__FILE__) . '/../view/wlan.php');