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

	public function setUp() {
		$this->adapter = new Simple();
	}

	public function tearDown() {}

	public function testCheck() {
		$result = $this->adapter->check(array('username' => 'Max'));
		$this->assertTrue($result);

		$result = $this->adapter->check(false);
		$this->assertFalse($result);
	}

	public function testFilter() {
		$set = array('key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3');
	}
}

?>