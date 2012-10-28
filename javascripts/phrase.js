(function () {
	'use strict';
	var App = Ember.Application.create();

	/**
	 * Return a range of integers as an array of numeric strings
	 * @param a number range start.
	 * @param b number range end.
	 * @return string[]
	 */
	App.range = function (a, b) {
		var i, array = [];
		for (i = a; i <= b; i += 1) {
			array.push(i.toString());
		}
		return array;
	};

	/**
	 * Fetch a new phrase from the API.
	 */
	App.newPhrase = function () {
		var data;
		data = App.phrase.spec.getProperties(
			['mn', 'mx', 'nw', 'ns', 'nd']
		);
		data.fm = 'json';
		$.ajax({
			url: 'api/index.php',
			data: data,
			dataType: 'JSON',
			/**
			 * @param {Object} response The API response object. See api/index.php?docs=1
			 * @param {String[]} response.results List of pass-phrases
			 */
			success: function (response) {
				console.log(response);
				//noinspection JSUnresolvedVariable
				App.phrase.set('value', response.results[0]);
			}
		});
	};

	App.phrase = Ember.Object.create({
		// Hash holding the user's current phrase specification params.
		spec: Ember.Object.create({
			mn: '14',
			mx: '20',
			nw: '4',
			ns: '1',
			nd: '1'
		}),
		// The current phrase to display.
		value: '',
		// Option values for each select input.
		ranges: Ember.Object.create({
			mn: App.range(10, 99),
			mx: App.range(14, 99),
			nw: App.range(1, 9),
			ns: App.range(0, 9),
			nd: App.range(0, 9)
		})
	});
	// When phrase.spec.mn changes, update the mx range.
	App.phrase.spec.addObserver('mn', function () {
		App.phrase.ranges.set('mx', App.range(Math.max(10, App.phrase.spec.get('mn')), 99));
	});
	// A change to any of the params triggers fetching a new phrase.
	App.phrase.spec.addObserver('mn', App.newPhrase);
	App.phrase.spec.addObserver('mx', App.newPhrase);
	App.phrase.spec.addObserver('nw', App.newPhrase);
	App.phrase.spec.addObserver('ns', App.newPhrase);
	App.phrase.spec.addObserver('nd', App.newPhrase);

	App.ApplicationController = Ember.Controller.extend();
	App.ApplicationView = Ember.View.extend({templateName: 'application'});

	App.PhraseController = Ember.ObjectController.extend();
	App.PhraseView = Ember.View.extend({templateName: 'phrase'});

	App.Router = Ember.Router.extend({
		root: Ember.Route.extend({
			index: Ember.Route.extend({
				route: '/',
				// "New phrase" button triggers newPhrase event.
				newPhrase: App.newPhrase,
				connectOutlets: function (router) {
					router.get('applicationController').connectOutlet('phrase', App.phrase);
				}
			})
		})
	});

	App.initialize();
	App.newPhrase();
}());

