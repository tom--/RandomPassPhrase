PHP Random Pass-Phrase Generator
================================

PHP code for generating random pass-phrases.

Words come from the [Become a Word Game Expert](http://www.becomeawordgameexpert.com/index.htm)
dictionary.

Random numbers used are "crypto-secure". Read the code to discover what this really means.

The web service has the following API:

- `@param int np  `Number of phrases to generate (1..19)
- `@param int nw  `Number of words in the phrase (1..9)
- `@param int wl  `Max number of ascii characters per word (5..99 )
- `@param int ns  `Number of non-alphanumeric ascii chars to insert (0..9)
- `@param int nd  `Number of digits to append (0..9)
- `@param string fm  `Response format (html|text|json)

The special characters are only there to placate password strength estimating algorithms. They
are chosen using `mt_rand()` which is not really random.