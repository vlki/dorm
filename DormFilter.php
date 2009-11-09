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
 * Basic filter which could be applied to table.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormFilter extends Object implements IDormFilter
{

	/** @var mixed */
	protected $conditions;

	/** @var mixed */
	protected $orderBy;

	public function __construct($conditions, $orderBy)
	{
		$this->conditions = $conditions;
		$this->orderBy = $orderBy;
	}
	
	public function getWhere()
	{
		return $this->conditions;
	}

	public function getOrderBy()
	{
		return $this->orderBy;
	}

}