<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\extensions\adapter\security\access;

use RuntimeException;

class Rules extends \lithium\core\Object {

	/**
	 * Configuration that will be automatically assigned to class properties.
	 *
	 * @var array
	 */
	protected $_autoConfig = array('rules');

	/**
	 * Rules are named closures that must either return `true` or `false`.
	 *
	 * @var array
	 */
	protected $_rules = array();

	/**
	 * Last error message
	 *
	 * @var array $_error
	 */
	protected $_error = array();

	/**
	 * Initializes default rules to use.
	 */
	protected function _init() {
		$this->_rules += array(
			'allowAll' => array(
				'allow' => function() {
					return true;
				}
			),
			'denyAll' => array(
				'allow' => function() {
					return false;
				}
			),
			'allowAnyUser' => array(
				'message' => 'You must be logged in.',
				'allow' => function($requester) {
					return $requester ? true : false;
				}
			),
			'allowIp' => array(
				'message' => 'Your IP is not allowed to access this area.',
				'allow' => function($requester, $request, $options) {
					$options += array('ip' => false);
					if (is_string($options['ip']) && strpos($options['ip'], '/') === 0) {
						return (boolean) preg_match($options['ip'], $request->env('REMOTE_ADDR'));
					}
					if (is_array($options['ip'])) {
						return in_array($request->env('REMOTE_ADDR'), $options['ip']);
					}
					return $request->env('REMOTE_ADDR') === $options['ip'];
				}
			)
		);
	}

	/**
	 * The check method
	 *
	 * @param mixed $requester The user data array that holds all necessary information about
	 *        the user requesting access or `false`.
	 * @param object $request The `Request` object.
	 * @param array $options Options array to pass to the rule closure.
	 * @return boolean `true` if access is ok, `false` otherwise.
	 */
	public function check($requester, $request, array $options = array()) {
		if (!isset($options['rules'])) {
			throw new RuntimeException("Missing `'rules'` option.");
		}

		$this->_error = array();
		$rules = (array) $options['rules'];

		$options = array();
		foreach ($rules as $name => $rule) {

			if (is_string($rule) && isset($this->_rules[$rule])) {
				$closure = $this->_rules[$rule];
			} elseif (is_array($rule)) {
				if (is_string($name) && isset($this->_rules[$name])) {
					$closure = $this->_rules[$name];
					$options = $rule;
				} elseif (isset($rule['allow'])) {
					$closure = $rule;
				}
			} elseif (is_callable($rule)) {
				$closure = $rule;
			} else {
				throw new RuntimeException("Invalid rule.");
			}

			$closure = !is_array($closure) ? array('allow' => $closure) : $closure;
			$closure += array('message' => 'You are not permitted to access this area.');

			if (call_user_func($closure['allow'], $requester, $request, $options) === false) {
				unset($closure['allow']);
				$this->_error = $closure;
				return false;
			}
		}
		return true;
	}

	/**
	 * Getter/setter for rules
	 *
	 * @param string $name The rule name to get/set. if `null` all rules are returned.
	 * @param function $rule A Closure which be called with the following parameter :
	 *        - first parameter : the requester (i.e. generally a user data array)
	 *        - second parameter : a `Request` instance
	 *        - third parameter : an options array
	 * @return mixed Either an array of rule closures, a single rule closure, or `null`.
	 */
	public function rules($name = null, $rule = null, $options = array()) {
		if (!$name) {
			return $this->_rules;
		}
		if ($rule) {
			$this->_rules[$name] = array('allow' => $rule) + $options;
			return;
		}
		return isset($this->_rules[$name]) ? $this->_rules[$name] : null;
	}

	/**
	 * Returns the last error array.
	 *
	 * @return array
	 */
	public function error() {
		return $this->_error;
	}
}

?>