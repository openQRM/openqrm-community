# this is the openQRM sbin Makefile
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

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-exec $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-cmd-queue $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-rabbitmq-cmd-queue $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-monitord $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-postencode $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-crypt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm*

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
	