//	_assets/js/_article.js

$(document).ready(function() {

		
  article.editor.setup();
  return;
		
});



var article = {
	
	save_callback: function(data){
	   //    window.console.info('article.save_callback');
	   //    window.console.info(JSON.stringify(data));
	    
	    //  Get form object:
	    	var $form = $('#'+data.form_id);
	    
	    //	Stop on error
	        if (data.meta.error < 0) {
	            
	            $form.find('.msg').text(data.meta.msg).addClass('warning').slideDown(tan);
	            if (data.hasOwnProperty('field_warning')){
	        	 $form.find('[name="'+data.field_warning+'"]').addClass('warning');
	            }
	            return;
	        }
	    
	    //	Clear local data:
	    	form.data.remove($form);
	    
	    //	Reload page:
	 	window.location.href = data.redirect;
	  
	 	return;
	},
	
	editor :{
	    
	    setup: function(){
	    if ($('#content').size() !=1) {return;}
	    
	    // Get popup for an insert:
	       $(document).on( "click", '[data-sa^="popup:"]', function(e) {
		   
	       // Get name of popup to open:	   
		   var $sa = sa.data($(this).attr('data-sa'));
		   var data= {};
		       data.popup = $sa.popup;
		       data.action_ok = 'article.editor.get_link;type:'+$sa.popup+';';
		       
		// Get html of popup:  	
		   var ajax = new gen.ajax('');
		   var htmlSA = sa.dataSA($('html').attr('data-sa'));
		       data.image_path = htmlSA['path'];
		   var url = htmlSA['application'] == "VBNE"?  'article/ajax/get_popup': htmlSA['application']+'/article/ajax/get_popup';
		   ajax.get({ url:url, data:  data , callback:'article.editor.popup_show' });
		   return;
		   
	       });
	       
	    // Insert image: 
	       $(document).on( "click", '#image_selector .thumb', function(e) { 
		   
	       //  Close popup:
		   $('#popup').addClass('ui-closed');
	       
	       //  Get ID:
		   var ID = parseInt($(this).attr('data-id'),10);
		   if (ID <1)  {
		       alert('Afbeelding kan niet worden toegevoeg.');
		       return;
		   }
		   
		// Get image-record:  
		   var data = {};
		       data.ID = ID;
		   var ajax = new gen.ajax('');
		   var htmlSA = sa.dataSA($('html').attr('data-sa'));
		   var url = htmlSA['application'] == "VBNE"?  'image/ajax/get_image': htmlSA['application']+'/image/ajax/get_image';
		   ajax.get({ url:url, data:  data , callback:'article.editor.insert_image' });
		  
	       
		   return;
	       });
	       
	     
	       
	       
	       
	    },
	    
	    popup_open: function(){
		
	       // Get name of popup to open:	   
		   var $sa = sa.data($(this).attr('data-sa'));
		   var data= {};
		       data.popup = $sa.popup;
		       data.action_ok = 'article.editor.get_link;type:'+$sa.popup+';';
		       
		// Get html of popup:  	
		   var ajax = new gen.ajax('');
		   var htmlSA = sa.dataSA($('html').attr('data-sa'));
		       data.image_path = htmlSA['path'];
		   var url = htmlSA['application'] == "VBNE"?  'article/ajax/get_popup': htmlSA['application']+'/article/ajax/get_popup';
		   ajax.get({ url:url, data:  data , callback:'article.editor.popup_show' });
		   return;
		
	    },
	    
	    get_node_attr: function(){
		
	    //  Get node from active editor:	
		var node = tinymce.activeEditor.selection.getNode();
		
	    //  Stop in case node has no attributes at all: 
		if (!node.hasAttributes()) { return {};}
		
		var $return = {};
      	        var attrs = node.attributes;
      	        for(var i = attrs.length - 1; i >= 0; i--) {
      	           $return [attrs[i].name]=  attrs[i].value;
      	        }
      	        return $return;
      	     
	    },
	    
	    insert_image: function(data){
	    // Catch error:
	       if (parseInt(data.meta.error,10) < 0) {
		    if (debug == "on") { window.console.error(JSON.stringify(data));}
		    alert('Afbeelding kan niet worden toegevoeg.');
		    return;
	        }
	        window.console.info('image record:');
	        window.console.log(JSON.stringify(data.record));
	        
	     // Get relative path image for this page:  
		var $sa  = sa.data($('html').attr('data-sa'));
		var src = $sa.path+'_img/';
		    src += $sa.application.toLowerCase()+'/img/';
		    src += data.record.dir =='/'     ?  '' :  data.record.dir;
		    src += 'img'+data.record.ID+'.'+data.record.type;
		    
		    window.console.log('src: '+src);
		   
		var alt  = data.record.alt == null? '' :data.record.alt;  
		     window.console.log('alt: '+alt);
		   
	     // Get node from active  editor:
	        var node = tinymce.activeEditor.selection.getNode();
	        var node_name =node.nodeName;
	        if ( node_name.toUpperCase() == 'IMG'){ 
	            
	        //  Change attrinutes:
	            node.setAttribute('src', src);
	            node.setAttribute('alt', alt);
	            node.setAttribute('data-image-id', data.record.ID);
	            
	        }else{
	            
	        //  Insert new image after selected content:
	            var selection = tinymce.activeEditor.selection;
	            var content   = selection.getContent();
	           
	            var img       = '<img data-image-id="'+data.record.ID+'" src="'+src+'" alt="'+alt+'" />';
	            window.console.log('img: '+img );
		    selection.setContent(content+img);
	            
	        }
	        
	        return;
		
	    },
	    
	   
	    popup_show: function(data){
	    // Catch error:
	       if (parseInt(data.meta.error,10) < 0) {
	         if (debug == "on") { window.console.error(JSON.stringify(data));}
	         return;
	       }
	       
	    // add html to popup and show:
	       $('#box-content').html(data.html);
	       if (data.size == "large") {
		   $('#popup').addClass('large');
	       }else{
		   $('#popup').removeClass('large');  
	       }
	       
	       $('#popup').removeClass("ui-closed");
	       return;
		
	    },
	    
	    get_link: function(event_data){
		
	    //  Close popup:
		$('#popup').addClass('ui-closed');
		
	       // Get type ID and text:
		   var type = event_data.type;
		   var ID = $('#record').find('option[selected="selected"]').val();
		   var selection = tinymce.activeEditor.selection;
		   
		   var data= {};
		   data.ID 	= ID;
		   data.type 	= type;
		   data.text  	= selection.getContent();
		
		       
		// Get html of popup:  	
		   var ajax = new gen.ajax('');
		   var htmlSA = sa.dataSA($('html').attr('data-sa'));
		   var url = htmlSA['application'] == "VBNE"?  'article/ajax/get_link': htmlSA['application']+'/article/ajax/get_link';
		   ajax.get({ url:url, data:  data , callback:'article.editor.insert_link' });
		   return;
	   
	    }, 
	    
            insert_link: function(data){
        	window.console.info('insert link:');
            // Catch error:
 	       if (parseInt(data.meta.error,10) < 0) {
 	         if (debug == "on") { window.console.error(JSON.stringify(data));}
 	         alert(data.meta.msg);
 	         return;
 	       }   
 	       
 	       var node = tinymce.activeEditor.selection.getNode();
 	       var node_name =node.nodeName;
 	       if ( node_name.toUpperCase() == 'A'){ 
	            
 		  //  Change attrinutes:
 		       node.setAttribute('href',data.record.href);
 		       node.setAttribute('title', data.record.title);
 		       node.setAttribute('target', data['target']);
 		       node.setAttribute('data-link-id', data['data-link-id']);
 		         
 		} else{
 		            
 		  //  Insert new link:
 		      var selection = tinymce.activeEditor.selection;
 		      selection.setContent(data.html);
 		            
 		}
        	return;
		
	    },
	    
	},


	
};
