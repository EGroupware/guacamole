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
$setup_info['guacamole']['version']   = '0.2';
$setup_info['guacamole']['app_order'] = 5;
$setup_info['guacamole']['enable']    = 1;
$setup_info['guacamole']['tables']    = [];
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
	 'versions' => Array('19.1')
);
