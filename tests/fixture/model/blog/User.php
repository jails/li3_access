<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\model\blog;

class User extends \lithium\data\Model {

	protected $_meta = ['key' => 'id'];

	public $hasMany = ['Post'];
	public $belongsTo = ['Group'];
}