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
 * Basic join of tables.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormJoin extends Object
{

	/** @var string  Type of join (== left|right|inner|outer) */
	protected $type;

	/** @var DormColumn  Column on the left side of association definition */
	protected $columnLeft;

	/** @var DormColumn  Column on the right side of association definition */
	protected $columnRight;

	public function __construct($type, DormColumn $columnLeft, DormColumn $columnRight)
	{
		$this->type = $type;
		$this->columnLeft = $columnLeft;
		$this->columnRight = $columnRight;
	}

	public function __toString()
	{
		return $this->type . md5($this->columnLeft) . md5($this->columnRight);
	}

	public function appendTo(DibiFluent $query)
	{
		return $query->{$this->type . 'Join'}($this->columnRight->getTable())->on($this->columnLeft->getEscaped() . ' = ' . $this->columnRight->getEscaped());
	}

}