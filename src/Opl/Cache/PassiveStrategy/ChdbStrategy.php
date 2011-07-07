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
use Opl\Cache\ReadableStrategyInterface;

/**
 * This strategy allows to read the data from the passive cache PHP extension
 * <tt>chdb</tt> (http://pecl.php.net/package/chdb).
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ChdbStrategy implements ReadableStrategyInterface
{
	/**
	 * The CHDB object representing the mapped file.
	 * @var chdb
	 */
	protected $chdb;
	
	/**
	 * Creates the strategy to map the specified shared memory file.
	 * 
	 * @param string $mappedFile The path to the shared memory file.
	 */
	public function __construct($mappedFile)
	{
		$this->chdb = new chdb($mappedFile);
	} // end __construct();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function get($key)
	{
		$value = $this->chdb->get($key);
		if(null === $value)
		{
			throw new KeyNotFoundException('The specified CHDB cache key does not exist: \''.$key.'\'.');
		}
		return $value;
	} // end get();
	
	/**
	 * @see ReadableStrategyInterface
	 */
	public function getLazy($key)
	{
		return $this->chdb->get($key);
	} // end getLazy();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function exists($key)
	{
		return null === $this->chdb->get($key);
	} // end exists();
} // end ChdbStrategy;