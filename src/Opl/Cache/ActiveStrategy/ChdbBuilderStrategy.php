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
 * This strategy allows to build a cache file for the CHDB extension. <strong>DO NOT</strong>
 * use it as an ordinary active cache strategy, because it will not work!
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ChdbBuilderStrategy implements ReadableStrategyInterface, WritableStrategyInterface
{
	/**
	 * Where the mapped memory file will be stored.
	 * @var string
	 */
	protected $file;
	/**
	 * The cached data.
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Initializes the strategy. The argument specifies the file, where the mapped
	 * memory for CHDB will be exported.
	 * 
	 * @param string $outputFile The output file name.
	 */
	public function __construct($outputFile)
	{
		if(!extension_loaded('chdb'))
		{
			throw new StrategyException('chdb extension is not loaded.');
		}
		$this->file = $outputFile;
	} // end __construct();
	
	/**
	 * @see WritableStrategyInterface
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	} // end set();

	/**
	 * @see ReadableStrategyInterface
	 */
	public function get($key)
	{
		if(!isset($this->data[$key]))
		{
			throw new StrategyException('The chdb cache key does not exist: \''.$key.'\'.');
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
	
	/**
	 * Exports the cache to the mapped memory file usable by CHDB. The data is
	 * actually exported to a secondary file, which is then moved to the current
	 * location. It allows to avoid problems with reading by concurrent processes,
	 * while updating the cache memory. 
	 */
	public function save()
	{
		chdb_create($this->file.'.0', $this->data);
		rename($this->file, $this->file.'.0');
	} // end save();
} // end ChdbBuilderStrategy;