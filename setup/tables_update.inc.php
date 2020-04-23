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
 * Adding timezone from user prefs
 *
 * @return string
 */
/*function guacamole_upgrade0_2()
{
	try {
		"SELECT *, json_value(preference_value, '$.tz') as timezone, json_value(preference_value, '$.lang') as lang, json_value(preference_value, '$.country') as country FROM `egw_preferences` WHERE `preference_app` LIKE 'common' ORDER BY `egw_preferences`.`preference_owner` ASC";
	}
	catch (Exception $e) {

	}
	return '0.3';
}*/