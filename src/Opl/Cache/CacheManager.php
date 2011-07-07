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

/**
 * This is the entry point for the caching platform. It allows to read and
 * write to the cache. The application cache is divided into several content
 * classes. Each class can have a different caching strategy assigned. The
 * strategy can be either passive (rebuilt by an external script) or active
 * (can be modified by the application itself).
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CacheManager
{
	const APPLICATION_CLASS = 0;
	const SYSTEM_CLASS = 1;
	const CONFIG_CLASS = 2;
	const SECURITY_CLASS = 3;

	const SMALL_DATA_CLASS = 10;
	const BIG_DATA_CLASS = 11;
	
	/**
	 * The list of strategies that allow to read the data.
	 * @var array
	 */
	protected $readStrategies = array();
	/**
	 * The list of strategies that allow to write the data.
	 * @var array
	 */
	protected $writeStrategies = array();
	
	/**
	 * Registers a new strategy for the specified data class. If the strategy implements
	 * <tt>WritableStrategyInterface</tt>, it is registered as an active cache strategy.
	 * 
	 * @param int $class The data class ID
	 * @param ReadableStrategyInterface|WritableStrategyInterface $strategy The strategy for the given class data.
	 * @return CacheManager Fluent interface
	 */
	public function registerStrategy($class, ReadableStrategyInterface $strategy)
	{
		if(!is_object($strategy))
		{
			throw new InvalidArgumentException('The second argument for CacheManager::registerStrategy() must be an object.');
		}
		if($strategy instanceof ReadableStrategyInterface)
		{
			$this->readStrategies[(int)$class] = $strategy;
		}
		if($strategy instanceof WritableStrategyInterface)
		{
			$this->writeStrategies[(int)$class] = $strategy;
		}
		return $this;
	} // end registerStrategy();
	
	/**
	 * Checks if the given data class uses a passive caching strategy.
	 * 
	 * @param int $class The data class ID
	 * @return boolean 
	 */
	public function isPassive($class)
	{
		$class = (int) $class;
		if(isset($this->readStrategies[$class]) && !isset($this->writeStrategies[$class]))
		{
			return true;
		}
		return false;
	} // end isPassive();

	/**
	 * Returns the strategy registered for the given class. An exception is
	 * thrown, if no strategy is registered for the class.
	 * 
	 * @param int $class The data class ID
	 * @return ReadableStrategyInterface
	 */
	public function getStrategy($class)
	{
		$class = (int) $class;
		if(!isset($this->readStrategies[$class]))
		{
			throw new OutOfRangeException('The specified cache data class does not have a defined strategy: '.$class);
		}
		return $this->readStrategies[$class];
	} // end getStrategy();

	/**
	 * Returns the strategy registered for the given class. An exception is
	 * thrown, if no strategy is registered for the class. Contrary to the
	 * <tt>getStrategy()</tt>, this method guarantees that the returned strategy
	 * is writable.
	 * 
	 * @param int $class The data class ID
	 * @return WritableStrategyInterface
	 */
	public function getWritableStrategy($class)
	{
		$class = (int) $class;
		if(!isset($this->writeStrategies[$class]))
		{
			throw new OutOfRangeException('The specified cache data class does not have a defined writable strategy: '.$class);
		}
		return $this->writeStrategies[$class];
	} // end getWritableStrategy();
	
	/**
	 * A convenience wrapper that allows to get the data from the cache. For performance
	 * reasons, you should use it only if you have a single key to read. Otherwise,
	 * please obtain the strategy object.
	 * 
	 * @param int $class The data class ID.
	 * @param mixed $key The cache key.
	 * @return mixed
	 */
	public function get($class, $key)
	{
		$strategy = $this->getStrategy($class);
		return $strategy->get($key);
	} // end get();

	/**
	 * A convenience wrapper that allows to check the data in the cache. For performance
	 * reasons, you should use it only if you have a single key to check. Otherwise,
	 * please obtain the strategy object.
	 * 
	 * @param int $class The data class ID.
	 * @param mixed $key The key.
	 * @return boolean
	 */
	public function exists($class, $key)
	{
		$strategy = $this->getStrategy($class);
		return $strategy->exists($key);
	} // end exists();
	
	/**
	 * A convenience wrapper that allows to save a new value in the cache. For performance
	 * reasons, you should use it only if you have a single value to modify. Otherwise,
	 * please obtain the strategy object.
	 * 
	 * @param int $class The data class ID.
	 * @param mixed $key The key.
	 * @param mixed $value The new value.
	 * @return CacheManager Fluent interface. 
	 */
	public function set($class, $key, $value)
	{
		$strategy = $this->getWritableStrategy($class);
		$strategy->set($key, $value);
		return $this;
	} // end set();
} // end CacheManager;