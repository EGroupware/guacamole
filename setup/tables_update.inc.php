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

use EGroupware\Api;

/**
 * Dummy update to set index URL again from setup.inc.php
 *
 * @return string
 */
function guacamole_upgrade0_1()
{
	return '0.2';
}

/**
 * Add new 1.6.0 AUDIT right and update guacamole_system_permission view
 *
 * @return string
 */
function guacamole_upgrade0_2()
{
	try {
		/**
		 * @var Api\Db
		 */
		$db = $GLOBALS['egw_setup']->db;
		// update guacamole_system_permission view
		$db->query("CREATE OR REPLACE VIEW guacamole_system_permission AS
(SELECT ABS(acl_account) AS entity_id, CASE WHEN acl_location IN ('AUDIT','ADMINISTER') THEN acl_location ELSE CONCAT('CREATE_', acl_location) END AS permission
FROM egw_acl
WHERE acl_appname='guacamole' AND acl_location IN ('AUDIT','ADMINISTER','CONNECTION','CONNECTION_GROUP','SHARING_PROFILE'))
UNION
(SELECT members.acl_account AS entity_id, CASE WHEN egw_acl.acl_location IN ('AUDIT','ADMINISTER') THEN egw_acl.acl_location ELSE CONCAT('CREATE_', egw_acl.acl_location) END AS permission
FROM egw_acl
JOIN egw_acl members ON members.acl_appname='phpgw_group' AND CAST(members.acl_location AS SIGNED)=egw_acl.acl_account
WHERE egw_acl.acl_appname='guacamole' AND egw_acl.acl_location IN ('AUDIT','ADMINISTER','CONNECTION','CONNECTION_GROUP','SHARING_PROFILE') AND egw_acl.acl_account < 0);
");
		// update guacamole_user view to query timezone from (forced, user or default preferences, in that order)
		$db->query("CREATE OR REPLACE VIEW guacamole_user AS
SELECT account_id AS user_id,account_id AS entity_id,
    NULL AS password_hash, NULL AS password_salt, FROM_UNIXTIME(account_lastpwd_change) AS password_date,
    CASE WHEN account_status='A' AND (account_expires=-1 OR account_expires>UNIX_TIMESTAMP()) THEN 0 ELSE 1 END AS disabled,
    1 AS expired, /* password expired */
    NULL AS access_window_start, NULL AS access_window_end,
    NULL AS valid_from, NULL AS valid_until,
   (SELECT JSON_VALUE(preference_value,'$.tz')
    FROM egw_preferences
    WHERE preference_app='common' AND preference_owner IN (-1/*forced*/,egw_accounts.account_id,-2/*default*/) AND JSON_VALUE(preference_value,'$.tz') IS NOT NULL
    ORDER BY CASE preference_owner WHEN -1 THEN 3 WHEN egw_accounts.account_id THEN 2 ELSE 1 END DESC LIMIT 1) AS timezone,
    n_fn AS full_name, contact_email AS email_address, org_name AS organization, contact_role AS organizational_role
FROM egw_accounts
JOIN egw_addressbook USING(account_id)
WHERE account_type='u' AND account_lid != 'anonymous'", __LINE__, __FILE__);
	}
	catch (Api\Exception\Db $e) {
		// ignore
	}
	// give Admins group rights to use AND manage Guacamole connections
	$adminsgroup = $GLOBALS['egw_setup']->add_account('Admins', 'Admins', 'Group', false, false);
	foreach(['run', 'READ', 'UPDATE', 'DELETE', 'AUDIT', 'ADMINISTER', 'CONNECTION', 'CONNECTION_GROUP', 'SHARING_PROFILE'] as $perms)
	{
		$GLOBALS['egw_setup']->add_acl('guacamole', $perms, $adminsgroup);
	}
	return '1.6';
}