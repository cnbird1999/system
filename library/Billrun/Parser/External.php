<?php

/**
 * @package         Billing
 * @copyright       Copyright (C) 2012-2013 S.D.O.C. LTD. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This defines an empty parser that does nothing but passing behavior to the outside plugins
 */
class Billrun_Parser_External extends Billrun_Parser_Base_Binary {

	static protected $type = "external";

	public function __construct($options) {
		parent::__construct($options);
		if ($this->getType() == "external") {
			throw new Exception('Billrun_Parser_External::__construct : cannot run without specifing a specific type for external parser, current type is :' . $this->getType());
		}
	}

	public function parse() {
		return Billrun_Factory::chain()->trigger('parseData', array($this->getType(), $this->getLine(), &$this));
	}

	public function parseField($data, $fileDesc) {
		return Billrun_Factory::chain()->trigger('parseSingleField', array($this->getType(), $data, $fileDesc, &$this));
	}

	public function parseHeader($data) {
		return Billrun_Factory::chain()->trigger('parseHeader', array($this->getType(), $data, &$this));
	}

	public function parseTrailer($data) {
		return Billrun_Factory::chain()->trigger('parseTrailer', array($this->getType(), $data, &$this));
	}

	/**
	 * Set the amount of bytes that were parsed on the last parsing run.
	 * @param $parsedBytes	Containing the count of the bytes that were processed/parsed.
	 */
	public function setLastParseLength($parsedBytes) {
		$this->parsedBytes = $parsedBytes;
	}

}

?>
