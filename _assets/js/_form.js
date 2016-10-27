//	_assets/js/_form.js

var form = {
	
	setup: function() {
	
	//  In debug mode ask for clear all data in case parameter "clean" is set
	    if (debug == "on"){
	   
	    	var url = window.location.href;
    	    	    url = url.toLowerCase();
        	    if (url.indexOf("?clean")>0 || url.indexOf("&clean")> 0 ) { 
                	    var clear_all = confirm('Alle data verwijderen?');
                	    if (clear_all ) {
                		form.data.clear();
                	    }
        	    }
        	    
        	    if (url.indexOf("?show")>0 || url.indexOf("&show")> 0  || show === "on") { 
        		window.console.info("All local storage:");
        		window.console.info(JSON.stringify(localStorage));
        	    }
    	    
	    }
	    	
	    
	//  Bind check input on change when pattern attribute is set:
	    $(document).on( "change", 'input[pattern]', function(e) {
           	 e.preventDefault();
           	 form.check_pattern($(this));
	    });
	    
	//  Bind check input on change when required attribute is set:    
	    $(document).on( "change", 'input[required]', function(e) {
          	 e.preventDefault();
          	 form.check_required($(this));
	    });
	   
	//  Catch submit action:
	    $(document).on( "submit", 'form[id]:not([data-sa*="submit"])', function(e) {
        	 e.preventDefault();
        	 
            //	POST data:
        	var data = {};
        	
        	// Form ID:
        	   data['form_id'] = $(this).attr('id');
        	   
        	
        	
        	//  Standard form fields:
        	    $.each($(this).serializeArray(),function(name,field){
        		if ($('input[name="'+field.name+'"][data-default*="NULL"], input[name="'+field.name+'"][data-default*="null"]').size()>0) {
        		    
        		    data[field.name]= field.value ==''? "NULL": field.value;
        		    
        		    
        		} else {
        		    data[field.name] = field.value;
        		}
        	    });
        	    
                //  Data custom radio buttons:
        	    $(this).find('[data-ce*="type:radio;"].checked').each( function(){
        		var dataCE = ce.data($(this).attr('data-ce'));
        	        data[dataCE['name']] = dataCE['value'];
        	    });
        	    
        	//  Publish field:
        	    var field_publish = $(this).find('.publish').size()===1 ? $(this).find('.publish'): null;
        	    if (field_publish !== null) {
        		 data['publish'] = field_publish.hasClass('not')? -1 : 1;
        	    }
        	    
             //  Do AJAX call:  
        	 if (debug == "on"){
        	    var url = window.location.href;
        	    url = url.toLowerCase();
        	    if (url.indexOf("?show")>0 || url.indexOf("&show")> 0  || show === "on") { 
        		//window.console.info("Form-data send to server:");
        		//$.each(data, function(name,value){
        		  //  window.console.info(name+': '+value);
        		//});
        	    }
        	 }
        	 var ajax = new gen.ajax('');
        //	window.console.info(JSON.stringify(data));
        	
        	 ajax.post({ url: $(this).attr('action'), data:data, callback:$(this).attr('data-callback')});
  
	    });
	    
	//  Bind form-reload action:
	    $(document).on( "click", 'form[id] [data-sa="reload"]', form.reload );
	   
	//  Load with local-data for all forms on page:
	    $('form[id]').each(function(){
		
		
	    //	Get ID, key and $data object:
		var ID 	  = $(this).attr('id');
		var $data = form.data.get($(this));
		
	    //	Prevent submit on enter at input:
		$(document).on( "keypress", '#'+ID+' input', function(e) {
		   
		    var code =  e.keyCode ? e.keyCode : e.which;
		    if (code == 13) { 
			 e.preventDefault();
			 var inputs = $(this).closest('form').find(':input[type="text"]');
			 var indx = inputs.index(this) < inputs.size() - 1? inputs.index(this) + 1: 0;
			 inputs.eq( indx).focus();
		    }
		    return;
		});
		
		
	    //	Load data for named objects:
		form.data.load_named_objects($(this));
		
	
	    // 	Binding for local storage on change of element in form
		$(document).on( "change", '#'+ID+' input[type="text"], #'+ID+' input[type="hidden"], #'+ID+' select, #'+ID+' textarea', form.local_storage );
		$(document).on( "click", '#'+ID+' [data-ce*="type:radio;"]', form.local_storage );
		
	    });
	    
	   // Bind open "change-positions" form:
	      $(document).on( "click", 'a[data-sa*="change-positions"] ', function(e) {
		 e.preventDefault();
		 var $me =  $(this);
		 var dataSA = sa.dataSA( $me.attr('data-sa') );
		 $me.addClass('hide'); 
		 $('ul[data-sa="'+dataSA['ul']+'"]').toggleClass('hide');
		 var dataSA= sa.dataSA($me.attr('data-sa'));
		 $('#'+dataSA['toggle_by_id']).slideToggle(tan);
		 return;
	      });
	      
	    // Bind open "add" form: 
	       $(document).on( "click", 'a[data-sa*="-add"] ', function(e) {
		   e.preventDefault();
		   var $me =  $(this);
		   $me.addClass('hide'); 
		   var dataSA= sa.dataSA($me.attr('data-sa'));
		   $('#'+dataSA['toggle_by_id']).slideToggle(tan);
		   return;
	       });
	       
	    // Initiate TinyMCE Editor on content textarea:
	       
	       /*
	        * 	newdocument, 
	        * bold, italic, underline, strikethrough, 
	        * alignleft, aligncenter, alignright, alignjustify, styleselect, 
	        * formatselect, fontselect, fontsizeselect, cut, copy, paste, bullist, numlist, outdent, indent, blockquote, undo, redo, removeformat, subscript, superscript

	        */
	       
	       var sa_html = sa.data($('html').attr('data-sa'));
	       
	       if ( window.hasOwnProperty('tinymce') ) {
    	       		tinymce.init({ 
    	       		    
    	       		    schema: "html5",
    	       	            content_css : sa_html.path+"_css/style.min.css?"+ new Date().getTime(),
    		   
    	       		    selector:'textarea[name="content"]' ,
    	       		    plugins: "code table link",
    	       		    
    	       		    toolbar: [
    	       		              'undo redo | cut copy  paste | styleselect formatselect removeformat bold italic | table | removeformat code',
    	       		              'alignleft aligncenter alignright | bullist  numlist outdent indent | link '
    	       		    ],
    	       		    style_formats : [
    	       		   
    	       		        {title : 'figuur', 	block : 'p', classes : 'figuur'},
    	       		        {title : 'img-frame', 	block : 'p', classes : 'img-frame'},
    	       		        {title :'icon help' , 	block : 'p', classes :    'icon help'},
    	       	                {title :'icon account', block : 'p', classes :   'icon account'},
    	                        {title :'icon account logedin' , block : 'p', classes :   'icon account logedin'},
    	                        {title :'icon imagsize'  , block : 'p', classes :    'icon imagsize'},
    	                        {title :'icon imagsize lr'  , block : 'p', classes :  'icon imagsize lr'},
    	                        {title :'icon reset'   , block : 'p', classes :    'icon reset'},
    	                        {title :'icon settings'  , block : 'p', classes :   'icon settings'},
    	                        {title :'icon showmenu'  , block : 'p', classes :   'icon showmenu'},
    	                        {title :'icon location'    , block : 'p', classes :    'icon location'  },
    	                        {title :'icon device'    , block : 'p', classes :    'icon device'  },
    	                        {title :'icon device mobile'    , block : 'p', classes :    'icon device mobile'  }
    	       		        
    	 
    	       		        
    	       		        ],
    	       		       
    	       		      

    	       		    menu:{},
    	       	
    		
    	       		    setup : function(ed) {
    		                  ed.on('change', function(e) {
    		                      $('label[for="content"]').addClass('changed');
    		                      $('textarea[name="content"]').val(ed.getContent());
    		                      $('textarea[name="content"]').trigger('change');
    		                  });
    		                  
    		                  if ($('textarea[name="content"]').hasClass('changed')) {
    		           	   $('label[for="content"]').addClass('changed');
    		                  }
    		                  
    	       		    },
    	       
    	       		   
    	       
    	       		});
	       }
	       
	 // Remove warning class on btn at click
	    $(document).on( "click", '.btn.warning', function(e) {
		$(this).removeClass('warning');
		return;
	    });
	       
		
	    
	},
	
	local_storage: function(e) {
	    
	//  Skip storage of a change on the field "publish" since it is directly saved in database.
	    if ( $(this).hasClass('publish') ) {return;}
	    
	    e.preventDefault();
	    var $form  = $(this).parents('form');
	   
	    
	//  Set type of custom element, for standard form elements: null:    
	    var ce_type = this.nodeName.toLowerCase();
	    
	    
	    var dataCE = {};
	   
	    
	    if ( $(this).filter('[data-ce]').size() == 1){
		dataCE = ce.data($(this).attr('data-ce'));
		ce_type = dataCE['type'];
	    }
	
	    
        //  Set local storage dependant of ce_type:
	    var name   = null;
	    var value  = null;
	   
	    switch (ce_type) {
	    
	    	case 'radio':	
	    	    
	    	    		name = dataCE['name'];
	    	    
	    	    		if ( $(this).hasClass('checked') ) {
	    	    		    value = dataCE['value'];
	    			} else {
	    			    value = null;
	    			}
	    	    		
	    			$('[data-ce*="name:'+name+';"]').addClass('changed');
	    			break;
	    
	    	case 'input':
	    	case 'select':
	    	case 'textarea':
	    	    		
	    			name   = $(this).attr('name');
	    			value  = $(this).val();
	    			$(this).addClass('changed');
	    			break;
	    			
	    	default:	ce_type = null;
	    	    
	    
	    } 
	    
	    if (ce_type !== null ) {
		form.data.set($form, name, value);
		$form.find('.msg').removeClass("warning").text("").slideUp(tan);
	    }
	    return;
	    
	},
		
	reload: function(e){
	    e.preventDefault();
	    
	    var remove = confirm('Alle aanpassingen ongedaan maken?')
	    if (remove == true) {
	    // Remove locala data for this form:   
		var $form = $(this).parents('form');
	        form.data.remove($form);
	    
	     // Relaod page from server:
	        window.location.reload(true);
	    }
	    return;
	
	},
	
	save_category: function(data){
	   
	    
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
	    	 if ( (no_redirect ==="on" )   ) { 
	    	     window.console.log(JSON.stringify(data));
	    	 } else {
	    	     window.location.href = data.redirect;
	    	 }
	    	return;
	},
	
	add_record: function(data){
	    if (debug =="on") {
	        window.console.info('form.add_record:');
	        window.console.info(JSON.stringify(data));
	    }
	    
	    //  Get form object:
	    	var $form = $('#'+data.form_id);
	    	
	    
	    //	Stop on error
	        if (data.meta.error < 0) {
	            window.console.error(data.meta.msg);
	            $form.find('.msg').html(data.meta.msg).addClass('warning').slideDown(tan);
	            if (data.hasOwnProperty('field_warning')){
	        	 $form.find('[name="'+data.field_warning+'"]').addClass('warning');
	            }
	         
	            if ( data.hasOwnProperty('error_field') ) {
	        	 window.console.error(data.error_field);
	        	 $('[name="'+data.error_field+'"], .btn[data-sa*="'+data.error_field+'"]').addClass('warning');
	            }
	            return;
	        }
	    
	    //	Clear local data:
	    	form.data.remove($form);
	    
	    //	Reload page:
		window.location.href = data.redirect;
	 
	    	return;
	},
	
	

	change_positions: function(data){
	    
	    
	    //  Get form object:
	    	var $form = $('#'+data.form_id);
	    
	    //	Stop on error
	        if (data.meta.error < 0) {
	            window.console.log($form.size());
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
	     //window.console.log(JSON.stringify(data));
	     // window.console.info(data.redirect);
	    	return;
	},

	
	check_pattern: function($obj){
	    var str_pattern = $obj.attr('pattern');
            if (debug === "on"){  window.console.log('pattern: '+str_pattern);}
            var pattern = new RegExp(str_pattern);
            if (pattern.test($obj.val()) ) {
        	$obj.removeClass('warning');
          	return true;
            } 
            $obj.addClass('warning'); 
            return false;
	},
	
	check_required: function($obj){
	    var val = $obj.val();
	        val = val.trim();
	    
            if (val.length > 0) {
        	$obj.removeClass('warning');
          	return true;
            } 
            $obj.addClass('warning'); 
            return false;
	},
		
	data: {
	    
	    	
	    	key: function ($form){
	    	  
	    	    
	    	   var 		key =  $form.attr('id');
	    	
	    	   		if ($form.find('input[type="hidden"][name="PARENT_ID"]').size() == 1) {
	    	   		    key +=  $form.find('input[type="hidden"][name="PARENT_ID"]').val(); 
	    	   		}
	    	   	
	    	   		if ($form.find('input[type="hidden"][name="FID_CAT"]').size() == 1) {
	    	   		    key +=  $form.find('input[type="hidden"][name="FID_CAT"]').val(); 
	    	   		}
	    	   	
	    	   		key += $form.find('input[type="hidden"][name="ID"]').val();
	    	 
	    	   		return key;
	    	},
	
        	set:function($form, name, value){
        	    var key = form.data.key($form);
      
        	    var $data 	= localStorage.getItem(key) === null? {} : JSON.parse(localStorage.getItem(key));
        	    $data[name] = value;
        	    localStorage.setItem(key, JSON.stringify($data));
        	    return $data;
        	},
        	
            	unset:function($form, name){
            	   
        	    var key = form.data.key($form);
      
        	    var $data 	= form.data.get($form);
        	   
        	    if ($data.hasOwnProperty(name) ) {
        		delete $data[name];
        	    }
        	    
        	    var $obj = {}; //empty object.
        	    if (JSON.stringify($data) != JSON.stringify($obj) ){
        		localStorage.setItem(key, JSON.stringify($data));
        		return $data;
        		
        	    }
        	 
        	    if (JSON.stringify($data) == JSON.stringify($obj) ){
        		 form.data.remove($form);
        		return {};
        	    }
        	
        	    return $data;
        	    
        	},
        	
        	get:function($form){
        	    var key = form.data.key($form);
        	    return localStorage.getItem(key) === null? {} : JSON.parse(localStorage.getItem(key));
        	},
        	
        	remove:function($form){
        	    var key = form.data.key($form);
        	    localStorage.removeItem(key);
        	    var $form_change_positions = $form.siblings('a[data-sa*="change-positions"]');
        	    if ($form_change_positions.size() >0) {
        		$form_change_positions.removeClass('hide');
        		var dataSA = sa.dataSA( $form_change_positions.attr('data-sa'));
        		$('ul[data-sa="'+dataSA['ul']+'"]').removeClass('hide');
        		$form.siblings('a[data-sa*="-add"]').removeClass('hide');
        		if ($form.hasClass('ui-closed')){ $form.slideUp(tan);}
        	    }
        	    return;
        	},
        	
        	clear: function(){
        	    localStorage.clear();
        	},
        	
        	loadform: function(data) {
        	    window.console.info(JSON.stringify(data));
        	    var $form = $('#'+data.form_id);
        	    if ( $form.size() !== 1) {
        		window.console.error('form_id: '+data.form_id+' is not found.');
        		return;
        	    }
        	    
        	    $form.find('.new').remove();
        	    
        	    if (data.meta.error != 0) {
        		window.console.error('Error'+data.meta.error);
        		$form.find('.msg').addClass("warning").text(data.meta.msg).slideDown(tan);
        		return;
        	    }
        	    
        	   
        	    $form.find('input[type="text"][name], select[name], textarea[name]').each( function() {
        		
        		 
        		
        		var type =  $(this).filter('[type]').size() == 1? $(this).attr('type') : null;
        		
        		   switch ( type) {
        		   	case null:
        		   	if ($(this).is('select')) {
    		   		     var val = data.ID == "new"? -1: data.ar[$(this).attr('name')];
    		   		     val =  val == null? -1 : val;
    		   		   
    		   		     $(this).val(val);
    		   		     if ( val == -1) { 
    		   			
    		   			 var name = $(this).attr('name');
    		   			 $('[data-sa="'+name+'"]').html('').addClass('ui-closed');
    		   		     }
    		   		
    		   	    	}
        		   	break;
        		   	default:
        		   	var val = data.ID == "new"? '': data.ar[$(this).attr('name')];
        		   	$(this).val(val); 
        		   	if ( $(this).is( "textarea" ) ) {
        		   	   $(this).text(val);
        		   	}
        		   
        		   }
        		  
        	    });
        	    
        	    $form.find('[data-ce]').each( function() {
        		
        		 dataCE = ce.data($(this).attr('data-ce'));
        		
         		 if ( data.ar.hasOwnProperty( dataCE['name']) ) {
         		      var value = data.ar[dataCE['name']];
         		      switch(dataCE['type']){
         			    
         			    case 'radio': if (dataCE['value'] == value) {
         			    	    	       $(this).trigger('click');
         			    		   }
         					   break;
         			    }
         			
         		     }
        		
        	    });
        	   
        	    if ( window.hasOwnProperty('tinymce') ) {
            	    	if(tinymce.activeEditor != null){
            	    	    tinymce.activeEditor.setContent( data.ar['content']);
            	    	}
        	    }
        	    form.data.remove($form);
        	    $form.find('.changed').removeClass('changed');
        	    $form.find('.warning').removeClass('warning');
        	   
        	    return;
        	   
        	},
        	
        	
        	
        	load_named_objects:function ($form){
        	    
        	    var $data = form.data.get($form);
        	    
        	    
        	    
        	   
        	//   Load data for named objects:
    		     $.each( $form.find('[name]'), function() {
    		 
    		          var name = $(this).attr('name');
    		          if ( $data.hasOwnProperty(name) ) {
    			      $(this).val($data[name]);
    		    	      $(this).addClass('changed');
    		    	      if (this.hasAttribute('required') ) { form.check_required($(this)); }
    		    	      if (this.hasAttribute('pattern') )  { form.check_pattern($(this)); }
    		    	      
    		    	     
    		          }
    		      });
    		
    	        //    Load data for custom elements:
    		      $.each( $form.find('[data-ce]'), function() {
    		            dataCE = ce.data($(this).attr('data-ce'));
    		            var value = null;
    		             if ( $data.hasOwnProperty( dataCE['name']) ) {
    			        value = $data[dataCE['name']];
    			
    			       switch(dataCE['type']){
    			    
    			    	   case 'radio':	if (dataCE['value'] == value) {
    			    	    			if (!$(this).hasClass("checked")){
    			    	    			$(this).trigger("click");
    			    	    			}
    			    	    			$(this).addClass("checked");
    			    			   } else {
    			    			    $(this).removeClass("checked"); 
    			    			}
    			    			$(this).addClass('changed');
    						break;
    			    
    			          }
    			
    			
    		      }
    		   
    		     });
        	
    	        //  Change events for related fields
    		      $.each($data, function(name, value){
    		      if ( $('[data-sa="'+name+'"]').size() >0 ) {
		    		 $.each($('[data-sa="'+name+'"]'), function(){
		    		     $('[name="'+name+'"]').trigger('change');
		    		 });  
		    	      }
    		      });
        	    
        	}
        	
        	
	}
	

	
};
