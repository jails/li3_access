<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_access\security\access\model\db_acl;

use li3_behaviors\data\model\Behaviors;

class AclNode extends \lithium\data\Model {
	use Behaviors;

	/**
	 * ACL models use the Tree behavior
	 *
	 * @var array
	 */
	protected $_actsAs = ['Tree'];

	/**
	 * Meta-information
	 *
	 * @var array
	 */
	protected $_meta = [
		'acl' => ['class' => 'class', 'alias' => 'alias', 'key' => 'fk_id']
	];

	/**
	 * Retrieves the Acl node for this model.
	 *
	 * @param mixed $ref Array with 'model' and 'fk_id', model object, or string value.
	 * @return array Nodes founded in database, `false` otherwise.
	 */
	public static function node($ref = null) {
		$db = static::connection();
		$name = static::meta('name');

		extract(static::meta('acl'), EXTR_SKIP);
		extract(static::actsAs('Tree', true), EXTR_SKIP);

		if (is_string($ref)) {
			$w = $with = 'Parent';
			$i = 0;
			$paths = explode('/', $ref);
			$start = $paths[0];
			unset($paths[0]);

			$query = [
				'alias' => $name,
				'fields' => [$name],
				'with' => [
					$with => [
						'alias' => "{$name}0",
						'constraints' => (object) [
							"{$name}0.{$alias}" => (object) $db->value($start, [
								'type' => 'string'
							])
						]
					]],
				'order' => "{$name}.{$left} DESC"
			];

			foreach ($paths as $i => $path) {
				$j = $i - 1;
				$w .= ".{$with}";
				$query['with'][$w] = [
					'alias' => "{$name}{$i}",
					'constraints' => (object) [
						"{$name}{$i}.{$left}" => ['>' => "{$name}{$j}.{$left}"],
						"{$name}{$i}.{$right}" => ['<' => "{$name}{$j}.{$right}"],
						"{$name}{$i}.{$alias}" => $db->value($path, ['type' => 'string']),
						"{$name}{$j}." . static::meta('key') => "{$name}{$i}.{$parent}"
					]
				];
			}

			$query['conditions'] = [
				static::_wrapCond($db, $name, $i, $left, $right)
			];

			$result = static::find('all', $query + ['return' => 'array']);

			$paths = array_values($paths);

			if (!isset($result[0]) ||
					($paths && $result[0][$alias] != $paths[count($paths) - 1]) ||
					(!$paths && $result[0][$alias] != $start)) {
				return false;
			}
			return $result;
		} elseif (is_object($ref)) {
			$self = static::_object();
			if ($ref instanceof $self->_classes['entity']) {
				$model = $ref->model();
				$id = $model::meta('key');
				$ref = [$class => $model, $id => $ref->$id];
			}
		}

		if ($ref && is_array($ref) && isset($ref[$class])) {
			$conditions = [];
			$model = $ref[$class];
			$id = $model::meta('key');
			if (isset($ref[$id])) {
				$conditions["{$name}0.{$key}"] = $ref[$id];
			} elseif (isset($ref[$key])) {
				$conditions["{$name}0.{$key}"] = $ref[$key];
			} else {
				return false;
			}
			$conditions["{$name}0.{$class}"] = $model;

			$constraints = (object) static::_wrapCond($db, $name, '0', $left, $right, false);

			$query = [
				'conditions' => $conditions,
				'fields' => [$name],
				'with' => [
					'Parent' => [
						'alias' => "{$name}0",
						'constraints' => $constraints
					]
				],
				'order' => "{$name}.{$left} DESC"
			];

			return static::find('all', $query + ['return' => 'array']);
		}
		return false;
	}

	/**
	 * Helper method which create a conditionnal array used be `AclNode::node()`
	 *
	 * @param object $db The database connection
	 * @param string $alias The alias of the model
	 * @param string $i An index
	 * @param string $left The left field name (MPTT)
	 * @param string $right The right field name (MPTT)
	 * @param boolean $escape If `true`, the field name will be escaped
	 * @return array The conditionnal array
	 */
	protected static function _wrapCond($db, $alias, $i, $left, $right, $escape = true) {
		$cond1 = "{$alias}{$i}.{$left}";
		$cond2 = "{$alias}{$i}.{$right}";

		if ($escape) {
			$cond1 = $db->name($cond1);
			$cond2 = $db->name($cond2);
		}

		return [
			"{$alias}.{$left}" => [
				'<=' => (object) $cond1
			],
			"{$alias}.{$right}" => [
				'>=' => (object) $cond2
			]
		];
	}
}
