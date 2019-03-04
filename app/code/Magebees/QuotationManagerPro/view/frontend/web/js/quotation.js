/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
     'jquery/ui','mage/storage',
	 'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/error-processor',
	'Magento_Ui/js/modal/modal'
], function ($,ui,storage,priceUtils,errorProcessor,modal) {
  

    $.widget('magebees_quotation.magebeesQuote', {
		 pool:[],		
		 is_loading: 0,
	  	_create: function () {
         	 var self=this;
			
/*start script for manage customer create and address section on quote page*/
	$('body').on('click','#edit_ship_add',function(e){
	var address_id=$(this).attr('address_id');
	self.editAddressPopup(address_id);				
});
	
	$('body').on('click','#edit_bill_add',function(e){				
		var address_id=$(this).attr('address_id');
		self.editAddressPopup(address_id);		
	});	
				
	$('body').on('click','#btn_save_quote_add',function(e){	
		 e.preventDefault();
		$('#popup-modal-address').modal('closeModal');
		var action=$('#form-validate-quote-add-popup').attr('action');
		var params=$('#form-validate-quote-add-popup').serialize();			$('#shiiping_address_loading').css('display','block');
		 $.ajax({
			url: action,
			data: params,
			type: 'post',
			dataType: 'json',
			success: function (data) {
					$('#shiiping_address_loading').css('display','none');
					if(data.items)
					{					$("#quoteItemlist").replaceWith(("#quoteItemlist",data.items));
					}
			$('#popup-modal-address').modal('closeModal');
			//var address_type=$('#quote_add_type').val();
			var address_type=data.added_address_type;
			var is_custom=data.is_custom;
			var quote_address_id=data.quote_address_id;
			var save_in_add_book=data.save_in_add_book;
			var address=data.added_address;
			//if(address_type=='add_quote_ship_add')
			if(is_custom)
			{
				var ship_class="selected_address shipping_address_custom_"+quote_address_id;
				var ship_add_data_id="ship_add_data_custom_"+quote_address_id;
			}
			else
			{
				var ship_class="selected_address shipping_address_"+quote_address_id;
					var ship_add_data_id="ship_add_data_"+quote_address_id;
			}
			if(address_type=='shipping')
			{
				 $("ul#shipping_address_data" ).children().removeClass('selected_address');				 $("ul#shipping_address_data").children().find('.default_shipping').css('display','block');
				var ship_add_str='<li class="'+ship_class+'" address_id='+quote_address_id+'><div id="'+ship_add_data_id+'">'+address+'</div><button id=btn_default_shipping_'+quote_address_id+' class=default_shipping style=display:none;>Use Default</button>';
				if(save_in_add_book!=1)
				{
					ship_add_str=ship_add_str+'<a id=edit_ship_add address_id='+quote_address_id+'>Edit</a>';
					$('#add_quote_ship_add').css('display','none');
				}
				ship_add_str=ship_add_str+'</li>';	
if($('body').find('.shipping_address_custom_'+quote_address_id).length)
{
$('body').find('#ship_add_data_custom_'+quote_address_id).html(address);
$('body').find('.shipping_address_custom_'+quote_address_id).addClass('selected_address');
$('#'+ship_add_data_id).next().css('display','none');	
}
else
{	$('body').find('#shipping_address_data').append(ship_add_str);
}
			}
			else
			{
				 $( "ul#billing_address_data" ).children().removeClass('selected_address');					  $("ul#billing_address_data").children().find('.default_billing').css('display','block');
				if(is_custom)
			{
				var bill_class="selected_address billing_address_custom_"+quote_address_id;
					var bill_add_data_id="bill_add_data_custom_"+quote_address_id;
			}
			else
			{
				var bill_class="selected_address billing_address_"+quote_address_id;
				var bill_add_data_id="bill_add_data_"+quote_address_id;
			}
				var bill_add_str='<li class="'+bill_class+'" address_id='+quote_address_id+'><div id="'+bill_add_data_id+'">'+address+'</div><button id=btn_default_billing_'+quote_address_id+' class=default_billing style=display:none;>Use Default</button>';
				if(save_in_add_book!=1)
				{
					bill_add_str=bill_add_str+'<a id="edit_bill_add" address_id='+quote_address_id+'>Edit</a>';
					$('#add_quote_bill_add').css('display','none');
				}
				bill_add_str=bill_add_str+'</li>';	
if($('body').find('.billing_address_custom_'+quote_address_id).length)
{	$('body').find('#bill_add_data_custom_'+quote_address_id).html(address);	$('body').find('.billing_address_custom_'+quote_address_id).addClass('selected_address');	$('body').find('#btn_default_billing_'+quote_address_id).css('display','none');	
}
else
{	$('body').find('#billing_address_data').append(bill_add_str);
}						

			}
					
		if((address_type=='add_quote_ship_add')||(address_type=='shipping'))
		{
var enable_shipping=$('#enable_shipping').val();
	if(enable_shipping!=0)
	{
		var is_customer_login = window.magebeesQuoteConfig.is_customer_login;
		var address;
		var action = $('#quoteItemlist').attr('action');
		var quote_mask_id =data.mask_id;
		var storeCode = window.magebeesQuoteConfig.storeCode;
		if(is_customer_login)
		{

			var serviceUrl = action.replace("quotation/quote/updatePost", "rest/"+storeCode+"/V1/carts/mine/estimate-shipping-methods"); 
		}
		else
		{
	 var serviceUrl = action.replace("quotation/quote/updatePost", "rest/"+storeCode+"/V1/guest-carts/"+quote_mask_id+"/estimate-shipping-methods"); 
		}
		 payload = JSON.stringify({
			address: {
				'city': data.city,
				'country_id': data.country_id,
				'postcode': data.postcode,
			}
		}
	); 
	storage.post(
	serviceUrl, payload, false
	).done(
		function (result) {
			$('#shipping_method').html('');
			$.each(result, function(i, data) {	
		var priceFormat = window.magebeesQuoteConfig.priceFormat;
var formatted_price_incl_tax=priceUtils.formatPrice(data.price_incl_tax,priceFormat);				
var formatted_price_excl_tax=priceUtils.formatPrice(data.price_excl_tax,priceFormat);			
				var method_string='<b><span>'+data.carrier_title+'</span></b>';
				if(data.available)
				{
					method_string=method_string+'<li><input name=shipping_method_val type=radio class=input_shipping  value='+data.carrier_code + '_'+data.method_code+'>'+data.carrier_title+' '+formatted_price_incl_tax+'<input type=hidden name=rate_incl_tax class=rate_incl_tax value='+data.price_incl_tax+'><input type=hidden name=rate_excl_tax class=rate_excl_tax value='+data.price_excl_tax+'></li>';
				}
				else
				{
					var error_msg=data.error_message;
					method_string=method_string+'<li class="message error">'+error_msg+'</li>';
				}
				$('#shipping_method').append(method_string);							
});	   
	}
	).fail(
		function (response) {
			errorProcessor.process(response);
		}
	);					
	}
	}
	}
				
	 });
		$('#quote_add_type').val();		
	});
			
 $('body').on('change','#quote-req-customer-account',function(e){	
	 var is_account_create=$('#quote-req-customer-account').attr('checked');
	 if(is_account_create=='checked')
	 {
		 
	  $('.quote_form_title').html('Create an Account');
	  $('#create_address_info').css('display','block');
	  $('#quote-pwd-create').css('display','block');
	  $('#basic_info').css('display','block');
	  $('#fieldset_shipping_method').css('display','block');
	 }
	 else
	 {		
		 $('.quote_form_title').html('Submit Quote as a Guest');
	   $('#create_address_info').css('display','none');
	   $('#quote-pwd-create').css('display','none');
	   $('#basic_info').css('display','block');
	   $('#fieldset_shipping_method').css('display','none');
	 }

 });
			
				
	$('body').on('focusout','.form-quote-create-account #quote_email',function(e){
		if(!$("#quote_email").val())
		{		
			
			$('#quote-password').css('display','none');
			$('#quote-login-action').css('display','none');
			$('#basic_info').css('display','block');
			$('#create_address_info').css('display','none');
			$('#quote_info').css('display','block');
			$('#quote-req-account').css('display','block');			
			$('#quote-req-customer-account').attr('checked','false');
			$('#quote-create-action').css('display','block');	
			
			var form_action = $('#quoteItemlist').attr('action');
			form_action = form_action.replace("updatePost", "submitQuote");
		$(".form-quote-create-account").attr('action',form_action);
		}
		if($(".form-quote-create-account #quote_email").valid())
		{
		
			var url = $('#quoteItemlist').attr('action');
			url = url.replace("quotation/quote/updatePost", "quotation/customer/emailavail");
   			var email=$("#quote_email").val();
		  $.ajax({
                       
                        url:url,
                     	method:'POST',
						dataType:"json",
						showLoader:true,
						data: {email: email},
                success: function (response) {
				var allow_guest=$('#quote_allow_guest').val();
				if(!response.avail)
				{
					
					$('#quote-password').css('display','block');	
				$('#quote-login-action').css('display','block');	
				$('#quote-create-action').css('display','none');
				$('#quote-pwd-create').css('display','none');
				$('#basic_info').css('display','none');
				$('#create_address_info').css('display','none');
				$('#quote_info').css('display','none');
				$('#quote-req-account').css('display','none');
								
					
					
				
								
		var form_action=$('#quote_login_post_url').val(); ;
		$(".form-quote-create-account").attr('action',form_action);
								
				}
				else
				{
					
				if(allow_guest==1)
				{
						
				$('#basic_info').css('display','block');
				$('#quote_info').css('display','block');
				$('#quote-req-account').css('display','block');
				$('#quote-password').css('display','none');	
				$('#quote-login-action').css('display','none');	
				$('#quote-create-action').css('display','block');
				}
				else
				{
					
					$('#basic_info').css('display','block');
					$('#quote_info').css('display','block');
				$('#quote-req-account').css('display','none');
				$('#quote-password').css('display','none');	
				$('#quote-login-action').css('display','none');	
				$('#quote-create-action').css('display','block');
				}
				
				if($('#quote-req-customer-account').attr('checked')||(allow_guest==0))
				{
				$('#create_address_info').css('display','block');	
				$('#quote-pwd-create').css('display','block');
				}
				
		var form_action = $('#quoteItemlist').attr('action');
		form_action = form_action.replace("updatePost", "submitQuote");
		$(".form-quote-create-account").attr('action',form_action);
				}
						}
		  });
		}
});
			
$('body').on('focusout','.form-quote-create-account #zip,#region',function(e){
self.getShippingMethodRate();	
});
$('body').on('change','.form-quote-create-account #country',function(e){
var country_id=$('.form-quote-create-account #country').val();	
	$('#default_country_id').val(country_id);
});			
$('body').on('change','.form-quote-create-account #country,#region_id',function(e){
self.getShippingMethodRate();	
});
			$('body').on('change','#address_selector',function(e){	
	 var is_same_as=$('#address_selector').attr('checked');
	 if(is_same_as=='checked')
	 {				 	
		$('#fieldset_billing').css('display','none');
	 }
	 else
	 {				   					$('#fieldset_billing').css('display','block');
	 }
			 
});
var enable_shipping=$('#enable_shipping').val();
if((!$('#ship_address_id').length)&&($('#default_country_id').length)&&(enable_shipping!=0))
{
	self.getShippingMethodRate();	
}
			
/*end script for manage customer create and address section on quote page*/
if(($('#ship_address_id').length)&&(enable_shipping!=0))
{
	self.getShippingMethodRate();			
}			
			
			/**start for collapse in message history section */
			$(".quote-msg-accordion").accordion({
				collapsible: true,
			});

			$('a[href^="#"]').on('click', function(event) {
				var target = $(this.getAttribute('href'));
				if( target.length ) {
					event.preventDefault();
					$('html, body').stop().animate({
						scrollTop: target.offset().top
					}, 1000);
				}
			}); 	
			/**end for collapse in message history section*/
			
			 $( document ).ready(function() {		
			 var quote_id =$('#magebees_quote').val(); 	  
			var action = $('#messageform').attr('action');
			 if(action)
			 {
					action = action.replace("saveMessage", "quoteStatus");		
					var params={ quote_id:quote_id };
					 $.ajax(action, {
					method:'GET',
					dataType:"json",
					showLoader:true,
					data: params,				
						success: function(response){

							var status=response.quote_status;
							$('.order-status').text(status);

						}
				});
			 }
		 });			 $('body').on('change','.input_shipping',function(e){			
var applied_rate_incl_tax=$(this).parent("li").find(".rate_incl_tax").val();
var applied_rate_excl_tax=$(this).parent("li").find(".rate_excl_tax").val();
			 $('.applied_rate_incl_tax').val(applied_rate_incl_tax);	$('.applied_rate_excl_tax').val(applied_rate_excl_tax);		 
			 });
			 $('body').on('click','.submit_quote',function(e){
				  e.preventDefault();
				 
				 //return false;
				
var shipping_method=$('body').find('input[name=shipping_method_val]:checked').val();
			var shipping_display=$('#fieldset_shipping_method').css('display');
				if(!shipping_method)
				{					
				 if($('body').find('.input_shipping').length)
				 {		

				 if((shipping_method==undefined)&&(shipping_display=='block'))
				 {		
					 		if(!$('body').find('#shipping_method_err').length)
					 {
						
					$('#shipping_method').append('<span id="shipping_method_err" style="background:#fdf0d5;color: #6f4400;">Please select shipping method</span>');
					 }
					 return false;
				 }
				 else
				 {		
					
					 $('body').find('.form-quote-create-account').submit();
				 }
				 }
				else
				{					
					 $('body').find('.form-quote-create-account').submit();
				}
				}	
				 else
				 {
					
					 $('body').find('#shipping_method_err'). css('display','none');
					  $('body').find('.form-quote-create-account').submit();
					 // $('body').find('#quoteform').submit();
				 }
			 });			
 $('body').on('click','.logout_quote',function(e){	
	  e.preventDefault();
	  var action=$(this).attr("href");	
	  $.ajax(action, {
	method:'POST',
	dataType:"json",
	showLoader:true,						
		success: function(transport){
			location.reload();
		}
	});

 });
 $('body').on('click','.qty_proposal',function(e){	
	 var qty=$(this).val();
	 var base_price=$(this).data("base-price");				 
	 var item_id=$(this).data("item-id");				 
	 var request_id=$(this).attr("request_id");
	 var action=$('#editqtyurl').html();
	 var quote_id=$('#magebees_quote').val();
	  var params={ qty :qty,base_price : base_price,request_id:request_id,quote_id:quote_id,item_id:item_id};	 $(this).parent().parent('td').children().find('input.qty_proposal').attr('checked', false);
	 $(this).attr('checked', true);
	$.ajax(action, {
	method:'POST',
	dataType:"json",
	showLoader:true,
	data: params,				
		success: function(data){
					location.reload();
		}
	});
});
			 $('body').on('click','.default_shipping',function(e){			
e.preventDefault();				 
$( "ul#shipping_address_data" ).children().removeClass('selected_address');				 $("ul#shipping_address_data").children().find('.default_shipping').css('display','block');
 var add_id= $(this).attr('address_id');					 if(add_id)
 {
 $('#ship_address_id').val(add_id); 
 $(this).parent('li').addClass('selected_address');
 $('#btn_default_shipping_'+add_id).css('display','none');
 $('#custom_ship_address_id').val('');
 }
 else
 {
	var custom_add_id= $(this).attr('custom_address_id');	 
	$('#custom_ship_address_id').val(custom_add_id);
	$(this).parent('li').addClass('selected_address');
 $('#btn_default_shipping_'+custom_add_id).css('display','none');
	  $('#ship_address_id').val('');
 }
self.getShippingMethodRate();	
 });			 $('body').on('click','.default_billing',function(e){			
	e.preventDefault();
	$( "ul#billing_address_data" ).children().removeClass('selected_address');					  $("ul#billing_address_data").children().find('.default_billing').css('display','block');
	var add_id= $(this).attr('address_id');	
	if(add_id)
	{
	 $('#bill_address_id').val(add_id);
	$(this).parent('li').addClass('selected_address');
	 $('#btn_default_billing_'+add_id).css('display','none');	
	   $('#custom_bill_address_id').val('');
	}
	else
	{
	 var custom_add_id= $(this).attr('custom_address_id');	
	  $('#custom_bill_address_id').val(custom_add_id);
	 $(this).parent('li').addClass('selected_address');	$('#btn_default_billing_'+custom_add_id).css('display','none');	
	  $('#bill_address_id').val('');
	}
});		
			
   /** Start for delete input box on click of remove tier button **/ 
 $('body').on('click','.btn-remove-quote-tier',function(e){	
	 var index = $(this).parent('div').attr('index');
	 var qpid = $(this).parent('div').attr('qpid');					
	 var quote_id =$(this).parent('div').attr('quote_id');
	 var product_id = $(this).parent('div').attr('product_id');
	 if($(this).attr('request_id')){
			var request_id = $(this).attr('request_id');
		}else{
			var request_id = null;
		}
	 if(request_id)
	 {
	self.removeTierQty(index,qpid,quote_id,product_id,request_id);
	 }
	  $(this).parent().remove();
  });	
   /** End for delete input box on click of remove tier button **/ 
			  
/** Start For save Request Info on blur event of textarea */
 $('body').on('blur','#shopping-quote-table textarea',function(e){	

	var itemid=$(this).attr('item_id');
	 var reqInfo = $(this).val();				 
	 self.saveRequestInfo(itemid,reqInfo);

 });
/** End For save Request Info on blur event of textarea */
			
  /** Start for set the ajax on blur event of input box **/
   $('body').on('blur','#shopping-quote-table input[type=text]',function(e){		
	return $(this).blur(function(e){

		var qpid = $(this).parent('div').attr('qpid');			
		var quote_id =$(this).parent('div').attr('quote_id');	
		var price = $(this).parent('div').attr('price');		
		var product_id = $(this).parent('div').attr('product_id');
		var index = $(this).parent('div').attr('index');
		var tierQty = $(this).val();
		var isDefault = $(this).attr('isdefault');		
		if($(this).attr('request_id')){
			var request_id = $(this).attr('request_id');
		}else{
			var request_id = null;
		}
		var tierQuantities = [];
		$('input[name="quote_request['+quote_id+'][qty][]"]').each(function (qty) {
					tierQuantities.push(this.value);		});

	if(tierQty)
	{	
	if (self.is_loading==0) {	self.saveTierQty(tierQty,quote_id,product_id,price,request_id,qpid,isDefault,index);
							}
	}
	});
});
   /** End for set the ajax on blur event of input box **/
        },
        _setAjaxLoad: function () {
            this.is_loading = 0;
        },		
		getShippingMethodRate: function()
		{
			var enable_shipping=$('#enable_shipping').val();
			if(enable_shipping!=0)
			{
				$('#fieldset_shipping_method').css('display','none');
				$('#shipping_method').html("");
			
			
			/*start guest customer parameter*/
			var default_county_id=$('#default_country_id').val();			
			/*end guest customer parameter*/
				var add_id=$('#ship_address_id').val();			
				var custom_add_id=$('#custom_ship_address_id').val();		
				var quote_id=$('#magebees_quote_id').val();			
				var action = $('#quoteItemlist').attr('action');
				action = action.replace("updatePost", "addressData");
			 	var params={shipping_add_id:add_id,custom_shipping_add_id:custom_add_id,quote_id:quote_id,country_id:default_county_id};
var is_customer_login = window.magebeesQuoteConfig.is_customer_login;
			var allow_guest=$('#quote_allow_guest').val();
			if(is_customer_login||(allow_guest==0)||($('#quote-req-customer-account').attr('checked')))
					{		
			$('#shiiping_address_loading').css('display','block');
					}
			
	/**Start for get estimate shipping method on change of shipping address*/
				$.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
				success: function(data){	
					if(data.items)
					{
					$("#quoteItemlist").replaceWith(("#quoteItemlist",data.items));
					}
			/**Start get shipping method using api from country and pincode*/
				var address;
					var action = $('#quoteItemlist').attr('action');
					var quote_mask_id =data.mask_id;
						var storeCode = window.magebeesQuoteConfig.storeCode;
					if(is_customer_login)
					{						
						var serviceUrl = action.replace("quotation/quote/updatePost", "rest/"+storeCode+"/V1/carts/mine/estimate-shipping-methods"); 
					}
					else
					{
				 var serviceUrl = action.replace("quotation/quote/updatePost", "rest/"+storeCode+"/V1/guest-carts/"+quote_mask_id+"/estimate-shipping-methods"); 
					}
                payload = JSON.stringify({
                        address: {
                            'city': data.city,
                            'country_id': data.country_id,
                            'postcode': data.postcode,
                        }
                    }
                );    				
				storage.post(
                serviceUrl, payload, false
                ).done(
                    function (result) {
					$('#shipping_method').html('');
						$.each(result, function(i, data) {	
					var priceFormat = window.magebeesQuoteConfig.priceFormat;
var formatted_price_incl_tax=priceUtils.formatPrice(data.price_incl_tax,priceFormat);				
var formatted_price_excl_tax=priceUtils.formatPrice(data.price_excl_tax,priceFormat);				
							var method_string='<b><span>'+data.carrier_title+'</span></b>';
				if(data.available)
				{
					method_string=method_string+'<li><input name=shipping_method_val type=radio class=input_shipping  value='+data.carrier_code + '_'+data.method_code+'>'+data.carrier_title+' '+formatted_price_incl_tax+'<input type=hidden name=rate_incl_tax class=rate_incl_tax value='+data.price_incl_tax+'><input type=hidden name=rate_excl_tax class=rate_excl_tax value='+data.price_excl_tax+'></li>';
				}
				else
				{
					var error_msg=data.error_message;
					method_string=method_string+'<li class="message error">'+error_msg+'</li>';
				}
				$('#shipping_method').append(method_string);
				if(!is_customer_login)
					{				  $('#fieldset_shipping_method').insertAfter('#fieldset_shipping');
					}
			if($('#quote-req-customer-account').length)
			{
				var is_account_create=$('#quote-req-customer-account').attr('checked');
			 if(is_account_create=='checked')
			 {	
				 $('#fieldset_shipping_method').css('display','block');
			 }
			}
			else
			{
				$('#fieldset_shipping_method').css('display','block');
			}
				
		
							
			});
						 $('#shiiping_address_loading').css('display','none');
						
                      
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                );	
			/**End get shipping method using api from country and pincode*/		
				
				}
				});
			}
		},
        addTierPrice: function (itemId,productId,inputName,count,price,quote_id) {
        
			var self = this;
			 if (!self.pool[itemId]) {
               // pool[itemId] = 1;
			   self.pool[itemId] = count;
            }
            var index = self.pool[itemId];
            index++;
            self.pool[itemId] = index;			
			
			    var parentElemt = document.getElementById('qdiv_' + itemId);
            var childElem = document.createElement('div');
            childElem.setAttribute("id", 'div_' + itemId + '_' + index);
			childElem.setAttribute("quote_id", quote_id);
			childElem.setAttribute("index", index);
			childElem.setAttribute("product_id", productId);
			childElem.setAttribute("qpid", itemId);
			childElem.setAttribute("price", price);
			
			childElem.className = "add-row-tier";          
            var link = '<a href="#" id="tier_rmv_' + itemId + '_' + index + '" class="btn-remove-quote-tier" title="Remove Tier Price">Remove</a>';			
			childElem.innerHTML = '<input type="text" class="qty-add-rmv" name="' + inputName + '"  id="quote_' + itemId + '_' + index + '" value=""  isdefault="0" class="required-entry validate-zero-or-greater qty">' + link;
            parentElemt.appendChild(childElem);
			
        },		
		 saveTierQty: function(tierQty,quote_id,product_id,price,request_id,qpid,isDefault,index){
			 var self=this;
			 var action = $('#quoteItemlist').attr('action');
				action = action.replace("updatePost", "saveTierQty");
			 var params={ qty: tierQty,quote_id :quote_id,product_id : product_id,price : price,request_id:request_id,qpid:qpid, isDefault:isDefault,index:index };
			  if (this.is_loading==0) {
            this.is_loading = 1;
				     $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(data){
					var request_id=data.request_id;						
						if(data.error_msg)
						{
								$("#quote_"+ qpid + "_" + index).val(data.tier_qty);
								window.scrollTo(0,0);
						}	
						if(data.error_qty)
						{							
								$("#quote_"+ qpid + "_" + index).val(data.tier_qty);
								window.scrollTo(0,0);
						}
					$("#tier_rmv_"+ qpid + "_" + index).attr('request_id',request_id);
					$("#quote_"+ qpid + "_" + index).attr('request_id',request_id);
						 self._setAjaxLoad();					
					}
				});
		 }
			 
		 },
		removeTierQty: function(index,qpid,quote_id,product_id,request_id){
			
			 var action = $('#quoteItemlist').attr('action');
				action = action.replace("updatePost", "removeTierQty");
			 var params={ quote :quote_id,product : product_id,request_id:request_id,qpid:qpid, index:index };
				     $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(transport){
						
					}
				});
			
		},
		saveRequestInfo:function(item_id,reqInfo)
		{
			 var action = $('#quoteItemlist').attr('action');
				action = action.replace("updatePost", "updateRequestInfo");
			 var params={ item_id :item_id,request_info:reqInfo};
				     $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(transport){
					$("#quote["+ item_id + "][request_info]").val(reqInfo);	
						
					}
				});
		},		
		updateAddress:function(quote_id,selected_bill_add,selected_ship_add)
		{
				var action = $('#quoteItemlist').attr('action');
				action = action.replace("updatePost", "submitQuote");
				var params={quote_id :quote_id,ship_address_id:selected_ship_add,bill_address_id:selected_bill_add};
			 $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(data){
				
						  window.location.href = data.redirect_url;
					}
				});
		},
		editAddressPopup:function(address_id)
		{
			var params={ address_id:address_id };
			var action = $('#quoteItemlist').attr('action');
				action = action.replace("quotation/quote/updatePost", "quotation/customer/editAddress");		
		 $.ajax({
                url: action,
                data: params,
                type: 'post',
                dataType: 'json',
                success: function (data) {
					 $('.form-address-edit input[name="firstname"]').val(data.address_data.firstname);
					 $('.form-address-edit input[name="lastname"]').val(data.address_data.lastname);
					 $('.form-address-edit input[name="telephone"]').val(data.address_data.telephone);
					 $('.form-address-edit input[name="city"]').val(data.address_data.city);
					 $('.form-address-edit input[name="region_id_add"]').val(data.address_data.region_id);
					 $('.form-address-edit input[name="region_add"]').val(data.address_data.region);
					 $('.form-address-edit input[name="postcode"]').val(data.address_data.postcode);				
					$('.form-address-edit select[name="country_id_add"] option[value='+data.address_data.country_id+']').attr('selected','selected');		
					$('#quote_add_type').val(data.address_data.address_type);
					
					$.each(data.street,function( index, value ) {
 
						var s_index=index+1;
						 $('.form-address-edit input[id=street_'+s_index+']').val(value);
						
});
					
				var save_in_book=data.address_data.save_in_add_book;
					if(save_in_book!=1)
					{						
			$('.form-address-edit div input[name="save_in_add_book"]').attr('checked',false);			
					}
					var action = $('#quoteItemlist').attr('action');
				action = action.replace("quotation/quote/updatePost", "quotation/customer/saveAddress?address_id="+address_id);
					 action = action.replace(/\/$/, '');
					$('.form-address-edit').attr('action',action);
					
					var options = {
type: 'popup',
responsive: true,
innerScroll: true,
modalClass: 'custom-block-customer-address',
buttons: false,
};

var popup = modal(options, $('#popup-modal-address'));
$('#popup-modal-address').modal('openModal');
					
				}
		 });
			
				
		}
		
    });

    return $.magebees_quotation.magebeesQuote;
});