#!/bin/bash
#http://jasperproject.github.io/documentation/configuration/
echo "Scriptpath: $1"

echo "Y" | apt-get install vim git-core python-dev python-pip bison libasound2-dev libportaudio-dev python-pyaudio --yes
pushd /opt
git clone https://github.com/jasperproject/jasper-client.git jasper
pip install --upgrade setuptools
pip install -r jasper/client/requirements.txt

#Bugfix for Audiooutput - Maybe change this dynamically?
pushd /opt
sed -i "s/cmd = \['aplay', '-D', 'hw:1,0', str(filename)\]/cmd = \['aplay', str(filename)\]/" jasper/client/tts.py

chmod +x jasper/jasper.py

#Add Asound-Config
sudo cp $1asound_jasper_pi.conf /usr/share/alsa/alsa.conf.d
sudo cp -f $1jasper/client/mic.py /opt/jasper/client/mic.py
sudo cp -f $1jasper/profile.yml /home/pi/.jasper/profile.yml

# Espeak TTS
echo "Y" | apt-get install espeak

# Google STT?
#echo "Y" | apt-get install python-pymad
#easy_install -U pip
#pip install --upgrade gTTS

#### Install pocketsphinx  ####
echo "Y" | apt-get remove libpulse-dev
export LD_LIBRARY_PATH="/usr/local/lib"
LIBRARY_PATH_SET=$(grep -i "LD_LIBRARY_PATH" /home/pi/.bashrc | wc -l)
if [ "$LIBRARY_PATH_SET" -lt "1" ]; then
	echo "export LD_LIBRARY_PATH=\"/usr/local/lib\"" >> /home/pi/.bashrc
fi

pushd /opt
wget http://downloads.sourceforge.net/project/cmusphinx/sphinxbase/0.8/sphinxbase-0.8.tar.gz
tar -zxvf sphinxbase-0.8.tar.gz
cd sphinxbase-0.8/
./configure --enable-fixed
make
sudo make install
cd ..

pushd /opt
wget http://downloads.sourceforge.net/project/cmusphinx/pocketsphinx/0.8/pocketsphinx-0.8.tar.gz
tar -zxvf pocketsphinx-0.8.tar.gz
cd pocketsphinx-0.8/
./configure
make
sudo make install
cd ..

echo "Y" | sudo apt-get install subversion autoconf libtool automake gfortran g++ --yes

pushd /opt
svn co https://svn.code.sf.net/p/cmusphinx/code/trunk/cmuclmtk/
cd cmuclmtk/
sudo ./autogen.sh && sudo make && sudo make install
cd ..

sudo su -c "echo 'deb http://ftp.debian.org/debian experimental main contrib non-free' > /etc/apt/sources.list.d/experimental.list"
sudo apt-get update
echo "Y" | sudo apt-get -t experimental install m2m-aligner mitlm

pushd /opt
wget http://distfiles.macports.org/openfst/openfst-1.3.3.tar.gz
wget https://phonetisaurus.googlecode.com/files/phonetisaurus-0.7.8.tgz
tar -xvf openfst-1.3.3.tar.gz
tar -xvf phonetisaurus-0.7.8.tgz

pushd /opt
cd openfst-1.3.3/
sudo ./configure --enable-compact-fsts --enable-const-fsts --enable-far --enable-lookahead-fsts --enable-pdt
sudo make install
cd ..

pushd /opt
cd phonetisaurus-0.7.8/
cd src
sudo make
cd ..
sudo cp phonetisaurus-0.7.8/phonetisaurus-g2p /usr/local/bin/phonetisaurus-g2p

pushd /opt
wget http://phonetisaurus.googlecode.com/files/g014b2b.tgz
tar -xvf g014b2b.tgz

cd g014b2b/
./compile-fst.sh

pushd /opt
mv g014b2b phonetisaurus

 
# TODO Copy Client!
#python /opt/jasper/client/populate.py
#Test with: /opt/jasper/jasper.py --debug –-local

#Test for pocketsphinx and German Dict from https://mattze96.safe-ws.de/blog/?p=640
cd /opt
wget http://goofy.zamia.org/voxforge/de/voxforge-de-r20141117.tgz
tar xvzf voxforge-de-r20141117.tgz
cd voxforge-de-r20141117
#./run-pocketsphinx.sh

# Set Up jasper .profile.yml to use voxforge model and espeak to use german

# Building own speech list http://cmusphinx.sourceforge.net/wiki/tutoriallm

# More Voices: http://espeak.sourceforge.net/mbrola.html
echo "Finished"
