<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormJoin
 *
 * @author vlki
 */
class DormJoin extends Object
{

	/** @var string  Type of join (== left|right|inner|outer) */
	protected $type;

	/** @var DormColumn  Column on the left side of association definition */
	protected $columnLeft;

	/** @var DormColumn  Column on the right side of association definition */
	protected $columnRight;

	public function __construct($type, DormColumn $columnLeft, DormColumn $columnRight) {
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