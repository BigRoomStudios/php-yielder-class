<?php

namespace BRS\Generator;

/**
 * Yeilder 
 *
 * This class brings Generator-style Yeilding to PHP > 5.0.0
 *
 * Generators are only available in PHP >= 5.5.0, and this allows a developer to
 * mimick this behaviour in ealier versions of PHP.  This class was developed
 * after realizing that, from reading it's description on PHP.net, this is all
 * Generators realy are, just with different keywords and helpers.
 *
 * @example yeilder_usage.php How to use the Yeilder Class.
 *
 * @see http://php.net/manual/en/language.generators.overview.php
 * @see \Iterator http://php.net/manual/en/class.iterator.php
 * 
 * @author Michael Mulligan <michael@bigroomstudios.com> 
 */
class Yeilder implements Iterator {

	/**
	 * @var bool If we should stop on a Callback Return of NULL
	 * @see execute_callback()
	 */
    private $null_stop;
	/**
	 * @var callable The Generator Callback
	 * @see execute_callback()
	 */
    private $callback;
    /**
	 * @var int The current Position in the Generation cycle
	 * @see execute_callback()
	 * @see rewind()
	 * @see next()
	 * @see key()
	 */
	private $position;
	/**
	 * @var bool If the Generator is currently stopped (end of cycle)
	 * @see execute_callback()
	 * @see rewind()
	 * @see valid()
	 */
	private $stopped;
    /**
	 * @var mixed Internal Generator Data Storage
	 * @see rewind()
	 * @see next()
	 * @see current()
	 * @see valid()
	 */
	private $data;

	/**
	 * Construct 
	 *
	 * Sets up internal pointers and flags.
	 * 
	 * @param callable $callback The Callback that will be yeilding data.
	 * @param bool $stop_on_null (optional) Wether or not to stop on NULL.
	 * 
	 * @return void
	 * 
	 * @throws \BRS\Generator\Exception Yeilding Function must be callable.
	 * 
	 * @access public 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function __construct($callback, $stop_on_null = TRUE) {
        if(!is_callable($callback)) {
            throw new Exception('Yeilding Function must be callable.');
        }
        $this->null_stop = (bool) $stop_on_null;
        $this->callback  = $callback;
		$this->rewind();
    }
	
	/**
	 * Execute Callback 
	 *
	 * Executes the stored Callback, setting the internal Stopped flag dependent
	 * on wether or not the callback set it's own Stop flag, or if it returned
	 * NULL and we are set to stop on NULL.  Returns the result of the Callback.
	 * 
	 * @param void
	 * 
	 * @return mixed The Callback's return data if we are not stopping.
	 * 
	 * @access private 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
	private function execute_callback() {
		if(!$this->stopped) {
			$stop = FALSE;
			$data = call_user_func_array($this->callback, array($this->position, &$stop));
			$this->stopped = (bool) ($stop || (is_null($data) && $this->null_stop));
			if(!$this->stopped) {
				return $data;
			}
		}
		return NULL;
	}

	/**
	 * Rewind 
	 *
	 * Resets the internal pointer to 0, resets the Stopped flag, and clears the
	 * internal data storage.
	 *
	 * @see http://php.net/manual/en/class.iterator.php
	 * 
	 * @param void
	 * 
	 * @return void
	 * 
	 * @access public 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function rewind() {
		$this->position = 0;
		$this->stopped  = FALSE;
		$this->data     = NULL;
	}
	
	/**
	 * Next 
	 *
	 * Increments the internal pointer up by one, and refreshes the internal
	 * data storage.
	 *
	 * @see http://php.net/manual/en/class.iterator.php
	 * 
	 * @param void
	 * 
	 * @return void
	 * 
	 * @access public 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function next() {
		$this->position++;
		$this->data = NULL;
	}
    
	/**
	 * Current 
	 *
	 * @see http://php.net/manual/en/class.iterator.php
	 * 
	 * @param void
	 * 
	 * @return mixed The data for the current position.
	 * 
	 * @access public 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function current() {
		return $this->data;
	}
	
	/**
	 * Key 
	 *
	 * @see http://php.net/manual/en/class.iterator.php
	 * 
	 * @param void
	 * 
	 * @return int Position 
	 * 
	 * @access public	 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function key() {
		return $this->position;
	}

	/**
	 * Valid 
	 *
	 * If we are not already stopped, executes the Callback.  The execution
	 * method will set the internal Stopped flag, as well as the internal Data
	 * Store.  Then returns the status of the Stoped flag. If we are stopped, we
	 * are not at a valid Index.
	 *
	 * @see http://php.net/manual/en/class.iterator.php
	 * 
	 * @param void
	 * 
	 * @return bool 
	 * 
	 * @access public 
	 *
	 * @author Michael Mulligan <michael@bigroomstudios.com> 
	 */
    public function valid() {
		if(!$this->stopped) {
			$this->data = $this->execute_callback();
		}
        return !$this->stopped;
    }
}
