#!/bin/bash

set -x

function prepare_net_init() {
        INIT_SCRIPTS_TO_PREPARE="$@"
        for INIT_SCRIPT in $INIT_SCRIPTS_TO_PREPARE; do
                if [ -f /mnt/$INIT_SCRIPT ]; then
                        if ! grep -A2 "stop)" /mnt/$INIT_SCRIPT | grep -q "openqrm" ; then
                                echo "Preparing init script $INIT_SCRIPT"
                                sed -e "s/^[ \t]*stop)\(.*\)/stop) exit # added by openqrm \n\1/" /mnt/$INIT_SCRIPT > /mnt/${INIT_SCRIPT}.openqrm
                                mv -f /mnt/${INIT_SCRIPT}.openqrm /mnt/$INIT_SCRIPT
                                chmod +x /mnt/$INIT_SCRIPT
                        fi
                fi
        done
}

export NETWORK_SERVICES_TO_ADJUST="/etc/init.d/portmap /etc/init.d/netfs /etc/rc.d/init.d/portmap /etc/rc.d/init.d/netfs /etc/init.d/network /etc/rc.d/init.d/network /etc/init.d/networking /etc/rc.d/init.d/networking"

prepare_net_init $NETWORK_SERVICES_TO_ADJUST