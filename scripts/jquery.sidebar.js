function panelSlide(id) {
  var id = id.substr(12, id.length);
  var panelContent = $("#panel-content-" + id);
  var panelTitle = $("#panel-title-" + id);
  if (panelContent.is(":hidden")) {
    panelContent.slideDown(150);
    panelTitle.removeClass('panel-title-plus').addClass('panel-title-minus');
  } else {
    panelContent.slideUp(150);
    panelTitle.removeClass('panel-title-minus').addClass('panel-title-plus');
  }
  window.setTimeout(function () {
    panelState();
  }, 500);
}

function panelSort() {
  var i = 0;
  var sort = new Array;
  $('.panel-holder').each(function () {
    var id = $(this).attr('id');
    id = id.substr(13, id.length);
    sort[i] = id + ':' + i;
    i++;
  });
  $.cookie('panelSort', sort.join('|'));
}

function panelState() {
  var i = 0;
  var state = new Array();
  $('.panel-content-hidden,.panel-content').each(function () {
    var id = $(this).attr('id');
    id = id.substr(14, id.length);
    state[i] = id + ':' + ($(this).is(':hidden') ? 0 : 1);
    i++;
  });
  $.cookie('panelState', state.join('|'));
}

function panelPlace() {
  var i = 0;
  var places = new Array;
  $('.panel-holder').each(function () {
    var id = $(this).attr('id').substr(13, $(this).attr('id').length);
    var place = $(this).parent().attr('id').substr(14, $(this).parent().attr('id').length);
    places[i] = id + ':' + (place == 'left' ? 'l' : 'r');
    i++;
  });
  $.cookie('panelPlace', places.join('|'));
}