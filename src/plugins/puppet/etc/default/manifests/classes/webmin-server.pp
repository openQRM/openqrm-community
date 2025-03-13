
class webmin-server {

	case "$lsbdistid" {
		Debian: {
			package { "webmin": ensure => installed }
			# We want to make sure that webmin is running.
			service { "webmin":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["webmin"],
			}
		}
		Ubuntu: {
			package { "webmin": ensure => installed }
			# We want to make sure that webmin is running.
			service { "webmin":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["webmin"],
			}
		}
		CentOS: {
			exec { "/usr/bin/wget -O /tmp/webmin.rpm http://downloads.sourceforge.net/webadmin/webmin-1.450-1.noarch.rpm && /bin/rpm --import http://www.webmin.com/jcameron-key.asc && /usr/bin/yum -y localinstall /tmp/webmin.rpm && /bin/rm -f /tmp/webmin.rpm":
				unless => "/bin/touch /etc/init.d/webmin && /etc/init.d/webmin status",
			}		
		}

		Fedora: {
			exec { "/usr/bin/wget -O /tmp/webmin.rpm http://downloads.sourceforge.net/webadmin/webmin-1.450-1.noarch.rpm && /bin/rpm --import http://www.webmin.com/jcameron-key.asc && /usr/bin/yum -y localinstall /tmp/webmin.rpm && /bin/rm -f /tmp/webmin.rpm":
				unless => "/bin/touch /etc/init.d/webmin && /etc/init.d/webmin status",
			}		
		}





	}
}

