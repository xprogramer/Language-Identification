<?php

/*******************************************************************************
 * SCA.php
 * year : 2014
 *
 * The SCA algorithm is based on identifying the language using the special 
 * characters of each language, and it is similar to CBA. However, the 
 * classification is performed on two different stages.
 *
 * NOTE: the algorithm requires including CHistogram.h, CStringUTF8.h, and 
 *       defines.h header files to work perfectly.
 ******************************************************************************/
 
require_once('CStringUTF8.php');
require_once('defines.php');


class SCA
{
	private $m_language_profiles;
	private $m_class_profiles;
	
	
	/*
     * Constructor, in which the reference characters are loaded for each 
     * class of languages.
     */
	public function SCA()
	{
		// allocate the memory for the classes profiles array
		$this->m_class_profiles = array();
		for($_class=0; $_class<NUMBER_CLASSES; $_class++)
		{
			$this->m_class_profiles[$_class] = new CHistogram();
		}
		
		$this->m_class_profiles[0]->loadCharsFromFile('./LID_tools/References/Classes/class_1.txt');
		$this->m_class_profiles[1]->loadCharsFromFile('./LID_tools/References/Classes/class_2.txt');
		$this->m_class_profiles[2]->loadCharsFromFile('./LID_tools/References/Classes/class_3.txt');
		$this->m_class_profiles[3]->loadCharsFromFile('./LID_tools/References/Classes/class_4.txt');
		$this->m_class_profiles[4]->loadCharsFromFile('./LID_tools/References/Classes/class_5.txt');
		$this->m_class_profiles[5]->loadCharsFromFile('./LID_tools/References/Classes/class_6.txt');
		$this->m_class_profiles[6]->loadCharsFromFile('./LID_tools/References/Classes/class_7.txt');
		$this->m_class_profiles[7]->loadCharsFromFile('./LID_tools/References/Classes/class_8.txt');
	}
	
	
	/*
     * identification: function to run the identification process.
     *
     * @param text: is the  text to identify.
	 */
	public function identification($text)
	{
		$promising_class = $this->classIdentification($text);
		
		if($promising_class == 0) return LNG_CHINESE;
		else if($promising_class == 1) return LNG_GREEK;
		else if($promising_class == 5) return LNG_HEBREW;
		else if($promising_class == 6) return LNG_HINDI;
		else if($promising_class == 7) return LNG_THAI;
		
		else return $this->languageIdentification($text,$promising_class);
	}
	
	
	private function uniord($string) 
	{
		$hex=0;
		for ($i=0; $i < strlen($string); $i++)
			$hex = ($hex<<8)|(ord($string[$i]));
		return ($hex);
	} 
	
	
	/*
     * classIdentification: function consists of identifying the languages class.
     *
     * @param text: is the  text to identify.
	 */
	public function classIdentification($text)
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
		$probabilities = Array(0,0,0,0,0,0,0,0);
		for($char=0; $char<count($histogram->m_vector); $char++)
		{
			// Chinese
			if($this->uniord($histogram->m_vector[$char]->m_element[0]) >= 0xE4B8A5 && $this->uniord($histogram->m_vector[$char]->m_element[0]) <= 0xE9BEA0)
			{
				$probabilities[0] += $histogram->m_vector[$char]->m_frequency;
			}
			// otherwise
			else for($_class=0; $_class<NUMBER_CLASSES; $_class++)
			{			
				for($char_ref=0; $char_ref<count($this->m_class_profiles[$_class]->m_vector); $char_ref++)
					if(utf8_strcmp($this->m_class_profiles[$_class]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
					{
						$probabilities[$_class] += $histogram->m_vector[$char]->m_frequency;
						break;
					}
			}
		}
		
		// retrieve the highest probability (sum of frequencies)
		$max = 0; // keeps the highest probability
		$promising_class = -1; // keeps the promising class
		for($_class=0; $_class<NUMBER_CLASSES; $_class++)
		{
			if($probabilities[$_class] > $max)
			{
				$max = $probabilities[$_class];
				$promising_class = $_class;
			}
		}
		
		return $promising_class;
	}
	
	
	/*
     * classIdentification: function consists of identifying exactly the language.
     *
     * @param text: is the  text to identify.
	 * @param promising_class: is the promising class returned by the previous step.
	 */
	public function languageIdentification($text, $promising_class)
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
		
		if($promising_class == 3)
		{
			$this->m_language_profiles = new CHistogram();
            $this->m_language_profiles->loadCharsFromFile("./LID_tools/References/Languages/russian.txt");
			 
			$probabilities = 0;
			
			for($char=0; $char<count($histogram->m_vector); $char++)
			{
				for($char_ref=0; $char_ref<count($this->m_language_profiles->m_vector); $char_ref++)
					if(utf8_strcmp($this->m_language_profiles->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
					{
						$probabilities += $histogram->m_vector[$char]->m_frequency;
						break;
					}
			}
			
			if($probabilities > 0) return LNG_RUSSIAN;
			else return LNG_BULGARIAN;
		}
		
		else if($promising_class == 2)
		{
			$this->m_language_profiles = array();
			for($language=0; $language<2; $language++) $this->m_language_profiles[$language] = new CHistogram();
			
            $this->m_language_profiles[0]->loadCharsFromFile("./LID_tools/References/Languages/persian.txt");
			$this->m_language_profiles[1]->loadCharsFromFile("./LID_tools/References/Languages/urdu.txt");
			
			$probabilities = Array(0,0);
			
			for($language=0; $language<2; $language++)
				for($char=0; $char<count($histogram->m_vector); $char++)
				{
					for($char_ref=0; $char_ref<count($this->m_language_profiles[$language]->m_vector); $char_ref++)
						if(utf8_strcmp($this->m_language_profiles[$language]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
						{
							$probabilities[$language] += $histogram->m_vector[$char]->m_frequency;
							break;
						}
				}
			
			
			// retrieve the highest probability (sum of frequencies)
			$max = 0; // keeps the highest probability
			$promising_language = -1; // keeps the promising language
			for($language=0; $language<2; $language++)
			{
				if($probabilities[$language] > $max)
				{
					$max = $probabilities[$language];
					$promising_language = $language;
				}
			}
			
			if($promising_language == 0) return LNG_PERSIAN;
			else if($promising_language == 1) return LNG_URDU;
			else return LNG_ARABIC;
		}
		
		else if($promising_class == 4)
		{
			$this->m_language_profiles = array();
			for($language=0; $language<16; $language++) $this->m_language_profiles[$language] = new CHistogram();
			
            $this->m_language_profiles[0]->loadCharsFromFile("./LID_tools/References/Languages/german.txt");
			$this->m_language_profiles[1]->loadCharsFromFile("./LID_tools/References/Languages/swedish.txt");
			$this->m_language_profiles[2]->loadCharsFromFile("./LID_tools/References/Languages/finnish.txt");
			$this->m_language_profiles[3]->loadCharsFromFile("./LID_tools/References/Languages/albanian.txt");
			$this->m_language_profiles[4]->loadCharsFromFile("./LID_tools/References/Languages/french.txt");
			$this->m_language_profiles[5]->loadCharsFromFile("./LID_tools/References/Languages/irish.txt");
			$this->m_language_profiles[6]->loadCharsFromFile("./LID_tools/References/Languages/italian.txt");
			$this->m_language_profiles[7]->loadCharsFromFile("./LID_tools/References/Languages/spanish.txt");
			$this->m_language_profiles[8]->loadCharsFromFile("./LID_tools/References/Languages/portuguese.txt");
			$this->m_language_profiles[9]->loadCharsFromFile("./LID_tools/References/Languages/hungarian.txt");
			$this->m_language_profiles[10]->loadCharsFromFile("./LID_tools/References/Languages/norwegian.txt");
			$this->m_language_profiles[11]->loadCharsFromFile("./LID_tools/References/Languages/danish.txt");
			$this->m_language_profiles[12]->loadCharsFromFile("./LID_tools/References/Languages/turkish.txt");
			$this->m_language_profiles[13]->loadCharsFromFile("./LID_tools/References/Languages/polish.txt");
			$this->m_language_profiles[14]->loadCharsFromFile("./LID_tools/References/Languages/icelandic.txt");
			$this->m_language_profiles[15]->loadCharsFromFile("./LID_tools/References/Languages/czech.txt");
			
			$probabilities = Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			
			for($language=0; $language<16; $language++)
				for($char=0; $char<count($histogram->m_vector); $char++)
				{
					for($char_ref=0; $char_ref<count($this->m_language_profiles[$language]->m_vector); $char_ref++)
						if(utf8_strcmp($this->m_language_profiles[$language]->m_vector[$char_ref]->m_element, $histogram->m_vector[$char]->m_element))
						{
							$probabilities[$language] += $histogram->m_vector[$char]->m_frequency;
							break;
						}
				}
			
			
			// retrieve the highest probability (sum of frequencies)
			$max = 0; // keeps the highest probability
			$promising_language = -1; // keeps the promising language
			for($language=0; $language<16; $language++)
			{
				if($probabilities[$language] > $max)
				{
					$max = $probabilities[$language];
					$promising_language = $language;
				}
			}
			
			if($promising_language == 2) return LNG_FINNISH;
			else if($promising_language == 1) return LNG_SWEDISH;
			else if($promising_language == 0) return LNG_GERMAN;
			
			else if($promising_language == 3) return LNG_ALBANIAN;
			else if($promising_language == 4) return LNG_FRENCH;
			else if($promising_language == 5) return LNG_IRISH;
			else if($promising_language == 6) return LNG_ITALIAN;
			else if($promising_language == 7) return LNG_SPANISH;
			else if($promising_language == 8) return LNG_PORTUGUESE;
			
			else if($promising_language == 9) return LNG_HUNGARIAN;
			
			else if($promising_language == 10) return LNG_NORWEGIAN;
			else if($promising_language == 11) return LNG_DANISH;
			
			else if($promising_language == 12) return LNG_TURKISH;
			else if($promising_language == 13) return LNG_POLISH;
			else if($promising_language == 14) return LNG_ICELANDIC;
			else if($promising_language == 15) return LNG_CZECH;

			else return LNG_ENGLISH;
		}
	}
}

?>