#!/bin/sh
(echo "$1"; echo "$1") | smbpasswd -s -a odroid
echo "password changed"
