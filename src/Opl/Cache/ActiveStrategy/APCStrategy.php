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
use Opl\Cache\Interfaces\ReadableStrategyInterface;
use Opl\Cache\Interfaces\WritableStrategyInterface;

/**
 * This strategy uses APC as its backend. Note that APC does not allow inter-process
 * memory sharing which means that the cached data <tt>is not</tt> accessible from
 * i.e. the command-line interface.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class APCStrategy implements ReadableStrategyInterface, WritableStrategyInterface
{
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
	 * @param string $prefix The cached key prefix.
	 * @param int $timeout The timeout in seconds or 0 to disable timeouts.
	 */
	public function __construct($prefix, $timeout)
	{
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
	 * @see WritableStrategyInterface
	 */
	public function set($key, $value)
	{
		apc_store($this->prefix.$key, $value, $this->timeout);
	} // end set();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function get($key)
	{
		$success = false;
		$value = apc_fetch($this->prefix.$key, $success);
		if(!$success)
		{
			throw new StrategyException('The APC cache key does not exist: \''.$key.'\'.');
		}
		return $value;
	} // end get();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function getLazy($key)
	{
		$success = false;
		$value = apc_fetch($this->prefix.$key, $success);
		if(!$success)
		{
			return null;
		}
		return $value;	
	} // end getLazy();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function exists($key)
	{
		return apc_exists($this->prefix.$key);
	} // end exists();
} // end APCStrategy;