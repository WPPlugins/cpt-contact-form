(function($) {

 	$(document).ready(function () {
 	
 		if('implicit'==cptcf.markRead) 
 			setTimeout(markAsRead,1000*cptcf.readAfter);
 		else
 			{
 			$('.mark-read-container')
 				.append("<button class='mark-read button-secondary'>"+cptcf.markReadLiteral+"</button>");
 			$('.mark-read').click(markAsRead);
 			}
 	
    }); // document ready
    
  })(jQuery);

function markAsRead()
	{
	(function($) {
  	$.post(
  		cptcf.ajaxurl,
  		{message:cptcf.messageId, action:'mark_as_read'},
  		function(result)
  			{
  			console.log("Message #"+result+" marked as read!");
  			$('.mark-read').remove();
  			}
  		);
	  })(jQuery);
	return false;
	}
