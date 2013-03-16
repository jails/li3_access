<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\security\access\model\db_acl;

class Aco extends \li3_access\security\access\model\db_acl\AclNode {

	public $belongsTo = [
		'Parent' => [
			'key' => 'parent_id',
			'to' => 'li3_access\security\access\model\db_acl\Aco'
	]];

	public $hasMany = ['Permission' => ['key' => 'aco_id']];

	public $hasAndBelongsToMany = ['Aro' => ['via' => 'Permission']];

}
