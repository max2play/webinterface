<?php

/**
 Kernel Setup Controller
 
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
class Kernelmodules_odroid_u3_Setup extends Service
{

    public $scriptPath = '/opt/max2play/';

    public function __construct()
    {
        parent::__construct();
        $this->scriptPath = dirname(__FILE__) . '/../scripts/';
        
        $this->pluginname = _('Kernelmodules U3');
        
        if ($this->checkLicense(true) == false)
            return true;
        
        if ($_GET['ajax'] == 1 && $_GET['action'] == 'buildHifaceWithKernel') {
            // Function to get Progress of Installation
            $this->_buildHifaceWithKernel(1);
            ob_end_clean();
            echo implode('<br />', $this->view->message);
            ob_flush();
            die();
        }
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'enableHifaceUSB') {
                $this->_enableHifaceUSB(true);
            }
            if ($_GET['action'] == 'disableHifaceUSB') {
                $this->_enableHifaceUSB(false);
            }
            if ($_GET['action'] == 'buildHifaceWithKernel') {
                $this->_buildHifaceWithKernel();
            }
        }
        
        $this->getHifaceUSBEnabled();
        // Get Debug Info
        $this->_getAllLogs();
    }

    public function getHifaceUSBEnabled()
    {
        $isenabled = trim(shell_exec('cat /etc/rc.local | grep snd-usb-hiface | wc -l'));
        $this->view->hiface_enabled = $isenabled;
        return $this->view->hiface_enabled;
    }

    /**
     * Get current Kernel and Build Hiface Module
     */
    private function _buildHifaceWithKernel($ajax = 0)
    {
        /* Komplett mit Kernel bauen und Headers */
        if ($ajax == 0) {
            ignore_user_abort(true);
            set_time_limit(6000);
            $this->view->message[] = _('Kernel Build started');
            if ($this->getProgressWithAjax('/opt/max2play/cache/build_kernel.txt', 1, 0)) {
                $shellanswer = $this->writeDynamicScript(array(
                    $this->scriptPath . "build_kernel.sh >> /opt/max2play/cache/build_kernel.txt &"
                ));
            }
        } else {
            // Get only last 20 Lines
            $status = $this->getProgressWithAjax('/opt/max2play/cache/build_kernel.txt', 0, 0, 20);
            $this->view->message[] = nl2br($status);
            if (strpos($status, 'Finished') !== FALSE) {
                shell_exec('rm /opt/max2play/cache/build_kernel.txt');
            }
        }
        return true;
    }

    /**
     * Enable Module HiFace USB on ODROID Ubuntu 14.04 Kernel
     */
    private function _enableHifaceUSB($enable = false)
    {
        $this->getHifaceUSBEnabled();
        if ($enable) {
            if ($this->view->hiface_enabled == 0) {
                $this->writeDynamicScript(array(
                    "sed -i 's/^exit 0/insmod " . str_replace('/', '\\/', $this->scriptPath) . "snd-usb-hiface.ko\\n\\nexit 0/' /etc/rc.local"
                ));
                $this->view->message[] = _('Added HiFace USB-Driver');
            }
        } else {
            if ($this->view->hiface_enabled == 1) {
                $this->writeDynamicScript(array(
                    "sed -i 's/^insmod " . str_replace('/', '\\/', $this->scriptPath) . "snd-usb-hiface.ko//' /etc/rc.local"
                ));
                $this->view->message[] = _('Removed HiFace USB-Driver');
            }
        }
        return true;
    }

    /**
     * get some Debug Output and save it for view
     */
    private function _getAllLogs()
    {
        $this->view->debug = $out;
    }
}

// Create an instance of your Class
$kernelmodules_odroid_u3 = new Kernelmodules_odroid_u3_Setup();

// This Line includes the View-Script -> it should have the same name as your class
include_once (dirname(__FILE__) . '/../view/setup.php');

