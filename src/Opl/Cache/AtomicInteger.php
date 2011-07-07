<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */
namespace Opl\Cache;
use Opl\Cache\Exception\KeyNotFoundException;

/**
 * Represents an atomic, distributed integer. The integer is guaranteed to be
 * updateable and shared across all the servers, providing the correct configuration.
 * As a backend, it uses Memcached. APC-based integers make no practical sense.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class AtomicInteger
{
	/**
	 * The integer name.
	 * @var string
	 */
	protected $name;
	/**
	 * The caching strategy
	 * @var AtomicIntegerStrategy 
	 */
	protected $strategy;
	
	/**
	 * Creates a new atomic integer with the given name. If the atomic
	 * integer has not been created yet, you will encounter lots
	 * of exceptions coming out of this class.
	 * 
	 * @param string $name The integer name.
	 * @param Memcached $memcached The storage.
	 */
	public function __construct($name, AtomicIntegerStrategy $strategy)
	{
		$this->name = (string) $name;
		$this->strategy = $strategy;
	} // end __construct();
	
	/**
	 * Increments the value by the given amount, and returns the new value.
	 * 
	 * @throws KeyNotFoundException
	 * @param integer $amount The amount, by which we increment the integer.
	 * @return integer The new value.
	 */
	public function increment($amount = 1)
	{
		return $this->strategy->increment($this->name, $amount);
	} // end increment();

	/**
	 * Decrements the value by the given amount, and returns the new value.
	 * 
	 * @throws KeyNotFoundException
	 * @param integer $amount The amount, by which we decrement the integer.
	 * @return integer The new value.
	 */
	public function decrement($amount = 1)
	{
		return $this->strategy->decrement($this->name, $amount);
	} // end decrement();
	
	/**
	 * Returns the current value of the integer.
	 * 
	 * @throws KeyNotFoundException
	 * @return integer 
	 */
	public function get()
	{
		return $this->strategy->get($this->name);
	} // end get();
	
	/**
	 * Stores the new value within the integer, and returns this value.
	 * 
	 * @param integer $value The stored value
	 * @return integer The stored value
	 */
	public function set($value)
	{
		$this->strategy->set($this->name, $this->value);
		return $value;
	} // end set();
} // end AtomicInteger;