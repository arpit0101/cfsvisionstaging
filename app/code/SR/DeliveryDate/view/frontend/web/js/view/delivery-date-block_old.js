define([
    'jquery',
    'ko',
    'uiComponent',
    'moment'
], function ($, ko, Component, moment) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'SR_DeliveryDate/delivery-date-block'
        },
        initialize: function () {
            this._super();
            var disabled 	= 	window.checkoutConfig.shipping.delivery_date.disabled;
            var noday 		= 	window.checkoutConfig.shipping.delivery_date.noday;
            var hourMin 	= 	parseInt(window.checkoutConfig.shipping.delivery_date.hourMin);
            var hourMax 	= 	parseInt(window.checkoutConfig.shipping.delivery_date.hourMax);
            var format 		= 	window.checkoutConfig.shipping.delivery_date.format;
            if(!format) {
                format = 'yy-mm-dd';
            }
           
            var disabledDay = disabled.split(",").map(function(item) {
                return parseInt(item, 10);
            });

            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    if(noday) {
                        var options = {
                            minDate: 0,
                            dateFormat:format,
                            hourMin: hourMin,
                            hourMax: hourMax
                        };
                    } else {
                        var options = {
                            minDate: 0,
                            dateFormat:format,
                            hourMin: hourMin,
                            hourMax: hourMax,
                            beforeShowDay: function(date) {
                                var day = date.getDay();
                                if(disabledDay.indexOf(day) > -1) {
                                    return [false];
                                } else {
                                    return [true];
                                }
                            }
                        };
                    }

                    $el.datetimepicker(options);
					
					let days 			= 	[];
					let ray 			= 	[];
					let daysRequired 	= 	7;
					$(".schedule-wrapper .color-red.warning").addClass('hide');
					for (let i = 0; i < daysRequired; i++) {
						var day 		=	moment().add(i, 'days').format('YYYY-MM-DD/dddd/D/e');
						var dayarray	=	day.split('/');
						var mystr 		= 	dayarray[0];
						var daynew 		= 	dayarray[1].slice(0, 3);
						var datevar 	= 	dayarray[2];
						var isdisabled	=	'';
						var shippingdate=	'shippingdate';
						var dayinint	=	parseInt(dayarray[3]);
						if($.inArray(dayinint, disabledDay) > -1){
							isdisabled		=	'disabled';
							shippingdate	=	'';
						}
						var fix_start_time  =   window.checkoutConfig.shipping.delivery_date.hourMin;
						var fix_end_time	=   window.checkoutConfig.shipping.delivery_date.hourMax;
						var increasehours	=	1;
						var todaydate		=	moment().format('YYYY-MM-DD');
						var end_time 		= 	new Date(moment(new Date(todaydate+' '+fix_end_time)).subtract(increasehours, 'hours').format('YYYY-MM-DD HH:mm'));
						var currentdatetime =	new Date();
						if(todaydate== dayarray[0] && end_time < currentdatetime){
							isdisabled		=	'disabled';
							shippingdate	=	'';
							$(".schedule-wrapper .color-red.warning").removeClass('hide');
						}
						
						if(i==0) {
							var data = '<li class="ember-view '+isdisabled+'"><a href="javascript:void(0)" class="'+shippingdate+'" data-date="'+dayarray[0]+'" >Today</a> </li>';
							$("#first_ul").append(data);
						}else if(i==1){
							var data = '<li class="ember-view '+isdisabled+'"><a href="javascript:void(0)" class="'+shippingdate+'" data-date="'+dayarray[0]+'" >Tomorrow</a> </li>';
							$("#first_ul").append(data);
							
						} else {
							var data = '<li class="ember-view '+isdisabled+'"><a class="'+shippingdate+'" href="javascript:void(0)" data-date="'+dayarray[0]+'" ><div class="weekday" id="ran_day" >'+daynew+'</div><div class="day">'+datevar+'</div></a> </li>';
							$("#second_ul").append(data);
						}
					}
					
					$(".shippingdate").click(function(){
						
						var dateselected 	=	$(this).attr('data-date');
						$("#first_ul, #second_ul").find("li").removeClass('active');
						$(this).parent().addClass('active');
						var fix_start_time  =   window.checkoutConfig.shipping.delivery_date.hourMin;
						var fix_end_time	=   window.checkoutConfig.shipping.delivery_date.hourMax;
						var increasehours	=	2;
						
						var start_time 				= 	new Date(dateselected+' '+fix_start_time);
						var end_time 				= 	new Date(dateselected+' '+fix_end_time);
						$("#third_ul").empty();
						var itemcount		=	0;
						while(start_time < end_time){
							
							var start_timestr 		=	moment(start_time).format('hh:mm A');
							var selecteddatetimestr =	moment(start_time).format('YYYY-MM-DD HH:mm');
							var cstart_time 		= 	new Date(dateselected+' '+moment(start_time).format('hh:mm A'));
							start_time 				= 	new Date(dateselected+' '+moment(start_time).add(increasehours, 'hours').format('hh:mm A'));
							
							if(start_time>end_time){
								start_time			=	new Date(dateselected+' '+moment(end_time).format('hh:mm A'));
							}
							var newtimestr 			=	(start_timestr+' - '+ moment(start_time).format('hh:mm A'));
							var classname  		 	=	"";
							
							var todaydate		=	moment().format('YYYY-MM-DD');
							var currentdatetime =	new Date();
							var active 			=	"";
							if(itemcount==0){
								active = "active";
								$("#delivery_date").val(dateselected+' '+newtimestr);
							}
							if(todaydate==dateselected && cstart_time < currentdatetime){
								
							}else{
								var data = '<li class="selecttime '+active+'" data-date="'+dateselected+' '+newtimestr+'"><a href="javascript:void(0)" '+classname+'> '+newtimestr+'</a> </li>';
								$("#third_ul").append(data);
								itemcount++;
							}
						}
						return false;
					});
					
					$(document).on('click',"#third_ul .selecttime",function(){
						$("#third_ul .selecttime").removeClass('active');
						$(this).addClass('active');
						var selecteddateandtime 	=	$(this).attr('data-date');
						$("#delivery_date").val(selecteddateandtime);
					});
					jQuery("ul li.ember-view").not(".disabled").each(function(index){
						if(index==0){
							jQuery(this).find('a').trigger('click');
						}
					});

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));
                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    //when the view model is updated, update the widget
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };
            return this;
        }
    });
});
