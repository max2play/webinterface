#!/bin/sh
(echo "$1"; echo "$1") | smbpasswd -s -a root
echo "password changed"
