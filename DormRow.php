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
 * Extended DibiRow. Allow using callbacks in Value Objects to other objects
 * in association -> lazy loading.
 *
 * @author     Jan Vlcek
 * @copyright  Copyright (c) 2009 Jan Vlcek
 * @license    New BSD License
 */
class DormRow extends DibiRow
{

	/** @var array */
	protected $callbacks = array();

	/** @var array */
	protected $results = array();

	public function offsetSet($index, $newval)
	{
		$index = (string) $index;
		if (is_callable($newval)) {
			if (isset($this->results[$index]))
				throw new LogicException('Results callback cannot be changed when results have been already populated.');
			$this->callbacks[$index] = $newval;
		} else {
			parent::offsetSet($index, $newval);
		}
	}

	public function offsetExists($index)
	{
		$index = (string) $index;
		return isset($this->callbacks[$index]) ? TRUE : parent::offsetExists($index);
	}

	public function offsetGet($index)
	{
		$index = (string) $index;
		if (isset($this->callbacks[$index])) {
			if (!isset($this->results[$index]))
				$this->results[$index] = call_user_func_array($this->callbacks[$index], array($index, $this));

			return $this->results[$index];
		} else {
			return parent::offsetGet($index);
		}
	}

	public function offsetUnset($index)
	{
		$index = (string) $index;
		if (isset($this->callbacks[$index])) {
			unset($this->callbacks[$index]);
			if (isset($this->results[$index]))
				unset($this->results[$index]);
		} else {
			parent::offsetUnset($index);
		}
	}

}