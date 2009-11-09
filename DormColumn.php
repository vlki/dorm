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
 * Base column.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormColumn extends Object
{

	/** @var string */
	protected $table;

	/** @var string */
	protected $column;

	/** @var string */
	protected $alias;

	public function __construct($columnName)
	{
		$parts = explode('.', $columnName);
		if (count($parts) != 2)
			throw new InvalidArgumentException('Column name must constist of two parts glued by a dot - table.column');
		$this->table = $parts[0];
		$this->column = $parts[1];
	}

	public function __toString()
	{
		return $this->table . $this->column . $this->alias;
	}

	public function getWithAlias()
	{
		return $this->getEscaped() . ($this->column !== '*' && $this->getAlias() !== $this->column ? ' AS [' . $this->getAlias() . ']' : '');
	}

	public function getPlain()
	{
	    return $this->table . '.' . $this->column;
	}

	public function getEscaped()
	{
	    return '[' . $this->table . '].[' . $this->column . ']';
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getColumn()
	{
	    return $this->column;
	}

	public function getAlias()
	{
		if (isset($this->alias))
			return $this->alias;

		return $this->table . ucfirst($this->column);
	}

	public function setAlias($alias)
	{
		$this->alias = $alias;
	}

	public function isGrouped()
	{
		return FALSE;
	}

}