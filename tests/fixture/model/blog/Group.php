<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\model\blog;

class Group extends \lithium\data\Model {

	protected $_meta = array('key' => 'id');

	public $hasMany = array('User');
}