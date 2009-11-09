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
 * Basic group-by by given column name.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormGroup extends Object
{

	/** @var DormColumn */
	protected $column;

	public function __construct($columnName)
	{
		$this->column = new DormColumn($columnName);
	}

	public function appendTo(DibiFluent $query)
	{
		
		return $query->groupBy($this->column->getEscaped());
	}

	public function __toString() {
		return (string) $this->column;
	}

}