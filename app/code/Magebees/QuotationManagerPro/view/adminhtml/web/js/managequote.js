/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
     'jquery/ui',
	"Magento_Catalog/catalog/product/composite/configure"
], function ($,ui) {
  

    $.widget('magebees_quotation.manageQuote', {
		 pool:[],
		 is_loading: 0,
		 productConfigureAddFields : {},
	  	_create: function () {
         	 var self=this;
			
			 $('body').on('blur','.edit-quote-table textarea',function(e){	
				
				var itemid=$(this).attr('item_id');
				 var reqInfo = $(this).val();				 
				 self.saveRequestInfo(itemid,reqInfo);
				 
			 });
			
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
				 if(quote_id){
					  
					    var action = $('#edit_form').attr('action');
						action = action.replace("save", "quoteStatus");		
						var params={ quote_id:quote_id };
						 $.ajax(action, {
						method:'GET',
						dataType:"json",
						showLoader:true,
						data: params,				
							success: function(response){
								
								var status=response.quote_status;
								$('select[name^="quote_status"] option[value="'+status+'"]').attr("selected","selected");
								 
							}
					});
				 }
			 });
			 $('body').on('click','#add_products',function(e){	
				   $("#quote-search").show();
				   $("#add_products").hide();
			 });
			 $('body').on('click','#submitproposal',function(e){				
				e.preventDefault();
				  var action = $('#edit_form').attr('action');
				 action = action.replace("save", "converToProposal");
    			$('#edit_form').attr('action', action).submit();
				
			 });
			 $('body').on('click','#createorder',function(e){	
				
				  e.preventDefault();
				  var action = $('#edit_form').attr('action');
				 action = action.replace("save", "createOrder");
    			$('#edit_form').attr('action', action).submit();
				
			 });
			
			
			 $('body').on('click','.remove_file',function(e){	
				    var action = $('#edit_form').attr('action');
				   var quote_id =$('#magebees_quote').val();
				 var id=$(this).data("id");
						action = action.replace("save", "removeFile");
				 var params={id:id,quote_id:quote_id};
				 $.ajax(action, {
						method:'POST',
						dataType:"json",
						showLoader:true,
					 	data: params,
							success: function(response){
								var attach_count=response.attachment_count;
								if(attach_count<=0)
								{
									  $('#attachment_title').remove();
								}
								  $('#attachment_'+id).remove();
							}
					});
			 });
			 $('body').on('click','.remove_item',function(e){	
				   e.preventDefault();
				  var itemId=$(this).data("item-id");
				  var quote_id =$('#magebees_quote').val();
				   if(confirm("Are you sure want to delete quote item ?") == true){
					  
					    var action = $('#edit_form').attr('action');
						action = action.replace("save", "removeQuoteItem");
						var params={ item_id:itemId,quote_id:quote_id };
						 $.ajax(action, {
						method:'POST',
						dataType:"json",
						showLoader:true,
						data: params,				
							success: function(response){
								// location.reload();
								
								var status=response.quote_status;
								$('select[name^="quote_status"] option[value="'+status+'"]').attr("selected","selected");
$("#quote-items").replaceWith(("#quote-items",response.items));
$(".quote-totals").replaceWith((".quote-totals",response.totals));
$(".quote-addresses").replaceWith((".quote-addresses",response.shipping_address));	
//$(".order-shipping-method-summary").replaceWith((".order-shipping-method-summary",response.shipping_method));	
$("#quote-shipping-method-summary").html(response.shipping_method);	
								
							}
					});
				   }
			 });
			
			
			 $('body').on('click','.update_items',function(e){	
				 self.itemsUpdate();
			 });
			
			 $('body').on('click','.item-configure',function(e){	
				 var itemId=$(this).data("item-id");
				self.showQuoteItemConfiguration(itemId);
			 });
		
			
			   /** Start for delete input box on click of remove tier button **/ 
			 $('body').on('click','.delete_req',function(e){	
				   e.preventDefault();
				 var index = $(this).parent('div').attr('index');
				 var qpid = $(this).parent('div').attr('qpid');					
				 var quote_id =$('#magebees_quote').val();
				 var product_id = $(this).parent('div').attr('product_id');
				 if( $(this).parent('div').attr('requestid')){
						var request_id = $(this).parent('div').attr('requestid');
					}else{
						var request_id = null;
					}
				
				  var isDefault = $(this).parent('div').attr('isdefault');
				 if(isDefault==0)
				 {
				  $(this).parent().remove();
				  $('#qty_'+qpid+'_'+index).remove();
				  $('#price_'+qpid+'_'+index).remove();
				  $('#costprice_'+qpid+'_'+index).remove();
				  $('#row_total_'+qpid+'_'+index).remove();
				  $('#margin_'+qpid+'_'+index).remove();
				if(request_id)
				 {
					self.removeTierQty(request_id);
				 }
				 }
				
			  });	
			   /** End for delete input box on click of remove tier button **/ 
			 
			
			$('body').on('click','.cost_save',function(e){
			});
			$('body').on('click','.cost_cancel',function(e){	
				 var index = $(this).parent('div').attr('index');
				 var itemId = $(this).parent('div').attr('qpid');	
				var value = $(this).parent('div').data("cost-price");
				
				if(value)
				{
					$(this).parent().html(value);	
				  
				}
				else
				{
					$(this).parent().attr('class','add-total-dynamic');
				$(this).parent().html('N/A <a id="change_cost' + itemId + '_' + index + '"  class="change_cost">Change</a>');
				}
				});
			
			$('body').on('click','.save_req,.defaultitemrequest',function(e){
				  e.preventDefault();
				 var index = $(this).parent('div').attr('index');
				 var itemId = $(this).parent('div').attr('qpid');	
				 var requestId = $(this).parent('div').attr('requestid');
				 var productId = $(this).parent('div').attr('product_id');
				 var quote_id =$('#magebees_quote').val();
				var qty=$('#Ownerqty_'+itemId+'_'+index).val();
				var base_price=$('#Ownerbaseprice_'+itemId+'_'+index).val();
				var cost_price=$('#cost_input_'+itemId+'_'+index).val();
				var default_request=$('#default_req_'+itemId+'_'+index).is(':checked');
				if(default_request)
				{
					var isDefault=1;
				}
				else
				{
					var isDefault=0;
				}
				if(!requestId)
				{
					var requestId = null;
				}
				if(qty){
				self.saveTierQty(qty,quote_id,productId,base_price,cost_price,requestId,itemId,isDefault,index);
				}
				else
				{
					$("#Ownerqty_"+ itemId + "_" + index).focus();
				}				
			});			
			$('body').on('click','.change_cost',function(e){	
				$(this).parent('div').removeClass('add-total-dynamic');
				 var index = $(this).parent('div').attr('index');
				 var qpid = $(this).parent('div').attr('qpid');					
				 var quote_id =$('#magebees_quote').val();
				 var product_id = $(this).parent('div').attr('product_id');
				 if($(this).attr('request_id')){
						var request_id = $(this).attr('request_id');
					}else{
						var request_id = null;
					}
				var value = $(this).parent('div').data("cost-price");
				var cost_value = $(this).parent('div').data("cost-value");
				if(value)
				{
					$(this).parent('div').data("cost-price",$(this).parent('div').html());
					
					$(this).parent().html('<input id= "cost_input_' + qpid + '_' + index + '" name="RequestedItem['+qpid+'][costprice][]" value="'+cost_value+'"/><a id="cost_cancel_' + qpid + '_' + index + '" class="cost_cancel">cancel</a>');				
				}
				else
				{
$(this).parent().html('<input id= "cost_input_' + qpid + '_' + index + '" name="RequestedItem['+qpid+'][costprice][]"/><a id="cost_cancel_' + qpid + '_' + index + '" class="cost_cancel">cancel</a>');
				}
			});		
        },
		 saveTierQty: function(qty,quote_id,productId,base_price,cost_price,requestId,itemId,isDefault,index){
			
			 var self=this;
			 var action = $('#edit_form').attr('action');
				action = action.replace("save", "saveTierQty");
			 var params={ qty: qty,quote_id :quote_id,product_id : productId,base_price : base_price,cost_price:cost_price,request_id:requestId,qpid:itemId, isDefault:isDefault,index:index };
				$.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(data){
					var request_id=data.request_id;		
						$(".quote_error").html(data.messages);
						if(data.error_msg)
						{
								$("#Ownerqty_"+ itemId + "_" + index).val(data.tier_qty);				
							// location.reload();
						}	
						if(data.error_qty)
						{
							
								$("#Ownerqty_"+ itemId + "_" + index).val(qty);
								$("#Ownerqty_"+ itemId + "_" + index).focus();
							 location.reload();
							
						}
						  $("#quote-items").replaceWith(("#quote-items",data.items));
			   $(".quote-totals").replaceWith((".quote-totals",data.totals));				
					}
				});
		 
			 
		 },
		showQuoteItemConfiguration: function(itemId){
			var self=this;
			  var listType = 'quote_items';
            var qtyElement = $('quote-items_grid').select('input[name="item\[' + itemId + '\]\[qty\]"]')[0];
            productConfigure.setConfirmCallback(listType, function () {
                // sync qty of popup and qty of grid
                var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                    qtyElement.value = confirmedCurrentQty.value;
                }
                self.productConfigureAddFields['item[' + itemId + '][configured]'] = 1;

            }.bind(this));
            productConfigure.setShowWindowCallback(listType, function () {
                // sync qty of grid and qty of popup
                var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                    formCurrentQty.value = qtyElement.value;
                }
            }.bind(this));
            productConfigure.showItemConfiguration(listType, itemId);
		},
		  itemsUpdate : function(){
			  var self=this;
            var area = ['sidebar', 'items', 'shipping_method', 'billing_method','totals', 'giftmessage'];
            // prepare additional fields
            var fieldsPrepare = {update_items: 1};
            var info = $('quote-items_grid').select('input', 'select', 'textarea');
            for(var i=0; i<info.length; i++){
                if(!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                    fieldsPrepare[info[i].name] = info[i].getValue();
                }
            }
            fieldsPrepare = Object.extend(fieldsPrepare, self.productConfigureAddFields);
            self.productConfigureSubmit('quote_items', area, fieldsPrepare);
           
        },
		productConfigureSubmit : function(listType, area, fieldsPrepare, itemsFilter) {
            // prepare loading areas and build url
          var self=this;
            self.loadingAreas = area;
			 var quote_id =$('#magebees_quote').val();		
			 var action = $('#edit_form').attr('action');
				var url= action.replace("quote/save", "quote_create/loadBlock/block/items,totals,shipping_address,shipping_method");
         

            // prepare additional fields
            fieldsPrepare = self.prepareParams(fieldsPrepare);
            fieldsPrepare.reset_shipping = 1;
            fieldsPrepare.json = 1;
            fieldsPrepare.quote_id = quote_id;

            // create fields
            var fields = [];
            for (var name in fieldsPrepare) {
                fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
            }
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            productConfigure.addListType(listType, {urlSubmit: url});
            productConfigure.setOnLoadIFrameCallback(listType, function(response){
                this.loadAreaResponseHandler(response);
            }.bind(this));
            productConfigure.submit(listType);
            // clean
            this.productConfigureAddFields = {};
        },
		  loadAreaResponseHandler : function (response) {
			   $("#quote-items").replaceWith(("#quote-items",response.items));
			   $(".quote-totals").replaceWith((".quote-totals",response.totals));			  
			  // $(".quote-addresses").replaceWith((".quote-addresses",response.shipping_address));			  
			  // $(".order-shipping-method-summary").replaceWith((".order-shipping-method-summary",response.shipping_method));		
			  $("#quote-shipping-method-summary").html(response.shipping_method);	
		  },
		 prepareParams : function(params){
            if (!params) {
                params = {};
            }
            if (!params.customer_id) {
                params.customer_id = this.customerId;
            }
            if (!params.store_id) {
                params.store_id = this.storeId;
            }
            if (!params.currency_id) {
                params.currency_id = this.currencyId;
            }
            if (!params.form_key) {
                params.form_key = FORM_KEY;
            }

           
            return params;
        },

		removeTierQty: function(request_id){
			
			 var action = $('#edit_form').attr('action');
				action = action.replace("save", "removeTierQty");
			 var params={ request_id:request_id };
				     $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(transport){
						
					}
				});
			
		},
        addTierPrice: function (itemId,productId,inputName,count,price,quote_id,converted_cost_price,cost_price) {
        
			var self = this;
			 if (!self.pool[itemId]) {
               // pool[itemId] = 1;
			   self.pool[itemId] = count;
            }
            var index = self.pool[itemId];
            index++;
            self.pool[itemId] = index;		
			 if($(this).attr('request_id')){
						var request_id = $(this).attr('request_id');
					}else{
						var request_id = null;
					}
				
			/**Start for add dynamic cost price element*/
			var costPriceParentElemt = document.getElementById('qcostpricediv_' + itemId);			
            var costPriceChildElem = document.createElement('div');
            costPriceChildElem.setAttribute("id", 'costprice_' + itemId + '_' + index);
			costPriceChildElem.setAttribute("quote_id", quote_id);
			costPriceChildElem.setAttribute("index", index);
			costPriceChildElem.setAttribute("product_id", productId);
			costPriceChildElem.setAttribute("qpid", itemId);
			costPriceChildElem.setAttribute("price", price);
			costPriceChildElem.setAttribute("isdefault", 0);
			
			costPriceChildElem.className = "add-cost-price add-total-dynamic";     
			if(cost_price)
			{
				costPriceChildElem.setAttribute("data-cost-price", cost_price);
				costPriceChildElem.setAttribute("data-cost-value", cost_price);
				//costPriceChildElem.innerHTML = cost_price;
				costPriceChildElem.innerHTML = '<span class="price">'+converted_cost_price+'</span><a id="change_cost' + itemId + '_' + index + '"  class="change_cost">Change</a>';
          
			}
			else
			{
			costPriceChildElem.innerHTML = 'N/A <a id="change_cost' + itemId + '_' + index + '"  class="change_cost">Change</a>';
          
			}
          
			  costPriceParentElemt.appendChild(costPriceChildElem);
			
				/**End for add dynamic cost price element*/
			
			/**Start for add dynamic price element*/
			var priceParentElemt = document.getElementById('qpricediv_' + itemId);			
            var priceChildElem = document.createElement('div');
            priceChildElem.setAttribute("id", 'price_' + itemId + '_' + index);
			priceChildElem.setAttribute("quote_id", quote_id);
			priceChildElem.setAttribute("index", index);
			priceChildElem.setAttribute("product_id", productId);
			priceChildElem.setAttribute("qpid", itemId);
			priceChildElem.setAttribute("price", price);
			priceChildElem.setAttribute("isdefault", 0);
			
			priceChildElem.className = "add-price";            
			priceChildElem.innerHTML = '<input id="Ownerbaseprice_' + itemId + '_' + index + '" name="RequestedItem['+itemId+'][price][]" value="'+price+'" type="text" isdefault="0"/>';
            priceParentElemt.appendChild(priceChildElem);
			
			/**End for add dynamic price element*/
	
			/**Start for add dynamic qty element*/
			var qtyParentElemt = document.getElementById('qqtydiv_' + itemId);			
            var qtyChildElem = document.createElement('div');
            qtyChildElem.setAttribute("id", 'qty_' + itemId + '_' + index);
			qtyChildElem.setAttribute("quote_id", quote_id);
			qtyChildElem.setAttribute("index", index);
			qtyChildElem.setAttribute("product_id", productId);
			qtyChildElem.setAttribute("qpid", itemId);
			qtyChildElem.setAttribute("price", price);
			qtyChildElem.setAttribute("isdefault", 0);
			
			qtyChildElem.className = "add-qty";            
			qtyChildElem.innerHTML = '<input class="defaultitemrequest" id="default_req_' + itemId + '_' + index + '" name="RequestedItemDefault['+itemId+'][]" type="radio"><input id="Ownerqty_' + itemId + '_' + index + '" name="RequestedItem['+itemId+'][qty][]" value="" type="text" isdefault="0"/>';
            qtyParentElemt.appendChild(qtyChildElem);		
			/**End for add dynamic qty element*/
			
			/**Start for add action element*/
			var actionParentElemt = document.getElementById('qactiondiv_' + itemId);			
            var actionChildElem = document.createElement('div');
            actionChildElem.setAttribute("id", 'action_' + itemId + '_' + index);
			actionChildElem.setAttribute("quote_id", quote_id);
			actionChildElem.setAttribute("index", index);
			actionChildElem.setAttribute("product_id", productId);
			actionChildElem.setAttribute("qpid", itemId);
			actionChildElem.setAttribute("price", price);
			actionChildElem.setAttribute("isdefault", 0);
			
			actionChildElem.className = "add-action";            
			actionChildElem.innerHTML = '<button class=save_req>Save</button>	<button class=delete_req>Cancel</button>';
            actionParentElemt.appendChild(actionChildElem);			
			/**End for add action element*/
			
			
			/**Start for add row total element*/
			var totalParentElemt = document.getElementById('qrowtotal_div_' + itemId);			
            var totalChildElem = document.createElement('div');
            totalChildElem.setAttribute("id", 'row_total_' + itemId + '_' + index);
		
			totalChildElem.setAttribute("index", index);
			totalChildElem.setAttribute("product_id", productId);
			totalChildElem.setAttribute("qpid", itemId);			
			totalChildElem.setAttribute("isdefault", 0);			
			totalChildElem.className = "add-row-total";            
			totalChildElem.innerHTML = '';
            totalParentElemt.appendChild(totalChildElem);			
			/**End for add row total element*/
			
			/**Start for add margin element*/
			var marginParentElemt = document.getElementById('qmargin_div_' + itemId);			
            var marginChildElem = document.createElement('div');
            marginChildElem.setAttribute("id", 'margin_' + itemId + '_' + index);		
			marginChildElem.setAttribute("index", index);
			marginChildElem.setAttribute("product_id", productId);
			marginChildElem.setAttribute("qpid", itemId);			
			marginChildElem.setAttribute("isdefault", 0);			
			marginChildElem.className = "add-margin";            
			marginChildElem.innerHTML = '';
            marginParentElemt.appendChild(marginChildElem);			
			/**End for margin element*/
        },
		saveRequestInfo:function(item_id,reqInfo)
		{
			 var action = $('#edit_form').attr('action');
				action = action.replace("save", "updateRequestInfo");
			 var params={ item_id :item_id,request_info:reqInfo};
				     $.ajax(action, {
				method:'POST',
				dataType:"json",
				showLoader:true,
				data: params,				
					success: function(transport){
					//$("#quote["+ item_id + "][request_info]").val(reqInfo);	
						
					}
				});
		}
		
    });

    return $.magebees_quotation.manageQuote;
});