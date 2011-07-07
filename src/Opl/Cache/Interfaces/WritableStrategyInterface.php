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
interface WritableStrategyInterface
{
	/**
	 * Stores the specified value under the given key. Note that in most caching
	 * strategies, the key must be a string.
	 * 
	 * @param mixed $key The data key.
	 * @param mixed $value The value
	 */
	public function set($key, $value);
} // end WritableStrategyInterface;