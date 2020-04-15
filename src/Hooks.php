<?php
/**
 * EGroupware - Guacamole - Hooks
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

namespace EGroupware\Guacamole;

use EGroupware\Api\Egw;

class Hooks
{
	/**
	 * hooks to build sidebox- and admin-menu
	 *
	 * @param string|array $args hook args
	 */
	public static function menu($args)
	{
		$appname = Bo::APP;
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>ts_admin_prefs_sidebox_hooks::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		/*if ($location == 'sidebox_menu')
		{
			$file = array(
				'Timesheet list' => Egw::link('/index.php',array(
					'menuaction' => 'timesheet.timesheet_ui.index',
					'ajax' => 'true')),
				array(
					'text' => lang('Add %1',lang(Link::get_registry($appname, 'entry'))),
					'no_lang' => true,
					'link' => "javascript:egw.open('','$appname','add')"
				),
			);
			$file['Placeholders'] = Egw::link('/index.php','menuaction=timesheet.timesheet_merge.show_replacements');
			display_sidebox($appname,$GLOBALS['egw_info']['apps'][$appname]['title'].' '.lang('Menu'),$file);
		}*/

		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$file = Array(
				//'Site Configuration' => Egw::link('/index.php','menuaction=admin.admin_config.index&appname=' . $appname,'&ajax=true'),
				'Connections' => Egw::link('/index.php','menuaction='.Bo::APP.'.'.Ui::class.'.index&ajax=true'),
				/*'Global Categories'  => Egw::link('/index.php',array(
					'menuaction' => 'admin.admin_categories.index',
					'appname'    => $appname,
					'global_cats'=> True,
					'ajax' => 'true',
				))*/
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}
}