<?php
require './Randomness.php';

class PassPhrase {

	public static $dict;
	public static $dictLen;

	public static function initDict() {
		if (self::$dict === null) {
			self::$dict = require 'words.php';
			self::$dictLen = count(self::$dict);
		}
	}

	/**
	 * Generate a random pass phrase.
	 *
	 * Uses a dictionary of words from http://www.becomeawordgameexpert.com/
	 * Specifying shorter max word length reduces entropy of the pass phrase by reducing the
	 * effective dictionary size.
	 *
	 * The digits and special characters are chosen using mt_rand() so they do not add any
	 * entropy to the phrase. They are included only to defeat silly password strength tests.
	 *
	 * @param int $numWords Number of words in phrase
	 * @param int $maxWordLen Max length of each word
	 * @param int $nSpecials Number of non-alphanumeric ascii chars to add
	 * @param int $nDigits Number of digit chars to add
	 * @param int $minPhraseLen Minimum number of ascii chars in phrase
	 * @param bool $cryptoStrong Set to use a cryptographically-strong random generator
	 * @return string The random pass phrase
	 */
	public static function randomPassPhrase(
		$minPhraseLen = 14,
		$maxPhraseLen = 20,
		$numWords = 4,
		$nSpecials = 1,
		$nDigits = 1,
		$cryptoStrong = true
	) {
		// Reconcile incompatible inputs.
		$minPhraseLen = max(10, $minPhraseLen);
		$maxPhraseLen = max($minPhraseLen, $maxPhraseLen);
		$phraseLen = mt_rand($minPhraseLen, $maxPhraseLen);
		$numWords = min(floor($phraseLen/2), $numWords);
		$aveWordLen = round(2 * $phraseLen/$numWords);

		// Allocate phrase length to the words.
		$allocated = 0;
		for ($i = 0; $i < $numWords - 1; $i += 1) {
			$max = $phraseLen - $allocated - 2 * ($numWords - $i);
			$max = min($max, $aveWordLen);
			$allocation[$i] = mt_rand(2, max(2, $max));
			$allocated += $allocation[$i]; 
		}
		$allocation[$numWords - 1] = $phraseLen - $allocated;

		self::initDict();
		$words = array();
		do {
			// Get a string of random bytes, length is biggest multiple fo 3 shorter than the
			// block length the random generator uses natively. Split into 3-byte words.
			$x = str_split(Randomness::randomBytes($cryptoStrong ? 18 : 63, $cryptoStrong), 3);

			// For 3-byte random string...
			foreach ($x as $y) {
				// ...concert it to an integer, mask lower 18 bits.
				$n = end(unpack('L', $y . chr(0))) & 0x3ffff;

				// Discard numbers > dictionary size and words longer than the max word length
				if ($n > self::$dictLen) {
					continue;
				}
				do {
					$word = self::$dict[$n];
				}
				if ($n < self::$dictLen
					&& strlen($word = self::$dict[$n]) <= $maxWordLen
					&& strlen($word) >= $minWordLen
				) {
					$words[] = ucwords(strtolower($word));
					if (count($words) >= $numWords) {
						if (strlen(implode('', $words)) < $minAlphas) {
							$l = PHP_INT_MAX;
							$k = false;
							foreach ($words as $j => $word)
								if (strlen($word) < $l) {
									$l = strlen($word);
									$k = $j;
								}
							unset($words[$k]);
							$minWordLen = min($maxWordLen,
								$minAlphas - strlen(implode('', $words)));
						} else
							break 2;
					}
				}
			}
		} while (true);

		// A sub-set of ASCII's non-alphnumeric characters
		$specials = str_split('~!@#$%^&-_+=|;:.');

		// Add ~half the words to the phrase
		$phrase = implode('', array_slice($words, 0, ceil($numWords / 2)));

		// Add the special chars. NOTE: mt_rand() is not really random
		if ($nSpecials)
			for ($i = 0; $i < $nSpecials; ++$i)
				$phrase .= $specials[mt_rand(0, count($specials) - 1)];

		// Add the remaining words to the phrase
		$phrase .= implode('',
			array_slice($words, ceil($numWords / 2), $numWords - ceil($numWords / 2)));

		// Add the digits. NOTE: mt_rand() is not really random
		if ($nDigits)
			for ($i = 0; $i < $nDigits; ++$i)
				$phrase .= mt_rand(0, 9);

		return $phrase;
	}

}

print_r(PassPhrase::randomPassPhrase());