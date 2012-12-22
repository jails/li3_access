<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\extensions\adapter\security\access;

class Simple extends \lithium\core\Object {

	/**
	 * The check method
	 *
	 * @param mixed $requester The user data array that holds all necessary information about
	 *        the user requesting access or `false`.
	 * @return boolean `true` if access is ok, `false` otherwise.
	 */
	public function check($requester) {
		return !!$requester;
	}
}

?>