<?php

namespace li3_access\resources\migration;

class Permission extends \li3_migrations\models\Migration {

	protected $_fields = array(
		'id' => array('type' => 'id'),
		'aro_id' => array('type' => 'integer', 'length' => 10, 'null' => false),
		'aco_id' => array('type' => 'integer', 'length' => 10, 'null' => false),
		'privileges' => array('type' => 'text')
	);

	protected $_records = array();

	protected $_source = 'permissions';

	public function up() {
		return $this->create();
	}

	public function down() {
		return $this->drop();
	}

}

?>