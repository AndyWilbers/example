//	_assets/js/_ge.js


$(document).ready(function() {
	
    $( document ).on( "click", '[data-sa*="geo.toggle_area"]', geo.toggle_area); 
    
    $(document).on( "submit", 'form[data-sa*="submit:get_location"]', geo.save_location);
    
    $( document ).on( "click", '#btn-location-ne', geo.get_location_ne); 
    $( document ).on( "click", '#btn-location-sw', geo.get_location_sw);
	
});


var geo = {
	
       active_tip:null,
	
       init: function(){
	   
	   
       //  Set up with default centre and zoom:
	   var lat = parseFloat($('#centre_LAT').val());
	   var lng = parseFloat($('#centre_LNG').val());
	   var zoom = parseFloat($('#zoom').val());
	 
	   map = new google.maps.Map(document.getElementById('map'), {
	         center: {lat: lat, lng: lng},
	         fullscreenControl:false,
	         zoom: zoom });
	
	// Fit bounds to last location in case available:
	   var ne_lat = parseFloat($('#NE_LAT').val());
	   if (isNaN(ne_lat)) { return;}
	   
	   var ne_lng = parseFloat($('#NE_LNG').val());
	   if (isNaN(ne_lng)) { return;}
	   
	   var sw_lat = parseFloat($('#SW_LAT').val());
	   if (isNaN(sw_lat)) { return;}
	   
	   var sw_lng = parseFloat($('#SW_LNG').val());
	   if (isNaN(sw_lng)) { return;}
	 
	   var NE =  new google.maps.LatLng( ne_lat, ne_lng);
	   var SW =  new google.maps.LatLng( sw_lat, sw_lng);
	
	   var map_bounds = new google.maps.LatLngBounds(SW,NE);
	  
	   NE = map_bounds.getNorthEast();
	   SW = map_bounds.getSouthWest();
	   
	   map.fitBounds(map_bounds);
	   map.panToBounds(map_bounds);
	   return;
	  
       },
		
       toggle_area:function(e){
	   e.preventDefault();
	   $('#msg').html('').removeClass('warning');
	   $('#geo_save, #area').toggleClass('hide');
	   
	
	 
	 
	   var LatLngBounds = map.getBounds();
	   var NE = LatLngBounds.getNorthEast();
	   var SW = LatLngBounds.getSouthWest();
	   
	   window.console.info('map NE:');
	   window.console.log('lat: '+NE.lat());
	   window.console.log('lng: '+NE.lng());
	   
	   window.console.info('map SW:');
	   window.console.log('lat: '+SW.lat());
	   window.console.log('lng: '+SW.lng());
	   
	   window.console.info('area NE:');
	   window.console.log('lat: '+$('#NE_LAT').val());
	   window.console.log('lng: '+$('#NE_LNG').val());
	   
	   window.console.info('area SW:');
	   window.console.log('lat: '+$('#SW_LAT').val());
	   window.console.log('lng: '+$('#SW_LNG').val());
	   
	   var span_lat = NE.lat()-SW.lat();
	   var span_lng = NE.lng()-SW.lng();
	   window.console.info('map span_lat: '+ span_lat);
	   window.console.info('map span_lng: '+ span_lng);
	   
	   var ne_x    = 100*( $('#NE_LNG').val()-SW.lng() )/span_lng;
	   var ne_y    = 100*( $('#NE_LAT').val()-SW.lat() )/span_lat;
	   var sw_x    = 100*( $('#SW_LNG').val()-SW.lng() )/span_lng;
	   var sw_y    = 100*( $('#SW_LAT').val()-SW.lat() )/span_lat;
	   
	   window.console.info('area-NE:');
	   window.console.log('x: '+ne_x+'%');
	   window.console.log('y: '+ne_y+'%');
	   
	   window.console.info('area-SW:');
	   window.console.log('x: '+sw_x+'%');
	   window.console.log('y: '+sw_y+'%');
	
	
	   
	   var left    = 100*($('#SW_LNG').val()-SW.lng())/span_lat;
	   var top     = 100*(NE.lat()-$('#NE_LAT').val())/span_lng;
	   var right   = 100*(NE.lng()-$('#NE_LNG').val() )/span_lat;
	   var bottom  = 100 - 100*($('#SW_LAT').val()-SW.lat())/span_lng;
	   
	   
	   window.console.info('area:');
	   window.console.log('top: '+top+'%');
	   window.console.log('left: '+left+'%');
	
	   window.console.log('bottom: '+bottom+'%');
	   window.console.log('right: '+right+'%');
	   
	

	   
	   var txt = $(this).text();
	   var title = $(this).attr('title');
	   $(this).text($(this).attr('data-txt'));
	   $(this).attr('data-txt',txt);
	   $(this).attr('title',$(this).attr('data-title'));
	   $(this).attr('data-title',title);
	   return;
       },
       
       save_location: function(e){
	   e.preventDefault();
	   $('#msg').html('').removeClass('warning');
	   
	   var  canvas_rect = document.getElementById('canvas').getBoundingClientRect();
	   var  area_rect   = document.getElementById('area').getBoundingClientRect();
	   
	   var canvas_w = 	canvas_rect.right - canvas_rect.left;
	   var canvas_h = 	canvas_rect.bottom - canvas_rect.top;
	   
	   var ne_top   = area_rect.top -canvas_rect.top;
	   var ne_left  = canvas_w - area_rect.right;
	   var sw_top   = canvas_h - area_rect.bottom;
	   var sw_left  = area_rect.left;
	   
	   var LatLngBounds = map.getBounds();
	   var NE = LatLngBounds.getNorthEast();
	   var SW = LatLngBounds.getSouthWest();
	   
	// Calculate and Save location data:  
	   var data = {};
	   data['ID'] 	  = $('#ID').val();
					
	   data['NE_LAT'] =  SW.lat() + ne_left * (NE.lat() -SW.lat())/canvas_w;
	   data['NE_LNG'] =  NE.lng() + ne_top  * (SW.lng() -NE.lng())/canvas_h;
	   data['SW_LAT'] =  SW.lat() + sw_left * (NE.lat() -SW.lat())/canvas_w;
	   data['SW_LNG'] =  NE.lng() + sw_top  * (SW.lng() -NE.lng())/canvas_h;
	   
	   var ajax = new gen.ajax('');
	   var htmlSA = sa.dataSA($('html').attr('data-sa'));
	   var url =  htmlSA['application']+'/ajax/observation_set_save_meta';
	   ajax.post({ url:url, data:  data , callback:'geo.location_saved' });
	   
	
	  return;
       },
       
       location_saved: function(data){
	   
	// Catch error:    
	   if (parseInt(data.meta.error,10) < 0) {
	      if (debug == "on") { window.console.error(JSON.stringify(data));}
	      $('#msg').html(data.meta.msg).addClass('warning');
	      return;
	   }
	  
	   var htmlSA = sa.dataSA($('html').attr('data-sa'));
	   url =  htmlSA['path']+htmlSA['application']+'/my_observations?id='+data.ID;
	   location.assign(url);
	   return;
       },
       
	
	
       
       get_location_ne:function(e){
	   geo.active_tip = 'NE';
	   return geo.get_location(e);
       },
       
       get_location_sw:function(e){
	   geo.active_tip = 'SW';
	   return geo.get_location(e);
       },
       
       get_location:function(e){
	   e.preventDefault();
	   var options = {
		   enableHighAccuracy: true,
		   timeout: 8000,
		   maximumAge: 0
	   };
	   navigator.geolocation.getCurrentPosition(geo.get_location_success, geo.get_location_error, options);
	   return;
       },
       
       
       get_location_success: function(pos) {
	   var crd = pos.coords;
	   
	// Save location data:  
	   var data = {};
	   data['ID'] 	  = $('#ID').val();
	   
	   if ( geo.active_tip == 'NE'){
	       data['NE_LAT'] =  crd.latitude;
	       data['NE_LNG'] = crd.longitude;
	   } 
	       
	   if ( geo.active_tip == 'SW'){
	       data['SW_LAT'] =  crd.latitude;
	       data['SW_LNG'] = crd.longitude;
	   }
	  			
	   var ajax = new gen.ajax('');
	   var htmlSA = sa.dataSA($('html').attr('data-sa'));
	   var url =  htmlSA['application']+'/ajax/observation_set_save_meta';
	   ajax.post({ url:url, data:  data , callback:'geo.location_saved' });
	 },

	 get_location_error:function error(err) {
	     
	   $('#msg').html(err.message).addClass('warning');
	 }
	
	
};
