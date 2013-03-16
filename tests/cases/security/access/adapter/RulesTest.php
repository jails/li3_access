<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\cases\security\access\adapter;

use lithium\action\Request;
use li3_access\extensions\adapter\security\access\Rules;

class RulesTest extends \lithium\test\Unit {

	protected $_adapter;

	public function setUp() {
		$this->_adapter = new Rules();
	}

	public function tearDown() {}

	public function testPatternBasedIpMatching() {
		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.2']]);

		$rules = ['allowIp' => ['ip' => '/10\.0\.1\.\d+/']];
		$result = $this->_adapter->check([], $request, compact('rules'));
		$this->assertTrue($result);

		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.255']]);
		$result = $this->_adapter->check([], $request, compact('rules'));
		$this->assertTrue($result);

		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.2.1']]);
		$result = $this->_adapter->check([], $request, compact('rules'));
		$this->assertFalse($result);

		$result = $this->_adapter->error();
		$this->assertEqual('Your IP is not allowed to access this area.', $result['message']);
	}

	public function testArrayBasedIpMatching() {
		$rules = ['allowIp' => ['ip' => ['10.0.1.2', '10.0.1.3', '10.0.1.4']]];

		foreach ([2, 3, 4] as $i) {
			$request = new Request(['env' => ['REMOTE_ADDR' => "10.0.1.{$i}"]]);
			$result = $this->_adapter->check([], $request, compact('rules'));
			$this->assertTrue($result);
		}

		foreach ([1, 5, 255] as $i) {
			$request = new Request(['env' => ['REMOTE_ADDR' => "10.0.1.{$i}"]]);
			$result = $this->_adapter->check([], $request, compact('rules'));
			$this->assertFalse($result);
			$result = $this->_adapter->error();
			$this->assertEqual('Your IP is not allowed to access this area.', $result['message']);
		}
	}

	public function testCheck() {
		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.1']]);

		$rules = ['allowAnyUser', 'allowAll', 'allowIp' => [
			'ip' => '10.0.1.1'
		]];

		$result = $this->_adapter->check(['username' => 'Nate'], $request, [
			'rules' => $rules
		]);
		$this->assertTrue($result);

		$expected = [
			'message' => 'You are not permitted to access this area.',
		];
		$result = $this->_adapter->check(['username' => 'Gwoo'], $request, [
			'rules' => 'denyAll'
		]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rules = ['allowAnyUser'];
		$expected = [
			'message' => 'You must be logged in.'
		];
		$result = $this->_adapter->check([], $request, ['rules' => $rules]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$result = $this->_adapter->check(false, $request, ['rules' => $rules]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testCheckWithOnTheFlyClosure() {
		$request = new Request();
		$rules = [
			[
				'message' => 'Access denied.',
				'allow' => function($user, $request, $options) {
					return $user['username'] == 'Nate';
				}
			]
		];
		$result = $this->_adapter->check(['username' => 'Nate'], $request, [
			'rules' => $rules
		]);
		$this->assertTrue($result);
	}

	public function testNoRules() {
		$request = new Request();
		$this->expectException("Missing `'rules'` option.");
		$result = $this->_adapter->check([], $request);
	}

	public function testInvalidRule() {
		$request = new Request();
		$this->expectException('Invalid rule.');
		$result = $this->_adapter->check([], $request, ['rules' => 'invalid']);
	}

	public function testGetSetRules() {
		$request = new Request();

		$this->_adapter->rules('testDeny', function() { return false;}, [
			'message' => 'Access denied.'
		]);

		$expected = ['message' => 'Access denied.'];
		$result = $this->_adapter->check(['username' => 'Tom'], $request, [
			'rules' => 'testDeny'
		]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rule = $this->_adapter->rules('testDeny');
		$this->assertTrue(is_callable($rule['allow']));
		$this->assertTrue(is_array($this->_adapter->rules()));
	}
}

?>
