jQuery.fn.trilemma = function(options){
	var options	= options || {};
	var cbfs	= this; // establish checkbox container
	var cbs		= this.find('input:checkbox');
	var maxnum	= options.max ? options.max : 2;
	
	
	cbs.each( function() {
		$(this).bind('click', function() {
			if ($(this).is(':checked')) {
				if (cbs.filter(':checked').length == maxnum) {
					cbs.not(':checked').each( function() {
						$(this).attr('disabled','true');
						if (options.disablelabels) {
							var thisid = $(this).attr('id');
							$('label[for="'+thisid+'"]').addClass('disabled');
						}
						
					});
				}
			} else {
				cbs.removeAttr('disabled');
				if (options.disablelabels) {
					cbfs.find('label.disabled').removeClass('disabled');
				}
			}
		}
		);
	});
  return this;

}