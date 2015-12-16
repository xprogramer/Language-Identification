<?php

/*******************************************************************************
 * CBA.php
 * year : 2014
 *
 * The CBA algorithm is based on identifying the language using the characters
 * of each language, where it consists of computing the sum of the character 
 * frequencies of each language, and consequently classifying the promising 
 * language corresponding to the one having the highest sum.
 *
 * NOTE: the algorithm requires including CHistogram.h, CStringUTF8.h, and 
 *       defines.h header files to work perfectly.
 ******************************************************************************/
 
require_once('CStringUTF8.php');
require_once('defines.php');


class CBA
{
	private $m_language_profiles;
	
	
	/*
     * Constructor, in which the reference characters are loaded for each 
     * language.
     */
	public function CBA()
	{
		// allocate the memory for the language profiles array
		$this->m_language_profiles = array();
		for($language=0; $language<NUMBER_LANGUAGES; $language++)
		{
			$this->m_language_profiles[$language] = new CHistogram();
		}
		
		$this->m_language_profiles[LNG_FRENCH]->loadCharsFromFile('./LID_tools/References/Chars/french.txt');
		$this->m_language_profiles[LNG_ENGLISH]->loadCharsFromFile('./LID_tools/References/Chars/english.txt');
		$this->m_language_profiles[LNG_ARABIC]->loadCharsFromFile('./LID_tools/References/Chars/arabic.txt');
		$this->m_language_profiles[LNG_RUSSIAN]->loadCharsFromFile('./LID_tools/References/Chars/russian.txt');
		$this->m_language_profiles[LNG_GERMAN]->loadCharsFromFile('./LID_tools/References/Chars/german.txt');		
		$this->m_language_profiles[LNG_ITALIAN]->loadCharsFromFile('./LID_tools/References/Chars/italian.txt');
		$this->m_language_profiles[LNG_GREEK]->loadCharsFromFile('./LID_tools/References/Chars/greek.txt');
		$this->m_language_profiles[LNG_SPANISH]->loadCharsFromFile('./LID_tools/References/Chars/spanish.txt');
		$this->m_language_profiles[LNG_PERSIAN]->loadCharsFromFile('./LID_tools/References/Chars/persian.txt');
		$this->m_language_profiles[LNG_CHINESE]->loadCharsFromFile('./LID_tools/References/Chars/chinese.txt');
		$this->m_language_profiles[LNG_TURKISH]->loadCharsFromFile('./LID_tools/References/Chars/turkish.txt');
		$this->m_language_profiles[LNG_FINNISH]->loadCharsFromFile('./LID_tools/References/Chars/finnish.txt');
		$this->m_language_profiles[LNG_HEBREW]->loadCharsFromFile('./LID_tools/References/Chars/hebrew.txt');
		$this->m_language_profiles[LNG_PORTUGUESE]->loadCharsFromFile('./LID_tools/References/Chars/portuguese.txt');
		$this->m_language_profiles[LNG_ROMAN]->loadCharsFromFile('./LID_tools/References/Chars/roman.txt');
		$this->m_language_profiles[LNG_POLISH]->loadCharsFromFile('./LID_tools/References/Chars/polish.txt');
		$this->m_language_profiles[LNG_HUNGARIAN]->loadCharsFromFile('./LID_tools/References/Chars/hungarian.txt');
		$this->m_language_profiles[LNG_DUTCH]->loadCharsFromFile('./LID_tools/References/Chars/dutch.txt');
		$this->m_language_profiles[LNG_IRISH]->loadCharsFromFile('./LID_tools/References/Chars/irish.txt');
		$this->m_language_profiles[LNG_SWEDISH]->loadCharsFromFile('./LID_tools/References/Chars/swedish.txt');
		$this->m_language_profiles[LNG_LATIN]->loadCharsFromFile('./LID_tools/References/Chars/latin.txt');
		$this->m_language_profiles[LNG_ICELANDIC]->loadCharsFromFile('./LID_tools/References/Chars/icelandic.txt');
		$this->m_language_profiles[LNG_HINDI]->loadCharsFromFile('./LID_tools/References/Chars/hindi.txt');
		$this->m_language_profiles[LNG_CZECH]->loadCharsFromFile('./LID_tools/References/Chars/czech.txt');
		$this->m_language_profiles[LNG_MALAYSIAN]->loadCharsFromFile('./LID_tools/References/Chars/malaysian.txt');
		$this->m_language_profiles[LNG_BULGARIAN]->loadCharsFromFile('./LID_tools/References/Chars/bulgarian.txt');
		$this->m_language_profiles[LNG_NORWEGIAN]->loadCharsFromFile('./LID_tools/References/Chars/norwegian.txt');
		$this->m_language_profiles[LNG_ALBANIAN]->loadCharsFromFile('./LID_tools/References/Chars/albanian.txt');
		$this->m_language_profiles[LNG_URDU]->loadCharsFromFile('./LID_tools/References/Chars/urdu.txt');
		$this->m_language_profiles[LNG_THAI]->loadCharsFromFile('./LID_tools/References/Chars/thai.txt');
		$this->m_language_profiles[LNG_INDONESIAN]->loadCharsFromFile('./LID_tools/References/Chars/indonesian.txt');
		$this->m_language_profiles[LNG_DANISH]->loadCharsFromFile('./LID_tools/References/Chars/danish.txt');
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
	
	
	private function uniord($string) 
	{
		$hex=0;
		for ($i=0; $i < strlen($string); $i++)
			$hex = ($hex<<8)|(ord($string[$i]));
		return ($hex);
	} 
	
	
	/*
     * ComputeProbabilities: function computes the language probabilities
     * which represent the sum of the characters frequencies.
     *
     * [output]: a table of probabilities corresponding to 32 languages.
     *
     * @param text: is the text to identify.
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
		$input_text->stripMultipleSeparator();
		$input_text->uppercaseToLowercase();
		
		// create a histogram of characters uni-grams
		$histogram = $input_text->histogramNgramChars(1);
		
		// compute the probabilities of the languages (sum of frequencies)
		$probabilities = Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		for($char=0; $char<count($histogram->m_vector); $char++)
		{
			// Chinese characters
			if($this->uniord($histogram->m_vector[$char]->m_element[0]) >= 0xE4B8A5 && $this->uniord($histogram->m_vector[$char]->m_element[0]) <= 0xE9BEA0)
			{
				$probabilities[LNG_CHINESE] += $histogram->m_vector[$char]->m_frequency;
			}
			
			// French characters	
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_FRENCH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_FRENCH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_FRENCH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
			
			// English characters			
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ENGLISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ENGLISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ENGLISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ARABIC]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ARABIC]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ARABIC] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_RUSSIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_RUSSIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_RUSSIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_GERMAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_GERMAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_GERMAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ITALIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ITALIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ITALIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_GREEK]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_GREEK]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_GREEK] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_PERSIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_PERSIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_PERSIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_SPANISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_SPANISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_SPANISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_TURKISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_TURKISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_TURKISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_FINNISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_FINNISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_FINNISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_HEBREW]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_HEBREW]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_HEBREW] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_PORTUGUESE]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_PORTUGUESE]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_PORTUGUESE] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ROMAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ROMAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ROMAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_POLISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_POLISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_POLISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_HUNGARIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_HUNGARIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_HUNGARIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_DUTCH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_DUTCH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_DUTCH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_IRISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_IRISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_IRISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_SWEDISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_SWEDISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_SWEDISH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_LATIN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_LATIN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_LATIN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ICELANDIC]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ICELANDIC]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ICELANDIC] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_HINDI]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_HINDI]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_HINDI] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_CZECH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_CZECH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_CZECH] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_MALAYSIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_MALAYSIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_MALAYSIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_BULGARIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_BULGARIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_BULGARIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_NORWEGIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_NORWEGIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_NORWEGIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_ALBANIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_ALBANIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_ALBANIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_URDU]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_URDU]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_URDU] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_THAI]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_THAI]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_THAI] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_INDONESIAN]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_INDONESIAN]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_INDONESIAN] += $histogram->m_vector[$char]->m_frequency;
					break;
				}
					
			for($char_ref=0; $char_ref<count($this->m_language_profiles[LNG_DANISH]->m_vector); $char_ref++)
				if(utf8_strcmp($this->m_language_profiles[LNG_DANISH]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
				{
					$probabilities[LNG_DANISH] += $histogram->m_vector[$char]->m_frequency;
					break;
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