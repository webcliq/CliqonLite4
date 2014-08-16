<?php 
/**
 * 
 * @package PhpMinifier
 * @author Radek Hřebeček <rhrebecek@gmail.com>
 * @license New BSD License
 * @copyright 2013
 * @final
 * @example 
 * 			$files = array(
 * 				'phpClass1.php',
 * 				'phpClass2.php',
 * 				'phpClass3.php',
 * 				'lib/phpClassExample.php',
 * 			)
 * 
 * 			PhpMinifier::minify($files, 'minifier.php');
 */
final class PhpMinifier { 
   /** 
	* List of tokens that can be written without sourrounding spaces
	* 
	* @var array Array([] => token) 
	*/ 
	static private $noSpaces = array( 
		T_AND_EQUAL,                	// &= 
		T_ARRAY_CAST,               	// (array) 
		T_BOOLEAN_AND,              	// && 
		T_BOOLEAN_OR,               	// || 
		T_BOOL_CAST,                	// (bool), (boolean) 
		T_CLOSE_TAG,                	/* ?> */ 
		T_CONCAT_EQUAL,             	// .= 
		T_CONSTANT_ENCAPSED_STRING, 	// 'string' 
		T_DEC,                      	// -- (one exception, see below) 
		T_DIV_EQUAL,                	// /= 
		T_DNUMBER,                  	// float number 
		T_DOLLAR_OPEN_CURLY_BRACES, 	// ${ 
		T_DOUBLE_ARROW,             	// => 
		T_DOUBLE_CAST,              	// (real), (double), (float) 
		T_DOUBLE_COLON,             	// :: 
		T_INC,                      	// ++ (one exception, see below) 
		T_INCLUDE,                  	// include 
		T_INCLUDE_ONCE,             	// include_once 
		T_INT_CAST,                 	// (int), (integer) 
		T_IS_EQUAL,                 	// == 
		T_IS_GREATER_OR_EQUAL,      	// >= 
		T_IS_IDENTICAL,             	// === 
		T_IS_NOT_EQUAL,             	// != or <> 
		T_IS_NOT_IDENTICAL,         	// !== 
		T_IS_SMALLER_OR_EQUAL,      	// <= 
		T_LNUMBER,                  	// integer number 
		T_MINUS_EQUAL,              	// -= 
		T_MOD_EQUAL,                	// %= 
		T_MUL_EQUAL,                	// *= 
		T_NS_SEPARATOR,             	// \ 
		T_NUM_STRING,               	// $a[0] 
		T_OBJECT_CAST,              	// (object) 
		T_OBJECT_OPERATOR,          	// -> 
		T_OPEN_TAG_WITH_ECHO,       	// <?= ou <%= 
		T_OR_EQUAL,                 	// |= 
		T_PAAMAYIM_NEKUDOTAYIM,     	// :: 
		T_PLUS_EQUAL,               	// += 
		T_REQUIRE,                  	// require 
		T_REQUIRE_ONCE,             	// require_once 
		T_SL,                       	// << 
		T_SL_EQUAL,                 	// <<= 
		T_SR,                       	// >> 
		T_SR_EQUAL,                 	// >>= 
		T_STRING_CAST,              	// (string) 
		T_UNSET_CAST,               	// (unset) 
		T_XOR_EQUAL                 	// ^= 
	); 

   /** 
	* Minify PHP source code of files in the $paths argument 
	* and store the minified code in the $outputFile argument
	* 
	* @param array $paths Array([] => path) 
	* @param string $outputFile 
	*/ 
	static function minify(array $paths, $outputFile)
	{ 
		$openTag = FALSE; 
		$code = '';

		foreach($paths as $path) { 
			if (is_file($path)) { 
				$min = self::compress($path, TRUE);

				if (strlen($min['code'])) { 
					if ($openTag) { 
						if (!$min['openTag']) { 
							$code .= '?>'; 
							$openTag = FALSE; 
						} 
					} else { 
						if ($min['openTag']) { 
							$code .= '<?php '; 
							$openTag = TRUE; 
						} 
					}

					$code .= $min['code']; 
				} 
			} 
		} 

		if ($openTag) { 
			$code .= '?>'; 
		} 

		file_put_contents($outputFile, $code); 
   } 

   /** 
	* compress
   	*
    * @param string $path 
    * @param bool $removeOpenCloseTags 
    * @return array Array(openTag => bool, code => string) 
    */ 
	static private function compress($path, $removeOpenCloseTags = TRUE)
	{ 
		$src = php_strip_whitespace($path); 

		$code = ''; 
		$openFound = FALSE; 

		if(empty($src)) { 
			return array('openTag' => $openFound, 'code' => $code); 
		} 

		$tokens = token_get_all($src); 
		$nb = count($tokens); 

		$nextToken = NULL; 
		$prevToken = NULL; 
		$prevIsSymbol = FALSE; 
		$prevSymbol = NULL; 

		for($i = 0; $i < $nb; ++$i) { 
			$token = $tokens[$i]; 

			if(!is_array($token)) { 
				$code .= $token; 
				$prevIsSymbol = TRUE; 
				$prevSymbol = $token; 

				continue; 
			} 

			list($index, $value) = $token; 

			if ($removeOpenCloseTags) { 
				if (($i === 0) && ($index === T_OPEN_TAG)) { 
					$openFound = TRUE; 
					continue; 
				} else if (($i === $nb-1) && ($index === T_CLOSE_TAG)) { 
					continue; 
				} 
			} 
			if ($index === T_START_HEREDOC) { 
				$code .= $value; 
				while(++$i < $nb) { 
					if (is_array($tokens[$i])) { 
						$code .= $tokens[$i][1]; 
						if ($tokens[$i][0] === T_END_HEREDOC) { 
							$code .= ";"; 
							++$i; 
							break; 
						} 
					} else { 
						$code .= $tokens[$i]; 
					} 
				} 
			} else if ($index === T_WHITESPACE) { 
				if ($i === 1) { 
					continue;
				} 

				if ($i) { 
					$prevToken = $tokens[$i-1]; 
				} 

				if ($i < $nb) { 
					$nextToken = $tokens[$i+1];
				} 

				if ($prevIsSymbol && is_array($nextToken)) { 
					if (($nextToken[0] === T_INC) && ($prevSymbol === '+') || ($nextToken[0] === T_DEC) && ($prevSymbol === '-')) { 
						$code .= $value; 
						$prevIsSymbol = FALSE; 
						$prevSymbol = NULL; 
					} 
					continue; 
				} else if ((!is_array($nextToken)) || (in_array($nextToken[0], self::$noSpaces))) { 
					continue; 
				} else if (is_array($nextToken)) { 
					if (($nextToken[0] === T_IF) && is_array($prevToken) && ($prevToken[0] === T_ELSE)) { 
						continue; 
					} 
					if (($nextToken[0] === T_VARIABLE) && (is_array($prevToken) && in_array($prevToken[0], array(T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_CASE, T_AS, T_RETURN, T_STATIC, T_ARRAY)))) { 
						continue; 
					} 
				}

				$code .= $value; 
			} else if (in_array($index, self::$noSpaces)) { 
				$code .= $value; 
				if ($i < $nb) { 
					$nextToken = $tokens[$i+1];

					if (is_array($nextToken) && ($nextToken[0] === T_WHITESPACE)) { 
						++$i;
					} 
				} 
			} else { 
				$code .= $value; 
			} 

			$prevIsSymbol = FALSE; 
			$prevSymbol = NULL; 
		} 

		return array('openTag' => $openFound, 'code' => $code); 
	} 
}

/*
// Strip white space
require_once("phpminifier.php");

$dir_iterator = new RecursiveDirectoryIterator($classpath); 
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

$files = array(); 
foreach ($iterator as $file) { 
	if(substr($file->getPathname(), -9) === 'class.php') { 
		$files[] = $file->getPathname(); 
	} 
} usort($files);
PhpMinifier::minify($files, "classes/min.class.php");

$origfile = array();
foreach($files as $original) {
	$origfile[0] = $original;
	PhpMinifier::minify($origfile, $original);
}
*/