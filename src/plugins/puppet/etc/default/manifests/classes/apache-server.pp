
class apache-server {

	case "$lsbdistid" {
		Debian: {
			package { "apache2": ensure => installed }
			exec { "reload-apache2":
				command => "/etc/init.d/apache2 reload",
				refreshonly => true,
			}
			exec { "force-reload-apache2":
				command => "/etc/init.d/apache2 force-reload",
				refreshonly => true,
			}
			# We want to make sure that Apache2 is running.
			service { "apache2":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["apache2"],
			}
		}
		Ubuntu: {
			package { "apache2": ensure => installed }
			exec { "reload-apache2":
				command => "/etc/init.d/apache2 reload",
				refreshonly => true,
			}
			exec { "force-reload-apache2":
				command => "/etc/init.d/apache2 force-reload",
				refreshonly => true,
			}
			# We want to make sure that Apache2 is running.
			service { "apache2":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["apache2"],
			}
		}
		CentOS: {
			package { "httpd": ensure => installed }
			exec { "reload-httpd":
				command => "/etc/init.d/httpd reload",
				refreshonly => true,
			}
			exec { "force-reload-httpd":
				command => "/etc/init.d/httpd force-reload",
				refreshonly => true,
			}
			# We want to make sure that httpd is running.
			service { "httpd":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["httpd"],
			}
		}

		Fedora: {
			package { "httpd": ensure => installed }
			exec { "reload-httpd":
				command => "/etc/init.d/httpd reload",
				refreshonly => true,
			}
			exec { "force-reload-httpd":
				command => "/etc/init.d/httpd force-reload",
				refreshonly => true,
			}
			# We want to make sure that httpd is running.
			service { "httpd":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["httpd"],
			}
		}





	}
}

