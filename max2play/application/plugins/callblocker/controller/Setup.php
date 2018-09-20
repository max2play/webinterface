<?php

/**
 Setup for Callblocker tellows
 
 @Copyright 2014 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de
 
 */
class Callblocker_Setup extends Service
{

    public function __construct()
    {
        parent::__construct();
        $this->registerLocale(dirname(__FILE__) . '/../locale', 'callblocker');
        $this->pluginname = _('tellows Callblocker Setup');
        
        if ($_GET['ajax'] == 1 && $_GET['actionupdate'] == 'updateCallblocker') {
            // Function to get Progress of Installation
            $this->_updateCallblocker(1);
            ob_end_clean();
            echo implode('<br />', $this->view->message);
            ob_flush();
            die();
        }
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'savetellows') {
                $this->_saveTellowsConf(); // zusätzlich speichern der SIP-Anrufabnahme (Sound)
            }
            if ($_GET['action'] == 'savelinphone') {
                $this->_saveLinphoneConf();
            } elseif ($_GET['action'] == 'updateCallblocker') {
                $this->_updateCallblocker();
            } elseif ($_GET['action'] == 'savemodemsettings') {
                $this->_saveModemSettings((int) $_GET['hangup_type']);
            }
        }
        
        if (isset($_GET['actionupdate']) && $_GET['actionupdate'] == 'updateCallblocker') {
            $this->_updateCallblocker();
        }
        if (isset($_GET['actionupdate']) && $_GET['actionupdate'] == 'reboot') {
            $reboot = $this->reboot();
        }
        
        // SIP Config
        $this->_getLinphoneConf();
        
        // Check, ob Logindaten stimmen
        $this->_getTellowsConf();
        
        // Check auf Modem
        if ($this->modemConnected()) {
            $this->_getModemSettings();
        }
        
        $this->getAllLogs();
        
        $this->getCBVersion();
        
        if ($reboot) {
            $this->writeDynamicScript(array(
                'sleep 2s;sudo /sbin/reboot -n;'
            ), false, true);
        }
    }

    private function _saveTellowsConf()
    {
        // Call Scripts
        $this->view->message[] = _t('Save tellows Settings');
        $this->writeDynamicScript(array(
            "echo 'partner=tellowskey\napikey=" . $_GET['tellows_apikey'] . "\nminscore=" . $_GET['tellows_minscore'] . "\ncountry=" . $_GET['tellows_country'] . "\nchecked=1\naudiofile=" . $_GET['tellows_audiofile'] . "' > /opt/callblocker/tellows.conf"
        ));
        $this->_getTellowsConf();
        if ($this->tellows->registered_bool === FALSE) {
            $this->writeDynamicScript(array(
                "echo 'partner=tellowskey\napikey=" . $_GET['tellows_apikey'] . "\nminscore=" . $_GET['tellows_minscore'] . "\ncountry=" . $_GET['tellows_country'] . "\nchecked=0\naudiofile=" . $_GET['tellows_audiofile'] . "' > /opt/callblocker/tellows.conf"
            ));
            $this->view->message[] = _t('API-Key could not be registered and seems to be wrong!');
        } else {
            $this->view->message[] = _t('API-Key successfully registered!');
        }
        // delete tellows blacklist and get new One!
        $this->writeDynamicScript(array(
            'rm /opt/callblocker/cache/tellows.csv',
            'sudo /opt/callblocker/tellowsblacklist.sh'
        ));
        return true;
    }

    /**
     * partner=tellowskey
     * apikey=test
     * minscore=7
     */
    private function _getTellowsConf()
    {
        $this->tellows = new stdClass();
        $output = shell_exec('cat /opt/callblocker/tellows.conf');
        preg_match('=apikey\=([a-zA-Z0-9]*)=', $output, $match);
        $this->tellows->apikey = $match[1];
        preg_match('=minscore\=([0-9])=', $output, $match);
        $this->tellows->minscore = $match[1];
        preg_match('=country\=([a-z]*)=', $output, $match);
        $this->tellows->country = $match[1];
        
        // What happens with callers on blacklist - play Sound or just block?
        if (! preg_match('=audiofile\=([0-9]*)=', $output, $match)) {
            // create entry
            $this->saveConfigFileParameter('/opt/callblocker/tellows.conf', 'audiofile', '1');
            $this->tellows->audiofile = 1;
        } else
            $this->tellows->audiofile = $match[1];
        
        // tellows Testcall
        $output = $this->writeDynamicScript(array(
            'wget -O /opt/callblocker/cache/apitest.txt "http://www.tellows.de/api/checklicense?partner=tellowskey&apikeyMd5=' . md5($this->tellows->apikey) . '"'
        ));
        $output = shell_exec('cat /opt/callblocker/cache/apitest.txt');
        $values = json_decode($output, true);
        
        if ($values['AUTHENTICATION'] == 'SUCCESSFUL') {
            $this->tellows->registered = _t('Connection Successful') . ' ' . _t('License Valid until') . ' ' . $values['VALIDUNTIL'];
            $this->tellows->registered_bool = true;
        } elseif ($values['AUTHENTICATION'] == 'FAILED') {
            $this->tellows->registered = str_replace('$MESSAGE', $values['MESSAGE'], _t('<span style="color:red;">Connection Failure - API-Key not valid: $MESSAGE</span>'));
            $this->tellows->registered_bool = false;
        } else {
            $this->tellows->registered = _t('Could not check tellows Connection! Internet not available?');
            $this->tellows->registered_bool = false;
        }
        
        // fetch Timestamp of List and number of phonenumbers in blacklist
        $this->tellows->blacklist_date = strftime('%d-%m-%Y', (int) shell_exec('stat -L --format %Y /opt/callblocker/cache/tellows.csv'));
        $this->tellows->blacklist_entries = (int) shell_exec('cat /opt/callblocker/cache/tellows.csv | wc -l') - 1;
        return true;
    }

    private function _getLinphoneConf()
    {
        $this->linphone = new stdClass();
        $output = shell_exec('cat /opt/callblocker/linphone.conf');
        $tmp = explode('--', $output);
        $this->linphone->host = trim(str_replace('host', '', $tmp[1]));
        $this->linphone->user = trim(str_replace('username', '', $tmp[2]));
        $this->linphone->password = trim(str_replace('password', '', $tmp[3]));
        
        if ($this->linphone->host != '' && $this->linphone->user != '') {
            $this->linphone->running = $this->status('linphonec');
            $this->linphone->registered = $this->writeDynamicScript(array(
                'linphonecsh generic "status registered"' //  2>&1 -> Pipe Connect Error on Debian Stretch Apache2
            ));
            // TODO: Add Debug Mode for start_linphone -d
            // TODO: cannot connect to Pipe with www-data! Error is printed instead -  strpos($this->linphone->registered, 'ERROR') !== FALSE
            if (strpos($this->linphone->registered, 'registered=-1') !== FALSE || strpos($this->linphone->registered, 'registered=0') !== FALSE) {
                $this->linphone->registered = _t('<span style="color:red;">Connection Failure - SIP not connected (check settings)</span>'). ' '. $this->linphone->registered;
            } else {
                $this->linphone->registered = _t('Successfull Connected') . ' ' . $this->linphone->registered;
            }
        } else {
            $this->linphone->registered = _t('SIP not configured');
        }
        return true;
    }

    private function _saveLinphoneConf()
    {
        // write linphone settings + write audiofile config
        $this->writeDynamicScript(array(
            "echo '--host " . $_GET['linphone_host'] . " --username " . $_GET['linphone_user'] . " --password " . $_GET['linphone_password'] . "' > /opt/callblocker/linphone.conf",
            'sed -i "s/audiofile=[0-9]/audiofile=' . $_GET['tellows_audiofile'] . '/" /opt/callblocker/tellows.conf'
        ));
        $this->view->message[] = _t('VOIP-Settings Updated');
        // Restart Linphone Service
        
        // TODO: NOT Working as User www-data with Apache on Stretch! -> shift to background service
        // if Stretch -> Reboot is needed! Workaround: use Nginx or run command in cron        
        $version = $this->getLinuxVersion();
        if(isset($version[1]) && $version[1] == "stretch"){
            $this->view->message[] = _t('Important: You need to reboot the device now, to connect to the VOIP Service.');
        }else{
            $this->writeDynamicScript(array(
                "linphonecsh init;sleep 2;linphonecsh generic 'soundcard use files';linphonecsh register $(cat /opt/callblocker/linphone.conf);sleep 2;chmod a+rw /dev/null;"
            ));
        }
        
        return true;
    }

    /**
     * Update Max2Play-Plugin AND Settings under /opt/callblocker
     * To extend Features for Callblocker in later Versions
     * 
     * @param $ajax if
     *            set just show progress
     */
    private function _updateCallblocker($ajax = 0)
    {
        if ($ajax == 0) {
            ignore_user_abort(true);
            set_time_limit(3000);
            $this->getCBVersion();
            // Check auf Update
            $file = file_get_contents('http://cdn.tellows.de/uploads/downloads/callblocker/currentversion/version.txt');
            if ((float) $this->view->version < (float) $file || ! $this->view->installed) {
                $this->view->message[] = _t('Callblocker update started');
                // Start Script -> Download Files for Webserver and Scripts
                if ($this->getProgressWithAjax('/opt/max2play/cache/update_callblocker.txt', 1, 0)) {
                    $shellanswer = $this->writeDynamicScript(array(
                        "/opt/max2play/update_callblocker.sh >> /opt/max2play/cache/update_callblocker.txt &"
                    ));
                }
            } else {
                $this->view->message[] = _t('Callblocker is up to date - no update required');
            }
        } else {
            $status = $this->getProgressWithAjax('/opt/max2play/cache/update_callblocker.txt');
            $this->view->message[] = nl2br($status);
            if (strpos($status, 'Finished') !== FALSE) {
                // Finished Progress - did not delete progressfile
                if (strpos($status, 'inflating: /opt/callblocker/incoming.sh') !== FALSE) {
                    $this->view->message[] = _t('UPDATE SUCCESSFUL - Please Restart Device');
                    shell_exec('rm /opt/max2play/cache/update_callblocker.txt');
                } else
                    $this->view->message[] = _t('UPDATE NOT SUCCESSFUL');
            }
        }
    }

    private function modemConnected()
    {
        $out = shell_exec('if [ -e /dev/ttyACM0 ]; then echo "1"; else echo "0"; fi;');
        $this->view->modemconnected = trim($out, "\n");
        return $out;
    }

    private function getAllLogs()
    {
        $out['NCIDD Restart'] = shell_exec('cat /opt/callblocker/cache/ncidd-restart.txt');
        $out['Blacklistevent Last Sync'] = shell_exec('cat /opt/callblocker/cache/blacklistevent_last.txt');
        $out['NCID Running'] = shell_exec('ps -Al | grep ncid');
        $out['NCID Version'] = shell_exec('/usr/sbin/ncidd -V 2>&1');
        $out['SIPPHONE Running'] = shell_exec('ps -Al | grep linphone');
        $out['LOAD AVERAGE'] = shell_exec('cat /proc/loadavg');
        $out['Button Blacklist'] = shell_exec('ps -Al | grep button');
        $out['CallerID LOG Last 50'] = shell_exec('tail -50 /var/log/cidcall.log');
        $out['SIP2NCID LOG Last 50'] = shell_exec('tail -50 /var/log/sip2ncid.log');
        $out['NCIDD LOG Last 50'] = shell_exec('tail -50 /var/log/ncidd.log');
        $out['tellows blacklist download'] = shell_exec('cat /opt/callblocker/cache/tellowsblacklist.txt');
        
        $this->view->debug = $out;
    }

    private function getCBVersion()
    {
        $this->view->version = shell_exec('cat /opt/callblocker/version.txt');
        
        // Check if CB /opt/callblocker/ is INSTALLED!!
        if (file_exists('/opt/callblocker/buttonblacklist.sh'))
            $this->view->installed = true;
        else {
            if (! isset($this->view->message[0])) {
                // Only print message if nothing else happend (e.g. install event)
                $this->view->message[] = _t('Callblocker is not installed - start installation by clicking on UPDATE button at the end of the page!');
            }
        }
        
        return $this->view->version;
    }

    private function _getModemSettings()
    {
        $this->modem = new stdClass();
        $hangup = preg_replace('=[^0-9]*=', '', shell_exec('grep -a "^set hangup" /etc/ncid/ncidd.conf'));
        $this->modem->hangup_type = $hangup;
        return $hangup;
    }

    private function _saveModemSettings($hangup_type = 1)
    {
        // SED-Call?
        $hangup = $this->_getModemSettings();
        $this->writeDynamicScript(array(
            "sed -i 's/^set hangup = " . $hangup . "/set hangup = " . $hangup_type . "/' /etc/ncid/ncidd.conf",
            'sudo /opt/callblocker/tellowsblacklist.sh'
        ));
        $this->view->message[] = _t('Modem Settings saved');
        return true;
    }

    /**
     * Check for Reboot
     * 
     * @return boolean
     */
    public function reboot()
    {
        $path = '/tmp/';
        
        if ($_REQUEST['ajax'] == 1) {
            ob_end_clean();
            // Check for time difference in reboot.txt and set finished only if more than 15 seconds passed since reboot
            $rebootTime = strtotime(trim(shell_exec('grep -io "[^|]*" ' . $path . 'reboot.txt')));
            if ($rebootTime < (time() - 15)) {
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
            if ($this->getProgressWithAjax($path . 'reboot.txt', 1, $reload, 0, _("REBOOT started"), $url))
                return true;
            else
                return false;
        }
    }
}

$cs = new Callblocker_Setup();

include_once (dirname(__FILE__) . '/../view/setup.php');

