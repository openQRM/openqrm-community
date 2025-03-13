# this is the openQRM linuxcoe-plugin Makefile
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

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:
	../../make-assistant openqrm_cache_or_download linuxcoe-sd etc/build.conf/linuxcoe-sd.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-data-centos etc/build.conf/linuxcoe-sd-data-centos.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-data-debian etc/build.conf/linuxcoe-sd-data-debian.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-data-ubuntu etc/build.conf/linuxcoe-sd-data-ubuntu.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-iso-ubuntu64 etc/build.conf/linuxcoe-sd-iso-ubuntu64.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-iso-debian64 etc/build.conf/linuxcoe-sd-iso-debian64.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-sd-iso-centos64 etc/build.conf/linuxcoe-sd-iso-centos64.conf
	../../make-assistant openqrm_cache_or_download linuxcoe-coe-bootimage etc/build.conf/linuxcoe-coe-bootimage.conf


install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/linuxcoe $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/init.d/linuxcoe
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/data
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/data/* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/data/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/osvend.d
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/osvend.d/* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/osvend.d/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/depots
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/depots/* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/depots/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/patches
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/patches/*.diff $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/patches/
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/linuxcoe* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/img/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/tpl
	. $(OPENQRM_SERVER_CONF) && cp web/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/tpl/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/class
	. $(OPENQRM_SERVER_CONF) && cp web/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/class/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/css
	. $(OPENQRM_SERVER_CONF) && cp web/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/css/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/lang
	. $(OPENQRM_SERVER_CONF) && cp web/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/lang/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/sbin
	. $(OPENQRM_SERVER_CONF) && cp sbin/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/sbin/openqrm*
	# copy linuxcoe packages, we cannot install here since DESTINATION_DIR won't fit
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd/source/linuxcoe-sd-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd/source/linuxcoe-sd-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-data-centos.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-centos/source/linuxcoe-sd-data-centos-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-centos/source/linuxcoe-sd-data-centos-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-data-debian.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-debian/source/linuxcoe-sd-data-debian-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-debian/source/linuxcoe-sd-data-debian-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-data-centos.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-ubuntu/source/linuxcoe-sd-data-ubuntu-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-data-ubuntu/source/linuxcoe-sd-data-ubuntu-$$OPENQRM_SOURCE_VERSION.tar.gz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-iso-centos64.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-centos64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-centos64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-iso-debian64.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-debian64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-debian64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-sd-iso-ubuntu64.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-ubuntu64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-sd-iso-ubuntu64/source/*.tar $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/lcoe-install/packages/; fi
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/linuxcoe-coe-bootimage.conf && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-coe-bootimage/source/*.tgz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/linuxcoe-coe-bootimage/source/*.tgz $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/etc/templates/; fi
	# menu icons
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/linuxcoe
	. $(OPENQRM_SERVER_CONF) && cp web/img/plugin.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/linuxcoe/

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe
	/bin/rm -f /etc/apache2/conf.d/LinuxCOE-SystemDesigner.conf
	/bin/rm -f /etc/httpd/conf.d/LinuxCOE-SystemDesigner.conf

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
