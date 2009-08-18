<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormFilter
 *
 * @author vlki
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