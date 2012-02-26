<?php

class Randomness {

	/**
	 * Platform independent strlen()
	 *
	 * Substitute for the dangerous PHP fn {@link http://www.php.net/manual/en/function.strlen.php}
	 *
	 * Owing to PHP's Multibyte String overloading feature, strlen() might actually be mb_strlen()
	 * in disguise and if Multibyte String's deault encoding is multi-byte, strlen() might not count
	 * the number of bytes.
	 *
	 * @param $string
	 * @return int
	 */
	public static function strlen($string) {
		return function_exists('mb_strlen')
			? mb_strlen($string, 'ISO-8859-1')
			: strlen($string);
	}

	/**
	 * Platform independent substr().
	 *
	 * Substitute for the dangerous PHP fn {@link http://www.php.net/manual/en/function.substr.php}
	 * For explaination {@see self::strlen}
	 *
	 * @param string $string
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public static function substr($string, $start = 0, $length = null) {
		if (func_num_args() < 3)
			$length = self::strlen($string);
		return function_exists('mb_substr')
			? mb_substr($string, $start, $length, 'ISO-8859-1')
			: substr($string, $start, $length);
	}

	public static function warn($msg) {
		if (class_exists('Yii'))
			/** @noinspection PhpUndefinedClassInspection */
			Yii::log($msg, 'warning', 'security');
		else
			error_log($msg);
	}

	/**
	 * Generate a pseudo random block of data using several sources.
	 *
	 * No appology for the dreadful nonsense hackery! You have been warned. But this is
	 * possibly better than using only mt_rand which is not really random at all.
	 *
	 * @param bool $warn set to log a warning when the function is called
	 * @return string of 64 pseudo random bytes
	 */
	public static function pseudoRanBlock($warn = true) {
		if ($warn)
			self::warn('Using ' . get_class() . '::pseudoRanBlock non-ctypto_strong bytes');

		/**
		 * @var array Keeps each pseudo-random datum found as a string
		 */
		$r = array();

		// Get some data from mt_rand()
		for ($i = 0; $i < 16; ++$i)
			$r[] = pack('V', mt_rand(0, 0xffffffff));

		// On unixy sustems the numerical values in ps, uptime and iostat ought to be fairly
		// unpredictable. Gather the non-zero digits from those
		/*foreach (array('ps', 'uptime', 'iostat') as $cmd) {
			@exec($cmd, $s, $ret);
			if (is_array($s) && $s && $ret === 0)
				foreach ($s as $v)
					if (false !== preg_match_all('/[1-9]+/', $v, $m) && isset($m[0]))
						$r[] = implode('', $m[0]);
		}*/

		// Gather the current time's microsecond part. Note: this is only a source of entropy on
		// the first call! If multiple calls are made, the entropy is only as much as the
		// randomness in the time between calls
		$r[] = substr(microtime(), 2, 6);

		// Concatenate everything gathered, mix it with sha512.
		// hash() is part of PHP core and enabled by default but it can be
		// disabled at compile time but we ignore that possibility here.
		return hash('sha512', implode('', $r), true);
	}

	/**
	 * Get random bytes from the system's entropy source via PHP's session manager.
	 *
	 * @return string 20-byte random binary string or false on error
	 */
	public static function sessionBlock() {
		// session.entropy_length must be set for session_id be crypto-strong
		ini_set('session.entropy_length', 20);
		if (ini_get('session.entropy_length') != 20)
			return false;

		// These calls are (supposed to be, according to PHP manual) safe even if there is
		// already an active session for the calling script
		@session_start();
		@session_regenerate_id();
		$s = session_id();
		if (!$s)
			return false;

		// $s has 20 bytes of entropy but the session manager converts the binary random bytes
		// into something readable. We have to convert that back. SHA-1 should do it without
		// losing entropy.
		return sha1($s, true);
	}

	/**
	 * Generate a string of random bytes.
	 *
	 * @param int $length Number of random bytes to return
	 * @param bool $cryptoStrong Set to require crytoStrong randomness
	 * @param bool $http Set to use the http://www.random.org service
	 * @return string|bool The random binary string or false on failure
	 */
	public static function randomBytes($length = 8, $cryptoStrong = true, $http = false) {
		/**
		 * @var string The string of random bytes to return
		 */
		$s = '';

		// If cryptoStrong bytes are required, try various entropy sources known to be good
		if ($cryptoStrong) {

			// openssl_random_pseudo_bytes() can return non-crypto-strong result but warns
			// when it does. Since crypto-strong is required discard result if it warns.
			if (function_exists('openssl_random_pseudo_bytes')
				&& false !== ($s = openssl_random_pseudo_bytes($length, $safe))
				&& $safe
				&& self::strlen($s) >= $length
			)
				return self::substr($s, 0, $length);

			// mcrypt_create_iv() with MCRYPT_RAND is not crypto-strong. With MCRYPT_DEV_URANDOM
			// it can (on Linux) return non-crypto-strong result without warning, so don't use that.
			if (function_exists('mcrypt_create_iv')
				&& false !== ($s = mcrypt_create_iv($length, MCRYPT_DEV_RANDOM))
				&& self::strlen($s) >= $length
			)
				return self::substr($s, 0, $length);

			// Try /dev/random directly. On Linux it may block so deal with that.
			if (false !== ($f = @fopen('/dev/random', 'r'))
				&& stream_set_blocking($f, 0)
				&& false !== ($s = @fread($f, $length))
				&& (fclose($f) || true)
				&& self::strlen($s) >= $length
			)
				return self::substr($s, 0, $length);

			// Try (three times max) stealing entropy from the session manager.
			$i = 0;
			while (
				self::strlen($s) < $length
				&& false !== ($r = self::sessionBlock())
				&& ++$i < 3
			)
				$s .= $r;
			if (self::strlen($s) >= $length)
				return self::substr($s, 0, $length);

			// Try http://random.org
			if (self::strlen($s) < $length
				&& $http
				&& false !== ($r = @file_get_contents(
					'http://www.random.org/cgi-bin/randbyte?format=f&nbytes=' . $length
				))
				&& self::strlen($s .= $r) >= $length
			)
				return self::substr($s, 0, $length);

			// No more sources for crypto-strong data available so
			return false;
		}

		// Use the wierd pseudo-random generator above
		while (self::strlen($s) < $length)
			$s .= self::pseudoRanBlock($cryptoStrong);

		return self::substr($s, 0, $length);
	}

	/**
	 * Generate a random Blowfish salt for use in PHP's crypt().
	 *
	 * @param $cost int cost parameter between 4 and 31
	 * @param bool $cryptoStrong set to require crytoStrong randomness
	 * @return string salt starting $2a$
	 */
	public static function blowfishSalt($cost = 10, $cryptoStrong = true) {
		return '$2a$'
			. str_pad($cost, 2, '0', STR_PAD_RIGHT) . '$'
			. strtr(substr(base64_encode(self::randomBytes(18, $cryptoStrong)), 0, 24), '+', '.');
	}

	/**
	 * Generate a random ASCII string.
	 *
	 * Use only [0-9a-zA-z~.] which are all transparent in raw urlencoding.
	 *
	 * @param int $length length of the string in characters
	 * @param bool $cryptoStrong set to require crytoStrong randomness
	 * @return string the random string
	 */
	public static function randomString($length = 8, $cryptoStrong = true) {
		return strtr(
			self::substr(
				base64_encode(self::randomBytes($length + 2, $cryptoStrong)), 0, $length
			),
			array('+' => '_', '/' => '~')
		);
	}

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
	 * Specifying shorter max word length reduces entropy of the passphrase by reducing the
	 * effective ditionary size.
	 *
	 * The digits and special characters are chosen using mt_rand() so they do not add any
	 * entropy to the phrase. They are included only to defeat silly password strength tests.
	 *
	 * @param int $length Number of words in phrase
	 * @param int $maxWordLen Max length of each word
	 * @param int $nSpecials Number of non-alphanumeric ascii chars to add
	 * @param int $nDigits Number of digit chars to add
	 * @param bool $cryptoStrong Set to use a cryptographically-strong random generator
	 * @return string The random pass phrase
	 */
	public static function randomPassPhrase(
		$length = 4,
		$maxWordLen = 10,
		$nSpecials = 1,
		$nDigits = 1,
		$cryptoStrong = false
	) {
		self::initDict();
		$words = array();
		do {
			// Get a string of random bytes, length is biggest multiple fo 3 shorter than the
			// block length the random generator uses natively. Split into 3-byte words.
			$x = str_split(self::randomBytes($cryptoStrong ? 18 : 63, $cryptoStrong), 3);

			foreach ($x as $i => $y) {
				// Convert each 3-byte word to an integer, mask lower 18 bits
				$n = end(unpack('L', $y . chr(0))) & 0x3ffff;

				// Discard numbers > doctionary size and words longer than the max word length
				if ($n < self::$dictLen && strlen(self::$dict[$n]) <= $maxWordLen)
					$words[] = ucwords(strtolower(self::$dict[$n]));

				if (count($words) >= $length)
					break 2;
			}
		} while (true);

		// A sub-set of ASCII's non-alphnumeric characters
		$specials = str_split('~!@#$%^&-_+=|;:.');

		// Add ~half the words to the phrase
		$phrase = implode('', array_slice($words, 0, ceil($length / 2)));

		// Add the special chars. NOTE: mt_rand() is not really random
		if ($nSpecials)
			for ($i = 0; $i < $nSpecials; ++$i)
				$phrase .= $specials[mt_rand(0, count($specials) - 1)];

		// Add the remaining words to the phrase
		$phrase .= implode('', array_slice($words, ceil($length / 2), $length - ceil($length / 2)));

		// Add the digits. NOTE: mt_rand() is not really random
		if ($nDigits)
			for ($i = 0; $i < $nDigits; ++$i)
				$phrase .= mt_rand(0, 9);

		return $phrase;
	}

}