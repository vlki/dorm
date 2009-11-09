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
 * Interface of basic association.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
interface IDormAssociation
{

	public function getForeignTable($fkValue = NULL);

	public function getBind($requestedColumns);

	public function getId();

	public function getColumnForeign();

	public function getColumnLocal();

	public function filterAddData(&$data);

	public function afterAdd($pk, $data);

	public function filterUpdateData(&$data);

	public function afterUpdate($pk, $data);

	public function afterDelete($pk);
}