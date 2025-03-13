# This is the OpenStack all-in-one recipe
# It installs all OpenStack compontents on a single system
# The following install configuration can be set through the
# server capabilities field as space separated key=value pairs
# HOST_IP
# FLOATING_RANGE
# FIXED_RANGE
# FIXED_NETWORK_SIZE
# FLAT_INTERFACE
# ADMIN_PASSWORD
# MYSQL_PASSWORD
# RABBIT_PASSWORD
# SERVICE_PASSWORD
# SERVICE_TOKEN
# e.g.
# HOST_IP=192.168.88.135 FLOATING_RANGE=192.168.88.0/27 FIXED_RANGE=10.20.10.0/24 FLAT_INTERFACE=eth0 ADMIN_PASSWORD=openqrm MYSQL_PASSWORD=openqrm RABBIT_PASSWORD=openqrm SERVICE_PASSWORD=openqrm SERVICE_TOKEN=openqrm

class openstack-all-in-one {
	exec { "/usr/bin/wget -O /tmp/openstack-install http://openqrm/openqrm/boot-service/puppet/openstack-all-in-one && chmod +x /tmp/openstack-install && /tmp/openstack-install":
		creates => "/opt/stack",
	}
}


