<?php
/**
 *	This package contains four functions to be used for inserting
 *	or removing portions of a file mid-stream while reducing memory 
 *	consumption by using a temp file instead for storing data in memory
 *
 *	These functions are best when used with large files that cause 
 *	memory capacity to be exceeded
 *
 *	@author Sam Shull <sam.shull@jhspecialty.com>
 *	@version 1.0
 *
 *	@copyright Copyright (c) 2009 Sam Shull <sam.shull@jhspeicalty.com>
 *	@license <http://www.opensource.org/licenses/mit-license.html>
 *
 *	Permission is hereby granted, free of charge, to any person obtaining a copy
 *	of this software and associated documentation files (the "Software"), to deal
 *	in the Software without restriction, including without limitation the rights
 *	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *	copies of the Software, and to permit persons to whom the Software is
 *	furnished to do so, subject to the following conditions:
 *	
 *	The above copyright notice and this permission notice shall be included in
 *	all copies or substantial portions of the Software.
 *	
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *	THE SOFTWARE.
 *
 */

/**
 *	Insert a string into a file at the current position
 *	of the file pointer
 *
 *	@param resource $fp
 *	@param string $str
 *	@param integer $length - maximum length to write to file
 *
 *	@return integer
 */
function finsert ($fp, $str, $length=null)
{
	$ret = 0;
	$length = !is_null($length) ? (int)$length : strlen($str) + 1;
	
	//flush all the written data
	fflush($fp);
	
	//track the current position
	$current = ftell($fp);
	
	//open a temp file for the rest of the data in the current file
	$temp = tmpfile();
	
	//copy the rest of the data in the file to a temp file
	//stream_copy_to_stream($fp, $temp, -1, $current);
	while (!feof($fp))
	{
		fwrite($temp, fread($fp, 4096));
	}
	
	//rewind the temp file
	rewind($temp);
	
	//rewind the file pointer to the current position
	//using truncate allows the use of a+
	ftruncate($fp, $current);
	
	//write in the text
	$ret += fwrite($fp, $str, $length);
	
	//copy all the data back in
	//stream_copy_to_stream($temp, $fp);
	while (!feof($temp))
	{
		fwrite($fp, fread($temp, 4096));
	}
	
	//move the cursor to the end of the inserted text
	fseek($fp, $current + $ret, SEEK_SET);
	
	//get rid of the temp file
	ftruncate($temp, 0);
	fclose($temp);
	
	//make sure to flush
	fflush($fp);
	
	return $ret;
}

/**
 *	Insert an array as a csv line into a file at the current position
 *	of the file pointer
 *
 *	@param resource $fp
 *	@param array $fields
 *	@param string $delimiter = ,
 *	@param string $enclosure = "
 *	@param string $escape = \
 *
 *	@return integer
 */
function finsertcsv ($fp, array $fields, $delimiter=",", $enclosure='"', $escape="\\")
{
	$func = create_function('$a', '
									$b=\''.str_replace("'", "\'", $enclosure).'\';
									$c=\''.str_replace("'", "\'", $escape . ($escape == "\\" ? $escape : "")).'\';
									return $b . str_replace($b, $c . $b, $a) . $b;
								');
	
	$str = implode(
				$delimiter, 
				array_map(
					$func, 
					$fields
				)
			);
	return finsert($fp, $str . PHP_EOL);
}

/**
 *	Remove a portion of a file with a given length
 *	at the current position of the file pointer
 *
 *	@param resource $fp
 *	@param integer $length
 *
 *	@return boolean
 */
function fremove ($fp, $length)
{
	$ret = false;
	
	//flush all the written data
	fflush($fp);
	
	//track the current position
	$current = ftell($fp);
	
	//move the cursor to the desired position
	fseek($fp, $length, SEEK_CUR);
	
	//open a temp file for the rest of the data in the current file
	$temp = tmpfile();
	
	//copy the rest of the data in the file to a temp file
	//stream_copy_to_stream($fp, $temp, -1, $current);
	while (!feof($fp))
	{
		fwrite($temp, fread($fp, 4096));
	}
	
	//rewind the temp file
	rewind($temp);
	
	//rewind the file pointer to the current position
	//using truncate allows the use of a+
	$ret = ftruncate($fp, $current);
	
	//copy all the data back in
	//stream_copy_to_stream($temp, $fp);
	while (!feof($temp))
	{
		fwrite($fp, fread($temp, 4096));
	}
	
	//move the cursor to the end of the inserted text
	fseek($fp, $current, SEEK_SET);
	
	//get rid of the temp file
	ftruncate($temp, 0);
	fclose($temp);
	
	//make sure to flush
	fflush($fp);
	
	return $ret;
}

/**
 *	Remove a csv line from a file at the current position
 *	of the file pointer
 *
 *	@param resource $fp
 *	@param array $fields
 *	@param string $delimiter = ,
 *	@param string $enclosure = "
 *
 *	@return integer
 */
function fremovecsv ($fp, $length=0, $delimiter=",", $enclosure='"', $escape="\\")
{
	$current = ftell($fp);
	version_compare(PHP_VERSION, "5.3", ">=") ? 
		fgetcsv($fp, $length, $delimiter, $enclosure, $escape) :
		fgetcsv($fp, $length, $delimiter, $enclosure);
	$now = ftell($fp);
	fseek($fp, $current, SEEK_SET);
	return fremove($fp, $now - $current);
}

/**
 *	Insert a string at the beginning of a file
 *
 *	@param resource $fp
 *	@param string $str
 *	@param integer $length - maximum length to write to file
 *
 *	@return integer
 */
function fprepend ($fp, $str, $length=null)
{
	$ret = 0;
	$length = !is_null($length) ? (int)$length : strlen($str) + 1;
	
	//flush all the written data
	fflush($fp);
	
	//track the current position
	$current = ftell($fp);
	rewind($fp);
	
	//open a temp file for the rest of the data in the current file
	$temp = tmpfile();
	
	//copy the rest of the data in the file to a temp file
	//stream_copy_to_stream($fp, $temp, -1, $current);
	while (!feof($fp))
	{
		fwrite($temp, fread($fp, 4096));
	}
	
	//rewind the temp file
	rewind($temp);
	
	//rewind the file pointer to the current position
	//using truncate allows the use of a+
	ftruncate($fp, 0);
	
	//write in the text
	$ret += fwrite($fp, $str, $length);
	
	//copy all the data back in
	//stream_copy_to_stream($temp, $fp);
	while (!feof($temp))
	{
		fwrite($fp, fread($temp, 4096));
	}
	
	//move the cursor to the end of the inserted text
	fseek($fp, $current + $ret, SEEK_SET);
	
	//get rid of the temp file
	ftruncate($temp, 0);
	fclose($temp);
	
	//make sure to flush
	fflush($fp);
	
	return $ret;
}

/**
 *	Insert a string into a file at a specific distance from a given position
 *
 *	@param resource $fp
 *	@param string $str
 *	@param integer $offset
 *	@param integer $length
 *	@param integer $whence = SEEK_SET
 *
 *	@return integer
 */
function fchange ($fp, $str, $offset, $length, $whence=SEEK_SET)
{
	$ret = 0;
	
	//flush all the written data
	fflush($fp);
	
	fseek($fp, $offset, $whence);
	fseek($fp, $length, SEEK_CUR);
	
	//open a temp file for the rest of the data in the current file
	$temp = tmpfile();
	
	//copy the rest of the data in the file to a temp file
	//stream_copy_to_stream($fp, $temp, -1, $current);
	while (!feof($fp))
	{
		fwrite($temp, fread($fp, 4096));
	}
	
	//rewind the temp file
	rewind($temp);
	
	//rewind the file pointer to the current position
	//using truncate allows the use of a+
	ftruncate($fp, $offset);
	
	//write in the text
	$ret += fwrite($fp, $str, $length);
	
	//copy all the data back in
	//stream_copy_to_stream($temp, $fp);
	while (!feof($temp))
	{
		fwrite($fp, fread($temp, 4096));
	}
	
	//move the cursor to the end of the inserted text
	fseek($fp, $offset, $whence);
	fseek($fp, $length, SEEK_CUR);
	
	//get rid of the temp file
	ftruncate($temp, 0);
	fclose($temp);
	
	//make sure to flush
	fflush($fp);
	
	return $ret;
}

/**
 *	Find an occurrence of a PCRE pattern in the next line from 
 *	a given stream resource - used the same way fscanf would be used
 *
 *	@param resource $fp
 *	@param string $regex
 *	@param array $matches = null
 *	@param integer $flags = 0
 *	@param integer $offset = 0
 *
 *	@return integer
 */
function fpreg_match($fp, $regex, &$matches=null, $flags=0, $offset=0)
{
	//if feof return 0 matches, else look for the pattern in the next line of the stream
	return feof($fp) ? 0 : preg_match($regex, fgets($fp), $matches, $flags, $offset);
}

/**
 *	Find all of the occurrences of a PCRE pattern in a file
 *	from the given pointer position
 *
 *	@param resource $fp
 *	@param string $regex
 *	@param array $matches = null
 *	@param integer $flags = 0
 *
 *	@return integer
 */
function fpreg_match_all($fp, $regex, &$matches=null, $flags=0)
{
	$pos = ftell($fp);
	
	$matched = 0;
	$match = null;
	$matches = array();
	
	$flag = $flags & PREG_OFFSET_CAPTURE;
	
	while (!feof($fp))
	{
		if (preg_match($regex, fgets($fp), $match, $flag))
		{
			++$matched;
			$matches[] = $match;
		}
	}
	
	fseek($fp, $pos, SEEK_SET);
	
	if ($matched)
	{
		if ($flags & PREG_SET_ORDER)
		{
			$ret = array();
			$number = count($matches[0]);
			
			foreach ($matches as $match)
			{
				for ($i=0;$i<$number;++$i)
				{
					if (!isset($ret[$i]))
					{
						$ret[$i] = array();
					}
					
					$ret[$i][] = isset($match[$i]) ? $match[$i] : null;
				}
			}
			
			$matches = $ret;
		}
	}
	
	return $matched;
}

?>
