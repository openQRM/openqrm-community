
drop table resource_info;
create table resource_info(
	resource_id INTEGER NOT NULL PRIMARY KEY,
	resource_localboot INTEGER,
	resource_kernel VARCHAR2(50),
	resource_kernelid INTEGER,
	resource_image VARCHAR2(50),
	resource_imageid INTEGER,
	resource_openqrmserver VARCHAR2(20),
	resource_basedir VARCHAR2(100),
	resource_applianceid INTEGER,
	resource_ip VARCHAR2(20),
	resource_subnet VARCHAR2(20),
	resource_broadcast VARCHAR2(20),
	resource_network VARCHAR2(20),
	resource_mac VARCHAR2(20),
	resource_nics INTEGER,
	resource_uptime INTEGER,
	resource_cpunumber INTEGER,
	resource_cpuspeed INTEGER,
	resource_cpumodel VARCHAR2(255),
	resource_memtotal INTEGER,
	resource_memused INTEGER,
	resource_swaptotal INTEGER,
	resource_swapused INTEGER,
	resource_hostname VARCHAR2(60),
	resource_vtype INTEGER,
	resource_vhostid INTEGER,
	resource_vname VARCHAR2(255),
	resource_vnc VARCHAR2(30),
	resource_load FLOAT,
	resource_execdport INTEGER,
	resource_senddelay INTEGER,
	resource_capabilities VARCHAR2(255),
	resource_lastgood VARCHAR2(10),
	resource_state VARCHAR2(20),
	resource_event VARCHAR2(20)
);



drop table kernel_info;
create table kernel_info(
	kernel_id INTEGER NOT NULL PRIMARY KEY,
	kernel_name VARCHAR2(255),
	kernel_version VARCHAR2(50),
	kernel_capabilities VARCHAR2(255),
	kernel_comment VARCHAR2(255)
);



drop table image_info;
create table image_info(
	image_id INTEGER NOT NULL PRIMARY KEY,
	image_name VARCHAR2(255),
	image_version VARCHAR2(30),
	image_type VARCHAR2(255),
	image_rootdevice VARCHAR2(255),
	image_rootfstype VARCHAR2(255),
	image_size INTEGER,
	image_storageid INTEGER,
	image_deployment_parameter VARCHAR2(1000),
	image_isshared INTEGER,
	image_isactive INTEGER,
	image_comment VARCHAR2(255),
	image_capabilities VARCHAR2(255)
);

drop table appliance_info;
create table appliance_info(
	appliance_id INTEGER NOT NULL PRIMARY KEY,
	appliance_name VARCHAR2(50),
	appliance_kernelid INTEGER,
	appliance_imageid INTEGER,
	appliance_starttime INTEGER,
	appliance_stoptime INTEGER,
	appliance_cpunumber INTEGER,
	appliance_cpuspeed INTEGER,
	appliance_cpumodel VARCHAR2(255),
	appliance_memtotal INTEGER,
	appliance_swaptotal INTEGER,
	appliance_nics INTEGER,
	appliance_capabilities VARCHAR2(1000),
	appliance_cluster INTEGER,
	appliance_ssi INTEGER,
	appliance_resources INTEGER,
	appliance_highavailable INTEGER,
	appliance_virtual INTEGER,
	appliance_virtualization VARCHAR2(20),
	appliance_virtualization_host INTEGER,
	appliance_state VARCHAR2(20),
	appliance_comment VARCHAR2(255),
	appliance_wizard VARCHAR2(255),
	appliance_event VARCHAR2(20)
);

drop table event_info;
create table event_info(
	event_id INTEGER NOT NULL PRIMARY KEY,
	event_name VARCHAR2(50),
	event_time VARCHAR2(50),
	event_priority INTEGER,
	event_source VARCHAR2(50),
	event_description VARCHAR2(255),
	event_comment VARCHAR2(100),
	event_capabilities VARCHAR2(255),
	event_status INTEGER,
	event_image_id INTEGER,
	event_resource_id INTEGER
);



drop table user_info;
create table user_info(
	user_id INTEGER NOT NULL PRIMARY KEY,
	user_name VARCHAR2(20),
	user_password VARCHAR2(20),
	user_gender VARCHAR2(1),
	user_first_name VARCHAR2(50),
	user_last_name VARCHAR2(50),
	user_department VARCHAR2(50),
	user_office VARCHAR2(50),
	user_role INTEGER,
	user_last_update_time VARCHAR2(50),
	user_description VARCHAR2(255),
	user_capabilities VARCHAR2(255),
	user_wizard_name VARCHAR2(255),
	user_wizard_step INTEGER,
	user_wizard_id INTEGER,
	user_state VARCHAR2(20),
	user_lang VARCHAR2(5)
);


drop table role_info;
create table role_info(
	role_id INTEGER NOT NULL PRIMARY KEY,
	role_name VARCHAR2(20)
);


drop table storage_info;
create table storage_info(
	storage_id INTEGER NOT NULL PRIMARY KEY,
	storage_name VARCHAR2(255),
	storage_resource_id INTEGER,
	storage_type INTEGER,
	storage_comment VARCHAR2(100),
	storage_capabilities VARCHAR2(255),
	storage_state VARCHAR2(20)
);


drop table resource_service;
create table resource_service(
	resource_id INTEGER NOT NULL PRIMARY KEY,
	service VARCHAR2(50)
);

drop table image_service;
create table image_service(
	image_id INTEGER NOT NULL PRIMARY KEY,
	service VARCHAR2(50)
);

drop table image_authentication_info;
create table image_authentication_info(
	ia_id INTEGER NOT NULL PRIMARY KEY,
	ia_image_id INTEGER,
	ia_resource_id INTEGER,
	ia_auth_type INTEGER
);

drop table auth_blocker_info;
create table auth_blocker_info(
	ab_id INTEGER NOT NULL PRIMARY KEY,
	ab_image_id INTEGER,
	ab_image_name VARCHAR2(255),
	ab_start_time VARCHAR2(20)
);

drop table deployment_info;
create table deployment_info(
	deployment_id INTEGER NOT NULL PRIMARY KEY,
	deployment_storagetype_id INTEGER,
	deployment_name VARCHAR2(50),
	deployment_type VARCHAR2(50),
	deployment_description VARCHAR2(50),
	deployment_storagetype VARCHAR2(50),
	deployment_storagedescription VARCHAR2(50),
	deployment_mapping VARCHAR2(255)
);

drop table virtualization_info;
create table virtualization_info(
	virtualization_id INTEGER NOT NULL PRIMARY KEY,
	virtualization_name VARCHAR2(50),
	virtualization_type VARCHAR2(50),
	virtualization_mapping VARCHAR2(255)
);

drop table datacenter_info;
create table datacenter_info(
	datacenter_id INTEGER NOT NULL PRIMARY KEY,
	datacenter_load_overall VARCHAR2(50),
	datacenter_load_server VARCHAR2(50),
	datacenter_load_storage VARCHAR2(50),
	datacenter_cpu_total VARCHAR2(50),
	datacenter_mem_total VARCHAR2(50),
	datacenter_mem_used VARCHAR2(50)
);

insert into kernel_info (kernel_id, kernel_name, kernel_version) values (0, 'openqrm', 'openqrm');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_isshared) values (0, 'openqrm', 'openqrm', 'ram', 'ram', 0);

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openqrm', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_kernel, resource_image, resource_openqrmserver, resource_ip) values ('0', '1', 'local', 'local', 'OPENQRM_SERVER_IP_ADDRESS', 'OPENQRM_SERVER_IP_ADDRESS');
insert into deployment_info (deployment_id, deployment_name, deployment_type, deployment_description, deployment_storagetype, deployment_storagedescription ) values ('1', 'ramdisk', 'ram', 'Ramdisk Deployment', 'none', 'none');
insert into virtualization_info (virtualization_id, virtualization_name, virtualization_type) values ('1', 'Physical System', 'physical');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '-', 'activated', 'en');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (1, 'anonymous', 'openqrm', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '-', 'activated', 'en');
insert into role_info (role_id, role_name) values (0, 'administrator');
insert into role_info (role_id, role_name) values (1, 'readonly');


quit
