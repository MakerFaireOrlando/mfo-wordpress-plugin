console.log("isotope helper js loaded");

// quick search regex
var qsRegex;

var $container = jQuery('#exhibits');    // Container for the all post items

// init
$container.isotope({
  // options
  itemSelector: '.item',    // Individual post item selector
  masonry: {
    	gutter: 20
  },
  filter: function() {
    return qsRegex ? jQuery(this).text().match( qsRegex ) : true;
  }


//  layoutMode: 'fitRows'
});

$container.imagesLoaded().progress( function () {
  $container.isotope('layout');
});
 // Enable filter buttons to behave as expected
jQuery('.button-group').on( 'click', 'button', function() {
  var filterValue = jQuery(this).attr('data-filter');
  $container.isotope({ filter: filterValue });
  console.log("filter:" + filterValue);
  var textValue = "Category: ";
  textValue += jQuery(this).attr('data-text');
  jQuery('div.category-text').text(textValue);
});


var fv = jQuery('#category').attr('class');
if (fv) {
	console.log("filter category:" + fv);
	$container.isotope({ filter: fv });


	//set select to the right item
	//document.getElementById('makers-category-select').value=fv;
}

//if url params are used...
//var cat = jQuery('#cat-param').text();
//var hashcat = "#" + cat;
//console.log("hashcat:" + hashcat);
//var fv = "." + cat;
//console.log("filter:" + fv);
//$container.isotope({ filter: fv });
//var textValue = "Category: ";
//textValue += jQuery(hashcat).attr('data-text');

//jQuery('div.category-text').text(textValue);


// use value of search field to filter
var $quicksearch = jQuery('.quicksearch').keyup( debounce( function() {
  qsRegex = new RegExp( $quicksearch.val(), 'gi' );
  $container.isotope(); 
}, 200 ) );

// debounce so filtering doesn't happen every millisecond
function debounce( fn, threshold ) {
  var timeout;
  return function debounced() {
    if ( timeout ) {
      clearTimeout( timeout );
    }
    function delayed() {
      fn();
      timeout = null;
    }
    timeout = setTimeout( delayed, threshold || 100 );
  }
}

// filter functions (need the outer function and leaving the inner as examples)
var filterFns = {
  // show if number is greater than 50
  numberGreaterThan50: function() {
    var number = $(this).find('.number').text();
    return parseInt( number, 10 ) > 50;
  },
  // show if name ends with -ium
  ium: function() {
    var name = $(this).find('.name').text();
    return name.match( /ium$/ );
  }
};
// bind filter on select change
jQuery('.filters-select').on( 'change', function() {
  // get filter value from option value
  var filterValue = this.value;
  // use filterFn if matches value
  filterValue = filterFns[ filterValue ] || filterValue;
  $container.isotope({ filter: filterValue });
  console.log("filterValue:" + filterValue);
  if (filterValue =="*") {
	window.history.pushState("object or string", "Title", "/makers");
	}
  else {
	window.history.pushState("object or string", "Title", "/makers/?category=" + filterValue.substring(1));
	}
});

