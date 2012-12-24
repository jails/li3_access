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

	protected $_adapter;

	public function setUp() {
		$this->_adapter = new DbAcl(array(
			'classes' => array(
				'permission' => $this->_model
			)
		));
	}

	public function testCheck() {
		$this->_adapter->check('root/user/john', 'controller/post/add', array('read', 'create'));
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('check', $method);
		$this->assertEqual(array(
			'root/user/john', 'controller/post/add', array('read', 'create')
		), $params);
	}

	public function testGet() {
		$this->_adapter->get('param1', 'param2');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('get', $method);
		$this->assertEqual(array('param1', 'param2'), $params);
	}

	public function testAllow() {
		$this->_adapter->allow('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', 1), $params);
	}

	public function testDeny() {
		$this->_adapter->deny('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', -1), $params);
	}

	public function testInherit() {
		$this->_adapter->inherit('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('allow', $method);
		$this->assertEqual(array('param1', 'param2', 'param3', 0), $params);
	}

	public function testError() {
		$this->_adapter->error('param1', 'param2', 'param3');
		$model = $this->_model;
		extract($model::$callStatic);
		$this->assertEqual('error', $method);
		$this->assertEqual(array(), $params);
	}
}

?>