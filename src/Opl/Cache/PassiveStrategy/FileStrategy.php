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
use Opl\Cache\Exception\StrategyException;
use Opl\Cache\Exception\KeyNotFoundException;
use Opl\Cache\Interfaces\ReadableStrategyInterface;

/**
 * This strategy implements the passive cache paradigm on a plain text file.
 * For real production environments, you should consider using CHDB and/or
 * Memcached, leaving this strategy for development purposes.
 * 
 * The cache file is a plain text file with a serialized array of keys and
 * their values.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class FileStrategy implements ReadableStrategyInterface
{
	/**
	 * The cached data.
	 * @var array
	 */
	protected $data;
	
	/**
	 * Reads the data from the cache file.
	 * 
	 * @param string $filename 
	 */
	public function __construct($filename)
	{
		$content = @file_get_contents($filename);
		if(false === $content)
		{
			throw new StrategyException('Cannot open file cache \''.$filename.'\' for reading.');
		}
		$this->data = unserialize($content);
	} // end __construct();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function get($key)
	{
		if(!isset($this->data[$key]))
		{
			throw new KeyNotFoundException('The specified file cache key does not exist: \''.$key.'\'.');
		}
		return $this->data[$key];
	} // end get();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function getLazy($key)
	{
		if(!isset($this->data[$key]))
		{
			return null;
		}
		return $this->data[$key];
	} // end getLazy();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function exists($key)
	{
		return isset($this->data[$key]);
	} // end exists();
} // end FileStrategy;