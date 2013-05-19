<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\security\access\model\db_acl;

use lithium\util\Set;
use RuntimeException;

class Permission extends \lithium\data\Model {

	/**
	 * @see lithium\data\Model::_autoConfig()
	 * @var array
	 */
	protected $_autoConfig = [
		'meta',
		'schema',
		'classes',
		'query',
		'finders',
		'initializers',
		'init',
		'forbid'
	];

	/**
	 * Class dependencies. For details on extending or replacing a class,
	 * please refer to that class's API.
	 *
	 * @var array
	 */
	protected $_classes = [
		'aro' => 'li3_access\security\access\model\db_acl\Aro',
		'aco' => 'li3_access\security\access\model\db_acl\Aco'
	];

	/**
	 * Model belongsTo relations.
	 *
	 * @var array
	 */
	public $belongsTo = ['Aro', 'Aco'];

	/**
	 * Forbid error message
	 * @var string
	 */
	protected $_forbid = 'You are not permitted to access this area.';

	/**
	 * @var array $_error Last error message
	 */
	protected $_error = [];

	/**
	 * Get the acl array between an Aro and an Aco
	 *
	 * @param string $requester The requesting identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 * @return array The permissions
	 */
	public static function acl($requester, $controlled) {
		$self = static::_object();
		$aro = $self->_classes['aro'];
		$aco = $self->_classes['aco'];

		if (!(($aroNode = $aro::node($requester)) && ($acoNode = $aco::node($controlled)))) {
			return false;
		}

		$acl = static::find('first', [
				'conditions' => [
					key(static::relations('Aro')->key()) => $aroNode[0]['id'],
					key(static::relations('Aco')->key()) => $acoNode[0]['id']
				],
				'return' => 'array'
		]);
		if (isset($acl['privileges'])) {
			$acl['privileges'] = json_decode($acl['privileges'], true);
		}

		return [
			'aro' => $aroNode[0]['id'],
			'aco' => $acoNode[0]['id'],
			'acl' => isset($acl) ? $acl: []
		];
	}

	/**
	 * Checks permission access
	 *
	 * @param string $requester The requester identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 * @return boolean Success (true if Aro has access to action in Aco, false otherwise)
	 */
	public static function check($requester, $controlled, $privileges) {
		$self = static::_object();
		$aro = $self->_classes['aro'];
		$aco = $self->_classes['aco'];

		if (!(($aroNodes = $aro::node($requester)) && ($acoNodes = $aco::node($controlled)))) {
			return false;
		}

		$inherited = [];
		$required = (array) $privileges;
		$count = count($required);
		$aro_id = key(static::relations('Aro')->key());
		$aco_id = key(static::relations('Aco')->key());
		$ids = Set::extract($acoNodes, '/'.$aco::meta('key'));
		$left = $aco::actsAs('Tree', true, 'left');

		foreach ($aroNodes as $node) {
			$id = $node[$aro::meta('key')];
			if ($datas = static::_permQuery($id, $ids, $aro_id, $aco_id, $left)) {
				foreach ($datas as $data) {
					if (!$privileges = json_decode($data['privileges'], true)) {
						break;
					}
					foreach ($required as $key) {
						if (isset($privileges[$key])) {
							if(!$privileges[$key]) {
								$self->_error = ['message' => $self->_forbid];
								return false;
							} else {
								$inherited[$key] = 1;
							}
						}
					}
					if (count($inherited) === $count) {
						$self->_error = [];
						return true;
					}
				}
			}
		}
		$self->_error = ['message' => $self->_forbid];
		return false;
	}

	/**
	 * Get all permission access
	 *
	 * @param string $requester The requesting identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 */
	public static function get($requester, $controlled) {
		$self = static::_object();
		$aro = $self->_classes['aro'];
		$aco = $self->_classes['aco'];

		if (!(($aroNodes = $aro::node($requester)) && ($acoNodes = $aco::node($controlled)))) {
			return false;
		}

		$privileges = [];
		$aro_id = key(static::relations('Aro')->key());
		$aco_id = key(static::relations('Aco')->key());
		$left = $aco::actsAs('Tree', true, 'left');
		$ids = Set::extract($acoNodes, '/'.$aco::meta('key'));

		foreach($aroNodes as $node) {
			$id = $node[$aro::meta('key')];
			if ($datas = static::_permQuery($id, $ids, $aro_id, $aco_id, $left)) {
				foreach ($datas as $data) {
					$privileges = $privileges + (array) json_decode($data['privileges'], true);
				}
			}
		}
		return $privileges;
	}

	/**
	 * Load permissions query
	 *
	 * @param mixed $id The Aro id
	 * @param array $ids The Aco ids
	 * @param string $aro_id The name of the Aro foreign key
	 * @param string $aco_id The name of the Aco foreign key
	 * @param string $left The name of the left field name (Tree behavior config)
	 * @return array Loaded permissions
	 */
	protected static function _permQuery($id, $ids, $aro_id, $aco_id, $left) {
		return static::find('all', [
			'alias' => 'Permission',
			'fields' => 'Permission',
			'conditions' => [
				'Permission.' . $aro_id => $id,
				'Permission.' . $aco_id => $ids
			],
			'order' => "Aco.{$left} DESC",
			'with' => ['Aco' => ['alias' => 'Aco']],
			'return' => 'array'
		]);
	}

	/**
	 * Allow access
	 *
	 * @param string $requester The requesting identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 * @param string $privileges Privileges to allow
	 * @param integer $value Access type (1 to allow, 0 to deny, null to inherit)
	 * @return boolean Success
	 */
	public static function allow($requester, $controlled, $privileges, $value = 1) {
		if (!$acl = static::acl($requester, $controlled)) {
			throw new RuntimeException("Invalid acl node.");
		}

		$datas = [];
		$privileges = (array) $privileges;
		$privileges = array_fill_keys($privileges, $value);

		$datas[key(static::relations('Aro')->key())] = $acl['aro'];
		$datas[key(static::relations('Aco')->key())] = $acl['aco'];

		$options = [];
		if ($acl['acl']) {
			$options = ['exists' => true];
			$datas += $acl['acl'];
			$privileges += $datas['privileges'];
		}
		$privileges = array_filter($privileges, function($val) {return $val !== null;});
		$datas['privileges'] = json_encode($privileges,  JSON_FORCE_OBJECT);
		$entity = static::create([], $options);
		$entity->set($datas);
		return $entity->save();
	}

	/**
	 * Deny access
	 *
	 * @param string $requester ARO The requesting object identifier.
	 * @param string $request ACO The controlled object identifier.
	 * @param string $privileges Privileges to deny
	 * @return boolean Success
	 */
	public static function deny($requester, $request, $privileges) {
		return static::allow($requester, $request, $privileges, 0);
	}

	/**
	 * Inherit access
	 *
	 * @param string $requester ARO The requesting object identifier.
	 * @param string $request ACO The controlled object identifier.
	 * @param string $privileges Privileges to inherit
	 * @return boolean Success
	 */
	public static function inherit($requester, $request, $privileges) {
		return static::allow($requester, $request, $privileges, null);
	}

	/**
	 * Returns the last error array or an empty array if no error.
	 *
	 * @return array
	 */
	public static function error() {
		return $this->_error;
	}
}
