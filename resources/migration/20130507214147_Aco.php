<?php

namespace li3_access\resources\migration;

class Aco extends \li3_migrations\models\Migration {

	protected $_fields = array(
		'id' => array('type' => 'id'),
		'parent_id' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'class' => array('type' => 'string', 'null' => true),
		'fk_id' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'alias'	=> array('type' => 'string', 'default' => ''),
		'lft' => array('type' => 'integer', 'length' => 10, 'null' => true),
		'rght' => array('type' => 'integer', 'length' => 10, 'null' => true)
	);

	protected $_records = array();

	protected $_source = 'acos';

	public function up() {
		return $this->create();
	}

	public function down() {
		return $this->drop();
	}

}

?>