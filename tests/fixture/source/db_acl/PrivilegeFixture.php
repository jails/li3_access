<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\source\db_acl;

class PrivilegeFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_access\security\access\model\db_acl\Privilege';

    protected $_fields = array(
		'id' => array('type' => 'id'),
		'name' => array('type' => 'string', 'length' => 32)
    );

    protected $_records = array(
        array('id' => 1, 'name' => 'create'),
        array('id' => 2, 'name' => 'read'),
        array('id' => 3, 'name' => 'update'),
        array('id' => 4, 'name' => 'delete')
    );
}
