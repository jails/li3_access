<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\security\access\model\db_acl;

class Aco extends \li3_access\security\access\model\db_acl\AclNode {

	public $belongsTo = array(
		'Parent' => array(
			'key' => 'parent_id',
			'to' => 'li3_access\security\access\model\db_acl\Aco'
	));

	public $hasMany = array('Permission' => array('key' => 'aco_id'));

	public $hasAndBelongsToMany = array('Aro' => array('via' => 'Permission'));

}
