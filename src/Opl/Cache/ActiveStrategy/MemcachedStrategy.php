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
namespace Opl\Cache\ActiveStrategy;
use Opl\Cache\Exception\KeyNotFoundException;
use Opl\Cache\Interfaces\AtomicIntegerInterface;
use Opl\Cache\Interfaces\ReadableStrategyInterface;
use Opl\Cache\Interfaces\WritableStrategyInterface;

/**
 * This strategy uses Memcached as its backend.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class MemcachedStrategy implements ReadableStrategyInterface, WritableStrategyInterface, AtomicIntegerInterface
{
	/**
	 * The Memcached object.
	 * @var type 
	 */
	protected $memcached;
	/**
	 * Cached key prefix.
	 * @var string
	 */
	protected $prefix;
	/**
	 * The current timeout in seconds.
	 * @var int
	 */
	protected $timeout;
	
	/**
	 * Initializes the APC caching strategy.
	 * 
	 * @param Memcached $memcached The memcached object.
	 * @param string $prefix The cached key prefix.
	 * @param int $timeout The timeout in seconds or 0 to disable timeouts.
	 */
	public function __construct(Memcached $memcached, $prefix, $timeout)
	{
		$this->memcached = $memcached;
		$this->prefix = (string) $prefix;
		$this->timeout = (int) $timeout;
	} // end __construct();
	
	/**
	 * Returns the current APC cache timeout. 0 means that the timeouts are
	 * disabled.
	 * 
	 * @return int 
	 */
	public function getTimeout()
	{
		return $this->timeout;
	} // end getTimeout();
	
	/**
	 * Sets the new APC cache timeout.
	 * 
	 * @param int $timeout The new timeout in seconds or 0 to disable timeouts.
	 * @return APCStrategy Fluent interface.
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = (int) $timeout;
		return $this;
	} // end setTimeout();
	
	/**
	 * Returns the cached key prefix.
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	} // end getPrefix();
	
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
	 * @see WritableStrategyInterface
	 */
	public function set($key, $value)
	{
		$this->memcached->set($this->prefix.$key, $value, $this->timeout);
	} // end set();
	
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
	
	/**
	 * @see AtomicIntegerStrategy
	 */
	public function increment($key, $amount)
	{
		$value = $this->memcached->increment($key, $amount);
		if($this->memcached->getResultCode() == Memcached::RES_NOTFOUND)
		{
			throw new KeyNotFoundException('The Memcached cache key \''.$key.'\' is not defined.');
		}
		return $value;
	} // end increment();

	/**
	 * @see AtomicIntegerStrategy
	 */
	public function decrement($key, $amount)
	{
		$value = $this->memcached->decrement($key, $amount);
		if($this->memcached->getResultCode() == Memcached::RES_NOTFOUND)
		{
			throw new KeyNotFoundException('The Memcached cache key \''.$key.'\' is not defined.');
		}
		return $value;
	} // end decrement();	
} // end APCStrategy;