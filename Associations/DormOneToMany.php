<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OneToMany
 *
 * @author vlki
 */
class DormOneToMany extends DormAssociation
{

	public function __construct($id, $local, $foreign, $options = array())
	{
		parent::__construct($id, $local, $foreign, $options);

		$this->columnLocal = new DormColumn($this->tableLocal->table . '.' . (!isset($options['columnLocal']) ? 'id' : $options['columnLocal']));
		$this->columnForeign = new DormColumn($this->tableForeign->table . '.' . (!isset($options['columnForeign']) ? $this->id . 'Id' : $options['columnForeign']));
	}

	public function getJoins($requestedColumns)
	{
		$columns = $this->tableForeign->getBind($requestedColumns, $this->id, FALSE)->getColumns();
		$group = TRUE;
		foreach($columns as $c)
			if (!$c->isGrouped()) { $group = FALSE; break; }

		if ($group) {
			return array(new DormJoin('left', $this->columnLocal, $this->columnForeign));
		} else {
			return array();
		}
	}

	public function getBind($requestedColumns)
	{
		$columns = $this->tableForeign->getBind($requestedColumns, $this->id, FALSE)->getColumns();
		$group = TRUE;
		foreach($columns as $c)
			if (!$c->isGrouped()) { $group = FALSE; break; }

		$joins = array();
		if (count($columns) > 0)
			$joins = array(new DormJoin('left', $this->columnLocal, $this->columnForeign));

		$groups = array();
		if ($group)
			$groups = array(new DormGroup($this->tableLocal->table . '.' . $this->tableLocal->primaryKey));

		return new DormBind(array(), $joins, $groups);
	}

	public function filterAddData(&$data)
	{
		return;
	}

	public function afterAdd($pk, $data)
	{
		return;
	}

	public function filterUpdateData(&$data)
	{
		return;
	}

	public function afterUpdate($pk, $data)
	{
		return;
	}

	public function afterDelete($pk)
	{
		return;
	}
    
}