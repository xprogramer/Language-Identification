<?php

/*******************************************************************************
 * WBA.php
 * year : 2014
 *
 * The WBA algorithm is based on identifying the language using the common words
 * of each language, where it consists of computing the sum of the word 
 * frequencies of each language, and consequently classifying the promising 
 * language corresponding to the one having the highest sum.
 *
 * NOTE: the algorithm requires including CHistogram.h, CStringUTF8.h, and 
 *       defines.h header files to work perfectly.
 ******************************************************************************/

require_once('CStringUTF8.php');
require_once('defines.php');


class WBA
{
	private $m_language_profiles;
	
	
	/*
     * Constructor, in which the reference words are loaded for each language.
     */
	public function WBA()
	{
		// allocate the memory for the language profiles array
		$this->m_language_profiles = array();
		for($language=0; $language<NUMBER_LANGUAGES; $language++)
		{
			$this->m_language_profiles[$language] = new CHistogram();
		}
		
		$this->m_language_profiles[LNG_FRENCH]->loadWordsFromFile('./LID_tools/References/Common_Words/french.txt');
		$this->m_language_profiles[LNG_ENGLISH]->loadWordsFromFile('./LID_tools/References/Common_Words/english.txt');
		$this->m_language_profiles[LNG_ARABIC]->loadWordsFromFile('./LID_tools/References/Common_Words/arabic.txt');
		$this->m_language_profiles[LNG_RUSSIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/russian.txt');
		$this->m_language_profiles[LNG_GERMAN]->loadWordsFromFile('./LID_tools/References/Common_Words/german.txt');		
		$this->m_language_profiles[LNG_ITALIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/italian.txt');
		$this->m_language_profiles[LNG_GREEK]->loadWordsFromFile('./LID_tools/References/Common_Words/greek.txt');
		$this->m_language_profiles[LNG_SPANISH]->loadWordsFromFile('./LID_tools/References/Common_Words/spanish.txt');
		$this->m_language_profiles[LNG_PERSIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/persian.txt');
		$this->m_language_profiles[LNG_CHINESE]->loadWordsFromFile('./LID_tools/References/Common_Words/chinese.txt');
		$this->m_language_profiles[LNG_TURKISH]->loadWordsFromFile('./LID_tools/References/Common_Words/turkish.txt');
		$this->m_language_profiles[LNG_FINNISH]->loadWordsFromFile('./LID_tools/References/Common_Words/finnish.txt');
		$this->m_language_profiles[LNG_HEBREW]->loadWordsFromFile('./LID_tools/References/Common_Words/hebrew.txt');
		$this->m_language_profiles[LNG_PORTUGUESE]->loadWordsFromFile('./LID_tools/References/Common_Words/portuguese.txt');
		$this->m_language_profiles[LNG_ROMAN]->loadWordsFromFile('./LID_tools/References/Common_Words/roman.txt');
		$this->m_language_profiles[LNG_POLISH]->loadWordsFromFile('./LID_tools/References/Common_Words/polish.txt');
		$this->m_language_profiles[LNG_HUNGARIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/hungarian.txt');
		$this->m_language_profiles[LNG_DUTCH]->loadWordsFromFile('./LID_tools/References/Common_Words/dutch.txt');
		$this->m_language_profiles[LNG_IRISH]->loadWordsFromFile('./LID_tools/References/Common_Words/irish.txt');
		$this->m_language_profiles[LNG_SWEDISH]->loadWordsFromFile('./LID_tools/References/Common_Words/swedish.txt');
		$this->m_language_profiles[LNG_LATIN]->loadWordsFromFile('./LID_tools/References/Common_Words/latin.txt');
		$this->m_language_profiles[LNG_ICELANDIC]->loadWordsFromFile('./LID_tools/References/Common_Words/icelandic.txt');
		$this->m_language_profiles[LNG_HINDI]->loadWordsFromFile('./LID_tools/References/Common_Words/hindi.txt');
		$this->m_language_profiles[LNG_CZECH]->loadWordsFromFile('./LID_tools/References/Common_Words/czech.txt');
		$this->m_language_profiles[LNG_MALAYSIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/malaysian.txt');
		$this->m_language_profiles[LNG_BULGARIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/bulgarian.txt');
		$this->m_language_profiles[LNG_NORWEGIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/norwegian.txt');
		$this->m_language_profiles[LNG_ALBANIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/albanian.txt');
		$this->m_language_profiles[LNG_URDU]->loadWordsFromFile('./LID_tools/References/Common_Words/urdu.txt');
		$this->m_language_profiles[LNG_THAI]->loadWordsFromFile('./LID_tools/References/Common_Words/thai.txt');
		$this->m_language_profiles[LNG_INDONESIAN]->loadWordsFromFile('./LID_tools/References/Common_Words/indonesian.txt');
		$this->m_language_profiles[LNG_DANISH]->loadWordsFromFile('./LID_tools/References/Common_Words/danish.txt');
	}
	
	
	/*
     * identification: function to run the identification process.
     *
     * @param text: is the  text to identify.
	 */
	public function identification($text)
	{
		$probabilities = $this->computeProbabilities($text);
		return $this->classification($probabilities);
	}
	
	
	/*
     * ComputeProbabilities: function computes the language probabilities
     * which represent the sum of the words frequencies.
     *
     * [output]: a table of probabilities corresponding to 32 languages.
     *
     * @param text: is the  text to identify.
	 */
	public function computeProbabilities($text)
	{
		$input_text = new CStringUTF8();
		$input_text->utf8_TextToArray($text);
		
		// pre-process the text
		$input_text->stripUserTags();
		$input_text->stripHashTags();
		$input_text->stripURLs();
		$input_text->stripUnusedChars();
		$input_text->separateWords();
		$input_text->stripMultipleSeparator();
		$input_text->uppercaseToLowercase();
		
		// extract a list of words from the text
		$words = $input_text->getWords();
		
		// compute the probabilities of the languages (sum of frequencies)
		$probabilities = Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		for($word=0; $word<count($words); $word++)
		{
			for($language=0; $language<NUMBER_LANGUAGES; $language++)
			{
				for($word_ref=0; $word_ref<count($this->m_language_profiles[$language]->m_vector); $word_ref++)
					if(utf8_strcmp($this->m_language_profiles[$language]->m_vector[$word_ref]->m_element, $words[$word]))
					{
						$probabilities[$language] ++;
						break;
					}
			}
		}
		
		return $probabilities;
	}
	
	
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