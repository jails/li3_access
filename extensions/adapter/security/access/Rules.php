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
	protected $_autoConfig = ['rules', 'defaults', 'allowAny', 'user'];

	/**
	 * Rules are named closures that must either return `true` or `false`.
	 *
	 * @var array
	 */
	protected $_rules = [];

	/**
	 * Lists a subset of rules defined in `$_rules` which should be checked by default on every
	 * call to `check()` (unless overridden by passed options).
	 *
	 * @var array
	 */
	protected $_defaults = [];

	/**
	 * Default access checks.
	 * If set to `true`, any_ access rule passes will return successful
	 *
	 * @var array
	 */
	protected $_allowAny = false;

	/**
	 * A closure used for retrieving the default current user if set.
	 *
	 * @var Closure
	 */
	protected $_user = null;

	/**
	 * Last error message
	 *
	 * @var array $_error
	 */
	protected $_error = [];

	/**
	 * Initializes default rules to use.
	 */
	protected function _init() {
		parent::_init();
		$this->_rules += [
			'allowAll' => [
				'rule' => function() {
					return true;
				}
			],
			'denyAll' => [
				'rule' => function() {
					return false;
				}
			],
			'allowAnyUser' => [
				'message' => 'You must be logged in.',
				'rule' => function($user) {
					return $user ? true : false;
				}
			],
			'allowIp' => [
				'message' => 'Your IP is not allowed to access this area.',
				'rule' => function($user, $request, $options) {
					$options += ['ip' => false];
					if (is_string($options['ip']) && strpos($options['ip'], '/') === 0) {
						return (boolean) preg_match($options['ip'], $request->env('REMOTE_ADDR'));
					}
					if (is_array($options['ip'])) {
						return in_array($request->env('REMOTE_ADDR'), $options['ip']);
					}
					return $request->env('REMOTE_ADDR') === $options['ip'];
				}
			]
		];
	}

	/**
	 * The check method
	 *
	 * @param mixed $user The user data array that holds all necessary information about
	 *        the user requesting access. If set to `null`, the default `Rules::_user()`
	 *        will be used.
	 * @param object $request The requested object.
	 * @param array $options Options array to pass to the rule closure.
	 * @return boolean `true` if access is ok, `false` otherwise.
	 */
	public function check($user, $request, array $options = []) {
		$defaults = ['rules' => $this->_defaults, 'allowAny' => $this->_allowAny];
		$options += $defaults;

		if (empty($options['rules'])) {
			throw new RuntimeException("Missing `'rules'` option.");
		}

		$rules = (array) $options['rules'];
		$this->_error = [];
		$params = array_diff_key($options, $defaults);

		if (!isset($user) && is_callable($this->_user)) {
			$user = $this->_user->__invoke();
		}

		foreach ($rules as $name => $rule) {

			$result = $this->_check($user, $request, $name, $rule, $params);

			if ($result === false && !$options['allowAny']) {
				return false;
			} elseif ($options['allowAny']) {
				return true;
			}
		}
		return true;
	}

	/**
	 * Helper for `Rules::check()`.
	 */
	protected function _check($user, $request, $name, $rule, $params) {
		$message = 'You are not permitted to access this area.';

		if (is_string($rule) && isset($this->_rules[$rule])) {
			$name = $rule;
			$closure = $this->_rules[$rule];
		} elseif (is_array($rule)) {
			if (is_string($name) && isset($this->_rules[$name])) {
				$params += $rule;
				$closure = $this->_rules[$name];
			} else {
				$closure = $rule;
			}
		} elseif (is_callable($rule)) {
			$closure = $rule;
		} else {
			throw new RuntimeException("Invalid rule.");
		}

		if (!is_callable($closure)) {
			$message = isset($closure['message']) ? $closure['message'] : $message;
			$closure = isset($closure['rule']) ? $closure['rule'] : $closure;
		}

		$params += compact('message');
		if(!call_user_func($closure, $user, $request, $params)) {
			$this->_error[$name] = $params['message'];
			return false;
		}
		return true;
	}

	/**
	 * Getter/setter for rules
	 *
	 * @param string $name The rule name to get/set. if `null` all rules are returned.
	 * @param function $rule A Closure which be called with the following parameter :
	 *        - first parameter : the user
	 *        - second parameter : a `Request` instance
	 *        - third parameter : an options array
	 * @return mixed Either an array of rule closures, a single rule closure, or `null`.
	 */
	public function rules($name = null, $rule = null, $options = []) {
		if (!$name) {
			return $this->_rules;
		}
		if ($rule) {
			$this->_rules[$name] = compact('rule') + $options;
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