<?php

/**
 Setup for Callblocker tellows
 
 @Copyright 2014 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de
 */
class Callblocker_Blacklist extends Service
{

    public $local_blacklist = '/opt/callblocker/cache/blacklist_local.txt';

    public $local_whitelist = '/opt/callblocker/cache/whitelist_local.txt';

    public $calllog = '/var/log/cidcall.log';

    public function __construct()
    {
        parent::__construct();
        $this->registerLocale(dirname(__FILE__) . '/../locale', 'callblocker');
        $this->pluginname = _('tellows Callblocker Blacklist');
        
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'save') {
                $this->_updateBlacklist($_POST['blacklist'], $_POST['whitelist']);
            }
            if ($_POST['action'] == 'whitelist') {
                $this->_addToWhitelist($_POST['number']);
            }
            if ($_POST['action'] == 'blacklist') {
                $this->_addToBlacklist($_POST['number']);
            }
        }
        
        $this->_loadLocalLists();
        $this->getCallLog();
        $this->_getTellowsCountry();
    }

    /**
     * Parsen nach CID (nach Device) / HUP (Blackist - IMMER) / END (nur POTS)
     */
    public function getCallLog()
    {
        $output = shell_exec('tail -30 ' . $this->calllog);
        $data = explode("\n", $output);
        $last_type = $last_time = '';
        $calls = array();
        foreach ($data as $line) {
            $type = substr($line, 0, 3);
            // type = HUP -> blacklist / CID -> normal / END -> normal
            if ($last_type != '' || $type != 'END') {
                // Regex zur Aufschlüsselung
                // CID: *DATE*06062014*TIME*1222*LINE*�620*NMBR***622*MESG*NONE*NAME*iPhone von StefanRic*
                // END: *HTYPE*CANCEL*DATE*06062014*TIME*1222*SCALL*06/06/2014 12:22:35*ECALL*06/06/2014 12:22:41*CTYPE*IN*LINE*�620*NMBR***622*NAME*iPhone von StefanRic*
                // HUP: *DATE*06062014*TIME*1222*LINE*�620*NMBR***610*MESG*NONE*NAME*Mobilteil 1*
                if ($type == 'CID' || $type == 'HUP') {
                    preg_match('=\*DATE\*([0-9]*)\*TIME\*([0-9]*)\*LINE\*[^0-9a-zA-Z]*([^\*]*)\*NMBR\*(\*?\*?\+?[a-zA-Z0-9]*)\*.*NAME\*([^\*]*)=', $line, $match);
                    $call['date'] = strftime('%d-%m-%Y %H:%M', strtotime(substr($match[1], 4, 4) . '-' . substr($match[1], 0, 2) . '-' . substr($match[1], 2, 2) . ' ' . substr($match[2], 0, 2) . ':' . substr($match[2], 2, 2)));
                    $call['line'] = $match[3];
                    $call['number'] = $match[4];
                    $call['name'] = $match[5];
                    $call['blacklist'] = ($type == 'HUP') ? true : false;
                    $call['blacklist_type'] = '';
                    $call['blacklist_byname'] = false;
                    
                    // Get Information for Blacklisted Callers by tellows OR local Blacklist
                    // If number is on Blacklist but wasn't blacklisted before -> mark it red! Same on Whitelist with green
                    
                    // Check local file
                    if (in_array($call['number'], $this->blacklist_array)) {
                        $call['blacklist_type'] = _t('local');
                    }
                    // Go through Blacklist_Array and find possible Regular Expressions
                    foreach ($this->blacklist_array as $entry)
                        if (strlen($entry) > 3 && preg_match('=' . $entry . '=', $call['number']))
                            $call['blacklist_type'] = _t('local');
                    
                    if (in_array($call['name'], $this->blacklist_array) && strlen($call['name']) > 2) {
                        // Block by Name
                        $call['blacklist_type'] = _t('local');
                        $call['blacklist_byname'] = true;
                    }
                    if ($call['blacklist_type'] == '') {
                        // TODO: check auf internationale Nummern mit Grep
                        $output = shell_exec('cat /opt/callblocker/cache/tellows.csv | grep ^' . $call['number'] . ';');
                        // Save tellows Information for Number
                        if (strlen($output) > 10) {
                            $tdata = explode(';', $output);
                            $call['blacklist_type'] = 'tellows';
                            $call['name'] = $tdata[6];
                        }
                    }
                    
                    if (in_array($call['number'], $this->whitelist_array)) {
                        $call['whitelist'] = true;
                    } else {
                        $call['whitelist'] = false;
                    }
                    
                    if ($last_time == '' || ($last_number != $call['number']) || ($last_time != $call['date'])) {
                        // Ignore duplicates by different lines (SIP & POTS)
                        $calls[] = $call;
                    }
                    $last_time = $call['date'];
                    $last_number = $call['number'];
                }
                $last_type = $type;
            }
        }
        $this->calllog = $calls;
        return true;
    }

    private function _updateBlacklist($blacklist, $whitelist)
    {
        // Call Scripts
        // Message Output (print it just once...)
        $this->view->message[_t('Updated local Blacklist / Whitlist for Callblocker')] = _t('Updated local Blacklist / Whitlist for Callblocker');
        
        if ($blacklist !== false) {
            $blacklist = preg_replace('=[^0-9\*\+\r\na-zA-Z\.\?]=', '', $blacklist);
            $this->writeDynamicScript(array(
                'echo "' . trim(str_replace("\r\n", "\n", $blacklist), "\n") . '" > ' . $this->local_blacklist
            ));
        }
        if ($whitelist !== false) {
            $whitelist = preg_replace('=[^0-9\*\+\r\na-zA-Z\.\?]=', '', $whitelist);
            $this->writeDynamicScript(array(
                'echo "' . trim(str_replace("\r\n", "\n", $whitelist), "\n") . '" > ' . $this->local_whitelist
            ));
        }
        // IMPORTANT: Prevent Script from running multiple times
        $count = 0;
        while ($count ++ < 20 && trim(shell_exec('ps -Al | grep tellowsblack | wc -l') > 0)) {
            sleep(1);
        }
        $this->writeDynamicScript(array(
            'sudo /opt/callblocker/tellowsblacklist.sh local'
        ), true);
        
        return true;
    }

    private function _loadLocalLists()
    {
        $this->view->blacklist = shell_exec('cat ' . $this->local_blacklist);
        $this->blacklist_array = explode("\n", $this->view->blacklist);
        
        $this->view->whitelist = shell_exec('cat ' . $this->local_whitelist);
        $this->whitelist_array = explode("\n", $this->view->whitelist);
        return true;
    }

    private function _addToWhitelist($number = '')
    {
        $this->_loadLocalLists();
        if (! in_array($number, $this->whitelist_array)) {
            $this->view->whitelist .= $number . "\n";
            $this->_updateBlacklist(false, $this->view->whitelist);
        }
        
        if (in_array($number, $this->blacklist_array)) {
            $this->blacklist_array = $this->array_remove($this->blacklist_array, $number);
            $this->view->blacklist = implode("\n", $this->blacklist_array);
            $this->_updateBlacklist($this->view->blacklist, false);
        }
        
        return true;
    }

    /**
     * Add to Blacklist and also remove from Whitelist!
     * 
     * @param string $number
     */
    private function _addToBlacklist($number = '')
    {
        $this->_loadLocalLists();
        if (! in_array($number, $this->blacklist_array)) {
            $this->view->blacklist .= $number . "\n";
            $this->_updateBlacklist($this->view->blacklist, false);
        }
        if (in_array($number, $this->whitelist_array)) {
            $this->whitelist_array = $this->array_remove($this->whitelist_array, $number);
            $this->view->whitelist = implode("\n", $this->whitelist_array);
            $this->_updateBlacklist(false, $this->view->whitelist);
        }
        
        return true;
    }

    /**
     * Helper Function
     * 
     * @param unknown $array
     * @param unknown $remove
     * @param string $remove_value
     * @return unknown
     */
    function array_remove($array, $remove, $remove_value = true)
    {
        foreach ($array as $key => $value) {
            if ($remove_value && $value != $remove) {
                $new_array[$key] = $value;
            } elseif (! $remove_value && $key != $remove) {
                $new_array[$key] = $value;
            }
        }
        return $new_array;
    }

    private function _getTellowsCountry()
    {
        $output = shell_exec('cat /opt/callblocker/tellows.conf');
        preg_match('=country\=([a-z]*)=', $output, $match);
        $this->tellows = new stdClass();
        $this->tellows->country = $match[1];
        if ($match[1] == 'us') {
            $this->tellows->country = 'com';
        } elseif ($match[1] == 'gb') {
            $this->tellows->country = 'co.uk';
        }
        return true;
    }
}

$cb = new Callblocker_Blacklist();

include_once (dirname(__FILE__) . '/../view/blacklist.php');

