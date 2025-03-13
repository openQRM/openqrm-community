
drop table resource_info;
create table resource_info(
	resource_id int8 NOT NULL PRIMARY KEY,
	resource_localboot int8,
	resource_kernel varchar(50),
	resource_kernelid int8,
	resource_image varchar(50),
	resource_imageid int8,
	resource_openqrmserver varchar(20),
	resource_basedir varchar(100),
	resource_applianceid int8,
	resource_ip varchar(20),
	resource_subnet varchar(20),
	resource_broadcast varchar(20),
	resource_network varchar(20),
	resource_mac varchar(20),
	resource_nics int8,
	resource_uptime int8,
	resource_cpunumber int8,
	resource_cpuspeed int8,
	resource_cpumodel varchar(255),
	resource_memtotal int8,
	resource_memused int8,
	resource_swaptotal int8,
	resource_swapused int8,
	resource_hostname varchar(60),
	resource_vtype int8,
	resource_vhostid int8,
	resource_vname varchar(255),
	resource_vnc varchar(30),
	resource_load decimal(4,2),
	resource_execdport int8,
	resource_senddelay int8,
	resource_capabilities varchar(255),
	resource_lastgood varchar(10),
	resource_state varchar(20),
	resource_event varchar(20)
);


drop table kernel_info;
create table kernel_info(
	kernel_id int8  NOT NULL PRIMARY KEY,
	kernel_name varchar(255),
	kernel_version varchar(50),
	kernel_capabilities varchar(255),
	kernel_comment varchar(255)
);


drop table image_info;
create table image_info(
	image_id int8  NOT NULL PRIMARY KEY,
	image_name varchar(255),
	image_version varchar(30),
	image_type varchar(255),
	image_rootdevice varchar(255),
	image_rootfstype varchar(255),
	image_size int8,
	image_storageid int8,
	image_deployment_parameter varchar(1000),
	image_isshared int8,
	image_isactive int8,
	image_comment varchar(255),
	image_capabilities varchar(255)
);


drop table appliance_info;
create table appliance_info(
	appliance_id int8 NOT NULL PRIMARY KEY,
	appliance_name varchar(50),
	appliance_kernelid int8,
	appliance_imageid int8,
	appliance_starttime int8,
	appliance_stoptime int8,
	appliance_cpunumber int8,
	appliance_cpuspeed int8,
	appliance_cpumodel varchar(255),
	appliance_memtotal int8,
	appliance_swaptotal int8,
	appliance_nics int8,
	appliance_capabilities varchar(1000),
	appliance_cluster int8,
	appliance_ssi int8,
	appliance_resources int8,
	appliance_highavailable int8,
	appliance_virtual int8,
	appliance_virtualization varchar(20),
	appliance_virtualization_host int8,
	appliance_state varchar(20),
	appliance_comment varchar(255),
	appliance_wizard varchar(255),
	appliance_event varchar(20)
);

drop table event_info;
create table event_info(
	event_id int8 NOT NULL PRIMARY KEY,
	event_name varchar(255),
	event_time varchar(50),
	event_priority int8,
	event_source varchar(255),
	event_description varchar(255),
	event_comment varchar(255),
	event_capabilities varchar(255),
	event_status int8,
	event_image_id int8,
	event_resource_id int8
);



drop table user_info;
create table user_info(
	user_id int8 NOT NULL PRIMARY KEY,
	user_name varchar(20),
	user_password varchar(20),
	user_gender varchar(1),
	user_first_name varchar(50),
	user_last_name varchar(50),
	user_department varchar(50),
	user_office varchar(50),
	user_role int8,
	user_last_update_time varchar(50),
	user_description varchar(255),
	user_capabilities varchar(255),
	user_wizard_name varchar(255),
	user_wizard_step int8,
	user_wizard_id int8,
	user_state varchar(20),
	user_lang varchar(5)
);


drop table role_info;
create table role_info(
	role_id int8 NOT NULL PRIMARY KEY,
	role_name varchar(20),
	role_comment varchar(255)
);


drop table storage_info;
create table storage_info(
	storage_id int8 NOT NULL PRIMARY KEY,
	storage_name varchar(255),
	storage_resource_id int8,
	storage_type int8,
	storage_comment varchar(255),
	storage_capabilities varchar(255),
	storage_state varchar(20)
);

drop table resource_service;
create table resource_service(
	resource_id int8 NOT NULL PRIMARY KEY,
	service varchar(50)
);

drop table image_service;
create table image_service(
	image_id int8 NOT NULL PRIMARY KEY,
	service varchar(50)
);

drop table image_authentication_info;
create table image_authentication_info(
	ia_id int8 NOT NULL PRIMARY KEY,
	ia_image_id int8,
	ia_resource_id int8,
	ia_auth_type int8
);

drop table auth_blocker_info;
create table auth_blocker_info(
	ab_id int8 NOT NULL PRIMARY KEY,
	ab_image_id int8,
	ab_image_name  varchar(255),
	ab_start_time  varchar(20)
);

drop table deployment_info;
create table deployment_info(
	deployment_id int8 NOT NULL PRIMARY KEY,
	deployment_storagetype_id int8,
	deployment_name varchar(50),
	deployment_type varchar(50),
	deployment_description varchar(50),
	deployment_storagetype varchar(50),
	deployment_storagedescription varchar(50),
	deployment_mapping varchar(255)
);

drop table virtualization_info;
create table virtualization_info(
	virtualization_id int8 NOT NULL PRIMARY KEY,
	virtualization_name varchar(50),
	virtualization_type varchar(50),
	virtualization_mapping varchar(255)
);

drop table datacenter_info;
create table datacenter_info(
	datacenter_id int8 NOT NULL PRIMARY KEY,
	datacenter_load_overall varchar(50),
	datacenter_load_server varchar(50),
	datacenter_load_storage varchar(50),
	datacenter_cpu_total varchar(50),
	datacenter_mem_total varchar(50),
	datacenter_mem_used varchar(50)
);

drop table lock_info;
create table lock_info(
	lock_id int8 NOT NULL PRIMARY KEY,
	lock_time varchar(50),
	lock_section varchar(50),
	lock_resource_id int8,
	lock_token varchar(50),
	lock_description varchar(255)
);


insert into kernel_info (kernel_id, kernel_name, kernel_version) values (0, 'openqrm', 'openqrm');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_isshared) values (0, 'openqrm', 'openqrm', 'ram', 'ram', 0);

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openQRM', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_kernel, resource_image, resource_openqrmserver, resource_ip, resource_vtype) values ('0', '1', 'local', 'local', 'OPENQRM_SERVER_IP_ADDRESS', 'OPENQRM_SERVER_IP_ADDRESS', '1');
insert into deployment_info (deployment_id, deployment_name, deployment_type, deployment_description, deployment_storagetype, deployment_storagedescription ) values ('1', 'ramdisk', 'ram', 'Ramdisk Deployment', 'none', 'none');
insert into virtualization_info (virtualization_id, virtualization_name, virtualization_type) values ('1', 'Physical System', 'physical');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated', 'en');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (1, 'anonymous', 'openqrm', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '', 'activated', 'en');
insert into role_info (role_id, role_name) values (0, 'administrator');
insert into role_info (role_id, role_name) values (1, 'readonly');

insert into appliance_info (appliance_id, appliance_name, appliance_kernelid, appliance_imageid, appliance_starttime, appliance_resources, appliance_virtualization, appliance_state, appliance_comment) values (1, 'openqrm', 0, 0, '10', 0, 1, 'active', 'openQRM server');

grant all on resource_info to :openqrmdbuser;
grant all on kernel_info to :openqrmdbuser;
grant all on image_info to :openqrmdbuser;
grant all on event_info to :openqrmdbuser;
grant all on user_info to :openqrmdbuser;
grant all on appliance_info to :openqrmdbuser;
grant all on role_info to :openqrmdbuser;
grant all on storage_info to :openqrmdbuser;
grant all on resource_service to :openqrmdbuser;
grant all on image_service to :openqrmdbuser;
grant all on deployment_info to :openqrmdbuser;
grant all on virtualization_info to :openqrmdbuser;
grant all on storage_info to :openqrmdbuser;
