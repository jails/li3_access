<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\cases\security;

use li3_access\security\Access;
use lithium\tests\mocks\core\MockCallable;

class AccessTest extends \lithium\test\Unit {

	protected $_adapter;

	public function setUp() {
		Access::reset();
		$this->_adapter = new MockCallable();
		Access::config([
			'test_access' => [
				'object' => $this->_adapter
			],
			'test_access_with_filters' => [
				'object' => $this->_adapter,
				'filters' => [
					function($self, $params, $chain) {
						return $chain->next($self, $params, $chain);
					},
					function($self, $params, $chain) {
						return 'Filter executed.';
					}
				]
			]
		]);
	}

	public function tearDown() {Access::reset();}

	public function testCheck() {
		$result = Access::check('test_access', ['username' => 'Gwoo'], false);
		extract($result);
		$this->assertEqual('check', $method);
		$this->assertEqual([['username' => 'Gwoo'], false, []], $params);
	}

	public function testFilters() {
		$result = Access::check('test_access_with_filters', false, false, []);
		$this->assertEqual('Filter executed.', $result);
	}

	public function testNoConfigurations() {
		Access::reset();
		$this->assertIdentical([], Access::config());
		$this->expectException("Configuration `test_no_config` has not been defined.");
		Access::check('test_no_config', false, false);
	}
}

?>
