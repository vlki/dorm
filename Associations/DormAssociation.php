<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormAssociation
 *
 * @author vlki
 */
abstract class DormAssociation extends Object
{

	/** @var string */
	protected $id;

	/** @var DormTable */
	protected $tableLocal;

	/** @var DormTable */
	protected $tableForeign;

	/** @var DormColumn */
	protected $columnLocal;

	/** @var DormColumn */
	protected $columnForeign;

	/** @var array  Array of DormColumn objects */
	protected $columnsToJoin;

	/** @var bool */
	protected $silent = FALSE;


	public function __construct($id, DormTable $local, DormTable $foreign, $options = array())
	{
		$this->id = $id;
		$this->tableLocal = $local;
		$this->tableForeign = $foreign;

		$this->columnsToJoin = array();
		if (isset($options['columns'])) {
			foreach($options['columns'] as $k => $c) {
				$col = new DormColumn($this->tableForeign->table . '.' . $c);
				$col->setAlias(is_string($k) ? $k : $this->id . ucfirst($c));
				$this->columnsToJoin[] = $col;
			}
		}
	}

	public function getColumnsToSelect()
	{
		return $this->silent ? array() : $this->columnsToJoin;
	}

	public function silent()
	{
		$this->silent = TRUE;
	}

	public function loud()
	{
		$this->silent = FALSE;
	}

	public function getColumnLocal()
	{
		return $this->columnLocal;
	}

	public function getColumnForeign()
	{
		return $this->columnForeign;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getBind($requestedColumns)
	{
		return new DormBind;
	}

	public function getForeignTable($fkValue = NULL)
	{
		if (isset($fkValue)) {
			$table = clone $this->tableForeign;
			$table->setFilter(new DormFilter(array($this->columnForeign->getPlain() => $fkValue), NULL));
			return $table;
		} else {
			return $this->tableForeign;
		}
	}

}