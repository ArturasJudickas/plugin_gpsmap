<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2009-2013 Andrew Aloia                                    |
 | Copyright (C) 2014 Wixiweb                                              |
 | Copyright (C) 2017-2018 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function gpsmap_upgrade_database() {
	global $config, $database_default, $old;

	include_once($config['library_path'] . '/database.php');

	gpsmap_setup_database();

	if ($old < '1.6'){
		db_execute('ALTER TABLE host CHANGE COLUMN latitude latitude DECIMAL(13,10) NOT NULL;');
		db_execute('ALTER TABLE host ALTER COLUMN latitude SET DEFAULT `0.0000000000`;');
		db_execute('ALTER TABLE host CHANGE COLUMN longitude longitude DECIMAL(13,10) NOT NULL;');
		db_execute('ALTER TABLE host ALTER COLUMN longitude SET DEFAULT `0.0000000000`;');
	}

	if ($old < '2.1') {
		if (!db_index_exists('gpsmap_templates', 'templateID')) {
			db_add_index('gpsmap_templates', 'unique', 'templateID', array('templateID'));
		}
	}

	include_once($config['base_path'] . '/plugins/gpsmap/setup.php');
}

function gpsmap_setup_database() {
	$v = plugin_gpsmap_version();

	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'latitude', 'type' => 'decimal(13,10)', 'NULL' => false, 'default' => '0', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'longitude', 'type' => 'decimal(13,10)', 'NULL' => false, 'default' => '0', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'GPScoverage', 'type' => 'varchar(3)', 'NULL' => false, 'default' => 'on', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'start', 'type' => 'int(3)', 'NULL' => false, 'default' => '0', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'stop', 'type' => 'int(3)', 'NULL' => false, 'default' => '360', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'groupnum', 'type' => 'int(3)', 'NULL' => false, 'default' => '0', 'after' => 'availability'));
	api_plugin_db_add_column('gpsmap', 'host', array('name' => 'rdistance', 'type' => 'decimal(10,6)', 'NULL' => false, 'default' => '0', 'after' => 'availability'));

	$data = array();
	$data['columns'][] = array('name' => 'templateID', 'type' => 'int(11)', 'NULL' => true);
	$data['columns'][] = array('name' => 'templateName', 'type' => 'varchar(100)', 'NULL' => true);
	$data['columns'][] = array('name' => 'upimage', 'type' => 'varchar(255)', 'NULL' => true);
	$data['columns'][] = array('name' => 'recoverimage', 'type' => 'varchar(255)', 'NULL' => true);
	$data['columns'][] = array('name' => 'downimage', 'type' => 'varchar(255)', 'NULL' => true);
	$data['columns'][] = array('name' => 'AP', 'type' => 'int(1)', 'NULL' => true);
	$data['type'] = 'MyISAM';
	$data['unique_keys'][] = array('name' => 'templateID' , 'columns' => 'templateID', 'unique' => true);
	$data['comment'] = 'Map icon template';
	api_plugin_db_table_create('gpsmap', 'gpsmap_templates', $data);

	db_execute('UPDATE plugin_config SET version = "' . $v['version'] . '" WHERE directory = "gpsmap"');
}

