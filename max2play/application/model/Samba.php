<?php

/**
 Class for Sambashare Definition
 
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
class Samba
{

    private $name;

    private $options;
 // writeable,create mode,etc
    protected $allowed_options = array(
        'writeable',
        'create mode',
        'path',
        'comment'
    );

    public function __construct($share = '')
    {
        if ($share != '') {
            // get Name
            preg_match('=\[(.*?)\]=', $share, $match);
            if (isset($match[1]) && $match[1] != '')
                $this->name = $match[1];
            
            // get Options
            preg_match_all('=(.*)\=(.*)=', $share, $matches);
            // $matches[1] = optionnames
            // $matches[2] = optionvalues
            for ($i = 0; $i < count($matches[1]); $i ++) {
                $this->setOption($matches[1][$i], $matches[2][$i]);
            }
        }
        return $this;
    }

    public function setName($name)
    {
        if ($name != '') {
            $this->name = $name;
            return true;
        }
        return false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOption($name = '', $value = '')
    {
        $name = trim($name);
        $value = trim($value);
        if ($name != '' && $value != '' && in_array($name, $this->allowed_options)) {
            $this->options[$name] = $value;
            return true;
        }
        return false;
    }

    public function getOptions($optionname = '', $assoc = true)
    {
        if ($optionname != '') {
            if (isset($this->options[$optionname])) {
                return $this->options[$optionname];
            } else
                return false;
        } elseif ($assoc) {
            return $this->options;
        } else {
            $opt = array();
            foreach ($this->options as $key => $value)
                $opt[] = $key . ' = ' . $value;
            return implode("\r\n", $opt);
        }
    }

    public function getShareConf()
    {
        return "[" . $this->getName() . "]\n" . $this->getOptions('', false);
    }
}
