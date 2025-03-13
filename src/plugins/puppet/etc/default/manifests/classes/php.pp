
class php {
	case "$lsbdistid" {
		Debian: {
			package { [ "php5", "php5-cli", "libapache2-mod-php5", "php5-mysql" ]: ensure => installed }
		}
		Ubuntu: {
			package { [ "php5", "php5-cli", "libapache2-mod-php5", "php5-mysql" ]: ensure => installed }
		}
		CentOS: {
			package { [ "php", "php-common", "mod_php", "php-cli", "php-mysql" ]: ensure => installed }
		}
		Fedora: {
			package { [ "php", "php-common", "php-cli", "php-mysql" ]: ensure => installed }
		}



	}
}

