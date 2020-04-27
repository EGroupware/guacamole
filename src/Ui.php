<?php
/**
 * EGroupware - Guacamole - User interface
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

namespace EGroupware\Guacamole;

use EGroupware\Api;

class Ui
{
	/**
	 * Methods callable via menuaction GET parameter
	 *
	 * @var array
	 */
	public $public_functions = [
		'index' => true,
		'connections' => true,
		'edit'  => true,
	];

	/**
	 * Connection protocols
	 *
	 * @var array
	 */
	protected static $protocols = [
		'rdp' => 'RDP',
		'vnc' => 'VNC',
		'ssh' => 'SSH',
		'kubernetes' => 'Kubernetes',
		'telnet' => 'Telnet',
	];

	protected static $color_depth = [
		'16' => 'High Color (16 bit)',
		'24' => 'True Color (24 bit)',
		'32' => 'Highest Color (32 bit)',
		'8'  => '256 Colors (8 bit)',
	];

	protected static $resize_method = [
		'display-update' => 'display update (RDP 8.1+)',
		'reconnect' => 'reconnect',
	];

	protected static $server_layout = [
		'de-de-qwertz' => 'German (Qwertz)',
		'en-us-qwerty' => 'English (US) (Qwerty)',
		'en-gb-qwerty' => 'English (GB) (Qwerty)',
		'de-ch-quertz' => 'Swiss German (Qwertz)',
		'fr-ch-qwertz' => 'Swiss French (Qwertz)',
		'fr-fr-azerty' => 'French (Azerty)',
		'it-it-qwerty' => 'Italian (Qwerty)',
		'es-es-qwerty' => 'Spanish (Qwerty)',
		'da-dk-qwerty' => 'Dansk (Qwerty)',
		'sv-se-qwerty' => 'Swedish (Qwerty)',
		'tr-tr-qwerty' => 'Turkish (Qwerty)',
		'pt-br-qwerty' => 'Portuguese (BR) (Qwerty)',
		'ja-jp-qwerty' => 'Japanese (Qwerty)',
		'failsafe'     => 'Unicode',
	];

	/**
	 * Instance of our business object
	 *
	 * @var Bo
	 */
	protected $bo;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// check if we need and have admin rights
		if ($_GET['menuaction'] !== Bo::APP.'.'.self::class.'.index' &&
			empty($GLOBALS['egw_info']['user']['apps']['admin']))
		{
			throw new NoPermission('Admin rights required!');
		}
		$this->bo = new Bo();
	}

	/**
	 * Edit a host
	 *
	 * @param array $content =null
	 */
	public function edit(array $content=null)
	{
		if (!is_array($content))
		{
			if (!empty($_GET['connection_id']))
			{
				if (!($content = $this->bo->read(['connection_id' => $_GET['connection_id']])))
				{
					Api\Framework::window_close(lang('Connection not found!'));
				}
				$content['permissions'] = $this->bo->readPerms($content['connection_id']);
			}
			else
			{
				$content = $this->bo->init([
					'#server-layout' => key(array_filter(self::$server_layout, function ($key)
					{
						$ret = substr($key, 0, 5) === strtolower(substr(
							str_replace('_', '-', $GLOBALS['egw_info']['user']['preferences']['common']['lang']).
							'-'.$GLOBALS['egw_info']['user']['preferences']['common']['country'], 0, 5));
						return $ret;
					}, ARRAY_FILTER_USE_KEY)),
					'#timezone' => $GLOBALS['egw_info']['user']['preferences']['common']['tz'],
					'#color-depth' => '16',
					'#enable-font-smoothing' => true,
					'#ignore-cert' => true,
				]);
			}
		}
		else
		{
			$button = key($content['button']);
			unset($content['button']);
			switch($button)
			{
				case 'save':
				case 'apply':
					if (!$this->bo->save($content))
					{
						$content['connection_id'] = $this->bo->data['connection_id'];
						$this->bo->updatePerms($content['connection_id'], $content['permissions']);

						Api\Framework::refresh_opener(lang('Connection saved.'),
							Bo::APP, $this->bo->data['connection_id'],
							empty($content['connection_id']) ? 'add' : 'edit');

						$content = array_merge($content, $this->bo->data);
					}
					else
					{
						Api\Framework::message(lang('Error storing connection!'));
						unset($button);
					}
					if ($button === 'save')
					{
						Api\Framework::window_close();	// does NOT return
					}
					Api\Framework::message(lang('Connection saved.'));
					break;

				case 'delete':
					if (!$this->bo->delete(['connection_id' => $content['connection_id']]))
					{
						Api\Framework::message(lang('Error deleting connection!'));
					}
					else
					{
						Api\Framework::refresh_opener(lang('Connection deleted.'),
							Bo::APP, $content['connection_id'], 'delete');

						Api\Framework::window_close();	// does NOT return
					}
			}
		}
		/* not (yet) used
		$content['link_to'] = [
			'to_id'  => $content['connection_id'],
			'to_app' => Bo::APP,
		];*/
		$readonlys = [
			'button[delete]' => !$content['connection_id'],
		];
		$tmpl = new Api\Etemplate(Bo::APP.'.edit');
		$tmpl->exec(Bo::APP.'.'.self::class.'.edit', $content, [
			'protocol' => self::$protocols,
			'#server-layout' => self::$server_layout,
			'#color-depth' => self::$color_depth,
			'#resize-method' => self::$resize_method,
		], $readonlys, $content, 2);
	}

	/**
	 * Fetch rows to display
	 *
	 * @param array $query
	 * @param array& $rows =null
	 * @param array& $readonlys =null
	 */
	public function get_rows($query, array &$rows=null, array &$readonlys=null)
	{
		return $this->bo->get_rows($query, $rows, $readonlys);
	}

	/**
	 * List connections
	 *
	 * @param array $content =null
	 */
	public function connections(array $content=null)
	{
		if (!is_array($content) || empty($content['nm']))
		{
			$content = [
				'nm' => [
					'get_rows'       =>	Bo::APP.'.'.self::class.'.get_rows',
					'no_filter'      => true,	// disable the diverse filters we not (yet) use
					'no_filter2'     => true,
					'no_cat'         => true,
					'order'          =>	'connection_id',// IO name of the column to sort after (optional for the sortheaders)
					'sort'           =>	'DESC',// IO direction of the sort: 'ASC' or 'DESC'
					'row_id'         => 'connection_id',
					'actions'        => $this->get_actions(),
					'placeholder_actions' => array('add')
				]
			];
		}
		elseif(!empty($content['nm']['action']))
		{
			try {
				Api\Framework::message($this->action($content['nm']['action'],
					$content['nm']['selected'], $content['nm']['select_all']));
			}
			catch (\Exception $ex) {
				Api\Framework::message($ex->getMessage(), 'error');
			}
		}
		Api\Translation::add_app(Bo::APP);
		$tmpl = new Api\Etemplate(Bo::APP.'.connections');
		$tmpl->exec(Bo::APP.'.'.self::class.'.connections', $content, [
			'protocol' => self::$protocols,
		], [], ['nm' => $content['nm']]);
	}

	/**
	 * Return actions for cup list
	 *
	 * @param array $cont values for keys license_(nation|year|cat)
	 * @return array
	 */
	protected function get_actions()
	{
		return [
			'edit' => [
				'caption' => 'Edit',
				'default' => true,
				'allowOnMultiple' => false,
				'url' => 'menuaction='.Bo::APP.'.'.self::class.'.edit&connection_id=$id',
				'popup' => '640x480',
				'group' => $group=0,
			],
			'add' => [
				'caption' => 'Add',
				'url' => 'menuaction='.Bo::APP.'.'.self::class.'.edit',
				'popup' => '640x320',
				'group' => $group,
			],
			'delete' => [
				'caption' => 'Delete',
				'confirm' => 'Delete this connection(s)',
				'group' => $group=5,
			],
		];
	}

	/**
	 * Execute ation on list
	 *
	 * @param string $action
	 * @param array|int $selected
	 * @param boolean $select_all
	 * @returns string with success message
	 * @throws Api\Exception\AssertionFailed
	 */
	protected function action($action, $selected, $select_all)
	{
		switch($action)
		{
			case 'delete':
				if (!($num = $this->bo->delete($select_all ? [] : ['connection_id' => $selected])))
				{
					Api\Framework::message(lang('Error deleting connection!'));
				}
				return lang('%1 connection(s) deleted.', $num);

			default:
				throw new Api\Exception\AssertionFailed("Unknown action '$action'!");
		}
	}

	/**
	 * Display Guacamole in an iframe
	 *
	 * @throws Api\Exception\AssertionFailed
	 */
	public function index()
	{
		$tmpl = new Api\Etemplate('guacamole.index');
		// check if we have a url to load (only allow different fragments, not arbitrary url!)
		if (!empty($_GET['load']) && preg_match( '|^/guacamole/(#.*)?$|', $_GET['load']))
		{
			$tmpl->setElementAttribute('guacamole', 'src', $_GET['load']);
		}
		$tmpl->exec(Bo::APP.'.'.self::class.'.index', []);
	}
}