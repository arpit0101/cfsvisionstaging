define([
    'jquery',
    'ko',
    'uiComponent',
    'moment',
	'mage/translate',
], function ($, ko, Component, moment, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'SR_DeliveryDate/delivery-date-block'
        },
        initialize: function () {
            this._super();
            var disabled 	= 	window.checkoutConfig.shipping.delivery_date.disabled;
            var noday 		= 	window.checkoutConfig.shipping.delivery_date.noday;
			var timeslots 	= 	window.checkoutConfig.shipping.delivery_date.timeslots;
			var delay 		= 	parseInt(window.checkoutConfig.shipping.delivery_date.delay);
			
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
                        };
                    } else {
                        var options = {
                            minDate: 0,
                            dateFormat:format,
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
					console.log(timeslots);
					$.each(timeslots, function(i, timeslot){
						
						var shippingdate	=	'shippingdate';
						var todayactive		=	'';
						var isdisabled		=	'';
						var dayinint		=	parseInt(timeslot.dayint);
						if(timeslot.timeslots.length < 1){
							isdisabled		=	'disabled';
							shippingdate	=	'';
							$(".schedule-wrapper .color-red.warning").removeClass('hide');
						}
						if($.inArray(dayinint, disabledDay) > -1){
							isdisabled		=	'disabled';
							shippingdate	=	'';
						}
						
						if(i==0) {
							if(timeslot.timeslots.length < 1){
								var todayactive		=	'';
							}else{
								var todayactive		=	'active';
							}
							var data = '<li class="ember-view '+isdisabled+' ' +todayactive+'"><a href="javascript:void(0)" class="'+shippingdate+'" data-date="'+timeslot.datestring+'" data-day="'+i+'" >"'+ $t('Today')+'"</a> </li>';
							$("#first_ul").append(data);
						}else if(i==1){
							if(timeslot.timeslots.length > 0 && timeslots[0].timeslots.length < 1){
								var todayactive		=	'active';
							}
							var data = '<li class="ember-view '+isdisabled+' '+todayactive+'"><a href="javascript:void(0)" class="'+shippingdate+'" data-date="'+timeslot.datestring+'" data-day="'+i+'" >"'+ $t('Tomorrow')+'"</a> </li>';
							$("#first_ul").append(data);
							
						} else {
							var data = '<li class="ember-view '+isdisabled+'"><a class="'+shippingdate+'" href="javascript:void(0)" data-date="'+timeslot.datestring+'" data-day="'+i+'" ><div class="weekday" id="ran_day" >'+timeslot.Day+'</div><div class="day">'+timeslot.date+'</div></a> </li>';
							$("#second_ul").append(data);
						}
					});
					$(".shippingdate").click(function(){
						
						var dateselected 	=	$(this).attr('data-date');
						var datesday 		=	$(this).attr('data-day');
						var classname  		=	"";
						$("#first_ul, #second_ul").find("li").removeClass('active');
						$(this).parent().addClass('active');
						var timeslotsdata		=	timeslots[datesday].timeslots;
						
						console.log(timeslotsdata);
						$("#third_ul").empty();
						$.each(timeslotsdata, function (itemcount, timeslotdata){
							var active 			=	"";
							if(itemcount==0){
								active 				= 	"active";
								$("#delivery_date").val(timeslotdata.withdate);
							}
							var data = '<li class="selecttime '+active+'" data-date="'+timeslotdata.withdate+'"><a href="javascript:void(0)" '+classname+'> '+timeslotdata.withoutdate+'</a> </li>';
							$("#third_ul").append(data);
						});
						return false;
					});
					/* let days 			= 	[];
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
						var currentdatetime =	new Date();
						currentdatetime 	=	currentdatetime.setHours(currentdatetime.getHours() + delay);
						var timeslotsdata 	=	timeslots[dayinint];
						timeslotsdata		=	timeslotsdata.replace(/\r\n/g, "\r").replace(/\n/g, "\r").split(/\r/);
						var first_slot 		=	timeslotsdata[0].split("-");
						var last_slot 		=	timeslotsdata[timeslotsdata.length-1].split("-");
						
						var fix_start_time  =   first_slot[0];
						var fix_end_time	=   last_slot[1];
						var increasehours	=	1;
						var todaydate		=	moment().format('YYYY-MM-DD');
						
						var end_time 		= 	new Date(moment(new Date(todaydate+' '+fix_end_time)).subtract(increasehours, 'hours').format('YYYY-MM-DD HH:mm'));
						
						
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
						var d 			= 	new Date(dateselected);
						var dayinint 	= 	d.getDay();
						
						var timeslotsdata 	=	timeslots[dayinint];
						timeslotsdata		=	timeslotsdata.replace(/\r\n/g, "\r").replace(/\n/g, "\r").split(/\r/);
						$("#third_ul").empty();
						$.each(timeslotsdata, function (itemcount, timeslot){
							var timeslotdata		=	timeslot.split("-");
							var start_time 			= 	new Date(dateselected+' '+timeslotdata[0]);
							var end_time 			= 	new Date(dateselected+' '+timeslotdata[1]);
							var start_timestr 		=	moment(start_time).format('hh:mm A');
							var cstart_time 		= 	new Date(dateselected+' '+moment(start_time).format('hh:mm A'));
							var classname  		 	=	"";
							var active 				=	"";
							var todaydate			=	moment().format('YYYY-MM-DD');
							var currentdatetime 	=	new Date();
							currentdatetime 		=	currentdatetime.setHours(currentdatetime.getHours() + delay);
							var newtimestr 			=	(start_timestr+' - '+ moment(end_time).format('hh:mm A')); 
							if(itemcount==0){
								active 				= 	"active";
								$("#delivery_date").val(dateselected+' '+newtimestr);
							}
							if(todaydate==dateselected && cstart_time < currentdatetime){
								
							}else{
								var data = '<li class="selecttime '+active+'" data-date="'+dateselected+' '+newtimestr+'"><a href="javascript:void(0)" '+classname+'> '+newtimestr+'</a> </li>';
								$("#third_ul").append(data);
							}
						});
						return false;
					}); */
					
					
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