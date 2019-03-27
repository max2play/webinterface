<?php

/**
 Raspberry Pi Specific Functions Controller
 @Copyright 2016 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.max2play.com
 
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
include_once (APPLICATION_PATH . '/controller/Service.php');

class Rpi_functions extends Service
{

    public $usbSoundCards = array(
        '' => array(
            'dtoverlay' => '',
            'name' => 'none',
            'mpd_mixer_control' => 'PCM',
            'amixercard' => 'default'
        ),
        'hifiberry-dac' => array(
            'dtoverlay' => 'hifiberry-dac',
            'name' => 'Hifi Berry DAC (PI A/B)',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'hifiberry-daclight' => array(
            'dtoverlay' => 'hifiberry-dac',
            'name' => 'Hifi Berry DAC+ Light (PI 2/3)',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'hifiberry-dacplus' => array(
            'dtoverlay' => 'hifiberry-dacplus',
            'name' => 'Hifi Berry DAC+ (PI 2/3)',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'hifiberry-dacpluspro' => array(
            'dtoverlay' => 'hifiberry-dacplus',
            'name' => 'Hifi Berry DAC+ Pro (PI 2/3)',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'hifiberry-digi' => array(
            'dtoverlay' => 'hifiberry-digi',
            'name' => 'Hifi Berry Digi/Digi+',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'hw:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital'
        ),
        'hifiberry-amp' => array(
            'dtoverlay' => 'hifiberry-amp',
            'name' => 'Hifi Berry Amp/Amp+',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Master',
            'amixercard' => 'sndrpihifiberry'
        ),
        'hifiberry-amp2' => array(
            'dtoverlay' => 'hifiberry-dacplus',
            'name' => 'Hifi Berry AMP 2 (B+ / PI 2/3)',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4:: -o hw:1 -V Digital',
            'shairport' => '-d default:CARD=sndrpihifiberry -t hardware -m hw:1 -c Digital',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'iqaudio-dac' => array(
            'dtoverlay' => 'iqaudio-dac',
            'name' => 'IQaudio Card DAC'
        ),
        'iqaudio-dacplus' => array(
            'dtoverlay' => 'iqaudio-dacplus',
            'name' => 'IQaudio Pi-DAC+',
            'squeezelite' => '-o sysdefault:CARD=IQaudIODAC -a 80:4::',
            'shairport' => '-d sysdefault:CARD=IQaudIODAC',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'IQaudIODAC'
        ),
        'iqaudio-ampplus' => array(
            'dtoverlay' => 'iqaudio-dacplus',
            'name' => 'IQaudio Pi-AMP+/DigiAMP+ (with unmute)',
            'squeezelite' => '-o sysdefault:CARD=IQaudIODAC -a 80:4:: -o hw:CARD=IQaudIODAC -V Digital',
            'shairport' => '-d sysdefault:CARD=IQaudIODAC -t hardware -m hw:CARD=IQaudIODAC -c Digital',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'IQaudIODAC'
        ),
        'iqaudio-digiplus' => array(
            'dtoverlay' => 'iqaudio-digi-wm8804-audio',
            'name' => 'IQaudIO Pi-Digi+',
            'squeezelite' => '-o sysdefault:CARD=IQAudIODigi -a 80:4::',
            'shairport' => '-d sysdefault:CARD=IQAudIODigi',
            'mpd_device' => 'sysdefault:CARD=IQAudIODigi',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'IQAudIODigi'
        ),
        'Audiophonics-I-Sabre-V3' => array(
            'dtoverlay' => 'hifiberry-dac',
            'name' => 'Audiophonics I-Sabre-V3',
            'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::',
            'shairport' => '-d default:CARD=sndrpihifiberry',
            'mpd_device' => 'default:CARD=sndrpihifiberry',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpihifiberry'
        ),
        'rpi-cirrus-wm5102-overlay' => array(
            'dtoverlay' => 'rpi-cirrus-wm5102-overlay',
            'name' => 'Wolfson Cirrus Logic (Kernelchange)',
            'squeezelite' => '-o default:CARD=sndrpiwsp -a 80:4::',
            'shairport' => '-d default:CARD=sndrpiwsp',
            'mpd_mixer_control' => 'Speaker Digital',
            'amixercard' => 'sndrpiwsp'
        ),
        'pifi-dacplus' => array(
            'dtoverlay' => 'hifiberry-dacplus',
            'name' => 'PiFi DAC+'
        ),
        'snd-usb-asyncaudio' => array(
            'dtoverlay' => '',
            'name' => 'USB Soundcards (Northstar Essensio, Hiface and others) - takes ~5 minutes to install!'
        ), // https://github.com/panicking/snd-usb-asyncaudio/wiki
        'justboom-digi' => array(
            'dtoverlay' => 'justboom-digi',
            'name' => 'JustBoom Digi',
            'squeezelite' => '-o sysdefault:CARD=sndrpijustboomd -a 80:4::',
            'shairport' => '-d sysdefault:CARD=sndrpijustboomd',
            'mpd_device' => 'hw:CARD=sndrpijustboomd,DEV=0',
            'mpd_mixer_control' => 'Digital'
        ),
        'justboom-dac' => array(
            'dtoverlay' => 'justboom-dac',
            'name' => 'JustBoom DAC',
            'squeezelite' => '-o sysdefault:CARD=sndrpijustboomd -a 80:4:: -o hw:CARD=sndrpijustboomd -V Digital',
            'shairport' => '-d sysdefault:CARD=sndrpijustboomd -t hardware -m hw:CARD=sndrpijustboomd -c Digital',
            'mpd_device' => 'sysdefault:CARD=sndrpijustboomd',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpijustboomd'
        ),
        'justboom-amp' => array(
            'dtoverlay' => 'justboom-dac',
            'name' => 'JustBoom AMP',
            'squeezelite' => '-o sysdefault:CARD=sndrpijustboomd -a 80:4:: -o hw:CARD=sndrpijustboomd -V Digital',
            'shairport' => '-d sysdefault:CARD=sndrpijustboomd -t hardware -m hw:CARD=sndrpijustboomd -c Digital',
            'mpd_device' => 'sysdefault:CARD=sndrpijustboomd',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'sndrpijustboomd'
        ),
        'raspiplay4' => array(
            'dtoverlay' => 'iqaudio-dacplus',
            'name' => 'RaspyPlay4 Hifi Audio Dac',
            'squeezelite' => '-o sysdefault:CARD=IQaudIODAC -a 80:4::',
            'shairport' => '-d sysdefault:CARD=IQaudIODAC',
            'amixercard' => 'IQaudIODAC'
        ),
        'Piano DAC' => array(
            'dtoverlay' => 'allo-piano-dac-pcm512x-audio',
            'name' => 'Allo Piano HiFi DAC 2.0',
            'squeezelite' => '-o default:CARD=PianoDAC -o hw:CARD=PianoDAC -V Digital -a 80:4::',
            'shairport' => '-d default:CARD=PianoDAC',
            'mpd_device' => 'default:CARD=PianoDAC',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'PianoDAC'
        ),
        'Piano DAC Subwoofer' => array(
            'dtoverlay' => 'allo-piano-dac-plus-pcm512x-audio',
            'name' => 'Allo Piano HiFi DAC 2.1',
            'squeezelite' => '-o default:CARD=PianoDACPlus -o hw:CARD=PianoDACPlus -V Digital -a 80:4::',
            'shairport' => '-d sysdefault:CARD=PianoDACPlus -t hardware -m hw:CARD=PianoDACPlus -c Digital',
            'mpd_device' => 'default:CARD=PianoDACPlus',
            'mpd_mixer_control' => 'Digital',
            'amixercard' => 'PianoDACPlus'
        ),
        'Allo Digione' => array(
            'dtoverlay' => 'allo-digione',
            'name' => 'Allo Digione',
            'squeezelite' => '-o default:CARD=sndallodigione',
            'shairport' => '-d default:CARD=sndallodigione',
            'mpd_device' => 'default:CARD=sndallodigione'
        ),
        'mamboberry-hd-DAC' => array('dtoverlay' => 'hifiberry-dac', 'name' => 'Mamboberry HD DAC+', 'squeezelite' => '-o sysdefault:CARD=sndrpihifiberry -a 80:4::', 'shairport' => '-d default:CARD=sndrpihifiberry',  'mpd_device' => 'default:CARD=sndrpihifiberry','mpd_mixer_control' => 'Digital', 'amixercard' => 'sndrpihifiberry'),
        'durio-sound-pro' => array(
            'dtoverlay' => 'hifiberry-dac',
            'name' => 'Durio Sound Pro'
        )
    );

    /**
     * Important! dtoverlay Parameter might be set multiple times in config.txt -> separate handling needed
     * 
     * @param string $value
     * @param string $dto_type
     *            Possible values: soundcard, single_option
     * @return boolean
     */
    public function saveDTOverlayConfig($value = '', $dto_type = 'notset')
    {
        $dto_entries['soundcard'] = array();
        $dto_entries['i2s-mmap'] = array(
            'i2s-mmap'
        ); // Support for Alsa DMIX on Soundcards
        $dto_entries['bluetooth'] = array(
            'pi3-disable-bt'
        );
        $dto_entries['lirc'] = array(
            'lirc-rpi(,gpio_in_pin=[0-9]+)?(,gpio_out_pin=[0-9]+)?'
        );
        $dto_value = '';
        $separator = '=';
        $configfile = '/boot/config.txt';
        $parameter = 'dtoverlay';
        // fill possible soundcard values
        foreach ($this->usbSoundCards as $card) {
            if (isset($card['dtoverlay']) && $card['dtoverlay'] != '')
                $dto_entries['soundcard'][] = $card['dtoverlay'];
        }
        if (file_exists($configfile)) {
            $old_parameter = explode("\n", trim($this->getConfigFileParameter($configfile, $parameter)));
            // $old_parameter is an array
            // get the right value to replace / add new
            foreach ($old_parameter as $oldparam) {
                if ($dto_type == 'lirc') {
                    // pattern recognition for flexible parameter values
                    foreach ($dto_entries[$dto_type] as $type) {
                        if (preg_match('@' . $type . '@', $oldparam, $match)) {
                            $dto_value = $oldparam;
                        }
                    }
                } else {
                    // normal recognition
                    if (in_array($oldparam, $dto_entries[$dto_type])) {
                        $dto_value = $oldparam;
                    }
                }
                
                if ($oldparam == $value) {
                    // Value existing -> do not change!
                    return true;
                }
            }
            if($dto_type == 'soundcard' && count($old_parameter) > 1){
                // TODO: Remove all but one entry - should never have more than 1? -- not really needed
            }
            
            if ($dto_value != '' && $value == '') {
                // Remove by oldvalue
                $this->writeDynamicScript(array(
                    'sed -i "s/^[ \t]*' . $parameter . $separator . $dto_value . '$//g" ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - existing Entry removed");
            } elseif ($dto_value == '') {
                // Add new
                $this->writeDynamicScript(array(
                    'echo "' . $parameter . $separator . $value . '" >> ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - new Entry created");
            } else {
                // Replace by old value
                $this->writeDynamicScript(array(
                    'sed -i "s/^[ \t]*' . $parameter . $separator . $dto_value . '$/' . $parameter . $separator . str_replace(array(
                        '/'
                    ), array(
                        '\/'
                    ), $value) . '/g" ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - existing Entry changed");
            }
        }
        return true;
    }
    
    /**
     * Important! dtparam Parameter might be set multiple times in config.txt -> separate handling needed
     *
     * @param string $value
     * @param string $dto_type
     *            Possible values: i2s, spi, i2c1,i2c_arm
     * @return boolean
     */
    public function saveDTParamConfig($value = '', $dto_type = 'notset')
    {        
        $dto_entries = array('i2s' => array('i2s=on','i2s=off'), 'spi' => array('spi=on','spi=off'), 'i2c1' => array('i2c1=on', 'i2c1=off'), 'i2c_arm' => 0);        
        $dto_value = '';
        $separator = '=';
        $configfile = '/boot/config.txt';
        $parameter = 'dtparam';
        // fill possible soundcard values
        
        if (file_exists($configfile)) {
            $old_parameter = explode("\n", trim($this->getConfigFileParameter($configfile, $parameter)));
            // $old_parameter is an array
            // get the right value to replace / add new
            foreach ($old_parameter as $oldparam) {
                // normal recognition
                if (in_array($oldparam, $dto_entries[$dto_type])) {
                    $dto_value = $oldparam;
                }                
                
                if ($oldparam == $value) {
                    // Value existing -> do not change!
                    return true;
                }
            }
            
            if ($dto_value != '' && $value == '') {
                // Remove by oldvalue
                $this->writeDynamicScript(array(
                    'sed -i "s/^[ \t]*' . $parameter . $separator . $dto_value . '$//g" ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - existing Entry removed");
            } elseif ($dto_value == '') {
                // Add new
                $this->writeDynamicScript(array(
                    'echo "' . $parameter . $separator . $value . '" >> ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - new Entry created");
            } else {
                // Replace by old value
                $this->writeDynamicScript(array(
                    'sed -i "s/^[ \t]*' . $parameter . $separator . $dto_value . '$/' . $parameter . $separator . str_replace(array(
                        '/'
                    ), array(
                        '\/'
                    ), $value) . '/g" ' . $configfile
                ));
                $this->view->message[] = _("Update Configfile - existing Entry changed");
            }
        }
        return true;
    }
    
    /**
     * get soundcard position e.g. hw:0 or hw:1
     * @param string $soundcard (name of amixercard)
     * @return string for MPD mixer_device
     */
    public function getSoundcardPosition($soundcard = ''){
        $aplayOutput = $this->writeDynamicScript(array('LANG=C && aplay -l | grep "card"'));
        //card 0: ALSA [bcm2835 ALSA], device 1: bcm2835 ALSA [bcm2835 IEC958/HDMI]
        //card 1: IQaudIODAC [IQaudIODAC], device 0: IQaudIO DAC HiFi pcm512x-hifi-0 []
        if(!preg_match_all('=^card ([0-9]): ([^ ]*)=im', $aplayOutput, $aplayMatches)){
            return 'default';
        }
        $count = count($aplayMatches[1]);
        $aplayCards = array();
        for($i = 0; $i < $count; $i++){
            $aplayCards[$aplayMatches[2][$i]] = $aplayMatches[1][$i];
        }
        if(isset($aplayCards[$soundcard])){
            return 'hw:'.$aplayCards[$soundcard];
        } else {
            return 'default';
        }
    }
    
    /**
     * Get current KernelVersion as Array
     * 
     * @return array
     */
    public function getKernelVersion()
    {
        $kernelversion = explode('.', trim($this->shell_exec('uname --all | grep -oe " [0-9]\+\.[0-9\.]\+"')));
        return $kernelversion;
    }

    /**
     * Compare a given Kernel Version to the currently installed Kernel
     * $version = String of Kernel Version
     * 
     * @param Kernelversion $version
     *            e.g. 4.9.31
     * @return true if installed Kernel Version is behind given version
     */
    public function compareKernelVersion($version)
    {
        $kernelversion = $this->getKernelVersion();
        $version = explode('.', $version);
        $smaller = false;
        foreach ($version as $key => $min) {
            if(isset($kernelversion[$key]) && $kernelversion[$key] > $min){
                return false;
            }elseif(isset($kernelversion[$key]) && $kernelversion[$key] == $min){
                // Check further...            
            } else {
                $smaller = true;
                return $smaller;
            }
        }
        return $smaller;
    }
}