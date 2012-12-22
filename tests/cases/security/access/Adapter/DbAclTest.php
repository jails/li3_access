<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\cases\security\access\adapter;

use li3_access\extensions\adapter\security\access\DbAcl;

class DbAclTest extends \lithium\test\Unit {

	protected $_model = 'lithium\tests\mocks\core\MockCallable';

	public function setUp() {
		$this->adapter = new DbAcl(array(
			'classes' => array(
				'permission' => $this->_model
			)
		));
	}

	public function testCheck() {
		$this->adapter->check('root/user/john', 'controller/post/add', array('read', 'create'));
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('check', $method);
		$this->assertEqual(array(
			'root/user/john', 'controller/post/add', array('read', 'create')
		), $params);
	}

	public function testGet() {
		$this->adapter->get('param1', 'param2');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('get', $method);
		$this->assertEqual(array('param1', 'param2'), $params);
	}

	public function testAllow() {
		$this->adapter->allow('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', 1), $params);
	}

	public function testDeny() {
		$this->adapter->deny('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', -1), $params);
	}

	public function testInherit() {
		$this->adapter->inherit('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', 0), $params);
	}

	public function testError() {
		$this->adapter->error('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('error', $method);
		$this->assertEqual(array(), $params);
	}
}

?>