# this is the main openQRM Makefile
#.SILENT:
#
# openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
#
# All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
#
# This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
# The latest version of this license can be found here: src/doc/LICENSE.txt
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://openqrm-enterprise.com
#
# Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
#

export OPENQRM_SERVER_CONF=$(shell pwd)/etc/openqrm-server.conf
export OPENQRM_SOURCE_DIR=$(shell pwd)

all: buildrequirements configure compile
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make all -s && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make configure -s && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make -s && cd ..; done

initrd:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_initrd_template
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pack_default_initrd_template

install:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make install -s && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.php | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.css | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.conf | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.png | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.gif | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.jpg | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.spec | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *configuration.template | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *deb.control | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name openqrm-pxelinux | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-functions | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-hook | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-deployment | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name root-mount* | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-maintenance | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-ifup | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *commit.tmpl | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *.conf | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *deb.control | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name openqrm-pxelinux | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *.spec | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *-functions | xargs -r chmod +x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *-maintenance | xargs -r chmod +x; fi


start: check
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm
	@. $(OPENQRM_SERVER_CONF) && ln -sf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm /etc/init.d/openqrm
	@/etc/init.d/openqrm start

stop:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make stop' as root" && exit 1)
	@if [ -x /etc/init.d/openqrm ]; then /etc/init.d/openqrm stop; fi

uninstall: stop
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm ]; then echo "openQRM not installed"; exit 1; fi
	@if [ -x /etc/init.d/openqrm ]; then /etc/init.d/openqrm uninstall; fi
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm"`; do cd $$SRC_DIR && make uninstall -s && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make clean -s && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make realclean -s && cd ..; done

rpm: all
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make rpm' as root" && exit 1)
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_rpm_package

reinstall: 
	make uninstall -s
	make clean -s
	make all -s
	make install -s
	make start -s

debsource: all
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_deb_source

deb:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make deb' as root" && exit 1)
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pbuilder

packages:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make deb' as root" && exit 1)
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_packages

check:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_requirements

buildrequirements:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_build_requirements
	if [ -d ./thirdparty ]; then mkdir -p ../buildtmp; cp -aR ./thirdparty/* ../buildtmp/; fi

updatesrc:
	@if [ -d .svn ]; then svn update;fi

update: all
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin ]; then echo "openQRM is not installed - $(DESTINATION_DIR) $$OPENQRM_SERVER_BASE_DIR $(OPENQRM_SERVER_CONF)" && exit 1; fi
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && cp -f bin/openqrm-update $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm-update && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm-update
	@. $(OPENQRM_SERVER_CONF) && cp -f include/openqrm-build-functions $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/include/
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pre_update
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant make install
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_base_update
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_post_update
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_initrd_update


.PHONY: all configure compile install start uninstall clean realclean reinstall rpm deb debsource packages check initrd buildrequirements updatesrc update

