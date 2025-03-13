
class mysql-client {
	case "$lsbdistid" {
		Debian: {
			package { "mysql-client": ensure => installed }
		}
		Ubuntu: {
			package { "mysql-client": ensure => installed }
		}
		CentOS: {
			package { "mysql": ensure => installed }
		}
		Fedora: {
			package { "mysql": ensure => installed }
		}
	}
}
