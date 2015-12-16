<?php

/*******************************************************************************
 * CHistogram.php
 * year : 2014
 *
 * This file defines the structure of a Histogram object with a function to load
 * the histogram content from file. Also, it defines the structure of the 
 * histogram element that contains the element's string and its frequency.
 *
 * NOTE: the algorithm requires including CStringUTF8.h header file to work
 *        perfectly.
 ******************************************************************************/

 
/*
 * CLASS HistogramElement: defines the structure of a histogram vector entry 
 *                         which contains the element and its frequency.
 */ 
class HistogramElement
{
	public $m_element; // contains a string
	public $m_frequency; // contains the frequency (or normalized frequency) of the string
	
	function HistogramElement($element,$frequency)
	{
		$this->m_element = $element;
		$this->m_frequency = $frequency;
	}
}


/*
 * CLASS CHistogram: define the structure of a histogram vector  
 *                         which contains a set of entries of HistogramElement
 *                         type.
 */
class CHistogram
{
	public $m_vector; // vector containing the element and their frequencies
	
	function CHistogram()
	{
		$this->m_vector = array();
	}
	
	
	/*
     * loadCharsFromFile: function to load all the reference character from a 
     * given file.
     *
     * @param filename: is the file path of the file containing reference
     *                  characters.
     */ 
	function loadCharsFromFile($filename)
	{
		$file_content = file_get_contents($filename); // read the file content
		$file_content = mb_substr($file_content,3);
		$strlen = mb_strlen($file_content,'UTF-8');
		$array = array();
		// split the text into an array of utf8 character
		while ($strlen)
		{
			$tmp = array();
			$tmp[] = mb_substr($file_content,0,1,'UTF-8'); // extract a character and put it in the vector
			$this->m_vector[] = new HistogramElement($tmp,1);
			$file_content = mb_substr($file_content,1,$strlen,'UTF-8'); // strip the processed character from the text
			$strlen = mb_strlen($file_content);
		}
	}
	
	
	/*
     * LoadWordsFromFile: function to load all the reference words from a 
     * given file.
     *
     * @param filename: is the file path of the file containing reference
     *                  words.
     */ 
	function loadWordsFromFile($filename)
	{
		$file_content = file_get_contents($filename); // read the file content

		$strlen = mb_strlen($file_content,'UTF-8'); // get the text length
		// split the text into an array of utf8 character
		$string = array();
		while ($strlen)
		{
			$string[] = mb_substr($file_content,0,1,'UTF-8'); // extract a character and put it in the array
			$file_content = mb_substr($file_content,1,$strlen,'UTF-8'); // strip the processed character from the text
			$strlen = mb_strlen($file_content);
		}
		
		// extract all elements from the file content
		$prec = 1;
		for($char=1; $char<count($string); $char++)
		{
			$tmp = array();
			if($string[$char] == "\r" || $string[$char] == "\n")
			{
				// copy the element string into a temporary variable
				for($c=$prec; $c<$char; $c++) $tmp[] = $string[$c];
				$this->m_vector[] = new HistogramElement($tmp,1);
				$prec = $char+1;
			}
			unset($tmp);
		}
		unset($file_content);
		unset($string);
	}
}

?>