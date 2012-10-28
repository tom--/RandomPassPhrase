<?php

class PassPhrase
{
    /**
     * @var array The dictionary of English words by word length.
     */
    public static $dict;
    /**
     * @var int[] The number of words of each wordlength in the dictionary.
     */
    public static $dictLen;
    /**
     * @var Randomness The Randomness object.
     */
    protected $randomness;
    /**
     * @var bool True to use a cryptographically-strong pseudo-random generator.
     */
    protected $cryptoStrong;
    /**
     * @var string[] A sub-set of ASCII's non-alphnumeric characters
     */
    protected $specials;

    public function __construct($cryptoStrong = true)
    {
        self::initDict();
        $this->randomness = new Randomness;
        $this->cryptoStrong = $cryptoStrong;
        $this->specials = str_split('~!@#$%^&-_+=|;:.');
    }

    public static function initDict()
    {
        if (self::$dict === null) {
            self::$dict = require 'wordsByLength.php';
            self::$dictLen = array();
            foreach (self::$dict as $length => $words) {
                self::$dictLen[$length] = count($words);
            }
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
     * @param int $minPhraseLen Minimum number of ascii chars in phrase
     * @param int $maxPhraseLen
     * @param int $numWords Number of words in phrase
     * @param int $nSpecials Number of non-alphanumeric ascii chars to add
     * @param int $nDigits Number of digit chars to add
     *
     * @internal param int $maxWordLen Max length of each word
     * @return string The random pass phrase
     */
    public function randomPassPhrase(
        $minPhraseLen = 14,
        $maxPhraseLen = 20,
        $numWords = 4,
        $nSpecials = 1,
        $nDigits = 1
    ) {
        // Sanitize and convert inputs.
        $minPhraseLen = max(10, $minPhraseLen);
        $maxPhraseLen = max($minPhraseLen, $maxPhraseLen);
        $phraseLen = mt_rand($minPhraseLen, $maxPhraseLen);
        $numWords = min(floor($phraseLen / 3), $numWords);
        $aveWordLen = round(2 * $phraseLen / $numWords);

        // Choose the length of each word to add up to $phraseLen.
        $allocated = 0;
        for ($i = 0; $i < $numWords - 1; $i += 1) {
            $max = $phraseLen - $allocated - 3 * ($numWords - $i);
            $max = min($max, $aveWordLen);
            $allocation[$i] = mt_rand(3, max(3, $max));
            $allocated += $allocation[$i];
        }
        $allocation[$numWords - 1] = $phraseLen - $allocated;

        // Choose random words from the doctionary according to phrase length.
        $words = array();
        foreach ($allocation as $wordLen) {
            $pos = $this->randomness->randInt(self::$dictLen[$wordLen], $this->cryptoStrong);
            $words[] = ucfirst(strtolower(self::$dict[$wordLen][$pos]));
        }

        // Add ~half the words to the phrase string.
        $halfNWords = ceil($numWords / 2);
        $phrase = implode('', array_slice($words, 0, $halfNWords));
        // Add the special chars. NOTE: mt_rand() is not really random.
        if ($nSpecials) {
            for ($i = 0; $i < $nSpecials; ++$i) {
                $phrase .= $this->specials[mt_rand(0, count($this->specials) - 1)];
            }
        }
        // Add the remaining words to the phrase.
        $phrase .= implode('', array_slice($words, $halfNWords));
        // Add the digits. NOTE: mt_rand() is not really random.
        if ($nDigits) {
            for ($i = 0; $i < $nDigits; ++$i) {
                $phrase .= mt_rand(0, 9);
            }
        }

        return $phrase;
    }
}
