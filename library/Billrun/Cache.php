<?php

/**
 * @package         Billing
 * @copyright       Copyright (C) 2012-2013 S.D.O.C. LTD. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Billing cache class
 *
 * @package  Config
 * @since    0.5
 */
class Billrun_Cache {

	/**
	 * the config instance (for singleton)
	 * 
	 * @var Billrun_Cache
	 */
	protected static $instance = null;

	/**
	 * the cache container
	 * 
	 * @var Zend_Cache
	 */
	protected $cache;

	/**
	 * constant of the cache prefix key
	 * 
	 * @var string
	 */
	const cachePrefixKey = 'cache_id_prefix';

	/**
	 * constructor of the class
	 * protected for converting this class to singleton design pattern
	 */
	protected function __construct($cache) {
		$this->cache = $cache;
	}

	/**
	 * set cache prefix. used for grouping
	 * 
	 * @param string $prefix the prefix to set
	 * 
	 * @return void
	 */
	protected function setPrefix($prefix) {
		$this->cache->setOption(self::cachePrefixKey, $prefix);
	}

	/**
	 * get cache prefix. used for grouping
	 * 
	 * @return string the cache prefix
	 */
	protected function getPrefix() {
		return $this->cache->getOption(self::cachePrefixKey);
	}

	/**
	 * add label to the current cache prefix. used for categorization
	 * 
	 * @param string $addPrefix the string to add to the current prefix (automatically separate with underscore)
	 * 
	 * @return string previous prefix (before the adding)
	 */
	protected function addPrefix($addPrefix) {
		$previousPrefix = $this->getPrefix();
		$newPrefix = $previousPrefix . '_' . $addPrefix;
		$this->setPrefix($newPrefix);
		return $previousPrefix;
	}

	/**
	 * magic method for get cache value
	 * 
	 * @param string $key the key in the cache container
	 * 
	 * @return mixed the value in the cache
	 */
	public function __get($key) {
		return $this->get($key);
	}

	/**
	 * magic method for get cache value
	 * 
	 * @param string $key    the key in the cache container
	 * @param string $prefix the prefix used for grouping
	 * 
	 * @return mixed the value in the cache
	 */
	public function get($key, $prefix = null) {

		if (!is_null($prefix)) {
			$prevPrefix = $this->addPrefix($prefix);
		}

		$value = $this->cache->load($key);

		if (!is_null($prefix)) {
			// revert to the default prefix
			$this->setPrefix($prevPrefix);
		}

		return $value;
	}

	/**
	 * magic method for set cache value
	 * 
	 * @param string $key    the key in the cache container
	 * @param mixed  $value  the value in the cache container 
	 *                       if automatic_serialization is not set (set by default), require to be a string
	 * @return mixed the value in the cache for the key
	 */
	public function __set($key, $value) {
		return $this->set($key, $value);
	}

	/**
	 * method for set cache value with specific life time
	 * 
	 * @param string  $key       the key in the cache container
	 * @param mixed   $value     the value in the cache container 
	 *                           if automatic_serialization is not set (set by default), require to be a string
	 * @param string  $prefix    the prefix used for grouping
	 * @param int     $lifetime  if != false, set a specific lifetime for this cache record (null => infinite lifetime)
	 * 
	 * @return mixed the value in the cache for the key
	 */
	public function set($key, $value, $prefix = null, $lifetime = false) {

		if (!is_null($prefix)) {
			$prevPrefix = $this->addPrefix($prefix);
		}

		$saveStatus = $this->cache->save($value, $key, array(), $lifetime);

		if (!is_null($prefix)) {
			// revert to the default prefix
			$this->setPrefix($prevPrefix);
		}

		if ($saveStatus !== FALSE) {
			return $value;
		}

		return FALSE;
	}

	/**
	 * magic method to unset cache value by key
	 * 
	 * @param string $key the key in the cache container
	 * 
	 * @return mixed the value in the cache if success to unset, else false
	 */
	public function __unset($key) {
		return $this->remove($key);
	}

	/**
	 * remove cache value by key & prefix
	 * 
	 * @param string  $key the key in the cache container
	 * @param string  $prefix    the prefix used for grouping
	 * 
	 * @return mixed the value in the cache if success to unset, else false
	 */
	public function remove($key, $prefix = null) {
		$value = $this->get($key, $prefix);

		if (!is_null($prefix)) {
			$prevPrefix = $this->addPrefix($prefix);
		}

		if ($this->cache->remove($key) !== FALSE) {
			return $value;
		}

		if (!is_null($prefix)) {
			$this->setPrefix($prevPrefix);
		}

		return FALSE;
	}

	/**
	 * method to clean all cache tier
	 * 
	 * @return mixed the value in the cache
	 */
	public function clean() {
		return $this->cache->clean();
	}

	/**
	 * method to get the instance of the class (singleton)
	 */
	static public function getInstance(array $args = array()) {
		if (count($args) < 2) {
			Billrun_Factory::log('Cache is not configure well. Not enough args to instantiate', Zend_Log::ERR);
			return false;
		}

		// default value of automatic_serialization is true
		if (!isset($args[2])) {
			$args[2] = array();
			$args[2]['automatic_serialization'] = true;
		} elseif (!isset($args[2]['automatic_serialization'])) {
			$args[2]['automatic_serialization'] = true;
		}

		if (is_null(self::$instance)) {
			$cache = forward_static_call_array(array('Zend_Cache', 'factory'), $args);
			self::$instance = new self($cache);
		}

		return self::$instance;
	}

}
