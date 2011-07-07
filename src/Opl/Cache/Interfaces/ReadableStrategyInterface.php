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
namespace Opl\Cache\Interfaces;

/**
 * The basic interface for the data storages supported by the cache
 * platform.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface ReadableStrategyInterface
{
	/**
	 * Returns the specified key value from the given storage. If the key
	 * does not exist, an exception should be thrown.
	 * 
	 * @throws StrategyException
	 * @param mixed $key The data key.
	 * @return mixed
	 */
	public function get($key);
	/**
	 * A modification of the <tt>get()</tt> method which returns NULL, if the
	 * key does not exist.
	 * 
	 * @param mixed $key The data key.
	 * @return mixed
	 */
	public function getLazy($key);
	
	/**
	 * Checks if the key exists in the storage.
	 * 
	 * @param mixed $key The data key.
	 * @return boolean
	 */
	public function exists($key);
} // end ReadableStrategyInterface;