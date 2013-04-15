<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\integration\security\access\model\db_acl;

use lithium\util\Set;
use lithium\data\Connections;
use lithium\data\source\Database;
use li3_fixtures\test\Fixtures;
use li3_access\security\access\model\db_acl\Aco;
use li3_access\security\access\model\db_acl\Aro;

class AclNodeTest extends \lithium\test\Integration {

	protected $_connection = 'test';

	protected $_models = [
		'_user' => 'li3_access\tests\fixture\model\blog\User',
		'_group' => 'li3_access\tests\fixture\model\blog\Group'
	];

	protected $_fixtures = [
		'aco' => 'li3_access\tests\fixture\source\db_acl\AcoFixture',
		'aro' => 'li3_access\tests\fixture\source\db_acl\AroFixture'
	];

	/**
	 * Skip the test if no test database connection available.
	 */
	public function skip() {
		$dbConfig = Connections::get($this->_connection, ['config' => true]);
		$isAvailable = (
			$dbConfig &&
			Connections::get($this->_connection)->isConnected(['autoConnect' => true])
		);
		$this->skipIf(!$isAvailable, "No {$this->_connection} connection available.");

		$db = Connections::get($this->_connection);

		$this->skipIf(
			!($db instanceof Database),
			"The {$this->_connection} connection is not a relational database."
		);
	}

	public function setUp() {
		Fixtures::config([
			'db' => [
				'adapter' => 'Connection',
				'connection' => $this->_connection,
				'fixtures' => $this->_fixtures
			]
		]);
	}

	public function tearDown() {
		foreach($this->_models as $key => $class){
			$class::reset();
		}
		Fixtures::clear('db');
	}

	function testCreate() {
		Fixtures::save('db');

		$aro = Aro::create();
		$aro->set(['alias' => 'Chotchkey']);
		$this->assertTrue($aro->save());

		$key = Aro::key();
		$parent = $aro->$key;

		$aro = Aro::create();
		$aro->set(['parent_id' => $parent, 'alias' => 'Joanna']);
		$this->assertTrue($aro->save());

		$aro = Aro::create();
		$aro->set(['parent_id' => $parent, 'alias' => 'Stapler']);
		$this->assertTrue($aro->save());

		$root = Aro::node('root');
		$parent = $root[0][Aco::key()];

		$aco = Aco::create();
		$aco->set(['parent_id' => $parent, 'alias' => 'Drinks']);
		$this->assertTrue($aco->save());

		$aco = Aco::create();
		$aco->set(['parent_id' => $parent, 'alias' => 'PiecesOfFlair']);
		$this->assertTrue($aco->save());
	}

	function testCreateWithParent() {
		Fixtures::save('db');

		$parent = Aro::find('first', ['conditions' => ['alias' => 'Peter']]);
		$key = Aro::key();
		$aro = Aro::create();
		$aro->set([
			'alias' => 'Subordinate',
			'model' => 'User',
			'fk_id' => 7,
			'parent_id' => $parent->$key
		]);
		$aro->save();

		$result = Aro::find('first', ['conditions' => ['alias' => 'Subordinate']]);
		$this->assertEqual(16, $result->lft);
		$this->assertEqual(17, $result->rght);
	}

	function testNode() {
		Fixtures::save('db');
		extract($this->_models);
		$result1 = Set::extract(Aco::node('root/printers/refill'), '/id');
		$result2 = Set::extract(Aco::node('printers/refill'), '/id');
		$result3 = Set::extract(Aco::node('refill'), '/id');

		$expected = ['9', '6', '1'];
		$this->assertEqual($expected, $result1);
		$this->assertEqual($expected, $result2);
		$this->assertEqual($expected, $result3);

		$result = Aco::node('root/refill');
		$this->assertFalse($result);

		$result = Aco::node('');
		$this->assertFalse($result);

		$result = Aro::node('root/users/Samantha');
		$expected = [
			[
				'id' => '7', 'parent_id' => '4', 'class' => $_user,
				'fk_id' => 3, 'alias' => 'Samantha', 'lft' => 11, 'rght' => 12
			],
			[
				'id' => '4', 'parent_id' => '1', 'class' => $_group,
				'fk_id' => 3, 'alias' => 'users', 'lft' => 10, 'rght' => 19
			],
			[
				'id' => '1', 'parent_id' => null, 'class' => null,
				'fk_id' => null, 'alias' => 'root', 'lft' => 1, 'rght' => 20
			]
		];
		$this->assertEqual($expected, $result);

		$result = Aco::node('root/tpsReports/view/current');
		$expected = [
			[
				'id' => '4', 'parent_id' => '3', 'class' => null,
				'fk_id' => null, 'alias' => 'current', 'lft' => 4, 'rght' => 5
			],
			[
				'id' => '3', 'parent_id' => '2', 'class' => null,
				'fk_id' => null, 'alias' => 'view', 'lft' => 3, 'rght' => 6
			],
			[
				'id' => '2', 'parent_id' => '1', 'class' => null,
				'fk_id' => null, 'alias' => 'tpsReports', 'lft' => 2, 'rght' => 9
			],
			[
				'id' => '1', 'parent_id' => null, 'class' => null,
				'fk_id' => null, 'alias' => 'root', 'lft' => 1, 'rght' => 20
			]
		];
		$this->assertEqual($expected, $result);
	}

	public function testNodeArrayFind() {
		Fixtures::save('db');
		extract($this->_models);
		$_user::config(['meta' => ['connection' => false]]);
		$result = Set::extract(Aro::node(['class' => $_user, 'id' => '1']), '/id');
		$expected = ['5', '2', '1'];
		$this->assertEqual($expected, $result);

		$result = Set::extract(Aro::node(['class' => $_user, 'fk_id' => '1']), '/id');
		$expected = ['5', '2', '1'];
		$this->assertEqual($expected, $result);

		$_group::config(['meta' => ['connection' => false]]);
		$result = Set::extract(Aro::node(['class' => $_group, 'id' => '1']), '/id');
		$expected = ['2', '1'];
		$this->assertEqual($expected, $result);

		$result = Set::extract(Aro::node(['class' => $_group, 'fk_id' => '1']), '/id');
		$expected = ['2', '1'];
		$this->assertEqual($expected, $result);
	}

	public function testNodeEntity() {
		Fixtures::save('db');
		extract($this->_models);
		$_user::config(['meta' => ['connection' => false]]);
		$user = $_user::create();
		$user->id = 1;
		$result = Set::extract(Aro::node($user), '/id');
		$expected = ['5', '2', '1'];
		$this->assertEqual($expected, $result);

		$_group::config(['meta' => ['connection' => false]]);
		$group = $_group::create();
		$group->id = 1;
		$result = Set::extract(Aro::node($group), '/id');
		$expected = ['2', '1'];
		$this->assertEqual($expected, $result);
	}

}
