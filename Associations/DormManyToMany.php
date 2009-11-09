<?php

/**
 * Simple Object Relational Mapping library. Is built over dibi
 * (http://dibiphp.com/) and simplifies the retrieving and associations
 * between tables. Is tightly connected to MySQL.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 * @link       http://github.com/vlki/dorm
 */

/**
 * Many to many (N:M) association. Implementation with one relational table
 * with two foreign keys.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormManyToMany extends DormAssociation
{

	public $db;

	protected $tableRelational;

	protected $columnLocalRelational;

	protected $columnForeignRelational;

	public function __construct($id, $local, $foreign, $options = array())
	{
		parent::__construct($id, $local, $foreign, $options);

		$this->columnLocal = new DormColumn($this->tableLocal->table . '.' . (!isset($options['columnLocal']) ? 'id' : $options['columnLocal']));
		$this->columnForeign = new DormColumn($this->tableForeign->table . '.' . (!isset($options['columnForeign']) ? 'id' : $options['columnForeign']));

		
		if (!isset($options['relationalTable'])) {
			$classes = array(
				$this->tableLocal->getTableName(),
				$this->tableForeign->getTableName(),
			);
			sort($classes);
			$this->tableRelational = strtolower($classes[0]) . strtolower($classes[1]);
		} else {
			$this->tableRelational = $options['relationalTable'];
		}

		$this->columnLocalRelational = new DormColumn($this->tableRelational . '.' . (!isset($options['relationalColumnLocal']) ? $this->tableLocal->getTableName() . 'Id' : $options['relationalColumnLocal']));
		$this->columnForeignRelational = new DormColumn($this->tableRelational . '.' . (!isset($options['relationalColumnForeign']) ? $this->tableForeign->getTableName() . 'Id' : $options['relationalColumnForeign']));

		$this->db = $this->tableLocal->db;
	}

	public function getBind($requestedColumns)
	{
		$columns = $this->tableForeign->getBind($requestedColumns, $this->id, FALSE)->getColumns();
		$group = TRUE;
		foreach($columns as $c)
			if (!$c->isGrouped()) { $group = FALSE; break; }

		$joins = array();
		if (count($columns) > 0)
			$joins = array(
				new DormJoin('left', $this->columnForeignRelational, $this->columnForeign),
        new DormJoin('left', $this->columnLocal, $this->columnLocalRelational),
			);

		$groups = array();
		if ($group)
			$groups = array(new DormGroup($this->tableLocal->table . '.' . $this->tableLocal->primaryKey));

		return new DormBind(array(), $joins, $groups);
	}

	public function getForeignTable($fkValue = NULL)
	{
		if (isset($fkValue)) {
			$foreignIds = $this->db->select('*')->from($this->tableRelational)
				->where(array($this->columnLocalRelational->getPlain() => $fkValue))
				->execute()->fetchPairs(NULL, (string) $this->columnForeignRelational->getColumn());

			$table = clone $this->tableForeign;
			$table->setFilter(new DormFilter(array($this->columnForeign->getPlain() . '%ex' => array('IN (%in', $foreignIds, ')')), NULL));
			return $table;
		} else {
			return $this->tableForeign;
		}
	}

	public function filterAddData(&$data)
	{
		unset($data[$this->columnForeignRelational->getColumn()]);
	}

	public function afterAdd($pk, $data)
	{
		if (array_key_exists($this->columnForeignRelational->getColumn(), $data)) {
			$this->addForeignKeyValues($pk, (array) $data[$this->columnForeignRelational->getColumn()]);
		}
	}

	public function filterUpdateData(&$data)
	{
		unset($data[$this->columnForeignRelational->getColumn()]);
	}

	public function afterUpdate($pk, $data)
	{
		if (array_key_exists($this->columnForeignRelational->getColumn(), $data)) {
			$this->db->delete($this->tableRelational)
				->where(array($this->columnLocalRelational->getPlain() => $pk))->execute();

			$this->addForeignKeyValues($pk, (array) $data[$this->columnForeignRelational->getColumn()]);
		}
	}

	public function afterDelete($pk)
	{
		return;
	}

	protected function addForeignKeyValues($pk, $fkValues)
	{
		if (is_array($fkValues) && count($fkValues) > 0) {
			$records = array();
			foreach($fkValues as $id) {
				$records[] = array(
					$this->columnLocalRelational->getColumn() => $pk,
					$this->columnForeignRelational->getColumn() => $id,
				);
			}
			$this->db->query("INSERT INTO [$this->tableRelational] %ex", $records);
		}
	}

}