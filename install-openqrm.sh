#!/bin/bash
#
# installs openQRM Community
#

clear
WHOAMI=`whoami`
if [ "$WHOAMI" != "root" ]; then
	echo "ERROR: Please run this install script as root!"
	exit 1
fi
export OPENQRM_SERVER_INSTALL_DIR=`pushd \`dirname $0\` 1>/dev/null && pwd && popd 1>/dev/null`
cd $OPENQRM_SERVER_INSTALL_DIR
clear
more openQRM-releasenotes.txt
echo
echo
echo
echo "==========================================================="
echo "Welcome to the openQRM Community installation"
echo "==========================================================="
echo "This script will install openQRM and additional required"
echo "third-party software on this system. By using this software"
echo "you accept the GPL License version 2 available at"
echo "http://www.gnu.org/licenses/gpl-2.0.html"
echo

read -p "Do you accept the above license terms? [y/n] " -n 1 -r CONFIRM
if [[ $CONFIRM =~ ^[Yy]$ ]]; then
	if [ -f /etc/debian_version ]; then
		apt-get update && apt-get install -y make rsyslog
		apt remove --assume-yes --purge apparmor
	elif [ -f /etc/redhat-release ]; then
		yum -y install make
	elif [ -f /etc/SuSE-release ]; then
		zypper --non-interactive install make
	else
		echo "ERROR: You are trying to install openQRM on an unsupported Linux Distribution!"
		exit 1
	fi
	cd src/
	make && make install && make start
fi

echo
cd -
exit 0

