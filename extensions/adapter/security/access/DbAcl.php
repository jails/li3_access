<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\extensions\adapter\security\access;

/**
 * The `DbAcl` access adapter.
 */
class DbAcl extends \lithium\core\Object {

	/**
	 * Lists `Controller`'s class dependencies. For details on extending or replacing a class,
	 * please refer to that class's API.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'permission' => 'li3_access\security\access\model\db_acl\Permission'
	);
	/**
	 * @see lithium\data\Model::_autoConfig()
	 * @var array
	 */
	protected $_autoConfig = array('classes');

	/**
	 * Check permission access
	 *
	 * @param string $requester The requester identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 * @return boolean Success (true if Aro has access to action in Aco, false otherwise)
	 */
	public function check($requester, $request, $perms) {
		$permission = $this->_classes['permission'];
		return $permission::check($requester, $request, $perms);
	}

	/**
	 * Get all permission access
	 *
	 * @param string $requester The requesting identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 */
	public function get($requester, $request) {
		$permission = $this->_classes['permission'];
		return $permission::get($requester, $request);
	}

	/**
	 * Allow access
	 *
	 * @param string $requester The requesting identifier (Aro).
	 * @param string $controlled The controlled identifier (Aco).
	 * @param string $perms Perms to allow (defaults to `*`)
	 * @param integer $value Access type (1 to allow, -1 to deny, 0 to inherit)
	 * @return boolean Success
	 */
	public function allow($requester, $controlled, $perms = "*", $value = 1) {
		$permission = $this->_classes['permission'];
		return $permission::allow($requester, $controlled, $perms, $value);
	}

	/**
	 * Deny access
	 *
	 * @param string $requester ARO The requesting object identifier.
	 * @param string $request ACO The controlled object identifier.
	 * @param string $perms Perms to deny (defaults to *)
	 * @return boolean Success
	 */
	public function deny($requester, $controlled, $perms = "*") {
		$permission = $this->_classes['permission'];
		return $permission::allow($requester, $controlled, $perms, -1);
	}

	/**
	 * Inherit access
	 *
	 * @param string $requester ARO The requesting object identifier.
	 * @param string $request ACO The controlled object identifier.
	 * @param string $perms Perms to inherit (defaults to *)
	 * @return boolean Success
	 */
	public function inherit($requester, $controlled, $perms = "*") {
		$permission = $this->_classes['permission'];
		return $permission::allow($requester, $controlled, $perms, 0);
	}

	/**
	 * Returns the last error array.
	 *
	 * @return array
	 */
	public function error() {
		$permission = $this->_classes['permission'];
		return $permission::error();
	}
}
