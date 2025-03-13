
class osupdate {
	case "$lsbdistid" {
		Debian: {
			exec { "/usr/bin/apt-get -y update":
				refreshonly => true,
			}
		}
		Ubuntu: {
			exec { "/usr/bin/apt-get -y update":
				refreshonly => true,
			}
		}
		CentOS: {
			exec { "/usr/bin/yum -y update":
				refreshonly => true,
			}
		}
		Fedora: {
			exec { "/usr/bin/yum -y update":
				refreshonly => true,
			}
		}




	}
}


