<?php

/*******************************************************************************
 * HA2.php
 * year : 2014
 *
 * The HA2 algorithm is a combination between two algorithms (CBA and WBA), where
 * the first one is based on the language characters, and the second one is 
 * based on the language and common words. The HA2 algorithm consists of adding
 * the sum of frequencies of the two algorithms for each language, and 
 * consequently, the promising language is the one having the highest new sum.
 *
 * NOTE: the algorithm requires including CBA.h, WBA.h and defines.h header
 *       files to work perfectly.
 ******************************************************************************/
 

require_once('CBA.php');
require_once('WBA.php');


class HA2
{
	private $cba;
	private $wba;
	
	/*
     * Constructor, in which the reference characters and reference words are
     * loaded for each language.
     */ 
	public function HA2()
	{
		// instantiate the CBA and WBA classes
		$this->cba = new CBA();
		$this->wba = new WBA();
	}
	
	
	/*
      * Identification: function concerns the identification of an input text 
      * file, and it consists of adding the probabilities computed by the CBA
      * and WBA for each language.
      *
      * [output]: the number of the promising language (between 0-31).
      *
      * @param text: is the text to identify.
      */
	public function identification($text)
	{
		$cbaProbabilities = $this->cba->computeProbabilities($text); // retrieve probabilities computed by CBA
		$wbaProbabilities = $this->wba->computeProbabilities($text); // retrieve probabilities computed by WBA
		// add the probabilities for each language
		$probabilities = array();
		for($language=0; $language<NUMBER_LANGUAGES; $language++)
		{
			$probabilities[$language] = $cbaProbabilities[$language] + $wbaProbabilities[$language];
		}
		
		return $this->classification($probabilities);
	}
	
	
	/*
      * Classification: function to classify the input text to the 
      * corresponding language using the sum of probabilities (CBA + WBA).
      *
      * [output]: the number of the promising language (between 0-31).
      *
      * @param probabilities: a table of probabilities of all the languages.
      */ 
	public function classification($probabilities)
	{
		// retrieve the highest probability (sum of frequencies)
		$max = 0; // keeps the highest probability
		$promising_language = -1; // keeps the promising language
		for($language=0; $language<NUMBER_LANGUAGES; $language++)
		{
			if($probabilities[$language] > $max)
			{
				$max = $probabilities[$language];
				$promising_language = $language;
			}
		}
		
		// determine exactly the promising language by applying an order of classification
		if($promising_language != LNG_CHINESE && $promising_language != LNG_GREEK &&
		   $promising_language != LNG_HEBREW && $promising_language != LNG_HINDI &&
		   $promising_language != LNG_THAI)
		{
			if($max == $probabilities[LNG_ARABIC]) $promising_language = LNG_ARABIC;
			else if($max == $probabilities[LNG_PERSIAN]) $promising_language = LNG_PERSIAN;
			else if($max == $probabilities[LNG_URDU]) $promising_language = LNG_URDU;
			
			else if($max == $probabilities[LNG_BULGARIAN]) $promising_language = LNG_BULGARIAN;
			else if($max == $probabilities[LNG_RUSSIAN]) $promising_language = LNG_RUSSIAN;
			
			else if($max == $probabilities[LNG_ENGLISH]) $promising_language = LNG_ENGLISH;
			else if($max == $probabilities[LNG_DUTCH]) $promising_language = LNG_DUTCH;
			else if($max == $probabilities[LNG_INDONESIAN]) $promising_language = LNG_INDONESIAN;
			else if($max == $probabilities[LNG_MALAYSIAN]) $promising_language = LNG_MALAYSIAN;
			else if($max == $probabilities[LNG_LATIN]) $promising_language = LNG_LATIN;
			else if($max == $probabilities[LNG_ROMAN]) $promising_language = LNG_ROMAN;
			
			else if($max == $probabilities[LNG_FRENCH]) $promising_language = LNG_FRENCH;
			else if($max == $probabilities[LNG_ITALIAN]) $promising_language = LNG_ITALIAN;
			else if($max == $probabilities[LNG_IRISH]) $promising_language = LNG_IRISH;
			else if($max == $probabilities[LNG_SPANISH]) $promising_language = LNG_SPANISH;
			else if($max == $probabilities[LNG_PORTUGUESE]) $promising_language = LNG_PORTUGUESE;
			else if($max == $probabilities[LNG_ALBANIAN]) $promising_language = LNG_ALBANIAN;
			else if($max == $probabilities[LNG_CZECH]) $promising_language = LNG_CZECH;
			else if($max == $probabilities[LNG_FINNISH]) $promising_language = LNG_FINNISH;
			else if($max == $probabilities[LNG_HUNGARIAN]) $promising_language = LNG_HUNGARIAN;
			else if($max == $probabilities[LNG_SWEDISH]) $promising_language = LNG_SWEDISH;
			else if($max == $probabilities[LNG_GERMAN]) $promising_language = LNG_GERMAN;
			else if($max == $probabilities[LNG_NORWEGIAN]) $promising_language = LNG_NORWEGIAN;
			else if($max == $probabilities[LNG_DANISH]) $promising_language = LNG_DANISH;
			else if($max == $probabilities[LNG_ICELANDIC]) $promising_language = LNG_ICELANDIC;
			else if($max == $probabilities[LNG_TURKISH]) $promising_language = LNG_TURKISH;
			else if($max == $probabilities[LNG_POLISH]) $promising_language = LNG_POLISH;
		}
		
		return $promising_language;
	}
}

?>