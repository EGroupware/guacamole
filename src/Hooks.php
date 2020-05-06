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
		$file = [];
		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$file = [
				'Active Sessions' => '/guacamole/#/settings/sessions',
				'History' => '/guacamole/#/settings/mysql/history',
				'Connections' => Egw::link('/index.php', 'menuaction=' . Bo::APP . '.' . Ui::class . '.connections&ajax=true', 'admin'),
			];
		}
		if ($location == 'admin')
		{
			foreach($file as $label => &$link)
			{
				if (strpos($link, '/guacamole/') === 0)
				{
					$link = Egw::link('/index.php', [
						'menuaction' => Bo::APP.'.'.Ui::class.'.index',
						'load' => $link,
						'ajax' => 'true',
					], 'guacamole');
				}
			}
			display_section($appname, $file);
		}
		else // sidebox, add regular user stuff to same menu (to have it always expanded)
		{
			$file = [
				'Home' => '/guacamole/',
			]+$file;

			foreach($file as $label => &$link)
			{
				if (strpos($link, '/guacamole/') === 0)
				{
					$link = "javascript:app.guacamole.load('$link')";
				}
			}
			display_sidebox($appname,
				($GLOBALS['egw_info']['user']['apps']['admin'] ? lang('Admin') : lang('Guacamole').' '.lang('Menu')), $file);

			display_sidebox($appname, lang('Help'), [
				['text' => 'Using Guacamole', 'link' => 'https://guacamole.apache.org/doc/gug/using-guacamole.html', 'target' => '_blank'],
				['text' => 'Administrating Guacamole', 'link' => 'https://guacamole.apache.org/doc/gug/administration.html', 'target' => '_blank'],
				['text' => 'Frequently Asked Questions', 'link' => 'https://guacamole.apache.org/faq/', 'target' => '_blank'],
				['text' => 'Forum', 'link' => 'https://help.egroupware.org/c/deutsch/guacamole', 'target' => '_blank'],
			]);
		}
	}
}