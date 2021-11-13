require(['jquery'], function(jQuery){
	jQuery(document).ready(function(){
		jQuery('.my-select-wrapper').click(function(){ jQuery('.storlist-wrapper').css('height','auto'); });
		jQuery('.marketplace-seller-collection').click(function(){
			jQuery('#seller_search_div').hide();
		});
		var ajax_request 	=	null;
		var ajax 			=	null;
		var requests 		=	[];
		jQuery( "#header_search" ).keyup(function(){ 
			jQuery('.glyphicon-search').hide();
			jQuery('.progress_image_outer').show();
			var term 			=	jQuery(this).val();
			clearTimeout(ajax_request);
			
			// abort all previous request;
			for(var i = 0; i < requests.length; i++)
				requests[i].abort();
			
			ajax_request =	setTimeout(function(){
				requests.push(
							jQuery.ajax({  
								url: "/region/ajax/search",  
								type: "post",
								beforeSend:function(){
									jQuery(".search-suggestions.narrow").empty().addClass('hide');
								},
								data: {  
									value: term
								},  
								success: function( data ) {  
									jQuery(".search-suggestions.narrow").html(data).removeClass('hide');
									jQuery('.glyphicon-search').show();
									jQuery('.progress_image_outer').hide();
								}  
							})
				);    
			},500);
		});
		
		
		/*
			Seller Product Search
		*/
		
		var ajax_request_seller 	=	null;
		var ajax 			=	null;
		var requestsSeller 		=	[];
		jQuery( "#seller_search" ).keyup(function(){ 
			jQuery('.search_glyp').hide();
			jQuery('.progress_image_new').show();
			
			var html = "";
			var seller_id 	= jQuery(this).attr('rel');
			var search_term = jQuery(this).val();
			var baseUrl = window.authenticationPopup.baseUrl;
			
			clearTimeout(ajax_request_seller);
			
			// abort all previous request;
			/* for(var i = 0; i < requestsSeller.length; i++)
				requests[i].abort(); */
			
			ajax_request_seller =	setTimeout(function(){
				requestsSeller.push(
							jQuery.ajax({  
								url : "/region/ajax/product", 
								type: "post",
								beforeSend:function(){
									jQuery("#seller_search_div").empty().addClass('hide');
								},
								data: {  
									search_term: search_term,
									seller_id: seller_id
								},  
								success: function( data ) {  
									
									 jQuery("#seller_search_div").show(); 
									jQuery("#seller_search_div").html(data).removeClass('hide');
									jQuery('.glyphicon-search').show();
									jQuery('.progress_image_new').hide();
								}  
							})
				);    
			},500);
		});
		
		
		/* jQuery('#seller_search').keyup(function(){
			
			jQuery('.search_glyp').hide();
			jQuery('.progress_image_inner').show();
			
			var html = "";
			var seller_id 	= jQuery(this).attr('rel');
			var search_term = jQuery(this).val();
			var baseUrl = window.authenticationPopup.baseUrl;
			
			
			jQuery.ajax({
				url : "/region/ajax/product",
				dataType : 'json',
				type : 'POST',
				data:{'seller_id':seller_id,'search_term':search_term},
				success : function(data){
					
					jQuery.each(data, function(index, product){
						console.log(product);
						html+='<div class="ember-view search-shop-group search-results">';
						html+='<div class="ember-view search-result">';
						html+='<a href="javascript: void(0);" class="body quickview weltpixel-quickview" data-quickview-url="'+baseUrl+'weltpixel_quickview/catalog_product/view/id/'+product.entity_id+'">';
						html+='<div class="image">';
						html+='<span class="ember-view image-container">';
						html+='<img class="image" src="http://ecommerce.stage02.obdemo.com/media/catalog/product/cache/small_image/175x179/beff4985b56e3afdbeabfc89641a4582'+product.small_image+'" alt="" title="">';
						html+='</span>';
						html+='</div>';
						html+='<div class="heading"><span class="title"> '+product.name+'</div>'
						
						html+='<div class="price">$ '+Math.round(product.price).toFixed(2)+' </div></a></div></div>';
						
					});
					jQuery('.progress_image_inner').hide();
					jQuery('.search_glyp').show();
					jQuery('#seller_search_div').show(); 
					jQuery('#seller_search_div').html(html);
				}
			});
		}); */
		
		jQuery('#btnSubmit').click(function(){
			
			var city_id = jQuery('#city_id').val();
			var area_id = jQuery('#area_id').val();
			
			city_id = city_id.replace(/\s/g, "");
			area_id = area_id.replace(/\s/g, "");
			if(city_id=="" && area_id==""){
				jQuery('.error_msg').css('display','block');
				jQuery('.error_msg_zone').css('display','none');
				return false;
			}else if(area_id==""){
				jQuery('.error_msg_zone').css('display','block');
				jQuery('.error_msg').css('display','none');
				return false;
			}
		});
		
		jQuery('.close').click(function(){
			jQuery(this).parent().css('display','none');
		})
		jQuery('.city_dropdown').change(function(){
			var url="";
			var html="";
			
			 var city_id = jQuery(this).val();
		
			try {
				jQuery('.progress_image_inner').show();
					jQuery.ajax({
					url : "/location/ajax/index",
					dataType : 'json',
					type : 'POST',
					data: { city_id: city_id},
					success : function(data){
						if(data.length>0){
							html+="<option value=''>--No zone selected--</option>";
							jQuery.each(data, function(idx, obj) {
								html+="<option value="+ obj.area_id +">"+obj.default_name+"</option>";
							});
							
						}else{
							html+="<option value=''>--No zone found--</option>";
						}
						jQuery('.area_dropdown').html(html);
						jQuery('.progress_image_inner').hide();
					}
				});
			}
			catch(e) {}
		});
		
		
		/* Zone According City On Store List Page*/
		
		jQuery('#select_region_again').change(function(){
			//alert(123);return false;
			var url="";
			var html="";
			
			 var city_id = jQuery(this).val();
		
			try {
				
					jQuery.ajax({
					url : "/location/ajax/index",
					dataType : 'json',
					type : 'POST',
					data: { city_id: city_id},
					success : function(data){
						if(data.length>0){
							html+="<option value=''>--No zone selected--</option>";
							jQuery.each(data, function(idx, obj) {
								html+="<option value="+ obj.area_id +">"+obj.default_name+"</option>";
							});
							
						}else{
							html+="<option value=''>--No zone found--</option>";
						}
						jQuery('.select_area_again').html(html);
						//jQuery('.progress_image_inner').hide();
					}
				});
			}
			catch(e) {}
		});
		
		var owl = jQuery('.partners-carousel');
		owl.owlCarousel({
			loop:true,
			margin:10,
			nav:true,
			responsive:{
				0:{
					items:1
				},
				600:{
					items:2
				},
				992:{
					items:3
				},
				1200:{
					items:4
				}
				
				}
		});
		jQuery('.partner_url').click(function(){
			url = jQuery(this).attr('href');
			if(url=="#location"){
				jQuery('#head_msg').show();
			}
		})
		
		
	/*Category Dropdown*/
	jQuery("#jquery-accordion-menu").jqueryAccordionMenu(); 
	/*quantity bar quickview*/
	jQuery('<div class="quantity-nav"><div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div></div>').insertAfter('.custom_quantity .control input');
		jQuery('.custom_quantity .control').each(function() {
		  var spinner = jQuery(this),
			input = spinner.find('input[type="number"]'),
			btnUp = spinner.find('.quantity-up'),
			btnDown = spinner.find('.quantity-down'),
			min = input.attr('min'),
			oninput = input.attr('oninput','validity.valid||(value="")'),
			max = input.attr('max');

		  btnUp.click(function() {
			var oldValue = parseFloat(input.val());
			if (oldValue >= max) {
			  var newVal = oldValue;
			} else {
			  var newVal = oldValue + 1;
			}
			if(newVal>0){
				spinner.find("input").val(newVal);
				spinner.find("input").trigger("change");
			}
		  });

		  btnDown.click(function() {
			var oldValue = parseFloat(input.val());
			if (oldValue <= min) {
			  var newVal = oldValue;
			} else {
			  var newVal = oldValue - 1;
			}
			if(newVal>0){
				spinner.find("input").val(newVal);
				spinner.find("input").trigger("change");
			}
		  });

		});
		
		jQuery("#header_search").keyup(function(e){
			 jQuery(".search-wrapper").show();
			 e.stopPropagation();
		});
		jQuery(".search-wrapper").click(function(e){
			 e.stopPropagation();
		});
		jQuery(document).click(function(){
			 jQuery(".search-wrapper").hide();
		});
		jQuery(document).click(function(){
			 jQuery(".dom-selectizing").removeClass("open");
		});
		
		jQuery('#header_area_id').change(function(){
			//var msg = confirm("Change location may affect cart ");
				
         		
				$.confirm({
					title: 'Change Location',
					content: 'Change Location May Affect Your Cart!',
					buttons: {
						confirm: function () {
							jQuery("#header_location_form").submit();
						},
						cancel: function () {
							
						},
						
					}
				});
			
		})
		jQuery('#body_area_id').change(function(){
			jQuery('#body_location_form').submit();
		})
		
		
		
		/* Location Selected On  Page Load */
		
		var url="";
		var html="";
			
		var city_id = jQuery('.city_dropdown').val();
		var area_id = jQuery('#area_id_hidden').val();
		//alert(city_id);
		//alert(area_id);
		try {
			//jQuery('.progress_image_inner').show();
				jQuery.ajax({
				url : "/location/ajax/index",
				dataType : 'json',
				type : 'POST',
				data: { city_id: city_id},
				success : function(data){
					if(data.length>0){
						
						jQuery.each(data, function(idx, obj) {
							if(area_id==obj.area_id){
								html+="<option selected value="+ obj.area_id +">"+obj.default_name+"</option>";
							}else{
								html+="<option value="+ obj.area_id +">"+obj.default_name+"</option>";
							}
						});
						
					}else{
						html+="<option value=' '>--No zone found--</option>";
					}
					jQuery('.area_dropdown').html(html);
					//jQuery('.area_dropdown').html(html);
					//jQuery('.progress_image_inner').hide();
				}
			});
		}
		catch(e) {}
		/* For Chekout Page*/
		jQuery(document).on ('click', '.add_new_address', function(){
			jQuery("#shipping_address_id").val('').trigger('change');
		});
		
		jQuery(document).on ('click', '.customer-address', function(){
			var addValue = jQuery(this).attr('rel');
			jQuery("#shipping_address_id").val(addValue).trigger('change');
			jQuery(document).find('.customer-address').removeClass('active');
			jQuery(this).addClass('active');
			
		});
		
		jQuery(document).on('click', '.add_new_address', function(){
			var areaName = jQuery('#area_name').val();
			var cityName = jQuery('#city_name').val();
			//alert(cityName);
			jQuery(document).find('#area').val(areaName);
			jQuery(document).find('#state').val(cityName);
		});
		jQuery(document).on('click', '.method-data', function(){
			console.log("gbhgbhbhbh");
			jQuery(document).find('.method-data').removeClass('active');
			jQuery(this).addClass('active test');	
			var ptype = jQuery(this).attr('rel');
			console.log(ptype);
			jQuery(document).find("#iwd_opc_payment_method_select").val(ptype).trigger('change');
			jQuery(document).find("#iwd_opc_payment_method_select").val(ptype).trigger('change');
			//jQuery(document).find("#iwd_opc_payment_method_select").val('').removed();
			//jQuery('#iwd_opc_payment_method_select option[value='+ptype+']').attr("selected", "selected");
			//jQuery(document).find("#iwd_opc_payment_method_select").addClass('bbgbgbgbg');
		}); 
		jQuery(document).find('.method-data:eq(1)').addClass('active');
		jQuery(document).find("#iwd_opc_payment_method_select").val('cashondelivery').trigger('change');
		
		
		jQuery(document).on('click', '.new_add_btn', function(){
			var error=0;
			jQuery("#addAddressForm input").each(function(){
				if(jQuery(this).val() == "")
				{
					error=1;
				}
			});
			if(error==1){
				jQuery('.error_msg').show();
				return false;
			}
			
		});	
		jQuery('li.active').parentsUntil( "ul.categories-list" ).css('display','block');
		
	});

	/*Sticky Sidaber Checkout Page*/
		jQuery(window).scroll(function(){
			if (jQuery(window).scrollTop() >= 125) {
			   jQuery('#iwd_opc_top').addClass('fixed-sidebar');
			}
			else {
			   jQuery('#iwd_opc_top').removeClass('fixed-sidebar');
			}
		});
	/*Sticky Sidaber Checkout Page END-----*/

			
});
