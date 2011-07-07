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
 * This is a file-based strategy for low-volume websites and development purposes.
 * The keys within a single cached file have a common timeout, which means that
 * it invalidates the entire file. The cache file contains a serialized array of
 * keys and their values.
 * 
 * This strategy can be used as the data generator for the passive cache file
 * strategy.
 * 
 * On Linux systems, the file cache can make use of the advisory file locking
 * which allows to avoid race conditions.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class FileStrategy implements ReadableStrategyInterface, WritableStrategyInterface
{
	protected $filename;
	protected $data;
	protected $timeout;
	protected $isModified = false;
	protected $fileHandle = null;
	
	/**
	 * Initializes the file cache strategy.
	 * 
	 * @param string $filename The file, where the cache data is stored.
	 * @param int $timeout The timeout in seconds or 0 to disable.
	 */
	public function __construct($filename, $timeout)
	{
		$this->filename = (string)$filename;
		$this->timeout = (int) $timeout;
		if(PHP_OS == 'Linux')
		{
			// Linux has a good advisory locking.
			$f = fopen($filename, 'r');
			flock($f, LOCK_SH);
			$content = '';
			while(!feof($f))
			{
				$content .= fread($f, 4096);
			}
			flock($f, LOCK_UN);
			fclose($f);
		}
		else
		{
			// Other systems do not or it is unreliable.
			$content = @file_get_contents($this->filename);
		}
		if(false === $content)
		{
			$this->data = array();
			if($timeout > 0)
			{
				$this->data['_next_regeneration'] = time() + $this->timeout;
			}
		}
		else
		{
			$this->data = unserialize($content);
		}
		
		if(!empty($this->data['_next_regeneration']))
		{
			if(time() > $this->data['_next_regeneration'])
			{
				$this->data = array('_next_regeneration' => time() + $this->timeout);
			}
		}
	} // end __construct();

	public function __destruct()
	{
		if($this->isModified)
		{
			if($this->timeout > 0)
			{
				$this->data['_next_regeneration'] = time() + $this->timeout;
			}
			fwrite($this->fileHandle, serialize($this->data));
			if(PHP_OS == 'Linux')
			{
				flock($this->fileHandle, LOCK_UN);
			}
			fclose($this->fileHandle);
		}
	} // end __destruct();
	
	/**
	 * Returns the current file cache timeout. 0 means that the timeouts are
	 * disabled.
	 * 
	 * @return int 
	 */
	public function getTimeout()
	{
		return $this->timeout;
	} // end getTimeout();
	
	/**
	 * Sets the new file cache timeout.
	 * 
	 * @param int $timeout The new timeout in seconds or 0 to disable timeouts.
	 * @return FileStrategy Fluent interface.
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = (int) $timeout;
		$this->markModified();
		return $this;
	} // end setTimeout();
	
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
	
	/**
	 * @see WritableStrategyInterface
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
		$this->markModified();
	} // end set();

	/**
	 * Marks the cache as modified.
	 */
	public function markModified()
	{
		if(!is_resource($this->fileHandle))
		{
			$this->fileHandle = fopen($this->filename, 'w');
			if(PHP_OS == 'Linux')
			{
				flock($this->fileHandle, LOCK_EX);
			}
		}
		$this->isModified = true;
	} // end markModified();
} // end FileStrategy;