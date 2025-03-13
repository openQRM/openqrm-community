
class docker-container {

	case "$lsbdistid" {
		Debian: {
			package { "docker.io": ensure => installed }
			service { "docker.io":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["docker.io"],
			}
			exec { "reload-dockerui":
				command => "/usr/share/openqrm/plugins/docker/etc/init.d/docker restart",
				refreshonly => true,
			}

		}
		Ubuntu: {
			package { "docker.io": ensure => installed }
			service { "docker.io":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["docker.io"],
			}
			exec { "reload-dockerui":
				command => "/usr/share/openqrm/plugins/docker/etc/init.d/docker restart",
				refreshonly => true,
			}
		}
		CentOS: {
			package { "docker": ensure => installed }
			service { "docker":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["docker"],
			}
			exec { "reload-dockerui":
				command => "/usr/share/openqrm/plugins/docker/etc/init.d/docker restart",
				refreshonly => true,
			}
		}

		Fedora: {
			package { "docker": ensure => installed }
			service { "docker":
				ensure => running,
				hasstatus => true,
				hasrestart => true,
				require => Package["docker"],
			}
			exec { "reload-dockerui":
				command => "/usr/share/openqrm/plugins/docker/etc/init.d/docker restart",
				refreshonly => true,
			}
		}





	}
}

