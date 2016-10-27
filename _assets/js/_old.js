var Tan = 1000;
var Path= "";
var booData = false;
var booDataDO= false;

$(document).ready(function() {
	Tan = $('html').attr('data-Tan');
	Path = $('html').attr('data-Path');
	

	DocReady();
	FormReady (); // Brings form in default state and bind.
	PopUpReady();
		
		
	MsgboxReady ();
		
		
//	Hide login form:
	if ($('html').attr('data-status')=="LogOn") {
		$('#login_admin, #login_screen').addClass("hidden");
	}

	var Template= 'admin';
	
/* Article Editor setup: 
   -------------------------------------------------------------------------------------------------*/	
   
   // 	Settings TinyMCE:
 		$('#AdminArticle textarea').tinymce({
	     // Location of TinyMCE script
            script_url : Path+'_factory/template/js/vendor/tinymce/tinymce.min.js',
		   	plugins: "code, table",
          	tools: "inserttable",
		   	schema: "html5",
		   	content_css :  Path+'_factory/template/css/normalize.css,'
		                 +Path+'_factory/template/'+Template+'/css/main.css,',

          	menu : { 
				file   : {title : 'File'  , items : 'newdocument'},
				edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'},
				insert : {title : 'Insert', items : 'link media | template hr'},
				view   : {title : 'View'  , items : 'visualaid'},
				format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
				table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'},
				tools  : {title : 'Tools' , items : 'spellchecker code'}
    		}
 		});
		
	 // AddPopUp Link:
  		$('button[data-button-id="AddPopUp"]').unbind('click');
		$('button[data-button-id="AddPopUp"]').bind('click',function(e){
			e.preventDefault();
			ClickInsertLink ({
				MsgBoxID: "#msgbox_select_note",
				AttrReplaceBy:"Note_PopUp",
				ClassReplaceBy:"note_popup"
			});	  
		 }); 
			 
	 // AddIndex Link:
  		$('button[data-button-id="AddIndex"]').unbind('click');
		$('button[data-button-id="AddIndex"]').bind('click',function(e){
			e.preventDefault();
			ClickInsertLink ({
				MsgBoxID: "#msgbox_select_index",
				AttrReplaceBy:"Index_Link",
				ClassReplaceBy:"index_link"
			}); 
		});

	 // AddArticle Link:
  		$('button[data-button-id="AddArticle"]').unbind('click');
		$('button[data-button-id="AddArticle"]').bind('click',function(e){
			e.preventDefault();
			ClickInsertLink ({
				MsgBoxID: "#msgbox_select_article",
				AttrReplaceBy:"Article_Link",
				ClassReplaceBy:"article_link"
			}); 
		});
		
	// AddUrl Link:
  		$('button[data-button-id="AddUrl"]').unbind('click');
		$('button[data-button-id="AddUrl"]').bind('click',function(e){
			e.preventDefault();
			ClickInsertLink ({
				MsgBoxID: "#msgbox_select_url",
				AttrReplaceBy:"Url_Link",
				ClassReplaceBy:"url_link"
			}); 
		});	
		
	// AddKey Link:
  		$('button[data-button-id="AddKey"]').unbind('click');
		$('button[data-button-id="AddKey"]').bind('click',function(e){
			e.preventDefault();
			ClickInsertLink ({
				MsgBoxID: "#msgbox_select_key",
				AttrReplaceBy:"Key_Link",
				ClassReplaceBy:"key_link"
			}); 
		});	
	
	// AddImage Link:
  		$('button[data-button-id="AddImage"]').unbind('click');
		$('button[data-button-id="AddImage"]').bind('click',function(e){
		 
			e.preventDefault();
			
			var Data = xml_meta_field ("FID_CATEGORY", -1);
			    Data += xml_meta_field ("CallBack", "ClickInsertImages");
				
		    serverrequest_do ("Media", "GET", "ImagePickerForAricles", Data);
 
		});			
		
		if ($('#AdminQuestion').size()>0) {DocReadyAdminQuestion();}
		
/*  Checklist Editor setup:
    --------------------------------------------------------------------------- */		
    if ($('#AdminCheck').size()>0) {CheckReady_admin();}
	
/*  Key Editor setup:
    --------------------------------------------------------------------------- */		
    if ($('#AdminKey').size()>0) {KeyReady_admin();}
	
	
}); // END document ready

/* DocReady Handlers: [DocReady_FreeFormatedName] overwite when DB.error = 0 (pass)
   _________________________________________________________________________________________________________________ */
   function DocReady_AdminNotes() {
      
	   //	Load notes:
		    if($('#AdminNotes').size()==1 ){	
				$('#screen').addClass('hidden');	
				$('#popup').addClass('hidden');	
				$('form.popup').addClass('hidden');
				requestLoadHTML ('note_ReadNotes', $('#AdminNotes div.structuur'));
		    }
	   
   } // END DocReady_AdminNotes()
   
   function DocReady_AdminArticles() {
	
	   //	Load Articles:
		    if($('#AdminArticles').size()==1 ){	
			    fncRestoreVerticalPosition($('#AdminArticles'));	
				$('#screen').addClass('hidden');	
				$('#popup').addClass('hidden');	
				$('form.popup').addClass('hidden');
				requestLoadHTML ('Article_ReadArticles', $('#AdminArticles div.structuur'));
		    }
	   
   } // DocReady_AdminArticles()
   
   function DocReady_AdminIndices() {
	   
	   //	Load Indices:
		    if($('#AdminIndices').size()==1 ){	
				$('#screen').addClass('hidden');	
				$('#popup').addClass('hidden');	
				$('form.popup').addClass('hidden');
				requestLoadHTML ('Index_ReadIndices', $('#AdminIndices div.structuur'));
		    }  
   } // END DocReady_AdminIndices()
   
   function DocReady_AdminUrls() {
	   
	   //	Load Urls:
		    if($('#AdminUrls').size()==1 ){	
				$('#screen').addClass('hidden');	
				$('#popup').addClass('hidden');	
				$('form.popup').addClass('hidden');
				requestLoadHTML ('Url_ReadUrls', $('#AdminUrls div.structuur'));
		    }  
   } // END DocReady_AdminUrls()
   
   function DocReady_AdminMedia() {
	   
	   //	Load Urls:
		    if($('#AdminMedia').size()==1 ){
				fncRestoreVerticalPosition($('#AdminMedia'));	
				$('#screen').addClass('hidden');	
				$('#popup').addClass('hidden');	
				$('form.popup').addClass('hidden');
			    requestLoadHTML ('Media_ReadRecords', $('#AdminMedia div.structuur'));
		    }  
   } // END DocReady_AdminMedia()

/* PreLoad Handlers: [free format name]: actions to take before filling in form in standard way.
   _________________________________________________________________________________________________________________ */
   function MediaAddImages(DB) {
	 	$('div.imagepicker').html(DB.Images);
		$('div.imagepicker>img').unbind("click").bind("click",function(e){
			var MySrc = $(this).attr("src");
			$('input[data-db-field=Content]').val(MySrc);
			
			$('[data-img-selected]').attr('src',MySrc);
		
		});
		
		
	    return;
   } //END  MediaAddImages(DB)....................................................................................
   
   

/* PostLoad Handlers: [free format name]: actions to take after filling in form in standaard way.
   _________________________________________________________________________________________________________________ */ 
   


/*  _prepare handlers: [ControlleNameMethodName_prepare | MethodName_prepare ]
   _________________________________________________________________________________________________________________ */
  /*
  	function  NoteRecordByID_prepare(Me){
		
		
		
		 if (typeof $(Me).attr("data-DATA") !== "undefined") {
			var Data =  XmlDataDATA (Me);
		 } else {
			var Data =  xml_meta_field ('ReturnID', 'AdminNote');
			    Data +=  xml_meta_field ('RecordByID', '#NOT_SET');
                Data +=  xml_meta_field ('FID_MENU', '-1');
				 Data +=  xml_meta_field ('CallBackAction', 'onbekend');
		 }
	
		
		 alert(Data);
		ServerRequest ('Note', 'GET', 'RecordByID', Data);
   	}
   
    function  NoteMenuByID_prepare(Me){
	
		
		 if (typeof $(Me).attr("data-DATA") !== "undefined") {
			var Data =  XmlDataDATA (Me);
		 } else {
			var Data =  xml_meta_field ('ReturnID', 'AdminNoteMenu');
			    Data +=  xml_meta_field ('RecordByID', '#NOT_SET');
                Data +=  xml_meta_field ('FID_PARENT', '-1');
				Data +=  xml_meta_field ('FID_ARTICLE', '-1');
		 }
		 alert(Data);
		
		
		ServerRequest ('Note', 'GET', 'MenuByID', Data);
  	}
   */
	  
	   /* 	MethodName__prepare handlers: [MethodName_prepare ]
      		_________________________________________________________________________________________________________________ */
			 function Publish_prepare(Me){
			 
				
					var Data= XmlMeta ({
						RecordID: $(Me).siblings('span[data-record-id]').attr('data-record-id')
					});
					
					if($(Me).parent('li').size()>0){
							Data+= XmlMeta ({Type:  'item'});
					} else {
							Data+= XmlMeta ({Type:  'category'});
					}
					
					var publish =1;
					//  toggle publish:
						if( $(Me).hasClass("publish_on") ){
								var publish =0;
						}else {
								var publish =1;
						}
						Data+= XmlRecord ({Publish:  publish });
					
					return Data;
			 }
	 		 

/*  _ServerRequest handlers: [ControlleNameMethodName_ServerRequest | MethodName_prepare ]
   _________________________________________________________________________________________________________________ */	 
	 function MediaUploadImage_ServerRequest(Me) { //NOT IMPLEMENTED
		 
		 var UploadForm = $(Me).parents('form');
		 
		 alert(UploadForm.attr('id'));
		 
		 var formData = new FormData("#"+UploadForm.attr('id'));
		  alert('ajax');
	/*	 
    $.ajax({
        url: 'upload.php',  //Server script to process data
        type: 'POST',
        xhr: function() {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            if(myXhr.upload){ // Check if upload property exists
                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        //Ajax events
        beforeSend: beforeSendHandler,
        success: completeHandler,
        error: errorHandler,
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });*/
		 return;
	 }
 	

/* _success handlers: [ControlleNameMethodName_success | MethodName_success ]
   _________________________________________________________________________________________________________________ */
   /*
    function  NoteRecordByID_success(DB){
		alert('NoteRecordByID_success');
		if ( DB.error <0 ) {
			alert('Error'+DB.error+': '+DB.description);
		}else {
			
		}
		return;
		
   	}
   
    function  NoteMenuByID_success(DB){
		alert('NoteMenuByID_success');
		if ( DB.error <0 ) {
			alert('Error'+DB.error+': '+DB.description);
		}else {
		}
		return;
		
  	}
   */
   
   /* Method_success handlers: [ MethodName_success ]:
      _________________________________________________________________________________________________________________ */
	  
		 function Publish_success(DB){
			
	 		 if (DB.error==0) {
				
			    if(DB.hasOwnProperty('ItemOrCategory')) {
				
					if (DB['ItemOrCategory']=="item")  {
						if(DB.Publish ==1 ) {
							$("span[data-record-id="+DB.ID+"]").siblings('button.publish').filter('.jsItem').removeClass('publish_off').addClass('publish_on');
						}
						else {
						  $("span[data-record-id="+DB.ID+"]").siblings('button.publish').filter('.jsItem').removeClass('publish_on').addClass('publish_off')
						}
					} else {
						if(DB.Publish ==1 ) {
							$("span[data-record-id="+DB.ID+"]").siblings('button.publish').filter('.jsCat').removeClass('publish_off').addClass('publish_on');
						}
						else {
							$("span[data-record-id="+DB.ID+"]").siblings('button.publish').filter('.jsCat').removeClass('publish_on').addClass('publish_off')
						}
					}

					
					
					
				} else {
			
				 if(DB.Publish ==1 ) {
					 $("span[data-record-id="+DB.ID+"]").siblings('button.publish').removeClass('publish_off').addClass('publish_on');
				 }
				 else {
					  $("span[data-record-id="+DB.ID+"]").siblings('button.publish').removeClass('publish_on').addClass('publish_off')
				 }
				}
				  FormReady (); 
			 }
		 } //END Publish_success(DB).......................................................................................
	
	
	

   /* ArticleMethod_success handlers: [ ArticeMethodName_success ]:
      _________________________________________________________________________________________________________________ */
	  
		function ArticleRecordByID_success(DB) {
			$("#"+DB.ReturnID+' div.message').removeClass('warning');
			if (DB.error >0 ) {
				$("#"+DB.ReturnID+' div.message').addClass('warning');
				if($("#"+DB.ReturnID+' div.message').size()>0) {
					$("#"+DB.ReturnID+' div.message').html(DB.description);
				} else {
					alert(DB.description);
				}
				
			}  else {
				if (typeof window['DocReady_'+DB.DocReady] === "function") {
					window['DocReady_'+DB.DocReady].apply(null); 
				} else {
					if(DB.has_data==1 ){
						$( "#"+DB.ReturnID + ' input[data-db-field], '+
						   "#"+DB.ReturnID + ' textarea[data-db-field]').each(function(index, element) {
							   if(DB.has_record==1) {
									$(this).val(DB[$(this).attr('data-db-field')]);
							   }else{
								   $(this).val('');
							   }
						});
						$( "#"+DB.ReturnID + ' select[data-db-field]').each(function(index, element) {
								$(this).val(DB[$(this).attr('data-db-field')]);
						});	
					} else {
						$("#"+DB.ReturnID + ' input[data-db-field]').val('');
						$("#"+DB.ReturnID + ' textarea[data-db-field]').val('');
						$("#"+DB.ReturnID+' div.message').html('<p>'+DB.description+'</p>');
						$("#"+DB.ReturnID+' div.message').removeClass("hidden"); 
					}
				}
			}
			return;
	   } // END ArticleRecordByID_success(DB)..............................................................................
   





   
   
 /* _error handlers:[ControlleNameMethodName_error | MethodName_error ]
   _________________________________________________________________________________________________________________ */
   
   
   
   
  /* post success handlers:
   _________________________________________________________________________________________________________________ */ 
    
   function authenticationLogin_template(DB) {
	 
	   $('nav.login_link *.jsUserName').html(DB.FullName);
	   $('nav.login_link *').toggleClass('hidden');
	   $('#login_admin, #login_screen').addClass("hidden");
	   requestView (DB.HtmlOnload);
	
	 
   }
   
   function authenticationLogout_template(DB) {
	   $("#main").html('');
	    $('#login_admin input').val('');
	   $('#login_admin, #login_screen').removeClass("hidden");
	   
   }
    
   
 /* Functions for Article Editor:
   _________________________________________________________________________________________________________________ */ 
     function ClickInsertLink (Params) {
	 
		 	// Inititalization:
			  
			   
			 	var MsgBoxID = Params["MsgBoxID"];
				var MyTinyMce = $('#AdminArticle textarea').tinymce();
			 	var Node = $($('#AdminArticle textarea').tinymce().selection.getNode());
			 	var ObjSelect = $(MsgBoxID +' select').eq(0);
				var AttrReplaceBy =  Params["AttrReplaceBy"];
				var ClassReplaceBy =  Params["ClassReplaceBy"];
				
				
				
			//	Inititalization SelectBox:
				 if (typeof Node.attr("data-record-id") !== "undefined") {
					  ObjSelect.val(Node.attr('data-record-id'));
					  ObjSelect.attr('data-new',"no");
				 } else {
					 ObjSelect.val(-1);
					  ObjSelect.attr('data-new',"yes");
				 }

				
				
		/* OBSOLETE:
			     ObjSelect.val(MyID);	
			    ObjSelect.attr('data-new',"yes");
				alert(Node.attr("data-replace-by")+ '| '+AttrReplaceBy);
				if (Node.attr("data-replace-by")==AttrReplaceBy) {
					 ObjSelect.val(Node.attr('data-record-id'));
					 ObjSelect.attr('data-new',"no");
				 } 
				 else {
					  ObjSelect.val('-1');
				 }*/
			
			//	Bind OnChange Event on SelectBox: 
				ObjSelect.unbind("change");
			 	ObjSelect.bind("change",function(e) {
					
					if($(this).val()!=-1) {
						if($(this).attr('data-new')=="yes") {
						//	MyTinyMce.execCommand('mceReplaceContent',false,'<span class="'+ClassReplaceBy+'" data-replace-by="'+AttrReplaceBy+'" data-record-id="'+$(this).val()+'">{$selection}</span>');	
						    MyTinyMce.execCommand('mceReplaceContent',false,'<span class="'+ClassReplaceBy+'" data-record-id="'+$(this).val()+'">{$selection}</span>');	
						} else {
							Node.attr("data-record-id",$(this).val());
						}
				
					 } else {
						if($(this).attr('data-new')=="no") {
						 	var Content = Node.html();
						 	Node.remove();
						 	MyTinyMce.execCommand('mceInsertContent',false,Content);
						}
				 	}
					$(MsgBoxID).addClass("hidden");
					$("#msgbox").addClass('hidden');
					$("#screen_msgbox").addClass('hidden');	
				});			
			 
			 // Show Message box:
			 	$(MsgBoxID ).removeClass("hidden");
			 	$("#msgbox").removeClass('hidden');
			 	$("#screen_msgbox").removeClass('hidden');
		 
	 } // END  ClickInsertLink (Params).....................
	
	
	  function ClickInsertImages () {
		  
		    
	 
		 	// Inititalization:
			 // Read DOM elements:
			 	var MsgBoxID = '#msgbox_select_image';
				var MyTinyMce = $('#AdminArticle textarea').tinymce();
			 	var Node = $($('#AdminArticle textarea').tinymce().selection.getNode());
				var MySelectedImageContainer = $('#msgbox_select_image div.selected_images');
				var MySelectedImages = $('#msgbox_select_image div.selected_images img');
				
				
			//  Load ImagePicker
			
			
				
			    var Data = xml_meta_field ("FID_CATEGORY", $('select[data-do="Media_GET_ImagePickerForAricles"]').eq(0).val());
		        ServerRequest ("Media", "GET", "ImagePickerForAricles", Data);
				
			
			//	Inititalization Selected Images:
			    MySelectedImageContainer.attr('data-new',"yes");
				if (typeof $(Node).attr("data-ID") !== "undefined" || $(Node).hasClass("imagecontainer")) {
					if (typeof $(Node).attr("data-ID") !== "undefined") {
							var ImgContainer = $(Node).parent();
					} else {
						   var ImgContainer = $(Node);
					}
					var HtmlImages ='';
					$(ImgContainer).children('img').each(function(index, element) {
                          HtmlImages += '<div data-record="" ><img data-ID= "'+$(this).attr('data-ID')+'"  src="'+$(this).attr('src') +'" /><button class= "btnClose"></button></div>';
                    });
					MySelectedImageContainer.attr('data-new',"no");
					
				} else {
					var HtmlImages ='';
				}
			    MySelectedImageContainer.html(HtmlImages);
				$(MySelectedImageContainer).children('div').children('button.btnClose').unbind('click').bind('click',function(e) {
						 $(this).parents('div[data-record]').remove();
				});
		
			
			//	Bind Click Event on Save button:
			    $('#msgbox_select_image button.save').unbind("click").bind('click', function(e) {
					
			 		var MyImagePicker = $('#msgbox_select_image div.imagepicker');
					var MySelectedImageContainer = $('#msgbox_select_image div.selected_images');
			 		var MySelectedImages = $('#msgbox_select_image div.selected_images img');
					var HtmlImages ='';
					
					var imgClass = "images";
					var IDs="";
					var Sep ='';
					MySelectedImages.each(function(index, element) {
						imgClass += MySelectedImages.size();
                        HtmlImages += '<img src="'+$(this).attr('src')+'" data-ID="'+$(this).attr('data-ID')+'" class="'+imgClass+'" />';
						imgClass = "next images";
						IDs+=Sep+$(this).attr('data-ID');
						Sep ='|';
                    });
					if (MySelectedImageContainer.attr('data-new')=="no") { ImgContainer.remove();}
					if (MySelectedImages.size()>0) {
						MyTinyMce.execCommand('mceInsertContent',false,'<div data-imagecontainerIDs="'+IDs+'" class="imagecontainer clearfix" >'+HtmlImages+'</div>');	
					}
				

					$('#msgbox_select_image').addClass("hidden");
					$("#msgbox").addClass('hidden');
					$("#screen_msgbox").addClass('hidden');	
				}); 	
			 
			 // Show Message box:
			 	$(MsgBoxID ).removeClass("hidden");
			 	$("#msgbox").removeClass('hidden');
			 	$("#screen_msgbox").removeClass('hidden');
		 
	 } // END  ClickInsertLink (Params).....................
	 
	
	
	
	 
	 function AddImageSetUI(DB){
	 	
		 $('div.imagepicker img').unbind("click").bind("click",function(e){
			 e.preventDefault();
		     
			 // Read DOM elements:
			 	var MyForm = $(this).parents('form.input_form');
			 	var MyImagePicker = $(this).parents('div.imagepicker');
			 	var MySelectedImages = $(MyImagePicker).siblings('div.top').children('div.selected_images');
			 	var MyMessageBox = $(MyImagePicker).siblings('div.top').children('div.message');
				
			// Initialize:
				var MaxSelected =4;
				var Nb = $(MySelectedImages).eq(0).children('div').size();
			 	$(MyMessageBox).html('').removeClass('warning');
				
			// 	In case the maximum number of selected items in not reached add image to selection:
				 if (Nb<MaxSelected ) {
					 var MySrc = $(this).attr('src');
					 $(MySelectedImages).append('<div data-record="" ><img  src="'+MySrc +'" /><button class= "btnClose"></button></div>');
					 $(MySelectedImages).children('div').children('button.btnClose').unbind('click').bind('click',function(e) {
						 $(this).parents('div[data-record]').remove();
					 });
				 } else {
					 $(MyMessageBox).html('<p>'+ADMIN_ARTICLE_MSG_MAX_NB_SELECTED_IMG+'<p>').addClass('warning');;
				 }
		 });
		 return;
	 } // END  AddImageSetUI(DB).....................
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
   