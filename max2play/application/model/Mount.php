<?php

/**
 Klasse für Mountpoint
 
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
class Mount
{

    private $mountpoint;

    private $type;

    private $path;

    private $options;

    public function __construct()
    {}

    public function setMountpoint($mp)
    {
        if ($mp != '') {
            $this->mountpoint = str_replace(' ', '\\040', $mp);
            return true;
        }
        return false;
    }

    /**
     * get Realpath with spaces
     * 
     * @param string $truepath
     */
    public function getMountpoint($realpath = true, $escape = false)
    {
        if ($realpath) {
            if ($escape)
                return str_replace('\\', '\\\\', $this->mountpoint);
            else
                return $this->mountpoint;
        } else
            return str_replace('\\040', ' ', $this->mountpoint);
    }

    public function setType($type)
    {
        if ($type != '') {
            $this->type = $type;
            return true;
        }
        return false;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPath($path)
    {
        if ($path != '') {
            $this->path = $path;
            return true;
        }
        return false;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setOptions($opt)
    {
        $this->options = $opt;
        return true;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
