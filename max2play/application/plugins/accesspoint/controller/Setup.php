<?php

/**
 Accesspoint Setup Controller
 
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

/**
 *
 * @author Stefan Rick
 *         Have a close look at the Service Class with all its functions!
 *        
 */
class Accesspoint_Setup extends Service
{

    public $scriptPath = '';

    public $config;

    public function __construct()
    {
        parent::__construct();
        $this->scriptPath = dirname(__FILE__) . '/../scripts/';
        $this->pluginname = _('Accesspoint Setup');
        $this->registerLocale(dirname(__FILE__) . '/../locale', 'accesspoint');
        $this->config = new stdClass();
        
        if ($this->checkLicense(true) == false)
            return true;
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'install') {
                $this->_install();
            }
            
            if ($_GET['action'] == 'uninstall') {
                $this->_uninstall();
            }
            
            if ($_GET['action'] == 'saveSettings') {
                $this->_saveSettings();
            }
        }
        if ($this->_checkInstall()) {
            $this->_getSettings();
        }
        // Get Debug Info
        $this->_getAllLogs();
    }

    private function _install()
    {
        $this->view->message[] = nl2br($this->writeDynamicScript(array(
            $this->scriptPath . 'install_accesspoint.sh ' . $this->scriptPath
        )));
        $this->view->message[] = _t('Reboot is needed to work properly!');
        return true;
    }

    private function _uninstall()
    {
        $this->view->message[] = nl2br($this->writeDynamicScript(array(
            $this->scriptPath . 'uninstall_accesspoint.sh'
        )));
        return true;
    }

    private function _getSettings()
    {
        $this->config->passphrase = $this->getConfigFileParameter('/etc/hostapd/hostapd.conf', 'wpa_passphrase');
        $this->config->ssid = $this->getConfigFileParameter('/etc/hostapd/hostapd.conf', 'ssid');
        $this->config->driver = $this->getConfigFileParameter('/etc/hostapd/hostapd.conf', 'driver');
        $this->config->standalone = trim(shell_exec('cat /etc/dnsmasq.conf | grep ^address=/#/192.168.189.1 | wc -l'));
        
        return true;
    }

    private function _saveSettings()
    {
        $this->_getSettings();
        $reload = false;
        if ($_GET['passphrase'] != $this->config->passphrase) {
            if (strlen($_GET['passphrase']) > 7) {
                $this->saveConfigFileParameter('/etc/hostapd/hostapd.conf', 'wpa_passphrase', $_GET['passphrase']);
                $reload = true;
            } else {
                $this->view->message[] = _t('Wrong passphrase (must be 8 to 64 characters)');
            }
        }
        if ($_GET['ssid'] != $this->config->ssid) {
            $this->saveConfigFileParameter('/etc/hostapd/hostapd.conf', 'ssid', $_GET['ssid']);
            $reload = true;
        }
        if ($_GET['driver'] != $this->config->driver) {
            if ($_GET['driver'] == false)
                $this->deleteConfigFileParameter('/etc/hostapd/hostapd.conf', 'driver');
            else {
                $this->saveConfigFileParameter('/etc/hostapd/hostapd.conf', 'driver', $_GET['driver']);
            }
            $reload = true;
        }
        if ($reload) {
            $this->view->message[] = nl2br($this->writeDynamicScript(array(
                '/etc/init.d/hostapd reload'
            )));
        }
        
        $script = array();
        if (isset($_GET['standalone']) && $this->config->standalone == 0) {
            // Add Rule for Redirect to self
            $script[] = 'echo "address=/#/192.168.189.1" >> /etc/dnsmasq.conf';
            // Activate Default Gateway to self
            $script[] = 'sed -i \'s/#gateway 192.168.189.1/gateway 192.168.189.1/\' /etc/network/interfaces';
            $script[] = 'sed -i \'s/#\/bin\/ip route add default via 192.168.189.1 dev wlan0/\/bin\/ip route add default via 192.168.189.1 dev wlan0/\' /etc/init.d/hostapd';
            $script[] = '/etc/init.d/dnsmasq restart;/etc/init.d/hostapd restart';
            $this->view->message[] = nl2br($this->writeDynamicScript($script));
        }
        if (! isset($_GET['standalone']) && $this->config->standalone == 1) {
            // Remove Rules and Gateway
            $script[] = 'sed -i "s/address=\/#\/192\.168\.189\.1//" /etc/dnsmasq.conf';
            $script[] = 'sed -i \'s/gateway 192.168.189.1/#gateway 192.168.189.1/\' /etc/network/interfaces';
            $script[] = 'sed -i \'s/ \/bin\/ip route add default via 192.168.189.1 dev wlan0/ #\/bin\/ip route add default via 192.168.189.1 dev wlan0/\' /etc/init.d/hostapd';
            $script[] = '/etc/init.d/dnsmasq restart;/etc/init.d/hostapd restart';
            $this->view->message[] = nl2br($this->writeDynamicScript($script));
        }
        return true;
    }

    /**
     * Check important parts of installation
     * 
     * @return boolean
     */
    private function _checkInstall()
    {
        $this->config->hostapd_installed = shell_exec('dpkg -s hostapd | grep Status');
        $this->config->dnsmasq_installed = shell_exec('dpkg -s dnsmasq | grep Status');
        $this->config->interfaces_installed = shell_exec('cat /etc/network/interfaces | grep Accesspoint | wc -l');
        if ($this->config->hostapd_installed && $this->config->dnsmasq_installed && $this->config->interfaces_installed > 0) {
            $this->view->installed = 1;
        } else {
            $this->view->installed = 0;
        }
        return $this->view->installed;
    }

    /**
     * get some Debug Output and save it for view
     */
    private function _getAllLogs()
    {
        $out['DNSMASQ Installed'] = $this->config->dnsmasq_installed;
        $out['HOSTAPD Installed'] = $this->config->hostapd_installed;
        $out['WiFi Interface Installed'] = $this->config->interfaces_installed;
        $out['DNSMASQ Running'] = shell_exec('ps -Al | grep dnsmasq');
        $out['HOSTAPD Running'] = shell_exec('ps -Al | grep hostapd');
        
        $this->view->debug = $out;
    }
}

// Create an instance of your Class
$accesspoint = new Accesspoint_Setup();

// This Line includes the View-Script -> it should have the same name as your class
include_once (dirname(__FILE__) . '/../view/setup.php');

