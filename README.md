# Random Pass-Phrase Generator

Webapp for generating random pass-phras.
Uses Zurb Foundation, Ember.js, Yii Framework.

Words come from the [Become a Word Game Expert](http://www.becomeawordgameexpert.com/index.htm)
dictionary.

Random numbers used are "crypto-secure". Read the code to discover what this really means.

The backend web service has an API docuemtned at:

 * /api/?docs=1

The special characters and digits are only there to placate password strength estimating algorithms.
They are chosen using `mt_rand()` which is not really random.