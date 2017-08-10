<?php

/**
 Squeezelite Administration Controller
 
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
class Squeezeplayer extends Service
{

    protected $pname = 'squeezelite';

    public $viewname = 'Squeezelite';

    public $soundDevices = array();

    public $soundDeviceLog = '';

    public $scriptPath = '';

    public function __construct()
    {
        parent::__construct();
        $this->pluginname = _('Audioplayer');
        $this->scriptPath = dirname(__FILE__) . '/../scripts/';
        
        if ($_REQUEST['ajax'] == 1) {
            // Function to get Progress of Installation
            $this->updateSqueezelite(1);
            ob_end_clean();
            echo implode('<br />', $this->view->message);
            ob_flush();
            die();
        }
        
        $this->getSoundDevices();
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'start') {
                // Backgroundfile is set as output in init.d Script!
                $this->view->message[] = $this->start($this->pname, $command = '', $statusname = '', $rootstart = false, $background = '/tmp/squeezelite.txt');
            }
            
            if ($_GET['action'] == 'stop') {
                $this->view->message[] = $this->stop($this->pname);
            }
            
            if ($_GET['action'] == 'kill') {
                $this->view->message[] = $this->kill($this->pname);
            }
            
            if ($_GET['action'] == 'save') {
                $this->selectAutostart(isset($_GET['autostartsqueeze']) ? 1 : 0);
                $this->saveSqueezeliteCommandline();
                $this->setUseUSB_DAC();
                $this->getSoundDevices();
            }
            
            if ($_GET['action'] == 'resetEqualizer') {
                $this->updateEqualizer(0, false, (strpos($this->view->squeezelite_soundcard, 'equalUSB') !== FALSE) ? $card = 'equalUSB' : $card = 'equal');
            }
            if ($_GET['action'] == 'saveEqualizer') {
                $this->getSqueezeliteCommandline();
                $this->updateEqualizer($_GET['settingsEqualizer'], false, (strpos($this->view->squeezelite_soundcard, 'equalUSB') !== FALSE) ? $card = 'equalUSB' : $card = 'equal');
            }
            
            if ($_GET['action'] == 'installLadspa') {
                $this->installLADSPA();
            }
            if ($_GET['update_squeezelite'] == 1) {
                $this->updateSqueezelite();
            }
            if ($_GET['action'] == 'setAudioOutputPI') {
                $this->setAudioOutputPI($_GET['AudioOutputPI']);
            }
        }
        
        $this->getAudioOutputPI();
        
        $this->configLADSPA();
        
        $this->getAllLogs();
        
        $this->getSqueezeliteCommandline();
        
        $this->getEqualizer(false, (strpos($this->view->squeezelite_soundcard, 'equalUSB') !== FALSE) ? $card = 'equalUSB' : $card = 'equal');
        
        $this->view->pid = $this->status($this->pname);
        
        $this->view->autostart = $this->checkAutostart($this->pname, true);
        
        $this->showHelpSidebar();
        
        $this->loadAPIHandler();
    }

    /**
     * Set additional Settings for Soundcards
     * 
     * @return boolean
     */
    public function setOptionsDependingSoundcard($setsoundcard = '')
    {
        // Wolfson Sound
        if (strpos($setsoundcard, 'CARD=sndrpiwsp') !== FALSE) {
            $this->view->wolfsonaddon = true;
            $user = $this->getSystemUser();
            // Start the 3 Scripts for Headphone, Lineout and SPDIF
            $script[] = 'su - ' . $user . ' -c \'/opt/max2play/wolfson/Playback_to_Headset.sh 2>&1\'';
            $script[] = 'su - ' . $user . ' -c \'/opt/max2play/wolfson/Playback_to_Lineout.sh 2>&1\'';
            $script[] = 'su - ' . $user . ' -c \'/opt/max2play/wolfson/SPDIF_playback.sh 2>&1\'';
            $this->view->message[] = _('Set Wolfson Audio-Output to play on Line-Out, Headphone and SPDIF');
            $this->view->message[] = $this->writeDynamicScript($script);
        }
        
        // Addon Hifiberry Sound
        if (strpos($setsoundcard, 'CARD=sndrpihifiberry') !== FALSE) {
            $user = $this->getSystemUser();
            $dtoverlay = $this->getConfigFileParameter('/boot/config.txt', 'dtoverlay');
            if (in_array($dtoverlay, array(
                'hifiberry-dacplus',
                'hifiberry-dac'
            ))) {
                $this->writeDynamicScript(array(
                    'su - ' . $user . ' -c \'amixer sset "PCM" 96%\'',
                    'su - ' . $user . ' -c \'amixer sset "Playback" 100%\'',
                    'su - ' . $user . ' -c \'alsactl store\''
                ));
                $this->view->message[] = _('Set HiFiBerry DAC Sound to 96% (Optimum value)');
            }
        }
        // IQAudIO Soundcard - Do this Only for DAC and NOT for AMP!! Use audioplayer in options.conf
        if (1 == 2 && strpos($setsoundcard, 'CARD=IQaudIODAC') !== FALSE) {
            $user = $this->getSystemUser();
            $dtoverlay = $this->getConfigFileParameter('/boot/config.txt', 'dtoverlay');
            if (in_array($dtoverlay, array(
                'iqaudio-dacplus'
            ))) {
                $this->writeDynamicScript(array(
                    'su - ' . $user . ' -c \'amixer -c IQaudIODAC sset "Playback" 100%\'',
                    'su - ' . $user . ' -c \'amixer -c IQaudIODAC sset "Analogue Playback Boost" 100%\'',
                    'su - ' . $user . ' -c \'amixer -c IQaudIODAC sset "Digital" 96%\'',
                    'su - ' . $user . ' -c \'alsactl store\''
                ));
                $this->view->message[] = _('Set IQAudIO DAC Sound to 96% (Optimum value)');
            }
        }
        return true;
    }

    /**
     * get Playable Sounddevices for Squeezelite / Shairport from Squeezelite
     * Style:
     * default:CARD=ALSA - bcm2835 ALSA, bcm2835 ALSA - Default Audio Device
     */
    public function getSoundDevices()
    {
        $this->soundDeviceLog = $this->writeDynamicScript(array(
            '/opt/squeezelite/squeezelite -l'
        ));
        // Regex zerlegung:
        // $matches[1][x] = Name
        // $matches[2][x] = Karte/Device
        // $matches[3][x] = Beschreibung
        // $matches[4][x] = Name ist PCM Software Device
        // $matches[5][x] = Beschreibung von PCM Software Device
        // $matches[6][x] = PCM Software Device ohne Beschreibung
        preg_match_all('=^[ ]*([^:\n]+)[ ]*:[ ]*([^-\n ]+)[ ]*-[ ]*(.*)[ ]*|^[ ]*([^\n- ]+)[ ]*-[ ]*(.*)[ ]*|^[ ]*([^\n-:]+)[ ]*$=im', $this->soundDeviceLog, $matches);
        $count = count($matches[1]);
        for ($i = 0; $i < $count; $i ++) {
            $dev = array();
            // Fallunterscheidung je nach Devices (Software / Hardware / mit und ohne Hinweistext)
            if ($matches[1][$i] !== '') {
                $dev['name'] = $matches[1][$i];
                $dev['card'] = $matches[2][$i];
                $dev['description'] = $matches[3][$i];
                $this->view->soundDevices[$matches[1][$i] . ':' . $matches[2][$i]] = $dev;
            } elseif ($matches[4][$i] !== '') {
                $dev['name'] = $matches[4][$i];
                $dev['card'] = '';
                $dev['description'] = $matches[5][$i];
                // add without "plug:"
                if ($matches[4][$i] == 'bluetoothspeaker' || $matches[4][$i] == 'pulse')
                    $this->view->soundDevices[$matches[4][$i]] = $dev;
                else
                    $this->view->soundDevices['plug:' . $matches[4][$i]] = $dev;
            } elseif ($matches[6][$i] !== '') {
                $dev['name'] = $matches[6][$i];
                $dev['card'] = '';
                $dev['description'] = $matches[6][$i];
                $this->view->soundDevices['plug:' . $matches[6][$i]] = $dev;
            }
        }
        unset($this->view->soundDevices['plug:null']);
        
        return true;
    }

    /**
     * Save Command-Line Options from
     * squeezelite_soundcard
     * squeezelite_commandline
     */
    public function saveSqueezeliteCommandline()
    {
        $commandLine = array();
        $setsoundcard = $_GET['squeezelite_soundcard'];
        
        $this->setOptionsDependingSoundcard($setsoundcard);
        
        if (in_array($setsoundcard, array_keys($this->view->soundDevices))) {
            $commandLine[] = '-o ' . $setsoundcard;
        } else {
            $commandLine[] = '-o plug:dmixer';
        }
        if (isset($_GET['squeezelite_alsaparam']) && $_GET['squeezelite_alsaparam'] != '') {
            $commandLine[] = '-a ' . $_GET['squeezelite_alsaparam'];
        }
        
        // TODO: Regex für korrekte Erkennung der Commandlineeingabe der Parameter
        $commandLine[] = trim($_GET['squeezelite_commandline']);
        
        $value = trim(implode(' ', $commandLine));
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'SQUEEZELITE_PARAMETER', $value)) {
            $this->view->message[] = str_replace('$SERVICE', $this->viewname, _('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
            
            if ($this->status($this->pname) !== FALSE) {
                // Restart Service
                $this->view->message[] = $this->stop($this->pname);
                $this->view->message[] = $this->start($this->pname, $command = '', $statusname = '', $rootstart = false, $background = '/tmp/squeezelite.txt');
            }
        }
        
        return true;
    }

    /**
     * Get Commandline Options for Audioplayer by Config File
     * 
     * @return boolean
     */
    public function getSqueezeliteCommandline()
    {
        $output = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SQUEEZELITE_PARAMETER');
        if (preg_match_all('=-o ([^ ]*)( -a ([^ ]*))?( (.*))?=', $output, $match)) {
            $this->view->squeezelite_soundcard = trim($match[1][0]);
            $this->view->squeezelite_alsaparam = trim($match[3][0]);
            $this->view->squeezelite_commandline = $match[5][0];
        }
        $this->view->use_usb_dac = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_USB_DAC');
        return true;
    }

    /**
     * Plugin for audiophile headphone listening
     */
    public function installLADSPA()
    {
        // if($this->checkLicense(true) == false)
        // return true;
        $this->view->message[] = nl2br($this->writeDynamicScript(array(
            '/opt/max2play/install_ladspa.sh "' . $this->getHardwareInfo() . '"'
        )));
        return true;
    }

    /**
     * controls [ 700 4.5 ] means lowpass filter cut frequency (700) and crossfeed level (4.5 dB)
     * Possible values: 300-1000 for lowpass filter and 1 to 15 for crossfeed level
     * Presets: http://bs2b.sourceforge.net/
     */
    public function configLADSPA()
    {
        $installcheck = shell_exec('grep -a controls /etc/asound.conf');
        if ($installcheck != '') {
            $this->view->ladspa_installed = true;
            // controls [ 500 4.5 ]
            preg_match_all('=controls.\[.([0-9]*).([0-9\.]*).?\]=', $installcheck, $matches);
            $this->view->ladspa_lowpass = $matches[1][0];
            $this->view->ladspa_crossfeed = $matches[2][0];
        } else {
            $this->view->ladspa_installed = false;
            return true;
        }
        if ($_GET['action'] == 'saveLadspa' && $_GET['lowpass'] && $_GET['crossfeed']) {
            $frequency = $_GET['lowpass'];
            $crossfeed = $_GET['crossfeed'];
            $this->writeDynamicScript(array(
                'sed -i "s/controls \[.*\]/controls [ ' . $frequency . ' ' . $crossfeed . ' ]/" /etc/asound.conf;alsa force-reload;'
            ));
            $this->view->message[] = _('Settings saved! Set Squeezelite options (advanced-button) to use headphones output with "-o headphones"');
            $this->view->ladspa_lowpass = $frequency;
            $this->view->ladspa_crossfeed = $crossfeed;
        }
        return true;
    }

    public function setUseUSB_DAC()
    {
        if ($_GET['use_usb_dac'] == 1) {
            $value = 1;
            // Set Soundoutput to 100% for Card 1 all Speakers - should be done on FIRST usage
            $user = $this->getSystemUser();
            $script[] = 'sudo --user ' . $user . ' amixer -c 1 sset Speaker 100%';
            $script[] = 'sudo cp /opt/max2play/alsa_max2play_usb.conf /usr/share/alsa/alsa.conf.d';
            $this->getLinuxVersion();
            if ($this->getHardwareInfo() == 'Raspberry PI' && ! ($this->info->linux[0] == "Raspbian" && $this->info->linux[1] == "jessie")) {
                // Remove Eq10 as Pluginname for USB-Equalizer in Raspbian Wheezy
                $script[] = 'sed -i "s@module \"Eq10\";@@" /usr/share/alsa/alsa.conf.d/alsa_max2play_usb.conf';
            }
            $this->writeDynamicScript($script);
        } else {
            $value = 0;
            $this->writeDynamicScript(array(
                'sudo rm /usr/share/alsa/alsa.conf.d/alsa_max2play_usb.conf'
            ));
        }
        
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_USB_DAC', $value)) {
            $this->view->message[] = str_replace('$SERVICE', 'USB DAC', _('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
        }
        return true;
    }

    private function getAllLogs()
    {
        $out['SQUEEZELITE VERSION'] = shell_exec('/opt/squeezelite/squeezelite -t | grep ^Squeezelite');
        $out['SQUEEZELITE BUILDOPTIONS'] = shell_exec('/opt/squeezelite/squeezelite -? | grep "^Build options"');
        $out['AUDIOPLAYER CONFIG'] = shell_exec('cat /opt/max2play/audioplayer.conf');
        $out['SQUEEZELITE -l'] = $this->soundDeviceLog;
        $out['SHAIRPORT VERSION'] = shell_exec('/opt/shairport/shairport -V');
        $out['OUTPUT SOUND DETAILS CARD 0'] = shell_exec('cat /proc/asound/card0/pcm0p/sub0/hw_params');
        $out['OUTPUT SOUND DETAILS CARD 1'] = shell_exec('cat /proc/asound/card1/pcm0p/sub0/hw_params');
        if ($this->debug === TRUE) {
            $out['SOUNDDEVICES IN USE'] = $this->writeDynamicScript(array(
                '/bin/fuser -v /dev/snd/* 2>&1'
            ));
        }
        $this->view->debug = $out;
        return true;
    }

    /**
     * Remove Autostart for time during Update!
     * Update Squeezelite from Sources and Compile it
     *
     * @return boolean
     */
    private function updateSqueezelite($ajax = 0)
    {
        $outfile = '/opt/max2play/cache/update_squeezelite.txt';
        
        if ($ajax == 0) {
            $autostart = $this->checkAutostart($this->pname, true);
            ignore_user_abort(true);
            set_time_limit(3600);
            $this->view->message[] = _t('Update Squeezelite started');
            $this->view->message[] = $this->stop($this->pname);
            if ($this->getProgressWithAjax($outfile, 1, 1)) {
                $shellanswer = $this->writeDynamicScript(array(
                    $this->scriptPath . "update_squeezelite.sh " . $autostart . " >> " . $outfile . " &"
                ), false, true);
            }
        } else {
            // Get only last 20 Lines
            $status = $this->getProgressWithAjax($outfile, 0, 0, 20);
            $this->view->message[] = nl2br($status);
            if (strpos($status, 'Finished') !== FALSE) {
                shell_exec('rm ' . $outfile);
                $this->view->message[] = $this->start($this->pname);
            }
        }
        
        return true;
    }

    /**
     * Function for Raspberry PI to switch Output between Default, Jack and HDMI
     * Default: if hdmi is connected it will be choosen
     */
    private function setAudioOutputPI($value = 0)
    {
        if ($this->getHardwareInfo() == 'Raspberry PI') {
            $this->view->audioOutputPI = $this->writeDynamicScript(array(
                'sudo -u pi amixer -c ALSA cset numid=3 ' . $value
            ));
            $this->view->message[] = str_replace('$VALUE', $value, _('Raspberry PI Audio Output set to $VALUE'));
        }
        return true;
    }

    /**
     * Function for Raspberry PI to get Audio-Output
     * can be Default=0, Headphone-Jack=1 or HDMI=2
     */
    private function getAudioOutputPI()
    {
        if ($this->getHardwareInfo() == 'Raspberry PI') {
            $this->view->audioOutputPI = trim($this->writeDynamicScript(array(
                'su -l pi -c \'amixer -c ALSA cget numid=3 | grep ": values=" | sed "s/.*values=//"\''
            )), "\n");
            return $this->view->audioOutputPI;
        } else
            return false;
    }

    /**
     * To Trim all values of an array
     * 
     * @param string $value
     *            reference
     * @return boolean
     */
    private function trim_value(&$value)
    {
        $value = trim($value);
        return true;
    }

    public function showHelpSidebar()
    {
        global $helpSidebar;
        $helpSidebar['title'] = _('Help - Audioplayer');
        $helpSidebar['content'] = _('<ul><li>On this page you may set up and configure all audioplayers. Each player has the button "Advanced Settings" to set up the soundcard and further options.</li><li>Some soundcards like HifiBerry need to be activated before you can assign them to a player. This can be done with the addon "Raspberry PI Settings".</li><li>To use Airplay you may choose the Shairport player OR the plugin Shairtunes (install on Squeezebox Server tab), that enables all Squeezeplayers (such as Squeezelite) to act as an Airplay device. This is useful if a soundcard supports only one connected player.</li></ul>');
        $helpSidebar['wikilink'] = 'https://www.max2play.com/en/wiki/audioplayer-squeezelites-shairport/';
        return true;
    }

/**
 * APIDOC Settings Equalizer
 *
 * @api {post} /plugins/max2play_settings/controller/Squeezeplayer.php Equalizer Settings
 * @apiName Equalizer Settings
 * @apiGroup Audioplayer API
 * @apiVersion 1.0.0
 * @apiDescription Fetch / Update / Reset Equalizer Settings on Max2Play Device.<br />
 *
 * @apiParam {String} apijson Always set to "1" when using the API
 * @apiParam {String} action Define what to do: may be empty or "saveEqualizer" or "resetEqualizer"
 * @apiParam {String} use_equalizer Override Max2Play Setting wether to read Equalizer settings: must be set to "1", if Equalizer is disabled in web interface
 * @apiParam {Array} settingsEqualizer Array to set new Equalizer Values for each frequency: Key => Value: [01.+31+Hz]=54
 * @apiSuccess {Array} equalvalues Object of Type Equalizer
 *
 *
 * @apiExample {json} Example get Equalizer Values:
 * curl -v -X GET "http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1"
 *
 * @apiExample {json} Example change Equalizer Values (GET):
 * curl -v -X GET "http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1&action=saveEqualizer&settingsEqualizer[01.+31+Hz]=54&settingsEqualizer[02.+63+Hz]=66%25&settingsEqualizer[03.+125+Hz]=66%25&settingsEqualizer[04.+250+Hz]=66%25&settingsEqualizer[05.+500+Hz]=66%25&settingsEqualizer[06.+1+kHz]=66%25&settingsEqualizer[07.+2+kHz]=66%25&settingsEqualizer[08.+4+kHz]=66%25&settingsEqualizer[09.+8+kHz]=66%25&settingsEqualizer[10.+16+kHz]=66%25
 *
 * @apiExample {json} Example reset Equalizer Values (GET):
 * curl -v -X GET "http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1&action=resetEqualizer"
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * Content-Type: application/json; charset=utf-8
 * X-API-Version: 1.0.0
 * {
 * ...
 * "equalvalues":{
 * "01. 31 Hz":"55%",
 * "02. 63 Hz":"60%",
 * "03. 125 Hz":"66%",
 * "04. 250 Hz":"66%",
 * "05. 500 Hz":"66%",
 * "06. 1 kHz":"70%",
 * "07. 2 kHz":"66%",
 * "08. 4 kHz":"66%",
 * "09. 8 kHz":"66%",
 * "10. 16 kHz":"66%"
 * },
 * ...
 * }
 */
}

$sp = new Squeezeplayer();

// zusätzlich die Konfig für Shairport und DLNA client laden
include_once (dirname(__FILE__) . '/Shairport.php');
include_once (dirname(__FILE__) . '/Gmediarender.php');

include_once (dirname(__FILE__) . '/../view/squeezeplayer.php');