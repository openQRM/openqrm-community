# this is the openQRM etc Makefile
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

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_initrd_templates

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc
	. $(OPENQRM_SERVER_CONF) && cp openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd
	. $(OPENQRM_SERVER_CONF) && cp httpd/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp templates/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres
	. $(OPENQRM_SERVER_CONF) && cp db/db2/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/db2/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_initrd_templates

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc

clean:

realclean:	clean

all:	configure compile

.PHONY: all configure compile install uninstall clean realclean


