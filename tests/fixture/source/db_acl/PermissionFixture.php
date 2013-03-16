<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\source\db_acl;

class PermissionFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_access\security\access\model\db_acl\Permission';

    protected $_fields = [
		'id' => ['type' => 'id'],
		'aro_id' => ['type' => 'integer', 'length' => 10, 'null' => false],
		'aco_id' => ['type' => 'integer', 'length' => 10, 'null' => false],
		'privileges' => ['type' => 'text']
    ];

    protected $_records = [
        ['id' => 1, 'aro_id' => '1', 'aco_id' => '1', 'privileges' => '{"create":0,"read":0,"update":0,"delete":0}'],
        ['id' => 2, 'aro_id' => '2', 'aco_id' => '1', 'privileges' => '{"read":1,"update":1,"delete":1}'],
        ['id' => 3, 'aro_id' => '3', 'aco_id' => '2', 'privileges' => '{"read":1}', ],
        ['id' => 4, 'aro_id' => '4', 'aco_id' => '2', 'privileges' => '{"create":1,"read":1,"delete":0}'],
        ['id' => 5, 'aro_id' => '4', 'aco_id' => '6', 'privileges' => '{"create":1,"read":1}'],
        ['id' => 6, 'aro_id' => '5', 'aco_id' => '1', 'privileges' => '{"create":1,"read":1,"update":1,"delete":1}'],
        ['id' => 7, 'aro_id' => '6', 'aco_id' => '3', 'privileges' => '{"create":0,"read":1,"update":0,"delete":0}'],
        ['id' => 8, 'aro_id' => '6', 'aco_id' => '4', 'privileges' => '{"create":0,"read":1,"update":0,"delete":1}'],
        ['id' => 9, 'aro_id' => '6', 'aco_id' => '6', 'privileges' => '{"create":0,"read":1,"update":1,"delete":0}'],
        ['id' => 10, 'aro_id' => '7', 'aco_id' => '2', 'privileges' => '{"create":0,"read":0,"update":0,"delete":0}'],
        ['id' => 11, 'aro_id' => '7', 'aco_id' => '7', 'privileges' => '{"create":1,"read":1,"update":1}'],
        ['id' => 12, 'aro_id' => '7', 'aco_id' => '8', 'privileges' => '{"create":1,"read":1,"update":1}'],
        ['id' => 13, 'aro_id' => '7', 'aco_id' => '9', 'privileges' => '{"create":1,"read":1,"update":1,"delete":1}'],
        ['id' => 14, 'aro_id' => '7', 'aco_id' => '10', 'privileges' => '{"delete":1}'],
        ['id' => 15, 'aro_id' => '8', 'aco_id' => '10', 'privileges' => '{"create":1,"read":1,"update":1,"delete":1}'],
        ['id' => 16, 'aro_id' => '8', 'aco_id' => '2', 'privileges' => '{"create":0,"read":0,"update":0,"delete":0}'],
        ['id' => 17, 'aro_id' => '9', 'aco_id' => '4', 'privileges' => '{"create":1,"read":1,"update":1,"delete":0}'],
        ['id' => 18, 'aro_id' => '9', 'aco_id' => '9', 'privileges' => '{"update":1,"delete":1}'],
        ['id' => 19, 'aro_id' => '10', 'aco_id' => '9', 'privileges' => '{"create":1,"read":1,"update":1,"delete":1}'],
        ['id' => 20, 'aro_id' => '10', 'aco_id' => '10', 'privileges' => '{"create":0,"read":0,"update":0,"delete":0}']
    ];
}
