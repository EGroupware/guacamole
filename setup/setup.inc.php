<?php
/**
 * EGroupware - Guacamole - setup definitions
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @subpackage setup
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

use EGroupware\Guacamole;

$setup_info['guacamole']['name']      = 'guacamole';
$setup_info['guacamole']['version']   = '1.6';
$setup_info['guacamole']['app_order'] = 5;
$setup_info['guacamole']['enable']    = 1;
$setup_info['guacamole']['tables']    = array(
	'guacamole_connection_group',
	'guacamole_connection',
	'guacamole_sharing_profile',
	'guacamole_connection_parameter',
	'guacamole_sharing_profile_parameter',
	'guacamole_user_attribute',
	'guacamole_user_group_attribute',
	'guacamole_connection_attribute',
	'guacamole_connection_group_attribute',
	'guacamole_sharing_profile_attribute',
	'guacamole_connection_permission',
	'guacamole_connection_group_permission',
	'guacamole_sharing_profile_permission',
	'guacamole_connection_history',
	'guacamole_user_history',
	'guacamole_user_password_history',
);
$setup_info['guacamole']['views']    = array(
	'guacamole_entity',
	'guacamole_user',
	'guacamole_user_group',
	'guacamole_user_group_member',
	'guacamole_system_permission',
	'guacamole_user_group_permission',
	'guacamole_user_permission',
);
$setup_info['guacamole']['skip_create_tables'] = true;  // done in default_records to add constrains too
$setup_info['guacamole']['index']     = 'guacamole.'.Guacamole\Ui::class.'.index&ajax=true';

$setup_info['guacamole']['author'] =
$setup_info['guacamole']['maintainer'] = array(
	'name'  => 'Ralf Becker',
	'email' => 'rb@egroupware.org',
);
$setup_info['guacamole']['license']  = 'GPL';
$setup_info['guacamole']['description'] =
'Application to manage Apache Guacamole via EGroupware.';

// Hooks we implement
$setup_info['guacamole']['hooks']['sidebox_menu'] = Guacamole\Hooks::class.'::menu';
$setup_info['guacamole']['hooks']['admin'] = Guacamole\Hooks::class.'::menu';

/* Dependencies for this app to work */
$setup_info['guacamole']['depends'][] = array(
	 'appname' => 'api',
	 'versions' => Array('21.1')
);