#!/bin/bash

# Path to current Directory
CURRENTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "Install-Script"

echo "Update sources"
sudo apt-get update

echo "Counter 1"
sleep 2
echo "Counter 2"
sleep 2
echo "Counter 3"
sleep 2
echo "Counter 4"

echo "finished"
exit 0