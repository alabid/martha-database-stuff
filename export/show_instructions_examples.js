(function($){
     // close instructions and examples on load
     $(document).ready(
        function() {
	    var close = ["instructions", "examples", "columns-constraints"];
	    $.each(close, function(i, val) {
		       $("#" + val).css("display", "none");
		   });
	    // on click on instructions tab
	    $("li.instr a").click(function() {
				      var par = $(this).parent();
				      var otherpar = $("ul li.resul a, ul li.cons-cols a").parent();

				      if (par.hasClass("active")) {
					  par.removeClass("active");
					  return false;
				      }
				      par.addClass("active");  
				      otherpar.removeClass("active");
				      $("#examples, #columns-constraints").slideUp();
				      $("#instructions").slideDown();
				      return false;
				  });

	    // on click on results
	    $("ul li.resul a").click(function() {
					 var par = $(this).parent();
					 var otherpar = $("ul li.instr a, ul li.cons-cols a").parent();

					 if (par.hasClass("active")) {
					     par.removeClass("active");
					     return false;
					 }
					 par.addClass("active");  
					 otherpar.removeClass("active");
					 $("#instructions, #columns-constraints").slideUp();
					 $("#examples").slideDown();
					 return false;
				     });
	    $("ul li.cons-cols a").click(function() {
		    var par = $(this).parent();
		    var otherpar = $("ul li.instr a, ul li.resul a").parent();

		    if (par.hasClass("active")) {
			par.removeClass("active");
			return false;
		    }
		    par.addClass("active");
		    otherpar.removeClass("active");
		    $("#instructions, #examples").slideUp();
		    $("#columns-constraints").slideDown();
		    return false;
		});
	    // on click on caret
	    $("#instructions-examples li a span.caret").parent().parent().click(function() {
								  ($("#instructions").css("display") === "block" ||
								   $("#examples").css("display") === "block" ?
								   $("#instructions, #examples, #columns-constraints").slideUp() :
								   $("#instructions, #examples, #columns-constraints").slideDown());
										    
								   $("ul li.resul, ul li.instr").removeClass("active");
								  
							      });
	});

       
})(jQuery);