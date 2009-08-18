<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DormManyToOne
 *
 * @author vlki
 */
class DormManyToOne extends DormAssociation
{

	public function __construct($id, $local, $foreign, $options = array()) {
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
		if (isset($data[(string) $this->columnLocal])) {
			try {
				$this->tableForeign->getOne($data[(string) $this->columnLocal]);
			} catch(BadRequestException $e) {
				throw new InvalidArgumentException('Foreign record with primary key value ' . $data[(string) $this->columnLocal] . ' does not exist.');
			}
		}
	}

	public function afterAdd($pk, $data)
	{
		return;
	}

	public function filterUpdateData(&$data)
	{
		if (array_key_exists($this->columnLocal->getColumn(), $data)) {
			try {
				$this->tableForeign->getOne($data[(string) $this->columnLocal]);
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