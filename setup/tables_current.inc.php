<?php
/**
 * EGroupware - Setup
 * https://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package guacamole
 * @subpackage setup
 */

$phpgw_baseline = array(
	'guacamole_connection' => array(
		'fd' => array(
			'connection_id' => array('type' => 'auto','nullable' => False),
			'connection_name' => array('type' => 'varchar','precision' => '128','nullable' => False),
			'parent_id' => array('type' => 'int','precision' => '4'),
			'protocol' => array('type' => 'varchar','precision' => '32','nullable' => False,'comment' => 'rdp, ssh, vnc, ...'),
			'proxy_port' => array('type' => 'int','precision' => '4'),
			'proxy_hostname' => array('type' => 'varchar','precision' => '512'),
			'proxy_encryption_method' => array('type' => 'varchar','precision' => '4','comment' => 'enum: NONE or SSL'),
			'max_connections' => array('type' => 'int','precision' => '4'),
			'max_connections_per_user' => array('type' => 'int','precision' => '4'),
			'connection_weight' => array('type' => 'int','precision' => '4'),
			'failover_only' => array('type' => 'bool','nullable' => False,'default' => '0')
		),
		'pk' => array('connection_id'),
		'fk' => array(),
		'ix' => array('parent_id'),
		'uc' => array(array('connection_name','parent_id'))
	),
	'guacamole_connection_parameter' => array(
		'fd' => array(
			'connection_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'parameter_name' => array('type' => 'varchar','precision' => '128','nullable' => False),
			'parameter_value' => array('type' => 'varchar','precision' => '4096','nullable' => False)
		),
		'pk' => array('connection_id','parameter_name'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'guacamole_connection_permission' => array(
		'fd' => array(
			'entity_id' => array('type' => 'int','precision' => 4,'nullable' => False),
			'connection_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'permission' => array('type' => 'varchar','precision' => '10','nullable' => False),
		),
		'pk' => array('entity_id','connection_id','permission'),
		'fk' => array(),
		'ix' => array('connection_id'),
		'uc' => array()
	),
);
