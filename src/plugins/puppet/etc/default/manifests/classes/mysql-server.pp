
class mysql-server {

	case "$lsbdistid" {
		Debian: {
			package { "mysql-server": ensure => installed }
			package { "mysql-common": ensure => installed }
			package { "mysql-client": ensure => installed }
			service { mysql:
				ensure    => running,
				subscribe => [Package[mysql-server]],
			}
		}
		Ubuntu: {
			package { "mysql-server": ensure => installed }
			package { "mysql-common": ensure => installed }
			package { "mysql-client": ensure => installed }
			service { mysql:
				ensure    => running,
				subscribe => [Package[mysql-server]],
			}
		}
		CentOS: {
			package { "mysql-server": ensure => installed }
			package { "mysql": ensure => installed }
			service { mysqld:
				ensure    => running,
				subscribe => [Package[mysql-server]],
			}
		}
		Fedora: {
			package { "mysql-server": ensure => installed }
			package { "mysql": ensure => installed }
			service { mysqld:
				ensure    => running,
				subscribe => [Package[mysql-server]],
			}
		}

	}
}