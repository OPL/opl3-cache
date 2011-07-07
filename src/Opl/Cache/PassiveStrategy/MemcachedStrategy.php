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
namespace Opl\Cache\PassiveStrategy;
use Opl\Cache\Exception\KeyNotFoundException;
use Opl\Cache\Interfaces\ReadableStrategyInterface;

/**
 * This strategy allows to read the data from Memcached. It implements the
 * passive cache paradigm.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class MemcachedStrategy implements ReadableStrategyInterface
{
	protected $memcached;
	protected $lastName;
	protected $lastValue;

	public function __construct(Memcached $memcached)
	{
		$this->memcached = $memcached;
	} // end __construct();
	
	/**
	 * Returns the used Memcached object.
	 * 
	 * @return Memcached
	 */
	public function getMemcached()
	{
		return $this->memcached;
	} // end getMemcached();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function get($key)
	{
		if(null !== $this->lastName)
		{
			if($key == $this->lastName)
			{
				$this->lastName = null;
				return $this->lastValue;
			}
		}
		$value = $this->chdb->get($key);
		if($this->memcached->getResultCode() == Memcached::RES_NOTFOUND)
		{
			throw new KeyNotFoundException('The specified Memcached cache key does not exist: \''.$key.'\'.');
		}
		return $value;
	} // end get();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function getLazy($key)
	{
		return $this->memcached->get($key);
	} // end getLazy();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function exists($key)
	{
		$this->lastName = $key;
		$this->lastValue = $this->chdb->get($key);
		return ($this->memcached->getResultCode() == Memcached::RES_NOTFOUND);
	} // end exists();
} // end MemcachedStrategy;