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

	public function testCheck() {
		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.1']]);

		$rules = ['allowAnyUser', 'allowAll', 'allowIp' => [
			'ip' => '10.0.1.1'
		]];

		$result = $this->_adapter->check(['username' => 'Nate'], $request, compact('rules'));
		$this->assertTrue($result);

		$expected = [
			'denyAll' => 'You are not permitted to access this area.',
		];
		$result = $this->_adapter->check(['username' => 'Gwoo'], $request, ['rules' => 'denyAll']);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rules = ['allowAnyUser'];
		$expected = [
			'allowAnyUser' => 'You must be logged in.'
		];
		$result = $this->_adapter->check([], null, compact('rules'));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$result = $this->_adapter->check(false, null, compact('rules'));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testCheckOverrideMessage() {
		$expected = [
			'denyAll' => 'Gwoo are not permitted to access this area.',
		];
		$result = $this->_adapter->check(['username' => 'Gwoo'], null, [
			'rules' => ['denyAll' => ['message' => $expected['denyAll']]]
		]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testCheckSimpleClosureOnTheFly() {
		$rules = [
			function($user) {
				return $user['username'] === 'Nate';
			}
		];
		$result = $this->_adapter->check(['username' => 'Nate'], null, [
			'rules' => $rules
		]);
		$this->assertTrue($result);
	}

	public function testCheckClosureOnTheFly() {
		$rules = [
			[
				'message' => 'Access denied.',
				'rule' => function($user) {
					return $user['username'] === 'Nate';
				}
			]
		];
		$result = $this->_adapter->check(['username' => 'Nate'], null, [
			'rules' => $rules
		]);
		$this->assertTrue($result);
	}

	public function testNoRules() {
		$this->expectException("Missing `'rules'` option.");
		$result = $this->_adapter->check([], null);
	}

	public function testInvalidRule() {
		$this->expectException('Invalid rule.');
		$result = $this->_adapter->check([], null, ['rules' => 'invalid']);
	}

	public function testGetSetRules() {
		$this->_adapter->rules('testDeny', function() { return false;}, [
			'message' => 'Access denied.'
		]);

		$expected = ['testDeny' => 'Access denied.'];
		$result = $this->_adapter->check(['username' => 'Tom'], null, [
			'rules' => 'testDeny'
		]);
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rule = $this->_adapter->rules('testDeny');
		$this->assertTrue(is_callable($rule['rule']));
		$this->assertTrue(is_array($this->_adapter->rules()));
	}

	public function testGlobalOptionsPassedToRule() {
		$adapter = new Rules([
			'rules' => [
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar';
				}
			],
			'defaults' => ['foobar']
		]);

		$result = $adapter->check(null, null, ['foo' => 'bar']);
		$this->assertTrue($result);

		$result = $adapter->check(null, null, ['foo' => 'baz']);
		$this->assertFalse($result);

	}

	public function testLocalOptionsPassedToRule() {
		$adapter = new Rules([
			'rules' => [
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar';
				}
			],
			'defaults' => ['foobar']
		]);

		$result = $adapter->check(null, null, ['rules' => ['foobar' => ['foo' => 'bar']]]);
		$this->assertTrue($result);

		$result = $adapter->check(null, null, ['rules' => ['foobar' => ['foo' => 'baz']]]);
		$this->assertFalse($result);

	}

	public function testGlobalAndLocalOptionsPassedToRule() {
		$adapter = new Rules([
			'rules' => [
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar' && $options['bar'] === 'foo';
				}
			]
		]);

		$result = $adapter->check(null, null, [
			'rules' => ['foobar' => ['foo' => 'bar']], 'bar' => 'foo'
		]);
		$this->assertTrue($result);

		$result = $adapter->check(null, null, [
			'rules' => ['foobar' => ['foo' => 'bar']], 'bar' => 'fox'
		]);
		$this->assertFalse($result);

	}

	public function testAutoUser() {
		$user = ['username' => 'Mehlah'];
		$adapter = new Rules([
			'rules' => [
				'isMehlah' => function($user, $request, $options) {
					return isset($user['username']) && $user['username'] == 'Mehlah';
				}
			],
			'defaults' => ['isMehlah'],
			'user' => function() use ($user) { return $user; }
		]);

		$result = $adapter->check($user, null);
		$this->assertTrue($result);

		$result = $adapter->check(null, null);
		$this->assertTrue($result);

		$result = $adapter->check(['username' => 'Bob'], null);
		$this->assertFalse($result);

		$expected = ['isMehlah' => 'You are not permitted to access this area.'];
		$result = $adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testAllowAny() {
		$rules = ['allowAll', 'allowAnyUser'];
		$allowAny = true;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertTrue($result);

		$allowAny = false;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertFalse($result);

		$rules = ['allowAnyUser', 'allowAll'];
		$allowAny = true;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertTrue($result);

		$allowAny = false;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertFalse($result);

		$rules = ['allowAnyUser'];
		$allowAny = true;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertFalse($result);

		$allowAny = false;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertFalse($result);

		$rules = ['allowAll'];
		$allowAny = true;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertTrue($result);

		$allowAny = false;
		$result = $this->_adapter->check([], null, compact('rules', 'allowAny'));
		$this->assertTrue($result);
	}

	public function testPatternBasedIpMatching() {
		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.2']]);

		$rules = ['allowIp' => ['ip' => '/10\.0\.1\.\d+/']];
		$result = $this->_adapter->check(null, $request, compact('rules'));
		$this->assertTrue($result);

		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.1.255']]);
		$result = $this->_adapter->check(null, $request,  compact('rules'));
		$this->assertTrue($result);

		$request = new Request(['env' => ['REMOTE_ADDR' => '10.0.2.1']]);
		$result = $this->_adapter->check(null, $request, compact('rules'));
		$this->assertFalse($result);

		$result = $this->_adapter->error();
		$this->assertEqual('Your IP is not allowed to access this area.', $result['allowIp']);
	}

	public function testArrayBasedIpMatching() {
		$rules = ['allowIp' => ['ip' => ['10.0.1.2', '10.0.1.3', '10.0.1.4']]];

		foreach ([2, 3, 4] as $i) {
			$request = new Request(['env' => ['REMOTE_ADDR' => "10.0.1.{$i}"]]);
			$result = $this->_adapter->check(null, $request, compact('rules'));
			$this->assertTrue($result);
		}

		foreach ([1, 5, 255] as $i) {
			$request = new Request(['env' => ['REMOTE_ADDR' => "10.0.1.{$i}"]]);
			$result = $this->_adapter->check(null, $request, compact('rules'));
			$this->assertFalse($result);
			$result = $this->_adapter->error();
			$this->assertEqual('Your IP is not allowed to access this area.', $result['allowIp']);
		}
	}
}

?>
