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
Name: OPENQRM_PACKAGE_NAME
Summary: OPENQRM_PACKAGE_NAME
Version: OPENQRM_PACKAGE_VERSION
Release: OPENQRM_PACKAGE_DISTRIBUTION
License: OPENQRM_PACKAGE_LICENSE
Group: Networking/Admin
AutoReqProv: no
Source: OPENQRM_PACKAGE_NAME-OPENQRM_PACKAGE_VERSION.tgz
Prefix: /
BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : OPENQRM_PACKAGE_DEPENDENCIES
BuildRequires: OPENQRM_SERVER_BUILD_REQUIREMENTS
Conflicts: OPENQRM_PACKAGE_CONFLICTS
%description
openQRM is the next generation data-center management platform.

%files
%defattr(-,root,root)
/usr/share/openqrm/*

%prep
%setup

%build
make

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/usr/share
make install DESTINATION_DIR=$RPM_BUILD_ROOT
OPENQRM_BUILD_POSTINSTALL

%pre
if [ -x "OPENQRM_PACKAGE_PREINSTALL_SCRIPT" ]; then OPENQRM_PACKAGE_PREINSTALL; fi

%post
OPENQRM_PACKAGE_POSTINSTALL

%preun
OPENQRM_PACKAGE_PREREMOVE

%postun
if [ ! -f "/usr/share/openqrm/package-update.state" ]; then rm -rf /usr/share/openqrm; rm -f /etc/init.d/openqrm; fi
if [ -f "/usr/share/openqrm/package-update.state" ]; then rm -f /usr/share/openqrm/package-update.state; /etc/init.d/openqrm start; fi

%clean
rm -rf $RPM_BUILD_ROOT
make clean
