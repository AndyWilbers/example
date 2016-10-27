//	_assets/js/_data.js



var data = {
		

		
//				
		parse: function (str_data){
			
			var data_array= str_data.split(";");
			var data = {};
			for (var index = 0; index < data_array.length; ++index) {
			   var d = data_array[index].split(":");
			   data[d[0]] = d[1];
			}
			return data;
		},
		
		stringify: function(data){
		    
		    var str_data	= '';
		    var glue		= '';
		 
		    $.each( data, function( index, value ){
			if (data.hasOwnProperty(index)){
    				str_data += glue+index+':'+value ;
    				glue = ';'
			}
		    });
		    
		    return str_data;
		}


	
};
