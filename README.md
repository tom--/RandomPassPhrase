PHP Random Pass-Phrase Generator
================================

PHP code for generating random pass-phrases.

Words come from the [Become a Word Game Expert](http://www.becomeawordgameexpert.com/index.htm)
dictionary.

Random numbers used are "crypto-secure". Read the code to discover what this really means.

The web service has the following API   `(range) [default]`

- `@param int np`   Number of phrases to generate `(1..19) [1]`
- `@param int nw`   Number of words in the phrase `(1..9) [4]`
- `@param int wl`   Maximum number of ascii characters per word `(5..99) [10]`
- `@param int ns`   Number of non-alphanumeric ascii chars to insert `(0..9) [1]`
- `@param int nd`   Number of digits to append `(0..9) [1]`
- `@param int pl`   Minimum number of ascii characters in phrase `(6..99) [14]`
- `@param string fm`   Response format `(html|text|json) [html]`

The special characters are only there to placate password strength estimating algorithms. They
are chosen using `mt_rand()` which is not really random.