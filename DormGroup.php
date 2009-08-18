<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormGroup
 *
 * @author vlki
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