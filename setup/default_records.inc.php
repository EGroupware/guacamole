<?php
/**
 * EGroupware - Guacamole - create default data
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @subpackage setup
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

// create Guacamole tables and views
foreach(preg_split('/;\n/', preg_replace(['|/\*.+\*/|Us', '/^--.*$/m', '/egroupware\./', "/\n+/"], ['', '', '', "\n"],
	file_get_contents(__DIR__.'/egroupware-account-view.sql'))) as $sql)
{
	$GLOBALS['egw_setup']->db->query($sql, __LINE__, __FILE__);
}

// create OAuth client for guacamole
$site_url = 'https://'.(!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' ?
	$_SERVER['HTTP_HOST'] : 'example.org').'/guacamole/';
$GLOBALS['egw_setup']->db->insert('egw_openid_clients', [
	'client_name' => 'Guacamole',
	'client_secret' => null,
	'client_redirect_uri' => $site_url,
	'client_created' => date('Y-m-d H:i:s'),
	'client_updated' => date('Y-m-d H:i:s'),
	'client_status' => 1,
	'app_name' => 'guacamole',
],['client_identifier' => 'guacamole'], __LINE__, __FILE__, 'openid');
if (($client_id = $GLOBALS['egw_setup']->db->get_last_insert_id('egw_openid_clients', 'client_id')))
{
	foreach([3] as $grant_id)	// 3 = Implicit grant
	{
		$GLOBALS['egw_setup']->db->insert('egw_openid_client_grants', [], [
			'client_id' => $client_id,
			'grant_id'  => $grant_id,
		], __LINE__, __FILE__, 'openid');
	}
}

// give Default group rights to use Guacamole
$defaultgroup = $GLOBALS['egw_setup']->add_account('Default', 'Default', 'Group', false, false);
$GLOBALS['egw_setup']->add_acl('guacamole', 'run', $defaultgroup);

// give Admins group rights to use AND manage Guacamole connections
$adminsgroup = $GLOBALS['egw_setup']->add_account('Admins', 'Admins', 'Group', false, false);
foreach(['run', 'READ', 'UPDATE', 'DELETE', 'ADMINISTER','CONNECTION','CONNECTION_GROUP','SHARING_PROFILE'] as $perms)
{
	$GLOBALS['egw_setup']->add_acl('guacamole', $perms, $adminsgroup);
}
