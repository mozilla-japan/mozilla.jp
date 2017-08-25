(function() {
'use strict';

$('#wrapper').addClass('js');

var AppView = Backbone.View.extend({
  el: 'body',
  events: {
    'click a[href*=#]:not("#projects a")': 'setScroll',
    'keydown': 'closeBox'
  },
  initialize: function() {
    this.content = new ContentView();
    this.menu = new MenuView();
    this.list = new ProjectListView();
    this.box = new BoxView();
  },
  'closeBox': function(e) {
    if (!app.box.get('current')) {
      return;
    }
    if (e.keyCode === 27) {
      app.router.navigate('#', {trigger: true});
      app.$window.scrollTop($('#project-thumbnails').position().top);
    }
  },
  setScroll: function(e) {
    e.preventDefault();
    this.scrollToAnchor(e.currentTarget.hash);
  },
  scrollToAnchor: function(hash) {
    var self = this;
    $.scrollTo($(hash).position().top - 30, 0, {
      duration: 320,
      onAfter: function() {
        self.setLocationHash(hash);
      }
    });
  },
  setLocationHash: function(hash) {
    if (history.pushState) {
      history.pushState('', null, location.pathname + hash);
    } else {
      location.hash = hash;
    }
  }
});

var MenuView = Backbone.View.extend({
  el: '#inpage-menu',
  initialize: function() {
    if ('ontouchstart' in window || 'onmsgesturechange' in window) {
      return;
    }
    var self = this;

    app.$window.on('load scroll resize', function() {
      if (self.shouldBeFixed()) {
        self.$el.addClass('active');
      } else {
        self.$el.removeClass('active');
      }
    });
  },
  shouldBeFixed: function() {
    return app.$window.scrollTop() >= app.view.content.getTop() && app.$window.width() > 761;
  }
});

var ContentView = Backbone.View.extend({
  el: '#main-content',
  getTop: function() {
    return this.$el.position().top;
  }
});

var ProjectListView = Backbone.View.extend({
  el: '#project-thumbnails ul',
  initialize: function() {
    this.link = new ProjectLinkView();
  },
  events: {
    'click .open-project, #close-button': 'setUrl'
  },
  setUrl: function(e) {
    e.preventDefault();

    var _currentTop = $(window).scrollTop();

    var nextUrl = e.currentTarget.hash;
    if (nextUrl === location.hash) {
      nextUrl = '#';
    }
    app.router.navigate(nextUrl, {trigger: true});

    if (nextUrl === '#') {
      app.$window.scrollTop(_currentTop);
      return;
    }
  },
  faint: function() {
    this.$el.addClass('faint');
  },
  show: function() {
    this.$el.removeClass('faint');
  }
});

var ProjectLinkView = Backbone.View.extend({
  el: '#project-thumbnails .open-project',
  initialize: function() {
    var $contents = $('#project-contents .project-content');

    this.$el.each(function(index) {
      var $box = $('<div class="box"></div>');
      var $boxContent = $('<div class="box-content"></div>');
      $boxContent
        .append($contents.eq(index))
        .append('<a href="#project' + index + '" id="close-button"><span class="curve"><span></span></span><div>×</div><span class="curve"><span></span></span></a>')
        .css('opacity', '0');
      $box.append($boxContent);
      $(this).after($box);
    });
  }
});

var BoxView = Backbone.View.extend({
  el: '.box',
  initialize: function() {
    this.listenTo(app.box, 'open', this.open);
    this.listenTo(app.box, 'close', this.close);
  },
  open: function(id) {
    app.view.list.faint();
    
    this.$el.eq(app.box.get('prev')).slideUp(0);
    
    var $box = this.$el.eq(id);
    var $boxContent = $box.find('.box-content');

    $boxContent.transition({opacity: '0'}, 0);
    $box.slideDown(200, function() {
      $boxContent.transition({opacity: '1'}, 200);
    });
    
    $.scrollTo(
      $box.position().top - 60, 0, {duration: 320}
    );
  },
  close: function(id) {
    this.$el.eq(id).slideUp(150);
    app.view.list.show();
  }
});

if (!$.support.transition) {
  $.fn.transition = $.fn.animate;
}

var app = app || {};
window.app = app;

app.$window = $(window);

$(function() {
  app.view = new AppView();
  app.$window.on('load', function() {
    if (location.hash && ! /project\d+/.test(location.hash)) {
      app.view.scrollToAnchor(location.hash);
    }
  });
});

var BoxState = Backbone.Model.extend({
  initialize: function() {
    this.set('current', '');
    this.set('prev', '');
  },
  isOpen: function() {
    return !!this.get('current');
  },
  open: function(page) {
    this.set('prev', this.get('current'));
    this.trigger('open', page);
    this.set('current', page);
  },
  close: function() {
    if (!this.isOpen()) {
      return;
    }
    this.set('prev', this.get('current'));
    this.trigger('close', this.get('current'));
    this.set('current', '');
  }
});

var Project = Backbone.Model.extend({});

app.box = new BoxState();

var MenuState = Backbone.Model.extend({
  initialize: function() {
    this.set('fixable', !('ontouchstart' in window || 'onmsgesturechange' in window));
  }
});

app.menu = new MenuState();

var Router = Backbone.Router.extend({
  constructor: function() {
    if (!Router.instance) {
      Router.instance = this;
      Backbone.Router.apply(Router.instance, arguments);
    }

    return Router.instance;
  },
  routes: {
    '': 'index',
    'project:id': 'project'
  },
  'index': function() {
    if (history.replaceState) {
      history.replaceState(null, null, location.pathname);
    }
    if (app.box.isOpen()) {
      app.box.close();
    }
  },
  'project': function(page) {
    app.box.open(page);
  }
});

app.router = new Router({root: location.pathname});
$(function() {
  Backbone.history.start({pushState: false});
});


}).call(this);