//	_assets/js/_dragg.js


$(document).ready(function() {
    
   $('#NE, #SW').bind("mousedown", dragg.start);
   $('#NE, #SW').bind("touchstart", dragg.start);
   $(document).bind("mouseup", dragg.stop);
   $(document).bind("touchend", dragg.stop);
   return;
});


var dragg = {
	    
	tip: null,
	
	start: function(e){
	    
	 	e = e || window.event;
	 	
	 	var  canvas_rect = document.getElementById('canvas').getBoundingClientRect();
	 	var  canvas_top 	= canvas_rect.top,
			 canvas_left	= canvas_rect.left,
			 canvas_bottom 	= canvas_rect.bottom,
			 canvas_right 	= canvas_rect.right;
	 	
	 	var $area = document.getElementById('area');
			
		var  area_rect = $area.getBoundingClientRect();
			
	//	$area.style.top		= area_rect.top-canvas_top +'px';
	//	$area.style.left	= area_rect.left-canvas_left +'px';
	//	$area.style.bottom	= canvas_bottom-area_rect.bottom +'px';
	//	$area.style.right	= canvas_right-area_rect.right +'px';
		$area.style.margin	=  0;
		$area.style.width	= "auto";
		$area.style.height	= "auto";
		 
	        dragg.tip = $(this).attr('id');
	      
		$(document).bind('mousemove', dragg.move);
	    
	},
	
	stop: function(e){
		$(document).unbind('mousemove', dragg.move);
		var $area = document.getElementById('area'); 
	},
	
        move: function(e){
            
            	e = e || window.event;
        
            	var tip = dragg.tip;
  
            	var  canvas_rect = document.getElementById('canvas').getBoundingClientRect();
        
		var $area = document.getElementById('area');	
		var  area_rect = $area.getBoundingClientRect();
      	       
	     	var posX = e.clientX - canvas_rect.left;
		var posY = e.clientY - canvas_rect.top;
   
		var  minX =  tip=='NE'? area_rect.left-canvas_rect.left + 24 : 2;	
		var  maxX =  tip=='NE'? canvas_rect.right-canvas_rect.left   : area_rect.right-canvas_rect.left - 24 ;    
		var  minY =  tip=='NE'? 2 :area_rect.top -canvas_rect.top +24;
		var  maxY =  tip=='NE'? area_rect.bottom - canvas_rect.top -24 : canvas_rect.bottom -canvas_rect.top -2 ;
      
      		posX = posX<minX? minX: posX;
		       posY = posY<minY? minY: posY;
		       posX = posX> maxX?  maxX: posX;
		       posY = posY> maxY? maxY: posY;
		 		
		var LatLngBounds= map.getBounds();
		var NE = LatLngBounds.getNorthEast();
		var SW = LatLngBounds.getSouthWest();
					
		var canvas_w = 	canvas_rect.right- canvas_rect.left;
		var canvas_h = 	canvas_rect.bottom- canvas_rect.top;
		var lat = SW.lat() + posX * (NE.lat() -SW.lat())/canvas_w;
	 	var lng = NE.lng() + posY * (SW.lng() -NE.lng())/canvas_h;
	 	
		 		
		if (tip=='NE'){
			var posR = canvas_rect.right- canvas_rect.left- posX;
		 	$area.style.right = posR  + 'px';
		 	$area.style.top = posY+ 'px';
		 		
		 	$('#NE_LAT').val(lat);
		 	$('#NE_LNG').val(lng);
		 } else {
		       var posB = canvas_rect.bottom- canvas_rect.top- posY;
		       $area.style.left = posX  + 'px';
	 	       $area.style.bottom = posB+ 'px';
	 	       $('#SW_LAT').val(lat);
	 	       $('#SW_LNG').val(lng)
		}
		 		
		 
    },
	
};