<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\tests\fixture\source\blog;

class PostFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_access\tests\fixture\model\blog\Post';

	protected $_fields = array(
		'id' => array('type' => 'id'),
		'title' => array('type' => 'string'),
		'body' => array('type' => 'text')
	);
}

?>