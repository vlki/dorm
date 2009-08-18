<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormColumnComplex
 *
 * @author vlki
 */
class DormColumnComplex extends DormColumn
{

	/** @var string */
	protected $definition;

	/** @var bool */
	protected $grouped;

	public function __construct($columnDefinition)
	{
		$this->definition = $columnDefinition;

		if (strpos($this->definition, 'GROUP_CONCAT') !== FALSE
		    || strpos($this->definition, 'COUNT') !== FALSE) {
			$this->grouped = TRUE;
		}
	}

	public function __toString()
	{
		return $this->definition . $this->alias;
	}

	public function getWithAlias()
	{
	    return $this->definition . ' AS [' . $this->getAlias() . ']';
	}

	public function getPlain()
	{
		throw new LogicException('You cannot get plain column name from complex column.');
	}

	public function getEscaped()
	{
		return $this->definition;
	}

	public function getTable()
	{
		throw new LogicException('You cannot get table from complex column.');
	}

	public function getColumn()
	{
		throw new LogicException('You cannot get column from complex column.');
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
		return $this->grouped;
	}
    
}
