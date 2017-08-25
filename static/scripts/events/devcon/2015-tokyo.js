(function(){

  var className = "fixed-menu";

  function scrollTop(event){
    return event.pageY || document.body.scrollTop;
  }

  window.addEventListener("load", function(){
    var navigationBar = document.querySelector("#main > nav");
    var main = document.querySelector("#main");

    var threshold = navigationBar.offsetTop;
    
    document.addEventListener("scroll", function(event){
      if(navigationBar && main && threshold <= scrollTop(event)){
        main.classList.add(className);
      }else{
        main.classList.remove(className);
      }
    });
  });

})();

(function(){
  NodeList.prototype.map = NodeList.prototype.map || function(func){
    var result = [];
    for(var i = 0; i < this.length; i++){
      result.push(func(this.item(i)));
    }
    return result;
  };

  var Foldable = function(el){
    this._el = el;
    var title = this._el.querySelector("h3");
    var a = document.createElement("a");
    a.href = "#";
    a.textContent = title.textContent;
    title.textContent = "";
    title.appendChild(a);
    this._link = a;

    this._initializeEventHandlers();
    this.fold();
  };
  Foldable.prototype = (function(){
    return {
      get parent(){
        return this._el.parentNode;
      },
      get link(){
        return this._link;
      },
      get folded(){
        return this._folded;
      },
      set folded(value){
        if(value){
          this.fold();
        }else{
          this.unfold();
        }
      },
      fold: function(){
        this._folded = true;
        this.parent.classList.remove("unfold");
        this.parent.classList.add("fold");
      },
      unfold: function(){
        this._folded = false;
        this.parent.classList.remove("fold");
        this.parent.classList.add("unfold");
      },
      toggle: function(){
        this.folded = !this.folded;
      },
      _initializeEventHandlers: function(){
        var self = this;
        this.link.addEventListener("click", function(event){
          event.preventDefault();
          self.toggle();
        });
      }
    };
  })();

  window.addEventListener("load", function(){
    var sessions = document.querySelectorAll("#sessions .session").map(function(sessions){
      return new Foldable(sessions);
    });
  });

})();

function initMap(){
  var myLatLng = {lat:35.672968,lng: 139.7169046};
  var map = new google.maps.Map(document.getElementById('map'), {zoom: 16, center: myLatLng, scrollwheel: false});
  var marker = new google.maps.Marker({position: myLatLng, map: map, title: 'TEPIA ホール'});
  var contentString = '<div class="container">'+ '<p><strong>TEPIA ホール</strong></p>'+ '<p> ' + '東京都港区北青山2-8-44' + '</p>'+ '</div>';
  var infowindow = new google.maps.InfoWindow({content: contentString});
  marker.addListener('click', function() {infowindow.open(map, marker);});
}
