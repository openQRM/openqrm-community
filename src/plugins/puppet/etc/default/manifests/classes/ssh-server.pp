
class ssh-server {
	case "$lsbdistid" {
		Debian: {
			package { ssh: ensure => installed }
			service { ssh:
				ensure    => running,
				subscribe => [Package[ssh]],
			}
		}
		Ubuntu: {
			package { ssh: ensure => installed }
			service { ssh:
				ensure    => running,
				subscribe => [Package[ssh]],
			}
		}
		CentOS: {
			package { openssh-server: ensure => installed }
			service { sshd:
				ensure    => running,
				subscribe => [Package[openssh-server]],
			}
		}
		Fedora: {
			package { openssh-server: ensure => installed }
			service { sshd:
				ensure    => running,
				subscribe => [Package[openssh-server]],
			}
		}




	}
}

