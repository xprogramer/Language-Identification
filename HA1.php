<?php

/*******************************************************************************
 * HA2.php
 * year : 2014
 *
 * The HA1 algorithm is a sequential combination between two algorithms (CBA and WBA), 
 * where the first one is based on the language characters, and the second one is 
 * based on the language and common words. The HA1 algorithm consists of executing
 * firstly the CBA algorithm, and if the promising language is Chinese, Hebrew,
 * Greek, Thai or Hindi, we then return this language. Otherwise, the WBA algorithm 
 * is executed secondly.
 *
 * NOTE: the algorithm requires including CBA.h, WBA.h and defines.h header files
 *       to work perfectly.
 ******************************************************************************/
 
require_once('CBA.php');
require_once('WBA.php');


class HA1
{
	private $cba;
	private $wba;
	
	
	/*
     * Constructor, in which the reference characters and reference words are
     * loaded for each language.
     */ 
	public function HA1()
	{
		// instantiate the CBA and WBA classes
		$this->cba = new CBA();
		$this->wba = new WBA();
	}
	
	
	/*
     * identification: function to run the identification process.
     *
     * @param text: is the  text to identify.
	 */
	public function identification($text)
	{
		$promising_language = $this->cba->identification($text); // run the identification using CBA
		
		if($promising_language != LNG_CHINESE && $promising_language != LNG_GREEK &&
		   $promising_language != LNG_HEBREW && $promising_language != LNG_HINDI &&
		   $promising_language != LNG_THAI)
		{
			$promising_language = $this->wba->identification($text); // run the identification using WBA
		}
		
		return $promising_language;
	}
}

?>