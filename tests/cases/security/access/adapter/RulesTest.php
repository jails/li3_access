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
		$request = new Request(array('env' => array('REMOTE_ADDR' => '10.0.1.1')));

		$rules = array('allowAnyUser', 'allowAll', 'allowIp' => array(
			'ip' => '10.0.1.1'
		));

		$result = $this->_adapter->check(array('username' => 'Nate'), $request, compact('rules'));
		$this->assertTrue($result);

		$expected = array(
			'denyAll' => 'You are not permitted to access this area.',
		);
		$result = $this->_adapter->check(array('username' => 'Gwoo'), $request, array(
			'rules' => 'denyAll'
		));

		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rules = array('allowAnyUser');
		$expected = array(
			'allowAnyUser' => 'You must be logged in.'
		);
		$result = $this->_adapter->check(array(), null, compact('rules'));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$result = $this->_adapter->check(false, null, compact('rules'));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testCheckOverrideMessage() {
		$expected = array(
			'denyAll' => 'Gwoo are not permitted to access this area.',
		);
		$result = $this->_adapter->check(array('username' => 'Gwoo'), null, array(
			'rules' => array('denyAll' => array('message' => $expected['denyAll']))
		));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testCheckSimpleClosureOnTheFly() {
		$rules = array(
			function($user, $request, $options) {
				return $user['username'] === 'Nate';
			}
		);
		$result = $this->_adapter->check(array('username' => 'Nate'), null, array(
			'rules' => $rules
		));
		$this->assertTrue($result);
	}

	public function testCheckClosureOnTheFly() {
		$rules = array(
			array(
				'message' => 'Access denied.',
				'rule' => function($user) {
					return $user['username'] === 'Nate';
				}
			)
		);
		$result = $this->_adapter->check(array('username' => 'Nate'), null, array(
			'rules' => $rules
		));
		$this->assertTrue($result);
	}

	public function testNoRules() {
		$this->expectException("Missing `'rules'` option.");
		$result = $this->_adapter->check(null, null);
	}

	public function testInvalidRule() {
		$this->expectException('Invalid rule.');
		$result = $this->_adapter->check(null, null, array('rules' => 'invalid'));
	}

	public function testGetSetRules() {
		$this->_adapter->rules('testDeny', function() { return false;}, array(
			'message' => 'Access denied.'
		));

		$expected = array('testDeny' => 'Access denied.');
		$result = $this->_adapter->check(array('username' => 'Tom'), null, array(
			'rules' => 'testDeny'
		));
		$this->assertFalse($result);
		$result = $this->_adapter->error();
		$this->assertEqual($expected, $result);

		$rule = $this->_adapter->rules('testDeny');
		$this->assertTrue(is_callable($rule['rule']));
		$this->assertTrue(is_array($this->_adapter->rules()));
	}

	public function testGlobalOptionsPassedToRule() {
		$adapter = new Rules(array(
			'rules' => array(
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar';
				}
			),
			'defaults' => array('foobar')
		));

		$result = $adapter->check(null, null, array('foo' => 'bar'));
		$this->assertTrue($result);

		$result = $adapter->check(null, null, array('foo' => 'baz'));
		$this->assertFalse($result);

	}

	public function testLocalOptionsPassedToRule() {
		$adapter = new Rules(array(
			'rules' => array(
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar';
				}
			),
			'defaults' => array('foobar')
		));

		$result = $adapter->check(null, null, array(
			'rules' => array('foobar' => array('foo' => 'bar'))
		));
		$this->assertTrue($result);

		$result = $adapter->check(null, null, array(
			'rules' => array('foobar' => array('foo' => 'baz'))
		));
		$this->assertFalse($result);

	}

	public function testGlobalAndLocalOptionsPassedToRule() {
		$adapter = new Rules(array(
			'rules' => array(
				'foobar' => function($user, $request, $options) {
					return $options['foo'] === 'bar' && $options['bar'] === 'foo';
				}
			)
		));

		$result = $adapter->check(null, null, array(
			'rules' => array('foobar' => array('foo' => 'bar')), 'bar' => 'foo'
		));
		$this->assertTrue($result);

		$result = $adapter->check(null, null, array(
			'rules' => array('foobar' => array('foo' => 'bar')), 'bar' => 'fox'
		));
		$this->assertFalse($result);

	}

	public function testAutoUser() {
		$user = array('username' => 'Mehlah');
		$adapter = new Rules(array(
			'rules' => array(
				'isMehlah' => function($user, $request, $options) {
					return isset($user['username']) && $user['username'] == 'Mehlah';
				}
			),
			'defaults' => array('isMehlah'),
			'user' => function() use ($user) { return $user; }
		));

		$result = $adapter->check($user, null);
		$this->assertTrue($result);

		$result = $adapter->check(null, null);
		$this->assertTrue($result);

		$result = $adapter->check(array('username' => 'Bob'), null);
		$this->assertFalse($result);

		$expected = array('isMehlah' => 'You are not permitted to access this area.');
		$result = $adapter->error();
		$this->assertEqual($expected, $result);
	}

	public function testAllowAny() {
		$rules = array('allowAll', 'allowAnyUser');
		$allowAny = true;
		$result = $this->_adapter->check(array(), null, compact('rules', 'allowAny'));
		$this->assertTrue($result);

		$allowAny = false;
		$result = $this->_adapter->check(array(), null, compact('rules', 'allowAny'));
		$this->assertFalse($result);
	}

	public function testPatternBasedIpMatching() {
		$request = new Request(array('env' => array('REMOTE_ADDR' => '10.0.1.2')));

		$rules = array('allowIp' => array('ip' => '/10\.0\.1\.\d+/'));
		$result = $this->_adapter->check(null, $request, compact('rules'));
		$this->assertTrue($result);

		$request = new Request(array('env' => array('REMOTE_ADDR' => '10.0.1.255')));
		$result = $this->_adapter->check(null, $request, compact('rules'));
		$this->assertTrue($result);

		$request = new Request(array('env' => array('REMOTE_ADDR' => '10.0.2.1')));
		$result = $this->_adapter->check(null, $request, compact('rules'));
		$this->assertFalse($result);

		$result = $this->_adapter->error();
		$this->assertEqual('Your IP is not allowed to access this area.', $result['allowIp']);
	}

	public function testArrayBasedIpMatching() {
		$rules = array('allowIp' => array('ip' => array('10.0.1.2', '10.0.1.3', '10.0.1.4')));

		foreach (array(2, 3, 4) as $i) {
			$request = new Request(array('env' => array('REMOTE_ADDR' => "10.0.1.{$i}")));
			$result = $this->_adapter->check(null, $request, compact('rules'));
			$this->assertTrue($result);
		}

		foreach (array(1, 5, 255) as $i) {
			$request = new Request(array('env' => array('REMOTE_ADDR' => "10.0.1.{$i}")));
			$result = $this->_adapter->check(null, $request, compact('rules'));
			$this->assertFalse($result);
			$result = $this->_adapter->error();
			$this->assertEqual('Your IP is not allowed to access this area.', $result['allowIp']);
		}
	}
}

?>
