var App = Ember.Application.create();

App.range = function (a, b) {
	var array = [];
	for (i = a; i <= b; i += 1) {
		array.push(i.toString());
	}
	return array;
};

App.newPhrase = function () {
	var data = {};
	data = App.phrase.spec.getProperties(
		['nw', 'wl', 'ns', 'nd', 'pl']
	);
	data.fm = 'json',
	$.ajax({
		url: 'phrase-api/index.php',
		data: data,
		dataType: 'JSON',
		success: function (response) {
			App.phrase.set('value', response[0]);
		}
	});
};

App.phrase = Ember.Object.create({
	ranges: {
		nw: App.range(1, 9),
		wl: App.range(5, 99),
		ns: App.range(0, 9),
		nd: App.range(0, 9),
		pl: App.range(6, 99)
	},
	spec: Ember.Object.create({
		nw: '3',
		wl: '10',
		ns: '1',
		nd: '1',
		pl: '14'
	}),
	value: ''
});
App.phrase.spec.addObserver('nw', App.newPhrase);
App.phrase.spec.addObserver('wl', App.newPhrase);
App.phrase.spec.addObserver('ns', App.newPhrase);
App.phrase.spec.addObserver('nd', App.newPhrase);
App.phrase.spec.addObserver('pl', App.newPhrase);

App.ApplicationController = Ember.Controller.extend();
App.ApplicationView = Ember.View.extend({templateName: 'application'});

App.PhraseController = Ember.ObjectController.extend();
App.PhraseView = Ember.View.extend({templateName: 'phrase'});

App.Router = Ember.Router.extend({
  root: Ember.Route.extend({
    index: Ember.Route.extend({
      route: '/',
      connectOutlets: function (router) {
	    router.get('applicationController').connectOutlet(
	    	'phrase',
	    	App.phrase
	    )
	  }
    })
  })
})

App.initialize();

App.newPhrase();