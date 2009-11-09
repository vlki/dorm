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
 * Many to one association.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormManyToOne extends DormAssociation
{

	public function __construct($id, $local, $foreign, $options = array())
	{
		parent::__construct($id, $local, $foreign, $options);

		$this->columnLocal = new DormColumn($this->tableLocal->table . '.' . (!isset($options['columnLocal']) ? $this->id . 'Id' : $options['columnLocal']));
		$this->columnForeign = new DormColumn($this->tableForeign->table . '.' . (!isset($options['columnForeign']) ? 'id' : $options['columnForeign']));
	}

	public function getBind($requestedColumns)
	{
		return new DormBind(array(), array(new DormJoin('left', $this->columnLocal, $this->columnForeign)), array());
	}

	public function filterAddData(&$data)
	{
		if (array_key_exists($this->columnLocal->getColumn(), $data) && $data[$this->columnLocal->getColumn()] !== NULL) {
			try {
				$this->tableForeign->getOne($data[$this->columnLocal->getColumn()]);
			} catch(BadRequestException $e) {
				throw new InvalidArgumentException('Foreign record with primary key value ' . $data[$this->columnLocal->getColumn()] . ' does not exist.');
			}
		}
	}

	public function afterAdd($pk, $data)
	{
		return;
	}

	public function filterUpdateData(&$data)
	{
		if (array_key_exists($this->columnLocal->getColumn(), $data) && $data[$this->columnLocal->getColumn()] !== NULL) {
			try {
				$this->tableForeign->getOne($data[$this->columnLocal->getColumn()]);
			} catch(BadRequestException $e) {
				throw new InvalidArgumentException('Foreign record with primary key value ' . $data[$this->columnLocal->getColumn()] . ' does not exist.');
			}
		}
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