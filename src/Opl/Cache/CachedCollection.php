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
use Opl\Cache\Interfaces\ReadableStrategyInterface;
use InvalidArgumentException;
use Iterator;

/**
 * Allows to represent a group of cached keys as a collection.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CachedCollection implements Iterator
{
	/**
	 * The collection name.
	 * @var string
	 */
	protected $name;
	/**
	 * The caching strategy.
	 * @var ReadableStrategyInterface
	 */
	protected $strategy;
	/**
	 * The retrieved keys.
	 * @var array
	 */
	protected $keyList;
	/**
	 * Is the collection active?
	 * @var boolean
	 */
	protected $isActive;
	/**
	 * For the iteration purposes.
	 * @var boolean
	 */
	protected $isValid;
	/**
	 * The optional rebuild handler, if the collection does not exist.
	 * @var callback
	 */
	protected $rebuilderCallback;
	
	/**
	 * Initializes the collection with the given name. The name represents
	 * a key, where the collection is saved in a cache.
	 * 
	 * @param string $name The collection name.
	 * @param ReadableStrategyInterface $strategy 
	 */
	public function __construct($name, ReadableStrategyInterface $strategy)
	{
		$this->name = (string)$name;
		$this->strategy = $strategy;

		$this->isActive = $strategy instanceof WritableStrategyInterface;
	} // end __construct();

	/**
	 * Allows to specify the rebuilder callback. Rebuilder is called every time
	 * the collection does not exist or is invalidated, and allows to reconstruct
	 * it. The rebuilder callback must accept the strategy object as the first
	 * and only argument, and return an array of cached keys that form the collection.
	 * 
	 * @param callback $rebuilderCallback
	 * @return CachedCollection Fluent interface.
	 */
	public function setRebuilder($rebuilderCallback)
	{
		if(!is_callable($rebuilderCallback))
		{
			throw new InvalidArgumentException('CachedCollection::setRebuilder() argument must be a valid callback.');
		}
		$this->rebuilderCallback = $rebuilderCallback;
		return $this;
	} // end setRebuilder();
	
	/**
	 * Returns the current rebuilder callback.
	 * 
	 * @return callback
	 */
	public function getRebuilder()
	{
		return $this->rebuilderCallback;
	} // end getRebuilder();
	
	/**
	 * Returns the keys from the cache. If the collection is not initialized, the
	 * rebuilder callback is called or an exception is thrown.
	 * 
	 * @throws CollectionException
	 * @internal
	 */
	protected function getKeys()
	{
		$this->keyList = $this->strategy->getLazy($this->name);
		if(!is_array($this->keyList))
		{
			if(null === $this->rebuilderCallback)
			{
				throw new CollectionException('The cached collection \''.$this->name.'\' cannot be initialized: no rebuilder callback defined.');
			}
			$callback = $this->rebuilderCallback;
			$this->keyList = $callback($strategy);
			if(!is_array($this->keyList))
			{
				throw new CollectionException('The cached collection \''.$this->name.'\' cannot be initialized.');
			}
		}
	} // end getKeys();

	/**
	 * @see Iterator
	 */
	public function rewind()
	{
		if(!is_array($this->keyList))
		{
			$this->getKeys();
		}
		reset($this->keyList);
		$this->isValid = true;
	} // end rewind();

	/**
	 * @see Iterator
	 */
	public function current()
	{
		return $this->strategy->get(current($this->keyList));
	} // end current();

	/**
	 * @see Iterator
	 */
	public function key()
	{
		return key($this->keyList);
	} // end key();
	
	/**
	 * @see Iterator
	 */
	public function next()
	{
		$this->isValid = (false !== next($this->keyList));
	} // end next();

	/**
	 * @see Iterator
	 */
	public function valid()
	{
		return $this->isValid;
	} // end valid();
} // end CachedCollection;