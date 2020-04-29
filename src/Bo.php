<?php
/**
 * EGroupware - Guacamole - Business logic
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

namespace EGroupware\Guacamole;

use EGroupware\Api;

class Bo extends Api\Storage
{
	const APP = 'guacamole';
	const TABLE = 'guacamole_connection';
	const EXTRA_TABLE = 'guacamole_connection_parameter';
	const EXTRA_NAME = 'parameter_name';
	const EXTRA_VALUE = 'parameter_value';
	const EXTRA_ID = 'connection_id';
	const PERMS_TABLE = 'guacamole_connection_permission';
	const ENTITY_TABLE = 'guacamole_entity';

	static $connection_perms = ['READ', 'UPDATE', 'DELETE', 'ADMINISTER'];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(self::APP, self::TABLE, self::EXTRA_TABLE, '',
			self::EXTRA_NAME, self::EXTRA_VALUE, self::EXTRA_ID);

		// get existing parameters
		Api\Cache::unsetInstance(self::APP, 'parameters');
		$this->customfields = Api\Cache::getInstance(self::APP, 'parameters', function()
		{
			$cfs = [
				'hostname' => ['name' => 'hostname'],
				'port' => ['name' => 'port'],
				'username' => ['name' => 'username'],
				'password' => ['name' => 'password'],
				'color-depth' => ['name' => 'color-depth'],	// 8, 16, 24, 32
				'ignore-cert' => ['name' => 'ignore-cert'],	// true
				'enable-font-smoothing' => ['name' => 'enable-font-smoothing'],
				'resize-method' => ['name' => 'resize-method'],	// display-update
				'server-layout' => ['name' => 'server-layout'],
				'timezone' => ['name' => 'timezone'],
				'security' => ['name' => 'security'],	// tls
			];
			foreach($this->db->select(self::EXTRA_TABLE, 'DISTINCT '.self::EXTRA_NAME, false, __LINE__, __FILE__) as $row)
			{
				$cfs[$row[self::EXTRA_NAME]] = ['name' => $row[self::EXTRA_NAME]];
			}
			return $cfs;
		});
	}

	/**
	 * Read connection permissions
	 *
	 * @param integer $connection_id
	 * @return array permission => array of account_id's
	 */
	function readPerms($connection_id)
	{
		$perms = [];
		foreach($this->db->select(self::PERMS_TABLE, '*', ['connection_id' => $connection_id],
			__LINE__, __FILE__, false, '', self::APP, 0,
			'JOIN '.self::ENTITY_TABLE.' ON '.self::PERMS_TABLE.'.entity_id='.self::ENTITY_TABLE.'.entity_id') as $row)
		{
			$perms[$row['permission']][] = ($row['type'] === 'USER' ? 1 : -1)*$row['entity_id'];
		}
		return $perms;
	}

	/**
	 * Update permissions of a connection
	 *
	 * @param integer $conection_id
	 * @param array $perms permission => array of account_id's
	 * @throws Api\Exception\WrongParameter
	 */
	function updatePerms($conection_id, array $perms)
	{
		$existing = $this->readPerms($conection_id);

		foreach(self::$connection_perms as $permision)
		{
			if (($deleted = array_diff((array)$existing[$permision], (array)$perms[$permision])))
			{
				$this->db->delete(self::PERMS_TABLE, [
					'connection_id' => $conection_id,
					'permission' => array_map(function ($account_id) { return abs($account_id); }, $deleted),
				], __LINE__, __FILE__, self::APP);
			}
			if (($added = array_diff((array)$perms[$permision], (array)$existing[$permision])))
			{
				foreach($added as $account_id)
				{
					$this->db->insert(self::PERMS_TABLE, [
						'connection_id' => $conection_id,
						'entity_id' => abs($account_id),
						'permission' => $permision,
					], false, __LINE__, __FILE__, self::APP);
				}
			}
		}
	}
}