# this is the main openQRM plugin Makefile
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

all:	configure compile
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make all; fi && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make configure; fi && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make; fi && cd ..; done

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make install; fi && cd ..; done

uninstall:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make uninstall; fi && cd ..; done
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make clean; fi && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && if [ ! -f deprecated ]; then make realclean; fi && cd ..; done

zips:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do zip -r `basename $$SRC_DIR`.zip $$SRC_DIR; done

.PHONY: all configure compile install uninstall clean realclean

