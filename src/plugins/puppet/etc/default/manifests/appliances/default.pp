# to make puppetmaster happy to include the appliance dir

class appliance-default {
			exec { "echo":
				refreshonly => true,
			}
}