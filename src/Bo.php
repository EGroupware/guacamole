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
		foreach($perms as &$entity_ids)
		{
			$entity_ids = self::getEntityId($entity_ids, false);
		}
		return $perms;
	}

	/**
	 * Update permissions of a connection
	 *
	 * @param integer $conection_id
	 * @param array $perms permission => array of account_id's
	 * @throws Api\Exception\WrongParameter
	 * @ToDo convert account_id eg. from AD or LDAP to the one used in egw_accounts and therefore Guacamole, in case they differ
	 */
	function updatePerms($conection_id, array $perms)
	{
		$existing = $this->readPerms($conection_id);

		foreach(self::$connection_perms as $permision)
		{
			if (($deleted = self::getEntityId(array_diff((array)$existing[$permision], (array)$perms[$permision]))))
			{
				$this->db->delete(self::PERMS_TABLE, [
					'connection_id' => $conection_id,
					'permission' => $permision,
					'entity_id' => $deleted,
				], __LINE__, __FILE__, self::APP);
			}
			if (($added = self::getEntityId(array_diff((array)$perms[$permision], (array)$existing[$permision]))))
			{
				foreach($added as $entity_id)
				{
					$this->db->insert(self::PERMS_TABLE, [
						'connection_id' => $conection_id,
						'permission' => $permision,
						'entity_id' => $entity_id,
					], false, __LINE__, __FILE__, self::APP);
				}
			}
		}
	}

	/**
	 * Convert account_id to entity_id
	 *
	 * Taken into account, that Guacamole unconditionally uses egw_accounts table, while EGroupware might use AD or LDAP directly!
	 *
	 * @param array $ids
	 * @param bool $account2entity_id
	 * @return void
	 */
	static protected function getEntityId(array $ids, bool $account2entity_id=true)
	{
		// no need to convert
		if (($GLOBALS['egw_info']['server']['account_repository'] ?? 'sql') === 'sql')
		{
			return $ids;
		}
		if ($account2entity_id)
		{
			$account_ids = [];
			foreach($GLOBALS['egw']->db->select(Api\Accounts\Sql::TABLE, 'account_id', [
				'account_lid' => array_map(Api\Accounts::class.'::id2name', $ids),
			], __LINE__, __FILE__) as $row)
			{
				$account_ids[] = (int)$row['account_id'];
			}
			return $account_ids;
		}
		$account_lids = [];
		foreach($GLOBALS['egw']->db->select(Api\Accounts\Sql::TABLE, 'account_lid', [
			'account_id' => $ids,
		], __LINE__, __FILE__) as $row)
		{
			$account_lids[] = $row['account_lid'];
		}
		return array_map([Api\Accounts::getInstance(), 'name2id'], $account_lids);
	}
}