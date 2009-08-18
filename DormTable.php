<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormTable
 *
 * @author vlki
 */
abstract class DormTable extends Object
{

	/** @var string  Table definition - :prefix:tableName */
	public $table;

	/** @var string  Primary modifier */
	public $primaryModifier = '%i';

	/** @var string  Primary key */
	public $primaryKey = 'id';

	/** @var string  Column which will be the title of specified row. */
	protected $rowTitleColumn = 'id';

	/** @var DormFilter */
	protected $filter;

	/** @var array  Array of DormAssociation objects */
	protected $associations;

	/** @var array  Array of column definitions (alias => definition) */
	protected $columnsExtensional = array();


//============================================== Connection

	/**
	 * Connection getter. Is called as $this->db in inherited classes
	 * because of syntactic suger provided by Object. Must be overlayed in
	 * inherited classes.
	 *
	 * @return DibiConnection
	 */
	abstract public function getDb();



//============================================== Changing data

	/**
	 * Add record to db
	 *
	 * @param  mixed  data to add
	 * @return int    added rows
	 */
	public function add($data)
	{
		$this->db->begin(); // transaction
		try {
			$filteredData = $data;
			foreach($this->getAssociations() as $assoc) {
				$assoc->filterAddData($filteredData);
			}
			if (!empty($filteredData)) {
				$addedRows = $this->db->query("INSERT INTO [" . $this->table . "]", $filteredData);
				$pk = isset($data[$this->primaryKey]) ? $data[$this->primaryKey] : $this->db->insertId();
			} else {
				$addedRows = 0;
				$pk = NULL;
			}

			foreach($this->getAssociations() as $assoc) {
				$assoc->afterAdd($pk, $data);
			}
		} catch(Exception $e) {
			$this->db->rollback(); // transaction
			throw $e;
		}

		$this->db->commit(); // transaction
		return $addedRows;
	}

	/**
	* Update record in db with given primary key
	*
	* @param  mixed  primary key
	* @param  mixed  data to update
	* @return int    updated rows
	*/
	public function update($pk, $data)
	{
		// existence check
		$row = $this->getOne($pk);

		$this->db->begin(); // transaction
		try {
			$filteredData = $data;
			foreach($this->getAssociations() as $assoc) {
				$assoc->filterUpdateData($filteredData);
			}
			if (!empty($filteredData)) {
				$updatedRows = $this->db->update($this->table, $filteredData)
					->where(array($this->table . '.' . $this->primaryKey . $this->primaryModifier => $pk))->execute();
			} else {
				$updatedRows = 0;
			}

			foreach($this->getAssociations() as $assoc) {
				$assoc->afterUpdate($pk, $data);
			}
		} catch(Exception $e) {
			$this->db->rollback(); // transaction
			throw $e;
		}

		$this->db->commit(); // transaction
		return $updatedRows;
	}

	/**
	 * Delete record in db with given primary key
	 *
	 * @param  mixed  primary key
	 * @return int    deleted rows
	 */
	public function delete($pk)
	{
		// existence check
		$row = $this->getOne($pk);

		$this->db->begin(); // transaction
		try {
			$deletedRows = $this->db->delete($this->table)
				->where(array($this->table . '.' . $this->primaryKey . $this->primaryModifier => $pk))->execute();

			foreach($this->getAssociations() as $assoc) {
				$assoc->afterDelete($pk);
			}
		} catch(Exception $e) {
			$this->db->rollback(); // transaction

			if ($e instanceof DibiException && $e->getCode() == 1451) {
				throw new InvalidStateException("Item with primary key '$pk' from table '$this->table' cannot be deleted. There are items in association.");
			} else {
				throw $e;
			}
		}

		$this->db->commit(); // transaction
		return $deletedRows;
	}




//============================================== Retrieving data

	/**
	* Default record reader.
	*
	* @param  mixed  primary key
	* @return DibiRecord
	*/
	public function getOne($pk)
	{
		$row = $this->select()->where(array($this->table . '.' . $this->primaryKey . $this->primaryModifier => $pk))->execute()->setRowClass('DormRow')->fetch();
		if (!$row)
			throw new BadRequestException("Record not found.");
		return $row;
	}

	/**
	 * Returns array of records in an associative array, where keys are primary key values
	 * and values the row title column values. In default it chooses all records. Could
	 * be filtered by association.
	 *
	 * @return array  Associative array
	 */
	public function getAllPairs()
	{
		$query = $this->defaultGet();
		$query->orderBy('[' . $this->rowTitleColumn . ']', 'ASC');
		return $query->execute()->fetchPairs($this->primaryKey, $this->rowTitleColumn);
	}

	/**
	 * Returns array of records. In default it chooses all records. Could be filtered
	 * by association.
	 *
	 * @return array  Array of DibiResult objects
	 */
	public function getAll()
	{
		$results = $this->defaultGet()->execute()->setRowClass('DormRow')->fetchAll();
		foreach($results as &$row) {
			foreach($this->getAssociations() as $assocId => $assoc) {
				if ($assoc instanceof DormOneToMany || $assoc instanceof DormManyToMany) {
					$row[$assocId] = array($this, 'getAllForeign');
				}
			}
		}
		return $results;
	}

	/**
	 * Returns array of records in an array, where values are primary keys. In default it
	 * chooses all records. Could be filtered by association.
	 *
	 * @return array  Simple array
	 */
	public function getAllPrimaryKeys()
	{
		return $this->defaultGet()->execute()->fetchPairs(NULL, $this->primaryKey);
	}

	public function getAllForFilter()
	{
		return array('?' => '?') + $this->defaultGet()->execute()->fetchPairs(NULL, $this->rowTitleColumn);
	}

	public function getAllForeign($assocId, DibiRow $row)
	{
		$assoc = $this->getAssociation($assocId);
		return $assoc->getForeignTable($row->{$assoc->getColumnLocal()->getColumn()})->getAll();
	}




//============================================== Building query

	/**
	 * Default get. Implements association filter.
	 *
	 * @return DibiFluent
	 */
	protected function defaultGet()
	{
		$query = $this->select();
		if (isset($this->filter)) {
			$query->where($this->filter->getWhere());
			$query->orderBy($this->filter->getOrderBy());
		}
		return $query;
	}

	/**
	 * Default select from table.
	 *
	 * @return DibiFluent
	 */
	protected function select($requestedColumns = array())
	{
		$columns = $joins = $groups = array();
		if (count($requestedColumns) == 0) {
			$columns = array(new DormColumn($this->table . '.*'));
			foreach($this->columnsExtensional as $alias => $c) {
				$col = new DormColumnComplex($c);
				$col->setAlias($alias);
				$columns[] = $col;
			}
		} else {
			$bind = $this->getBind($requestedColumns);
			$columns = $bind->getColumns();
			$joins = $bind->getJoins();
			$groups = $bind->getGroups();

			// default column = this table's primary
			$col = new DormColumn($this->table . '.' . $this->primaryKey);
			$col->setAlias($this->primaryKey);
			$columns[] = $col;
		}

		return $this->buildDefaultSelect($columns, $joins, $groups);
	}

	/**
	 * Fluent select query builder.
	 *
	 * @param array  Array of DormColumn objects
	 * @param array  Array of DormJoin objects
	 * @return DibiFluent
	 */
	protected function buildDefaultSelect($columns, $joins, $groups)
	{
		$joins = array_reverse($joins);
		
		array_unique_obj($columns);
		array_unique_obj($joins);
		array_unique_obj($groups);

		$select = '';
		foreach($columns as $c) $select .= $c->getWithAlias() . ', ';
		$select = substr($select, 0, -2);

		$query = $this->db->select($select)->from($this->table);

		foreach($joins as $join) $query = $join->appendTo($query);
		foreach($groups as $group) $query = $group->appendTo($query);

		return $query;
	}

	public function getBind($requestedColumns, $assocId = '', $deep = TRUE)
	{
		$columns = $joins = $binds = array();
		foreach($requestedColumns as $k => $c) {
			if (is_string($k) && is_array($c)) { // columns through association
				if ($deep) {
					$assoc = $this->getAssociation($k);
					$binds[] = $assoc->getForeignTable()->getBind($c, $k);
					$binds[] = $assoc->getBind($c);
				}
				continue;

			} elseif (is_string($k) && is_string($c)) { // special complex column
				$col = new DormColumnComplex($c);
				$col->setAlias($assocId === '' ? $k : $assocId . ucfirst($k));

			} elseif (isset($this->columnsExtensional[$c])) { // saved complex column
				$col = new DormColumnComplex($this->columnsExtensional[$c]);
				$col->setAlias($assocId === '' ? $c : $assocId . ucfirst($c));

			} elseif ($c == 'count') { // special saved complex column
				$col = new DormColumnComplex('COUNT([' . $this->table . '].[' . $this->primaryKey . '])');
				$col->setAlias($assocId === '' ? $c : $assocId . ucfirst($c));

			} else { // common column
				$col = new DormColumn($this->table . '.' . $c);
				$col->setAlias($assocId === '' ? $c : $assocId . ucfirst($c));
			}

			$columns[] = $col;
		}

		$bind = new DormBind($columns, $joins, array());
		array_map(array($bind, 'add'), $binds);
		return $bind;
	}


//============================================== Filtering

	public function setFilter(DormFilter $filter)
	{
		$this->filter = $filter;
	}

	public function getFilter()
	{
		return $this->filter;
	}



//============================================== Setters & getters

	

	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * Return table name = table definition without prefix
	 *
	 * @return string
	 */
	public function getTableName()
	{
		$pos = strrpos($this->table, ':');
		if ($pos) {
			return substr($this->table, $pos + 1);
		} else {
			return $this->table;
		}
	}

	public function getAssociations()
	{
		if (!isset($this->associations)) {
			$associations = $this->createAssociations();
			foreach($associations as $assocId => $assoc) {
				$assoc->setId($assocId);
			}
			$this->associations = $associations;
		} elseif (is_array($this->associations) && count($this->associations) > 0 && is_array(current($this->associations))) {
			$this->generateAssociations($this->associations);
		}
		return $this->associations;
	}

	protected function createAssociations()
	{
		return array();
	}

	protected function generateAssociations($definition)
	{
		$associations = array();
		foreach($definition as $assocType => $assocs) {
			foreach($assocs as $assocId => $options) {
				$conventionalTableName = ucfirst($assocId) . 'Model';
				$foreignTableName = class_exists($conventionalTableName) ? $conventionalTableName : $options['foreignTable'];
				$associations[$assocId] = new $assocType($assocId, $this, new $foreignTableName, $options);
			}
		}
		$this->associations = $associations;
	}

	public function getAssociation($id)
	{
		$associations = $this->getAssociations();
		if (!isset($associations[$id]))
			throw new InvalidArgumentException("Association from table '$this->table' identified by '$id' does not exist.");
		return $associations[$id];
	}

}