<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\security;

use lithium\core\ConfigException;

class Access extends \lithium\core\Adaptable {

	/**
	 * Stores configurations for various authentication adapters.
	 *
	 * @var object `Collection` of authentication configurations.
	 */
	protected static $_configurations = array();

	/**
	 * Libraries::locate() compatible path to adapters for this class.
	 *
	 * @see lithium\core\Libraries::locate()
	 * @var string Dot-delimited path.
	 */
	protected static $_adapters = 'adapter.security.access';

	/**
	 * Performs an access check.
	 *
	 * @param string $name The name of the adapter to check against.
	 * @param mixed $user The user data array or a user instance.
	 * @param action\Request $request A `Request` instace.
	 * @param array $options An array of options.
	 * @return boolean `true` if access is granted, `false otherwise`.
	 */
	public static function check($name, $user, $request, $options = array()) {
		if (($config = static::_config($name)) === null) {
			throw new ConfigException("Configuration `{$name}` has not been defined.");
		}
		$filter = function($self, $params) use ($name) {
			$user = $params['user'];
			$request = $params['request'];
			$options = $params['options'];
			return $self::adapter($name)->check($user, $request, $options);
		};
		$params = compact('user', 'request', 'options');
		return static::_filter(__FUNCTION__, $params, $filter, (array) $config['filters']);
	}

	/**
	 * Static calls are transfered to adapters.
	 *
	 * @param string $method Method name caught by `__call()`.
	 * @param array $params Arguments given to the above `$method` call.
	 * @return mixed
	 */

	public static function __callStatic($method, $params) {
		$name = array_shift($params);
		if (($config = static::_config($name)) === null) {
			throw new ConfigException("Configuration `{$name}` has not been defined.");
		}
		$filter = function($self, $params) use ($name, $method) {
			return call_user_func_array(array($self::adapter($name), $method), $params);
		};
		return static::_filter(__FUNCTION__, $params, $filter, (array) $config['filters']);
	}
}

?>