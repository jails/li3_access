<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\source\db_acl;

class AcoFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_access\security\access\model\db_acl\Aco';

	protected $_fields = array(
		'id' => array('type' => 'id'),
		'parent_id' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'class' => array('type' => 'string', 'null' => true),
		'fk_id' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'alias'	=> array('type' => 'string', 'default' => ''),
		'lft' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'rght' => array('type' => 'integer', 'length' => 10, 'null' => true)
	);

	protected $_records = array(
		array('id' => 1, 'parent_id' => null, 'class' => null, 'fk_id' => null, 'alias' => 'root', 'lft' => 1,  'rght' => 20),
		array('id' => 2, 'parent_id' => 1,	'class' => null, 'fk_id' => null, 'alias' => 'tpsReports', 'lft' => 2,  'rght' => 9),
		array('id' => 3, 'parent_id' => 2, 'class' => null, 'fk_id' => null, 'alias' => 'view', 'lft' => 3,  'rght' => 6),
		array('id' => 4, 'parent_id' => 3, 'class' => null, 'fk_id' => null, 'alias' => 'current', 'lft' => 4,  'rght' => 5),
		array('id' => 5, 'parent_id' => 2, 'class' => null, 'fk_id' => null, 'alias' => 'update',	'lft' => 7,  'rght' => 8),
		array('id' => 6, 'parent_id' => 1,	'class' => null, 'fk_id' => null, 'alias' => 'printers', 'lft' => 10, 'rght' => 19),
		array('id' => 7, 'parent_id' => 6, 'class' => null, 'fk_id' => null, 'alias' => 'print', 'lft' => 11, 'rght' => 14),
		array('id' => 8, 'parent_id' => 7, 'class' => null, 'fk_id' => null, 'alias' => 'lettersize', 'lft' => 12, 'rght' => 13),
		array('id' => 9, 'parent_id' => 6, 'class' => null, 'fk_id' => null, 'alias' => 'refill', 'lft' => 15, 'rght' => 16),
		array('id' => 10, 'parent_id' => 6, 'class' => null, 'fk_id' => null, 'alias' => 'smash', 'lft' => 17, 'rght' => 18),
	);
}
