<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\cases\security\access\adapter;

use li3_access\extensions\adapter\security\access\Simple;

class SimpleTest extends \lithium\test\Unit {

	protected $_adapter;

	public function setUp() {
		$this->_adapter = new Simple();
	}

	public function tearDown() {}

	public function testCheck() {
		$result = $this->_adapter->check(['username' => 'Max']);
		$this->assertTrue($result);

		$result = $this->_adapter->check(false);
		$this->assertFalse($result);
	}

	public function testFilter() {
		$set = ['key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3'];
	}
}

?>