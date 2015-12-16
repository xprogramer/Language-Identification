<?php

/*******************************************************************************
 * CStringUTF8.php
 * year : 2014
 *
 * This file defines the structure of the UTF-8 String object with several basic
 * text processing operations.
 *
 ******************************************************************************/

require_once("CHistogram.php");


/*
 * utf8_strcmp: function to compare two UTF8 strings, and it return true if they
 *              are similar.
 */
function utf8_strcmp($str1,$str2)
{
	if(count($str1) != count($str2)) return false; // if the strings do not have the same length
	// matching all characters of the two strings
	for($char=0; $char<count($str1); $char++) if($str1[$char] != $str2[$char]) return false;
	
	return true;
}


class CStringUTF8
{
	public $m_string; // contains an array of utf8 characters, 
					  // where the first element always contains the UTF-8 three bytes
	
	
	public function utf8_TextToArray($text)
	{
		$strlen = mb_strlen($text,'UTF-8'); // get the text length
		// split the text into an array of utf8 character
		while ($strlen)
		{
			$this->m_string[] = mb_substr($text,0,1,'UTF-8'); // extract a character and put it in the array
			$text = mb_substr($text,1,$strlen,'UTF-8'); // strip the processed character from the text
			$strlen = mb_strlen($text);
		}
		
		unset($text);
	}
	
	
	/*
	 * utf8_FileToArray: function to load a text from an UTF-8 file
	 *
	 * @param filename: is the file path
	 */
	public function utf8_FileToArray($filename)
	{
		$file_content = file_get_contents($filename); // read the file content
		$strlen = mb_strlen($file_content,'UTF-8'); // get the text length
		// split the text into an array of utf8 character
		while ($strlen)
		{
			$this->m_string[] = mb_substr($file_content,0,1,'UTF-8'); // extract a character and put it in the array
			$file_content = mb_substr($file_content,1,$strlen,'UTF-8'); // strip the processed character from the text
			$strlen = mb_strlen($file_content);
		}
		
		unset($file_content);
	}
	
	
	/*
	 * stripUnusedChars: function to strip insignificant characters
	 */	
	public function stripUnusedChars()
	{
		$temp = array();
		$len = count($this->m_string);
		for($char=0; $char<$len; $char++)
		{
			if( // ASCII characters
				$this->m_string[$char]!="0" && $this->m_string[$char]!="1" && $this->m_string[$char]!="2" &&
				$this->m_string[$char]!="3" && $this->m_string[$char]!="4" && $this->m_string[$char]!="5" &&
				$this->m_string[$char]!="6" && $this->m_string[$char]!="7" && $this->m_string[$char]!="8" &&
				$this->m_string[$char]!="9" && 
				$this->m_string[$char]!="\"" && $this->m_string[$char]!="(" &&
				$this->m_string[$char]!=")" && $this->m_string[$char]!="," && $this->m_string[$char]!="." &&
				$this->m_string[$char]!=";" && $this->m_string[$char]!="!" && $this->m_string[$char]!="?" &&
				$this->m_string[$char]!="[" && $this->m_string[$char]!="]" && $this->m_string[$char]!="=" &&
				$this->m_string[$char]!=":" && $this->m_string[$char]!="_" && $this->m_string[$char]!="*" &&
				$this->m_string[$char]!="{" && $this->m_string[$char]!="}" && $this->m_string[$char]!="#" &&
				$this->m_string[$char]!="+" && $this->m_string[$char]!="@" && $this->m_string[$char]!="%" &&
				$this->m_string[$char]!="€" && $this->m_string[$char]!="|" && $this->m_string[$char]!="$" &&
				$this->m_string[$char]!="£" && $this->m_string[$char]!="\\" && 
				// Arabic Chars
				hexdec(bin2hex($this->m_string[$char])) != 0xD89E && hexdec(bin2hex($this->m_string[$char])) != 0xD88C && 
				hexdec(bin2hex($this->m_string[$char])) != 0xD89F && hexdec(bin2hex($this->m_string[$char])) != 0xD9AA && 
				hexdec(bin2hex($this->m_string[$char])) != 0xD89B &&
				// Extended Latin characters
				hexdec(bin2hex($this->m_string[$char])) != 0xE28090 && hexdec(bin2hex($this->m_string[$char])) != 0xE28091 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE28092 && hexdec(bin2hex($this->m_string[$char])) != 0xE28093 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE28094 && hexdec(bin2hex($this->m_string[$char])) != 0xE28095 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE28096 && hexdec(bin2hex($this->m_string[$char])) != 0xE28097 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE28098 && hexdec(bin2hex($this->m_string[$char])) != 0xE28099 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE2809a && hexdec(bin2hex($this->m_string[$char])) != 0xE2809b &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE2809c && hexdec(bin2hex($this->m_string[$char])) != 0xE2809d &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE2809e && hexdec(bin2hex($this->m_string[$char])) != 0xE2809f &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE280a0 && hexdec(bin2hex($this->m_string[$char])) != 0xE280a1 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE280a2 && hexdec(bin2hex($this->m_string[$char])) != 0xE280a3 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE280a4 && hexdec(bin2hex($this->m_string[$char])) != 0xE280a5 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xE280a6 && hexdec(bin2hex($this->m_string[$char])) != 0xE280a7 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xC2AB && hexdec(bin2hex($this->m_string[$char])) != 0xC2BB &&
				// Arabic numbers
				hexdec(bin2hex($this->m_string[$char])) != 0xD9A0 && hexdec(bin2hex($this->m_string[$char])) != 0xD9A1 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9A2 && hexdec(bin2hex($this->m_string[$char])) != 0xD9A3 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9A4 && hexdec(bin2hex($this->m_string[$char])) != 0xD9A5 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9A6 && hexdec(bin2hex($this->m_string[$char])) != 0xD9A7 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9A8 && hexdec(bin2hex($this->m_string[$char])) != 0xD9A9 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9AB && hexdec(bin2hex($this->m_string[$char])) != 0xD9AC &&
				hexdec(bin2hex($this->m_string[$char])) != 0xD9AA && 
				hexdec(bin2hex($this->m_string[$char])) != 0xDBB0 && hexdec(bin2hex($this->m_string[$char])) != 0xDBB1 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xDBB2 && hexdec(bin2hex($this->m_string[$char])) != 0xDBB3 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xDBB4 && hexdec(bin2hex($this->m_string[$char])) != 0xDBB5 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xDBB6 && hexdec(bin2hex($this->m_string[$char])) != 0xDBB7 &&
				hexdec(bin2hex($this->m_string[$char])) != 0xDBB8 && hexdec(bin2hex($this->m_string[$char])) != 0xDBB9
			) 
			$temp[] = $this->m_string[$char];
		}
			
		unset($this->m_string);
		$this->m_string = $temp;
		unset($temp);
	}
		
	
	/*
	 * separateWords: function to separate contracted words
	 */		
	public function separateWords()
	{
		$len = count($this->m_string);
		// looking for minus, slash and quote characters
		for($char=0,$j=0; $char<$len; $char++)
		{
			// replace minus, slash and quote byte white spaces
			if($this->m_string[$char]=='/' || $this->m_string[$char]=='-' || $this->m_string[$char]=='\'') $this->m_string[$char]=" ";
		}
	}
	
	
	/*
	 * stripMultipleSeparator: function to strip multiple word delimiters
	 */		
	public function stripMultipleSeparator()
	{
		$temp = array();
		$already = false; // detect if the delimiter already token into account
		$len = count($this->m_string);
		// strip multiple delimiters and keep only one
		for($i=0; $i<$len; $i++)
		{
			if($this->m_string[$i]==" " || $this->m_string[$i]=="\n" || $this->m_string[$i]=="\r")
			{
				if(!$already) $temp[] = $this->m_string[$i]; // the first word delimiter
				$already = true;
			}
			else // multiple word delimiter
			{
				$temp[] = $this->m_string[$i];
				$already = false;
			}
		}
			
		unset($this->m_string);
		$this->m_string = $temp;
		unset($temp);
	}
	
	
	/*
	 * stripUserTags: function to strip user tags
	 */		
	public function stripUserTags()
	{
		$temp = array();
		// looking for a sub-string starting with @
		for($char=1; $char<count($this->m_string); $char++)
		{
			if($this->m_string[$char] == "@" && ($char < count($this->m_string)-2))
			{
				// seek the characters until the end of the user tagged name
				$char = $char + 1;
				for( ; $this->m_string[$char]!=" " && $this->m_string[$char] != "\r" && $this->m_string[$char]!="\n"; $char++);
			}
			else
			{
				$temp[] = $this->m_string[$char];
			}
		}
		
		unset($this->m_string);
		$this->m_string = $temp;
		unset($temp);
	}
	
	
	/*
	 * stripHashTags: function to strip hash tags
	 */	
	public function stripHashTags()
	{
		$temp = array();
		// looking for a sub-string starting with #
		for($char=1; $char<count($this->m_string); $char++)
		{
			if($this->m_string[$char] == "#" && ($char < count($this->m_string)-2))
			{
				// seek the characters until the end of the hash-tag
				$char = $char + 1;
				for( ; $this->m_string[$char]!=" " && $this->m_string[$char] != "\r" && $this->m_string[$char]!="\n"; $char++);
			}
			else
			{
				$temp[] = $this->m_string[$char];
			}
		}
		
		unset($this->m_string);
		$this->m_string = $temp;
		unset($temp);
	}
	
	
	/*
	 * stripURLs: function to strip URLs
	 */		
	public function stripURLs()
	{
		$temp = array();
		// looking for a sub-string starting with http://
		for($char=1; $char<count($this->m_string); $char++)
		{
			if($char <= count($this->m_string)-7)
				if($this->m_string[$char]=="h" && $this->m_string[$char+1]=="t" && $this->m_string[$char+2]=="t" && 
					$this->m_string[$char+3]=="p" && $this->m_string[$char+4]==":" && $this->m_string[$char+5]=="/" && 
					$this->m_string[$char+6]=="/")
				{
					// seek the characters until the end of the URL
					$char = $char + 7;
					for( ; $this->m_string[$char]!=' ' && $this->m_string[$char] != "\r" && $this->m_string[$char]!="\n"; $char++);
				}
				else
				{
					$temp[] = $this->m_string[$char];
				}
			else
			{
				$temp[] = $this->m_string[$char];
			}
		}
		
		unset($this->m_string);
		$this->m_string = $temp;
		unset($temp);
	}
	
	
	/*
	 * uppercaseToLowercase: function to convert uppercase letters to lowercase letters
	 */		
	public function uppercaseToLowercase()
	{
		// looking for capital letters and replace them with small letters
		for($char=1; $char<count($this->m_string); $char++)
		{
			if(     $this->m_string[$char] == 'A') $this->m_string[$char] = "a";
			else if($this->m_string[$char] == 'B') $this->m_string[$char] = "b";
			else if($this->m_string[$char] == 'C') $this->m_string[$char] = "c";
			else if($this->m_string[$char] == 'D') $this->m_string[$char] = "d";
			else if($this->m_string[$char] == 'E') $this->m_string[$char] = "e";
			else if($this->m_string[$char] == 'F') $this->m_string[$char] = "f";
			else if($this->m_string[$char] == 'G') $this->m_string[$char] = "g";
			else if($this->m_string[$char] == 'H') $this->m_string[$char] = "h";
			else if($this->m_string[$char] == 'I') $this->m_string[$char] = "i";
			else if($this->m_string[$char] == 'J') $this->m_string[$char] = "j";
			else if($this->m_string[$char] == 'K') $this->m_string[$char] = "k";
			else if($this->m_string[$char] == 'L') $this->m_string[$char] = "l";
			else if($this->m_string[$char] == 'M') $this->m_string[$char] = "m";
			else if($this->m_string[$char] == 'N') $this->m_string[$char] = "n";
			else if($this->m_string[$char] == 'O') $this->m_string[$char] = "o";
			else if($this->m_string[$char] == 'P') $this->m_string[$char] = "p";
			else if($this->m_string[$char] == 'Q') $this->m_string[$char] = "q";
			else if($this->m_string[$char] == 'R') $this->m_string[$char] = "r";
			else if($this->m_string[$char] == 'S') $this->m_string[$char] = "s";
			else if($this->m_string[$char] == 'T') $this->m_string[$char] = "t";
			else if($this->m_string[$char] == 'U') $this->m_string[$char] = "u";
			else if($this->m_string[$char] == 'V') $this->m_string[$char] = "v";
			else if($this->m_string[$char] == 'W') $this->m_string[$char] = "w";
			else if($this->m_string[$char] == 'X') $this->m_string[$char] = "x";
			else if($this->m_string[$char] == 'Y') $this->m_string[$char] = "y";
			else if($this->m_string[$char] == 'Z') $this->m_string[$char] = "z";
		}
	}
	
	
	/*
	 * getWords: function to extract words from the text
	 */	
	public function getWords()
	{
		$words = array();
		$len = count($this->m_string);
		// extract all words from the text
		$prec = 1;
		for($char=1; $char<$len; $char++)
		{
			$tmp = array();
			if($this->m_string[$char] == "\r" || $this->m_string[$char] == "\n" || $this->m_string[$char] == " ")
			{
				// copy the word string into a temporary variable
				for($c=$prec; $c<$char; $c++) $tmp[] = $this->m_string[$c];
				$words[] = $tmp;
				$prec = $char+1;
			}
			else if($char == $len-1) // if the end of the text
			{
				// copy the word string into a temporary variable
				for($c=$prec; $c<$char+1; $c++) $tmp[] = $this->m_string[$c];
				$words[] = $tmp;
			}
			unset($tmp);
		}
		
		return $words;
	}
	
	
	/*
	 * getNgramChars: function to extract ngram characters from the text
	 */	
	public function getNgramChars($NGRAM)
	{
		$ngram_list = array();
		
		for($char=1; $char<count($this->m_string)-($NGRAM-1); $char++)
		{
			$tmp = array();
			for($c=0; $c<$NGRAM; $c++) $tmp[] = $this->m_string[$char+$c];
			
			$ngram_list[] = $tmp;
			unset($tmp);
		}
		//for($i=0; $i<count($ngram_list); $i++){for($j=0; $j<count($ngram_list[$i]); $j++) echo $ngram_list[$i][$j];echo "#";}
		return $ngram_list;
	}
	
	
	/*
	 * histogramNgramChars: function to create histogram of ngram characters
	 */	
	public function histogramNgramChars($NGRAM)
	{
		$histogram = new CHistogram();
		
		for($char=1; $char<count($this->m_string)-($NGRAM-1); $char++)
		{
			// copy the n-gram element into a temporary variable
			$tmp = array();
			for($c=0; $c<$NGRAM; $c++) $tmp[] = $this->m_string[$char+$c];
			
			// looking for the existence of the n-gram element in the histogram
			$exists = false;
			for($elt=0; $elt<count($histogram->m_vector); $elt++)
			{
				// test if the temporary n-gram element matches the current histogram element
				// if it exists in the histogram its frequency is incremented, and we break the loop
				if(utf8_strcmp($histogram->m_vector[$elt]->m_element, $tmp))
				{
					$histogram->m_vector[$elt]->m_frequency++;
					$exists = true;
					break;
				}
			}
			
			// if the temporary n-gram element does not exist
			if(!$exists)
			{
				$histogram->m_vector[] = new HistogramElement($tmp,1);
			}
		}

		return $histogram;
	}
};

?>