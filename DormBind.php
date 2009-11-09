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
 * Bind of columns, joins and group-bys from association.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormBind extends Object
{

	/** @var array  Array of DormColumn objects */
	protected $columns = array();

	/** @var array  Array of DormJoin objects */
	protected $joins = array();

	/** @var array  Array of DormGroup objects */
	protected $groups = array();


	public function __construct($columns = array(), $joins = array(), $groups = array())
	{
		$this->columns = $columns;
		$this->joins = $joins;
		$this->groups = $groups;
	}

	public function add(DormBind $bind)
	{
		$this->columns = array_merge($this->columns, $bind->getColumns());
		$this->joins = array_merge($this->joins, $bind->getJoins());
		$this->groups = array_merge($this->groups, $bind->getGroups());
	}

	public function getColumns()
	{
		array_unique_obj($this->columns);
		return $this->columns;
	}

	public function getJoins()
	{
		array_unique_obj($this->joins);
		return $this->joins;
	}

	public function getGroups()
	{
		array_unique_obj($this->groups);
		return $this->groups;
	}

}
