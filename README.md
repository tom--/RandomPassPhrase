# Random Pass-Phrase Generator

Webapp for generating random pass-phras.

## Purpose

Be a useful pass-phrase generator

Try out the following combination of tecnhologies:

   * Ember.js front-end MVC framework
   * Zurb Foundation CSS framework
   * Yii Framework for the back-end web service
   * My TApi extension to Yii for REST web service API
   * My Randomness class for crypto-strong random numbers

Words are from the [Become a Word Game Expert](http://www.becomeawordgameexpert.com/index.htm)
dictionary.

## About the API

The web service API is self-docuemnting. TApi automatically
generates docs from information from the Yii core classes and PHP docblocks. The
PhraseQuery model is an example of how to document an TApiQuery model class so that
TApi can generate complete documentation. Specifically you must:

 * for each public property of the TApiQuery model, provide an @property tag in the
   class docblock (not a @var tag on the property's docblock), and
 * complete the attributeLabels() method, covering all public property.

GET the method in question with docs=1 to return documentation, e.g.

 * /api/index.php?docs=1

## About RandomPassPhrase

You may specify a minimum and maximum number of letters in the phrase
(not counting special characters and digits added at the end). You can also
specify the number of words in the phrase. (Too many or too few words for the
phrase's number of letters can cause trouble. Use common sense.)

Some apps demand that you add special characters or digits. This service will add
them if you like. They are chosen using `mt_rand()` which is not crypto-strong random
so you shouldn't consider these to be adding entropy.