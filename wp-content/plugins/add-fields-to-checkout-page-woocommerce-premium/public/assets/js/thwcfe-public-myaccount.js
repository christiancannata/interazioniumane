var thwcfe_public_base = (function($, window, document) {
	'use strict';
	
	var LINKED_DATE_FORMAT = /^{[a-z\_]+[a-z0-9\_]*}$/;
	var DATE_FORMAT_1 = /^(19|20)\d{2}-(0?[1-9]|1[0-2])-(0?[1-9]|1\d|2\d|3[01])$/;
	var DATE_FORMAT_2 = /^(0?[1-9]|1[0-2])\/(0?[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/;
	var weekDays = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
	
	Date.prototype.addDays = function(days) {
		this.setDate(this.getDate() + parseInt(days));
		return this;
	};
	
	$.fn.getType = function(){
		try{
			return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); 
		}catch(err) {
			return 'E001';
		}
	}
	
	function remove_duplicates(arr){
	    var unique = arr.filter(function(elem, index, self) {
            return index == self.indexOf(elem);
        })   
        return unique;
	}

	function is_subset_of(set, subset){
		var is_subset = true;
		if($.isArray(set) && $.isArray(subset)){
			$.each(subset, function( index, value ) {
				if($.inArray(value, set) == -1){
					is_subset = false;
					return false;
				}
			});
		}
		return is_subset;
	}

	function array_intersection(arr1, arr2){
		var intersection = $.map(arr1,function(a){return $.inArray(a, arr2) < 0 ? null : a;});
		//var intersection = arr1.filter(x => arr2.includes(x));
		return intersection;
	}

	function is_empty_arr(value){
		var is_empty = true;
		if(Array.isArray(value) && value.length){
			is_empty = false;
		}
		return is_empty;
	}
	
	function padZero(s, len, c){
		s = ""+s;
		var c = c || '0';
		while(s.length< len) s= c+ s;
		return s;
	}

	function uniqueId(length){
	   	var uid = '';
	   	var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	   	var len = chars.length;
	   	for(var i = 0; i < length; i++){
	      uid += chars.charAt(Math.floor(Math.random() * len));
	   	}
	   	return uid;
	}
	
	function isInt(value) {
	  	return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
	}
	
	function isEmpty(val){
		return (val === undefined || val == null || val.length <= 0) ? true : false;
	}
				
	function may_parse_date(dateStr){
		if(DATE_FORMAT_1.test(dateStr)){
			var date = new Date(dateStr);
			if(date){
				return date;
			}
		}
		return dateStr;
	}

	function isInputField(field){
		if(field && field.length > 0){
			var tagName = field[0].tagName.toLowerCase();
			if($.inArray(tagName, ["input", "select", "textarea"]) > -1){
				return true;
			}
		}
		return false;
	}
	
	function getInputField(key){
		var field = null;
		if(key){
			field = $('#'+key);
			if(!isInputField(field)){
				field = $("input[name='"+key+"']");
				if(!isInputField(field)){
					field = $("input[name='"+key+"[]']");
					if(!isInputField(field)){
						field = $("input[name='"+key+"[0]']");
					}
				}
			}
		}
		return field;
	}
	
	function prepare_date(dateStr, format, strict){
		var date = null;
		
		if(!isEmpty(dateStr)){
			try{
				date = $.datepicker.parseDate(format, dateStr);
				date.setHours(0,0,0,0);
			}catch(err) {
				if(!strict){
					var pattern = dateStr.split(" ");
					var years = null;
					var months = null;
					var days = null;
			
					if(pattern.length > 0){
						for(var i = 0; i < pattern.length; i++) { 
							var x = pattern[i];
							x = x.toLowerCase();
							
							if(x.indexOf("y") != -1){
								x = x.replace(/y/gi, "");
								years = parseInt(x);
							}else if(x.indexOf("m") != -1){
								x = x.replace(/m/gi, "");
								months = parseInt(x);
							}else if(x.indexOf("d") != -1){
								x = x.replace(/d/gi, "");
								days = parseInt(x);
							}
						}
					}
					
					if(!isEmpty(years) || !isEmpty(months) || !isEmpty(days)){
						date = new Date();
						date.setHours(0,0,0,0);
						
						if(years && years != 0){
							date.setFullYear(date.getFullYear() + years);
						}
						if(months && months != 0){
							date.setMonth(date.getMonth() + months);
						}
						if(days && days != 0){
							date.setDate(date.getDate() + days);
						}
					}
				}
			}
		}
		
		return date;
	}
	
	function compare_dates(field, cvalue){
		var result = null;
		var value = field.val();
		var format = field.data("date-format");
		
		if(isEmpty(value) || isEmpty(cvalue)){
			return null;
		}
		
		var d1 = prepare_date(value, format, true);
		var d2 = prepare_date(cvalue, format, false);
		
		if(d1 && d2){
			try{
				if(d1 > d2){
					result = 1; 
				}else if(d1 < d2){
					result = -1; 
				}else if(d1.getTime() === d2.getTime()){
					result = 0; 
				}
			}catch(err) {
				result = null;
			}
		}
		return result;
	}
	
	function is_same_date(date1, date2){
		if(date1 && date2){
			var day1 = date1.getDate();
			var month1 = date1.getMonth() + 1;
			var year1 = date1.getFullYear();
			
			var day2 = date2.getDate();
			var month2 = date2.getMonth() + 1;
			var year2 = date2.getFullYear();
			
			var matchYear = isInt(day1) && isInt(day2) && (day1 == day2) ? true : false;
			var matchMonth = isInt(month1) && isInt(month2) && (month1 == month2) ? true : false;
			var matchDay = isInt(year1) && isInt(year2) && (year1 == year2) ? true : false;
			
			return matchYear && matchMonth && matchDay;
		}
		return false;
	}
	
	function is_date_eq(field, cvalue){
		var result = compare_dates(field, cvalue);
		return (result != null && result === 0) ? true : false;
	}
	
	/*function is_date_ne(field, cvalue){
		var result = compare_dates(field, cvalue);
		return result ? true : false;
	}*/
	
	function is_date_gt(field, cvalue){
		var result = compare_dates(field, cvalue);
		return (result != null && result === 1) ? true : false;
	}
	
	function is_date_lt(field, cvalue){
		var result = compare_dates(field, cvalue);
		return (result != null && result === -1) ? true : false;
	}
	
	function is_day_eq(field, cvalue){
		var result = false;
		
		if(!isEmpty(cvalue)){
			var value = field.val();
			var format = field.data("date-format");
			var date = prepare_date(value, format, true);
			
			if(date){
				var day = date.getDay();
				//var daysArr = cvalue.split(",");
				if(isInt(cvalue)){
					cvalue = parseInt(cvalue);
					result = (day != null && day === cvalue) ? true : false;
				}else {
					cvalue = cvalue.toLowerCase();
					if($.inArray(cvalue, weekDays) >= 0){
						var daystring = weekDays[day];
						result = (daystring != null && daystring === cvalue) ? true : false;
					}
				}
			}
		}
		return result;
	}
	
	function setup_enhanced_select(form, class_selector, data){
		form.find('select.'+class_selector).each(function(){
			var ms = $(this);
			ms.select2({
				minimumResultsForSearch: 10,
				allowClear : true,
				placeholder: ms.data('placeholder'),
				maximumSelectionLength: ms.data('maxselections'),
				language: data.language
			}).addClass('enhanced');
		});
	}
	
	/******************************************
	***** DATE PICKER FUNCTIONS - START *******
	******************************************/
	function calculate_minutes_from_hr_min(_hour, _min){
		var minutes = null;
		if(isInt(_hour) && isInt(_min)){
			minutes = (_hour*60) + _min;
		}
		return minutes;
	}
	
	function get_minutes_from_time_24hr(time){
		var minutes = null;
		var timeArr = time.split(":");
		if(timeArr.length == 2){
			var _hour = parseInt(timeArr[0]);
			var _min = parseInt(timeArr[1]);
			minutes = calculate_minutes_from_hr_min(_hour, _min);
		}
		return minutes;
	}
	
	function no_sundays(date) {
		var day = date.getDay();
		return [day != 0, ''];
	}
	function no_saturdays(date) {
		var day = date.getDay();
		return [day != 6, ''];
	}
	function no_weekends(date) {
		return $.datepicker.noWeekends(date);
	}
	function no_christmas(date) {
		var day = date.getDate();
		var month = date.getMonth() + 1;
		return [!(day === 25 && month === 12), ''];
	}
	function no_new_years_day(date) {
		var day = date.getDate();
		var month = date.getMonth() + 1;
		return [!(day === 1 && month === 1), ''];
	}
	function no_holidays(date) {
		var datestring = $.datepicker.formatDate('yy-mm-dd', date);
    	return [ holidays.indexOf(datestring) == -1, '' ];
	}
	
	function no_weekends_or_holidays(date) {
		var noWeekend = $.datepicker.noWeekends(date);
		if (noWeekend[0]) {
			return no_holidays(date);
		} else {
			return noWeekend;
		}
	}
	
	function no_specific_days(date, disableDays) {
		var day = date.getDay();
		var daystring = weekDays[day];
    	return [ disableDays.indexOf(daystring) == -1, '' ];
	}
	
	function no_specific_dates(date, datestring) {
		var day = date.getDate();
		var month = date.getMonth() + 1;
		var year = date.getFullYear();
		
		var dateArr = datestring.split("-");
		if(dateArr.length == 3){
			var matchYear = isInt(dateArr[0]) ? dateArr[0] == year : true;
			var matchMonth = isInt(dateArr[1]) ? dateArr[1] == month : true;
			var matchDay = isInt(dateArr[2]) ? dateArr[2] == day : true;
			
			if(isInt(dateArr[0]) || isInt(dateArr[1]) || isInt(dateArr[2])){
				return [!(matchYear && matchMonth && matchDay), ''];
			}else{
				return [true, ''];
			}
		}else if(LINKED_DATE_FORMAT.test(datestring)){
			var sd = get_linked_datepicker_selected_date(datestring, 'disabled');
			if(sd){
				return [!is_same_date(date, sd), ''];
			}else{
				return [true, ''];
			}
		}else{
			var _now = new Date();
			if(is_same_date(date, _now)){
				var _hour = _now.getHours();
				var _min = _now.getMinutes();
				
				var op = "eq";
				if(datestring.indexOf("+") != -1){
					op = "gt";
					datestring = datestring.replace("+", "");
				}else if(datestring.indexOf("-") != -1){
					op = "lt";
					datestring = datestring.replace("-", "");
				}
				
				var _minutes = calculate_minutes_from_hr_min(_hour, _min);
				var minutes = get_minutes_from_time_24hr(datestring);
				
				if(isInt(minutes) && isInt(_minutes)){
					if((op === "eq" && _minutes == minutes) || (op === "gt" && _minutes > minutes) || (op === "lt" && _minutes < minutes)){
						return [false, ''];
					}
				}
			}
		}
		return [true, ''];
	}
	
	function disable_dates(date){
		var disabledDays = $(this).data("disabled-days");
		if(disabledDays && disabledDays.length > 0){
			var daysArr = disabledDays.split(",");
			var disabledDay = no_specific_days(date, daysArr);
			
			if(!disabledDay[0]) {
				return disabledDay;
			}
			
			/*if(daysArr.length > 0){
				for (i = 0; i < daysArr.length; i++) { 
					var dayIndex = weekDays.indexOf(daysArr[i].trim());
					
					var disabled = noSpecificDays(date, dayIndex);
					if(!disabled[0]) {
						return disabled;
					}
				}
			}*/
		}
		
		var disabledDates = $(this).data("disabled-dates");
		if(disabledDates && disabledDates.length > 0){
			var datesArr = disabledDates.split(",");
			/*var disabledDate = noSpecificDates(date, datesArr);
			
			if(!disabledDate[0]) {
				return disabledDate;
			}*/
			if(datesArr.length > 0){
				for (var i = 0; i < datesArr.length; i++) { 
					var disabledDate = no_specific_dates(date, datesArr[i].trim());
					if(!disabledDate[0]) {
						return disabledDate;
					}
				}
			}
		}
		
		return [true, ''];
	}

	function get_linked_datepicker_selected_date(dateStr, option){
		var sd = null;
		if(LINKED_DATE_FORMAT.test(dateStr)){
			var linkedDate = dateStr.replace(/[{}]/g, "");
			if(linkedDate){
				var dp = $("#"+linkedDate);
				if( dp.length ) {
				    sd = dp.datepicker("getDate");
				}
			}
		}
		return sd;
	}

	function check_if_linked_date(dateStr, targetDp, option){
		if(LINKED_DATE_FORMAT.test(dateStr)){
			var linkedDate = dateStr.replace(/[{}]/g, "");
			if(linkedDate){
				var dp = $("#"+linkedDate);
				if( dp.length ) {
					dp.datepicker("option", "onSelect", function(dateText, inst){
				        if(option === 'minDate'){
				        	var df = dp.data("date-format");
							var dpDate = prepare_date(dateText, df, true);
				    		var msecsInADay = 86400000;
				        	var nextDate = new Date(dpDate.getTime() + msecsInADay);

				        	targetDp.datepicker("option", "minDate", nextDate);
				        }else if(option === 'maxDate'){
				        	targetDp.datepicker("option", "maxDate", dp.datepicker("getDate"));
				        }
					});
				}
			}
			dateStr = '';
		}
		return dateStr;
	}
	
	function setup_date_picker(form, class_selector, data){
		form.find('.'+class_selector).each(function(){
			var dateFormat = $(this).data("date-format");		
			var defaultDate = $(this).data("default-date");
			var maxDate = $(this).data("max-date");
			var minDate = $(this).data("min-date");
			var yearRange = $(this).data("year-range");
			var numberOfMonths = $(this).data("number-months");
			var firstDay = $(this).data("first-day");
			
			maxDate = may_parse_date(maxDate);
			minDate = may_parse_date(minDate);

			maxDate = check_if_linked_date(maxDate, $(this), "maxDate");
			minDate = check_if_linked_date(minDate, $(this), "minDate");
			
			dateFormat = dateFormat == '' ? 'dd/mm/yy' : dateFormat;
			defaultDate = defaultDate == '' ? null : defaultDate;
			maxDate = maxDate == '' ? null : maxDate;
			minDate = minDate == '' ? null : minDate;
			yearRange = yearRange == '' ? '-100:+1' : yearRange;
			numberOfMonths = numberOfMonths > 0 ? numberOfMonths : 1;
			
			var value = $(this).val();
			if(value.trim()){
				defaultDate = value;
			}

			var showButtonPanel = data.dp_show_button_panel ? data.dp_show_button_panel : false;
			var changeMonth = data.dp_change_month ? data.dp_change_month : false;
			var changeYear = data.dp_change_year ? data.dp_change_year : false;
			
			//minDate = new Date().getHours() >= 2 ? 1 : 0;
			
			$(this).datepicker({
				defaultDate: defaultDate,
				maxDate: maxDate,
				minDate: minDate,
				yearRange: yearRange,
				numberOfMonths: numberOfMonths,
				showButtonPanel: showButtonPanel,
				changeMonth: changeMonth,
				changeYear: changeYear			
			});
			$(this).datepicker("option", $.datepicker.regional[data.language]);
			$(this).datepicker("option", "dateFormat", dateFormat);
			$(this).datepicker("option", "beforeShowDay", disable_dates);
			$(this).datepicker("option", "firstDay", firstDay);
			$(this).datepicker("setDate", defaultDate);

			if(data.readonly_date_field){
				$(this).prop('readonly', true);
			}
		});

		if(data.notranslate_dp){
			$('.ui-datepicker').addClass('notranslate');
		}
	}

	function setup_time_picker_linked_date(form, class_selector, data){
		$('.'+class_selector).change(function(){
			var dp = $(this);
			var tp = $('.thwcfe-linked-date-'+dp.prop('id'));
			if(tp.length){
		    	adjust_time_slots_based_on_date_selected(dp, tp, data);
			}
		});
	}
	/******************************************
	***** DATE PICKER FUNCTIONS - END *********
	******************************************/
	
    /******************************************
	***** TIME PICKER FUNCTIONS - START *******
	******************************************/
	function split_hour_min(hourMinStr){
		var hours = 0;
		var minutes = 0;
		
		if(hourMinStr && (typeof hourMinStr === 'string' || hourMinStr instanceof String)){
			var _hourMin = hourMinStr.split(" ");
			
			if(_hourMin.length > 0){
				for(var i = 0; i < _hourMin.length; i++) { 
					var x = _hourMin[i];
					x = x.toLowerCase();
					
					if(x.indexOf("h") != -1){
						x = x.replace(/h/gi, "");
						hours = parseInt(x);
					}else if(x.indexOf("m") != -1){
						x = x.replace(/m/gi, "");
						minutes = parseInt(x);
					}
				}
			}
			
			hours = hours ? hours : 0;
			minutes = minutes ? minutes : 0;
			
			if(minutes >= 60){
				hours = hours + 1;
				minutes = 0;
			}
		}
		
		return [hours, minutes];
	}
	
	function get_start_hr_min(startTime){
		var timeInfo = {};
		if(startTime){
			var startTimeArr = split_hour_min(startTime);
			if(startTimeArr.length > 1){
				var currTime = new Date();
				var currHour = currTime.getHours();
				var currMin  = currTime.getMinutes();
				
				var _startHour = startTimeArr[0];
				var startDays = parseInt(_startHour/24);
				var startDate = new Date();
				startDate.addDays(startDays).setHours(0,0,0,0);
				var startHour = _startHour%24;
				var startMin  = startTimeArr[1];
				
				startHour = currHour+startHour;
				startMin  = currMin+startMin;
				if(startMin >= 60){
					startHour++;
					startMin = startMin-60;
				}else if(startMin < 0){
					startHour--;
					startMin = 60+startMin;
				}
				
				timeInfo['startDate'] = startDate;
				timeInfo['startDays'] = startDays;
				timeInfo['startHour'] = startHour;
				timeInfo['startMin'] = startMin;
				timeInfo['hour'] = startTimeArr[0];
				timeInfo['min'] = startTimeArr[1];
			}
		}
		return timeInfo;		
	}
	
	function get_time_suffix(time){
		var suffix = "";
		if(time){
			time = time.toLowerCase();
			if(time.indexOf("am") != -1){
				suffix = "am";
			}else if(time.indexOf("pm") != -1){
				suffix = "pm";
			}
		}
		return suffix;
	}
		
	function split_time_string(time, ampm){
		time = time.replace(/pm/gi, "");
		time = time.replace(/am/gi, "");
		var timeArr = time.split(":");
		
		var hours = parseInt(timeArr[0]);
		var minutes = parseInt(timeArr[1]);
		
		if(ampm == "pm" && hours < 12){
			hours = hours + 12;
		}else if(ampm == "am" && hours == 12){
			hours = hours - 12;
		}
		
		return [hours, minutes];
	}
	
	function split_time_string_12hr(time){
		var ampm = get_time_suffix(time);
		return split_time_string(time, ampm);
	}
	
	function get_disabled_time_ranges(minTime, maxTime, startTime){
		var minHour = minTime[0];
		var minMin = minTime[1];
		
		var maxHour = maxTime[0];
		var maxMin = maxTime[1];
		
		var currTime = new Date();
		var currHour = currTime.getHours();
		var currMin  = currTime.getMinutes();
		currTime.setSeconds(0, 0);
		
		var startHour = startTime["startHour"];
		var startMin = startTime["startMin"];
		var startDate = new Date();
		startDate.setHours(startHour, startMin, 0, 0);
				
		minHour = padZero(minHour, 2);
		minMin = padZero(minMin, 2);
		
		startHour = padZero(startHour, 2);
		startMin = padZero(startMin, 2);
		
		var disMinRange = minHour+":"+minMin;
		var disMaxRange = startHour+":"+startMin;
		
		var disRange = [[disMinRange, disMaxRange]];
		return disRange;
	}
	
	function disable_all_time_slots(tp, minTime, maxTime){
		var suffixMaxTime = get_time_suffix(maxTime);
		var maxTimeArr = split_time_string(maxTime, suffixMaxTime);
		var maxHour = maxTimeArr[0];
		var maxMin  = maxTimeArr[1];
		
		maxHour = padZero(maxHour, 2);
		maxMin = padZero(parseInt(maxMin)+1, 2);
							
		var newMaxTime = maxHour+':'+maxMin;
		tp.timepicker('option', 'disableTimeRanges', [[minTime, newMaxTime]]);
		//TODO correct newMaxTime for border cases (24:00)
	}
	
	function adjust_time_slots_based_on_date_selected(dp, tp, data){
		var dpDate = null;
		
		if(dp){
			var df = dp.data("date-format");
			var sd = dp.val();
			dpDate = prepare_date(sd, df, true);
		}
	   	
		var minTime = tp.data("min-time");
	   	var maxTime = tp.data("max-time");
		var startTime = tp.data("start-time");
		
		var startTimeArr = get_start_hr_min(startTime);
		if(startTimeArr){
			var startDate = startTimeArr["startDate"];
			
			if(dp != null && dpDate < startDate){
				disable_all_time_slots(tp, minTime, maxTime);
			}else if(dp != null && dpDate > startDate){
				tp.timepicker('option', 'disableTimeRanges', []);
			}else{
				// If dates are equal check for current time
				// If current time is gt maxTime then clear time slots
				// If current time is lt minTime the set original minTime as minTime
				// If current time is within in the allowed range then set minTime as next available slot.
				var minTimeArr = split_time_string_12hr(minTime);
				var minHour = minTimeArr[0];
				var minMin = minTimeArr[1];
				
				var maxTimeArr = split_time_string_12hr(maxTime);
				var maxHour = maxTimeArr[0];
				var maxMin = maxTimeArr[1];
				
				var startHour = startTimeArr["startHour"];
				var startMin = startTimeArr["startMin"];
						
				if(startHour > maxHour || (startHour == maxHour && startMin > maxMin)){
					if(data.restrict_time_slots_for_same_day){
						disable_all_time_slots(tp, minTime, maxTime);
					}else{
						var disabledTimeRanges = get_disabled_time_ranges(minTimeArr, maxTimeArr, startTimeArr);
						tp.timepicker('option', 'disableTimeRanges', disabledTimeRanges);
					}
				}else if(startHour < minHour || (startHour == minHour && startMin < minMin)){
					tp.timepicker('option', 'disableTimeRanges', []);
				}else{
					var disabledTimeRanges = get_disabled_time_ranges(minTimeArr, maxTimeArr, startTimeArr);
					tp.timepicker('option', 'disableTimeRanges', disabledTimeRanges);
				}				
			}
		}
	}

	/*function tpMinTime(format, step, minTime, maxTime, startTime){
		if(startTime){
			var _startTime = thwcfeSplitHourMin(startTime);
			
			if(_startTime.length > 1){
				
				var suffixMinTime = thwcfeGetTimeSuffix(minTime);
				var suffixMaxTime = thwcfeGetTimeSuffix(maxTime);
				
				var _minTime = thwcfeSplitTimeString(minTime, suffixMinTime);
				var _maxTime = thwcfeSplitTimeString(maxTime, suffixMaxTime);
				
				if(_minTime.length > 1){
					var currTime = new Date();
					
					var minHour = _minTime[0];
					var minMin  = _minTime[1];
					
					var dateMin = new Date();
					dateMin.setHours(minHour, minMin);
					
					//if(currTime >= dateMin){
						var maxHour = _maxTime[0];
						var maxMin  = _maxTime[1];
						
						var currHour = currTime.getHours();
						var currMin  = currTime.getMinutes();
						
						var startHour = _startTime[0];
						var startMin  = _startTime[1];	
						
						currHour = currHour + startHour;
						currMin  = currMin + startMin;
						if(currMin >= 60){
							currHour += 1;
							//currMin = 00;
							currMin = currMin-60;
						}
						
						var dateStart = new Date();
						dateStart.setHours(currHour, currMin);
						var dateDiff = dateStart - dateMin;
						dateDiff = dateDiff/60000;
					
						if(step < 60){
							var ns = Math.floor(dateDiff/step);
							var newStartTime = (ns+1)*step;
							
							var newStartTimeHour = Math.floor(newStartTime/60);
							var newStartTimeMin = Math.floor(newStartTime%60);
							
							currHour = minHour + newStartTimeHour;
							currMin = minMin + newStartTimeMin;
							
							//var ns = Math.floor(currMin/step);
							//currMin = (ns+1)*step;
							
							if(currMin >= 60){
								currHour += 1;
								//currMin = 00;
								currMin = currMin-60;
							}
						}
						
						//if(currHour > maxHour){
						//	maxHour = maxHour;
						//	currMin = maxMin;
						//}else if(currHour = maxHour && currMin > maxMin){
						//	currMin = maxMin;
						//}
						
						if(currHour < minHour || (currHour == minHour && currMin < minMin)){
							return minTime;
						}
						
						if(currHour > maxHour || (currHour == maxHour && currMin > maxMin)){
							return minTime;
							//maxMin += 1;
							//return maxHour+":"+maxMin;
						}
					
						currHour = padZero(currHour, 2);
						currMin  = padZero(currMin, 2);
						
						minTime  = currHour+":"+currMin;
					//}
				}
			}
		}
		
		return minTime;
	}

	function thwcfeTimeInfoFromTimeString(timeStr){
		var timeInfo = {};
		if(timeStr){
			var suffix = thwcfeGetTimeSuffix(timeStr);
			var timeArr = thwcfeSplitTimeString(timeStr, suffix);
						
			if(timeArr.length > 1){
				timeInfo['suffix'] = suffix;
				timeInfo['hour'] = timeArr[0];
				timeInfo['min'] = timeArr[1];
				timeInfo['date'] = new Date().setHours(timeArr[0], timeArr[1]);
			}
		}
		return timeInfo;
	}*/
	
	function setup_time_picker(form, class_selector, data){
		form.find('.'+class_selector).each(function(){
			var minTime = $(this).data("min-time");		
			var maxTime = $(this).data("max-time");
			var step    = $(this).data("step");
			var format  = $(this).data("format");
			var startTime = $(this).data("start-time");
			var linkedDate = $(this).data("linked-date");
							
			minTime = minTime ? minTime : '12:00am';
			maxTime = maxTime ? maxTime : '11:30pm';
			step 	= step ? step : '30';
			format 	= format ? format : 'h:i A';
			
			var args = {
				'minTime': minTime,
				'maxTime': maxTime,
				'step': step,
				'timeFormat': format,
				'forceRoundTime': true,
				//'showDuration':true,
				'disableTextInput': true,
				'disableTouchKeyboard': true,
				'lang': data.lang
			}		
			$(this).timepicker(args);
			//$(this).timepicker('option', 'minTime', tpMinTime(format, step, minTime, maxTime, startTime));
			
			if(linkedDate){
				var dp = $("#"+linkedDate);
				if( dp.length ) {
					adjust_time_slots_based_on_date_selected(dp, $(this), data);
				}
			}else{
				adjust_time_slots_based_on_date_selected(null, $(this), data);	
			}
		});
	}
    /******************************************
	***** TIME PICKER FUNCTIONS - END *********
	******************************************/
	
	/********************************************
	***** CHARACTER COUNT FUNCTIONS - START *****
	********************************************/
	$('.thwcfe-char-count').keyup(function(){
        display_char_count($(this), true);
	});
	
	$('.thwcfe-char-left').keyup(function(){
        display_char_count($(this), false);
	});

	function display_char_count(elm, isCount){
		var fid = elm.prop('id');
        var len = elm.val().length;
		var displayElm = $('#'+fid+"-char-count");
		
		if(isCount){
			displayElm.text('('+len+' characters)');
		}else{
			var maxLen = elm.prop('maxlength');
			var left = maxLen-len;
			displayElm.text('('+left+' characters left)');
			if(rem < 0){
				displayElm.css('color', 'red');
			}
		}
	}
    /******************************************
	***** CHARACTER COUNT FUNCTIONS - END *****
	******************************************/
	
	function set_field_value_by_id(elm, type, value){
		//TODO
	}
	function set_field_value_by_name(elm, type, value){
		//TODO
	}

	function set_field_value_by_elm(elm, type, value){
		switch(type){
			case 'radio':
				elm.val([value]);
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					value = value ? value : [];
					elm.val(value);
				}else{
					elm.val([value]);
				}
				break;
			case 'select':
				if(elm.prop('multiple')){
					elm.val(value);
				}else{
					elm.val([value]);
				}
				break;
			default:
				elm.val(value);
				break;
		}
	}
	
	function get_field_value(type, elm, name){
		var value = '';
		switch(type){
			case 'radio':
				value = $("input[type=radio][name='"+name+"']:checked").val();
				break;
			case 'checkbox':
				if(elm.data('multiple') == 1){
					var valueArr = [];
					$("input[type=checkbox][name='"+name+"[]']:checked").each(function(){
					   valueArr.push($(this).val());
					});
					value = valueArr;//.toString();
				}else{
					value = $("input[type=checkbox][name='"+name+"']:checked").val();
				}
				break;
			case 'select':
				value = elm.val();
				break;
			case 'multiselect':
				value = elm.val();
				break;
			default:
				value = elm.val();
				break;
		}
		return value;
	}

	function get_field_name(type, name, id, multiple){
		if(type == 'checkbox' && multiple){
			name = name.replace("[]", "");
		}else if(type == "select" && multiple){
			name = id;
		}
		return name;
	}
	
	return {
		setup_enhanced_select : setup_enhanced_select,
		setup_date_picker : setup_date_picker,
		setup_time_picker : setup_time_picker,
		setup_time_picker_linked_date : setup_time_picker_linked_date,
		display_char_count : display_char_count,
		remove_duplicates : remove_duplicates,
		is_subset_of : is_subset_of,
		array_intersection : array_intersection,
		is_empty_arr : is_empty_arr,
		set_field_value_by_elm : set_field_value_by_elm,
		get_field_value : get_field_value,
		get_field_name : get_field_name,
		isInputField : isInputField,
		getInputField : getInputField,
		is_date_eq : is_date_eq,
		is_date_gt : is_date_gt,
		is_date_lt : is_date_lt,
		is_day_eq : is_day_eq,
		uniqueId : uniqueId,
	};
}(window.jQuery, window, document));

var thwcfe_public_conditions = (function($, window, document) {
	'use strict';
	
	function hide_section(celm, validations, needSetup){
		celm.hide();
		
		var sid = celm.prop('id');
		celm.addClass('thwcfe-disabled-section');
		
		if(celm.find('.thwcfe-input-field-wrapper').length){
			celm.find('.thwcfe-input-field-wrapper').each(function(){
			  	//$(this).find('.thwcfe-input-field').removeAttr("required");
				var validations = $(this).data("validations");
				disable_field_validations($(this), validations);
			});
		}
		
		var disabled_snames = $('#thwcfe_disabled_sections').val();
		var disabled_snames_x = disabled_snames ? disabled_snames.split(",") : [];
		
		disabled_snames_x.push(sid); 
		disabled_snames = disabled_snames_x.toString();
		
		$('#thwcfe_disabled_sections').val(disabled_snames);

		var trigger_price_calc = needSetup ? false : true;
		enable_disable_price_fields(celm, false, trigger_price_calc);
	}
	
	function show_section(celm, validations, needSetup){
		celm.show();
		
		var sid = celm.prop('id');
		celm.removeClass('thwcfe-disabled-section');
		
		if(celm.find('.thwcfe-input-field-wrapper').length){
			celm.find('.thwcfe-input-field-wrapper:not(.thwcfe-disabled-field-wrapper)').each(function(){
			  	//$(this).find('.thwcfe-input-field').attr("required", true);
				var validations = $(this).data("validations");
				disable_field_validations($(this), validations);
			});
		}
		
		var disabled_snames = $('#thwcfe_disabled_sections').val();
		var disabled_snames_x = disabled_snames ? disabled_snames.split(",") : [];
		
		disabled_snames_x = jQuery.grep(disabled_snames_x, function(value) {
		  	return value != sid; 
		}); 
		disabled_snames = disabled_snames_x.toString();
		
		$('#thwcfe_disabled_sections').val(disabled_snames);

		var trigger_price_calc = needSetup ? false : true;
		enable_disable_price_fields(celm, true, trigger_price_calc);
	}
	
	function hide_field(cfield, validations, needSetup){
		var fid = '';

		if(cfield.hasClass('thwcfe-html-field-wrapper')){
			cfield.hide();
			fid = cfield.data('name');
			
		}else{
			var cinput = cfield.find(":input.thwcfe-input-field");
			if(cfield.getType() === 'hidden'){
				cinput = cfield;
			}

			if(cinput.hasClass('thwcfe-disabled-field') && !cinput.is(":visible")){
				return;
			}

			var ftype = cinput.getType();
			fid = cinput.prop('id');

			if(ftype == "radio"){
				fid = cinput.prop('name');
			}else if(ftype == "checkbox"){
			    fid = cinput.prop('name');
			    fid = fid.replace("[]", "");   
			}
			
			cinput.data('current-value', thwcfe_public_base.get_field_value(ftype, cinput, fid));
			cfield.hide();	
			thwcfe_public_base.set_field_value_by_elm(cinput, ftype, '');
			cinput.addClass('thwcfe-disabled-field');
			cfield.addClass('thwcfe-disabled-field-wrapper');

			var change_event_disabled_fields = thwcfe_public_var.change_event_disabled_fields;
			var change_e_disabled_fields = change_event_disabled_fields ? change_event_disabled_fields.split(",") : [];
			if($.inArray(fid, change_e_disabled_fields) === -1){
				cinput.change();
			}
			
			if(ftype == "E001" && cfield.attr('data-name')){
				fid = cfield.data('name');
			}

			disable_field_validations(cfield, validations);
		}
		
		if(fid){
			var disabled_fnames = $('#thwcfe_disabled_fields').val();
			var disabled_fnames_x = disabled_fnames ? disabled_fnames.split(",") : [];
			
			disabled_fnames_x.push(fid); 
			disabled_fnames = disabled_fnames_x.toString();
			
			$('#thwcfe_disabled_fields').val(disabled_fnames);
		}
	}

	function show_field(cfield, validations, needSetup){
		var fid = '';

		if(cfield.hasClass('thwcfe-html-field-wrapper')){
			cfield.show();
			fid = cfield.data('name');

		}else{
			//var cinput = cfield.find(":input");
			var cinput = cfield.find(":input.thwcfe-input-field");
			if(cfield.getType() === 'hidden'){
				cinput = cfield;
			}

			if(!cinput.hasClass('thwcfe-disabled-field')){
				return;
			}

			var ftype = cinput.getType();
			fid = cinput.prop('id');

			if(ftype == "radio"){
				fid = cinput.prop('name');
			}else if(ftype == "checkbox"){
			    fid = cinput.prop('name');
			    fid = fid.replace("[]", "");   
			}
			
			cfield.show();	
			var fval = cinput.data('current-value');
			if(fval){
				thwcfe_public_base.set_field_value_by_elm(cinput, ftype, fval);
			}
			cfield.removeClass('thwcfe-disabled-field-wrapper');
			cinput.removeClass('thwcfe-disabled-field');

			var change_event_disabled_fields = thwcfe_public_var.change_event_disabled_fields;
			var change_e_disabled_fields = change_event_disabled_fields ? change_event_disabled_fields.split(",") : [];
			if($.inArray(fid, change_e_disabled_fields) === -1){
				cinput.change();
			}
			//cfield.find(":input").val('');

			if(ftype == "E001" && cfield.attr('data-name')){
				fid = cfield.data('name');
			}

			enable_field_validations(cfield, validations);
		}

		if(fid){
			var disabled_fnames = $('#thwcfe_disabled_fields').val();
			var disabled_fnames_x = disabled_fnames ? disabled_fnames.split(",") : [];
			
			disabled_fnames_x = jQuery.grep(disabled_fnames_x, function(value) {
			  	return value != fid; 
			});
			
			disabled_fnames = disabled_fnames_x.toString();
			
			$('#thwcfe_disabled_fields').val(disabled_fnames);
		}
	}
	
	function hide_elm(elm, validations, needSetup){
		var elmType = elm.data("rules-elm");
		if(elmType === 'section'){
			hide_section(elm, validations, needSetup);
		}else{
			hide_field(elm, validations, needSetup);
		}
	}
	
	function show_elm(elm, validations, needSetup){
		var elmType = elm.data("rules-elm");
		if(elmType === 'section'){
			show_section(elm, validations, needSetup);
		}else{
			show_field(elm, validations, needSetup);
		}
	}

	function disable_field_validations(elm, validations){
		if(validations) {
			elm.removeClass(validations);
			elm.removeClass('woocommerce-validated woocommerce-invalid woocommerce-invalid-required-field');
		}
	}
	
	function enable_field_validations(elm, validations){
		elm.removeClass('woocommerce-validated woocommerce-invalid woocommerce-invalid-required-field');
		if(validations) {
			elm.addClass(validations);
		}	
	}
	
	function enable_disable_price_fields(wrapper, enable, trigger_price_calc){
		var price_fields = wrapper.find('.thwcfe-price-field');
		if(price_fields.length){
			if(enable){
				price_fields.removeClass('thwcfe-disabled-shipping-field');
			}else{
				price_fields.addClass('thwcfe-disabled-shipping-field');
			}

			if(trigger_price_calc){
				thwcfe_public_price.calculate_extra_cost(thwcfe_public_var);
			}
		}
	}
	
	function validate_condition(condition, valid, needSetup, cfield){
		if(condition){
			var operand_type = condition.operand_type;
			var operand = condition.operand;
			var operator = condition.operator;
			var cvalue = condition.value;
			
			if(operand_type === 'field' && operand){
				jQuery.each(operand, function() {
					var field = thwcfe_public_base.getInputField(this);
					
					if(thwcfe_public_base.isInputField(field)){
						var ftype = field.getType();
						var value = thwcfe_public_base.get_field_value(ftype, field, this);

						if(operator === 'empty' && value != ''){
							valid = false;
							
						}else if(operator === 'not_empty' && value == ''){
							valid = false;
							
						}else if(operator === 'value_eq' && value != cvalue){
							valid = false;
							
						}else if(operator === 'value_ne' && value == cvalue){
							valid = false;
							
						}else if(operator === 'value_in'){
							var value_arr = [];
							var cvalue_arr = [];

							if(value){
								value_arr = $.isArray(value) ? value : value.split(',');
							}
							if(cvalue){
								cvalue_arr = $.isArray(cvalue) ? cvalue : cvalue.split(',');
							}
							
							if(thwcfe_public_base.is_empty_arr(value_arr) || !thwcfe_public_base.is_subset_of(cvalue_arr, value_arr)){
								valid = false;
							}
							
						}else if(operator === 'value_cn'){
							var value_arr = [];
							var cvalue_arr = [];

							if(value){
								value_arr = $.isArray(value) ? value : value.split(',');
							}
							if(cvalue){
								cvalue_arr = $.isArray(cvalue) ? cvalue : cvalue.split(',');
							}
							
							if(!thwcfe_public_base.is_subset_of(value_arr, cvalue_arr)){
								valid = false;
							}
							
						}else if(operator === 'value_nc'){
							var value_arr = [];
							var cvalue_arr = [];

							if(value){
								value_arr = $.isArray(value) ? value : value.split(',');
							}
							if(cvalue){
								cvalue_arr = $.isArray(cvalue) ? cvalue : cvalue.split(',');
							}

							var intersection = thwcfe_public_base.array_intersection(cvalue_arr, value_arr);
							if(!thwcfe_public_base.is_empty_arr(intersection)){
								valid = false;
							}
							
						}else if(operator === 'value_gt'){
							if($.isNumeric(value) && $.isNumeric(cvalue)){
								valid = (Number(value) <= Number(cvalue)) ? false : valid;
							}else{
								valid = false;
							}
							
						}else if(operator === 'value_le'){
							if($.isNumeric(value) && $.isNumeric(cvalue)){
								valid = (Number(value) >= Number(cvalue)) ? false : valid;
							}else{
								valid = false;
							}
							
						}else if(operator === 'value_sw' && !value.startsWith(cvalue)){
							valid = false;

						}else if(operator === 'value_nsw' && value.startsWith(cvalue)){
							valid = false;

						}else if(operator === 'date_eq' && !thwcfe_public_base.is_date_eq(field, cvalue)){
							valid = false;
							
						}else if(operator === 'date_ne' && thwcfe_public_base.is_date_eq(field, cvalue)){
							valid = false;
							
						}else if(operator === 'date_gt' && !thwcfe_public_base.is_date_gt(field, cvalue)){
							valid = false;
							
						}else if(operator === 'date_lt' && !thwcfe_public_base.is_date_lt(field, cvalue)){
							valid = false;
							
						}else if(operator === 'day_eq' && !thwcfe_public_base.is_day_eq(field, cvalue)){
							valid = false;
							
						}else if(operator === 'day_ne' && thwcfe_public_base.is_day_eq(field, cvalue)){
							valid = false;
							
						}else if(operator === 'checked'){
							var checked = field.prop('checked');
							valid = checked ? valid : false;
							
						}else if(operator === 'not_checked'){
							var checked = field.prop('checked');
							valid = checked ? false : valid;

						}else if(operator === 'regex'){
							if(cvalue){
								var regex = new RegExp(cvalue);
								if(!regex.test(value)){
									valid = false;
								}
							}
						}
						
						if(needSetup){
							var depFields = field.data("fields");

							if(depFields){
								var depFieldsArr = depFields.split(",");
								depFieldsArr.push(cfield.prop('id'));
								depFields = depFieldsArr.toString();
							}else{
								depFields = cfield.prop('id');
							}

							field.data("fields", depFields);
							add_field_value_change_handler(field);
						}
					}
				});
			}
		}
		return valid;
	}

	function validate_field_condition(cfield, needSetup){
		var conditionalRules = cfield.data("rules");	
		var conditionalRulesAction = cfield.data("rules-action");
		var validations = cfield.data("validations");
		var valid = true;
		
		if(conditionalRules){
			try{
				jQuery.each(conditionalRules, function() {
					var ruleSet = this;	
					
					jQuery.each(ruleSet, function() {
						var rule = this;
						var validRS = false;
						
						jQuery.each(rule, function() {
							var conditions = this;								   	
							var validCS = true;
							
							jQuery.each(conditions, function() {
								validCS = validate_condition(this, validCS, needSetup, cfield);
							});
							
							validRS = validRS || validCS;
						});
						valid = valid && validRS;
					});
				});
			}catch(err) {
				alert(err);
			}
			
			if(conditionalRulesAction === 'hide'){
				if(valid){
					hide_elm(cfield, validations, needSetup);
				}else{
					show_elm(cfield, validations, needSetup);
				}
			}else{
				if(valid){
					show_elm(cfield, validations, needSetup);
				}else{
					hide_elm(cfield, validations, needSetup);
				}	
			}
		}
	}
	
	function conditional_field_value_change_listner(event){
	    var depFields = $(this).data("fields");
		var depFieldsArr = depFields.split(",");
		depFieldsArr = thwcfe_public_base.remove_duplicates(depFieldsArr);

		jQuery.each(depFieldsArr, function() {
			if(this.length > 0){	
				var cfield = $('#'+this);
				validate_field_condition(cfield, false);	
			}
		});	
	}
	
	function add_field_value_change_handler(field){
		field.off("change", conditional_field_value_change_listner);
		field.on("change", conditional_field_value_change_listner);
	}

	function validate_all_conditions(wrapper){
		if(wrapper){
			wrapper.find('.thwcfe-conditional-field').each(function(){
				validate_field_condition($(this), true);								 
			});
			wrapper.find('.thwcfe-conditional-section').each(function(){
				validate_field_condition($(this), true);								 
			});
		}else{
			$('.thwcfe-conditional-field').each(function(){
				validate_field_condition($(this), true);										 
			});
			$('.thwcfe-conditional-section').each(function(){
				validate_field_condition($(this), true);										 
			});
		}
	}

	function prepare_shipping_conitional_fields(elm, trigger_price_calc){
		var ship_to_different_address = $('#ship-to-different-address-checkbox');
		var shipping_wrapper = $('.woocommerce-shipping-fields');

		enable_disable_price_fields(shipping_wrapper, ship_to_different_address.is(':checked'), trigger_price_calc);
		/*if(trigger_price_calc){
			thwcfe_public_price.calculate_extra_cost(thwcfe_public_var);
		}*/

		/*if(ship_to_different_address.is(':checked')){
			shipping_wrapper.find('.thwcfe-price-field').removeClass('thwcfe-disabled-shipping-field');
		}else{
			shipping_wrapper.find('.thwcfe-price-field').addClass('thwcfe-disabled-shipping-field');
		}*/
	}
	
	return {
		validate_field_condition : validate_field_condition,
		validate_all_conditions : validate_all_conditions,
		prepare_shipping_conitional_fields : prepare_shipping_conitional_fields,
	};
}(window.jQuery, window, document));

var thwcfe_public_file_upload = (function($, window, document) {
	'use strict';
	
	//var currRequest = null;
	var IMG_FILE_TYPES = ["image/jpg", "image/png", "image/gif", "image/jpeg"];
	
	function setup_file_upload(wrapper, data){
		wrapper.find('.thwcfe-checkout-file').on('change', upload_file);
        //wrapper.find('.thwcfe-delete-file').on('click', remove_uploaded);
	}
	
	function upload_file(event){
		var files = event.target.files;
		var parent = $("#" + event.target.id).parent();
		var wrapper = $(this).closest('.thwcfe-input-field-wrapper');
		var input = wrapper.find('.thwcfe-checkout-file-value');
		var field_name = input.attr('name');
		var field_multiple = input.attr('multiple');
		
		var data = new FormData();
		var filenames_arr = [];
		var filenames = '';
		data.append("action", "thwcfe_file_upload");
		data.append("field_name", field_name);

		$.each(files, function(key, value){
			if (typeof field_multiple !== typeof undefined && field_multiple == 'multiple') {
				data.append("thwcfe_file_upload[]", value);
			} else {
				data.append("file", value);
			}

			if(value.name && $.inArray(value.name, filenames_arr) == -1){
				filenames_arr.push(value.name);
			}
		});

		if(filenames_arr.length){
			filenames = filenames_arr.toString();
		}

		$.ajax({
			type: 'POST',
			url: thwcfe_public_var.ajax_url,
			data: data,
			cache: false,
			dataType: 'json',
			processData: false, // Don't process the files
			contentType: false, // Set content type to false as jQuery will tell the server its a query string request
			beforeSend : function()    {           
				wrapper.find('.thwcfe-file-upload-status').show();
				input.val('');
				clear_message(wrapper);
			},
		})
		.done(function(data, textStatus, jqXHR){
			if(data.response == "SUCCESS"){
				var uploaded = data.uploaded;
				input.val(JSON.stringify(uploaded));
				input.data('file-name', filenames);

				var remove_btn = wrapper.find('.thwcfe-remove-uploaded');
				remove_btn.data('file', uploaded.file);
				remove_btn.show();
				
				if (typeof field_multiple !== typeof undefined && field_multiple == 'multiple') {
					var multiple_preview = true;
				} else {
					var multiple_preview = false;
				}
				var prev_html = prepare_preview_html(uploaded, multiple_preview);
				wrapper.find('.thwcfe-upload-preview').html(prev_html);
				
				wrapper.find('.thwcfe-uloaded-files').show();
				wrapper.find('.thwcfe-checkout-file').hide();

				input.trigger("change");


				/*var preview = "";
				if( data.type === "image/jpg" || data.type === "image/png"
					|| data.type === "image/gif" || data.type === "image/jpeg") {
					preview = "<img style='width:3rem; height: auto' src='" + data.url + "' />";
				} else {
					preview = data.filename;
				}

				var previewID = parent.attr("id") + "_preview";
				var previewParent = $("#"+previewID);
				previewParent.show();
				previewParent.children(".ibenic_file_preview").empty().append( preview );
				previewParent.children( "button" ).attr("data-fileurl",data.url );
				parent.children("input").val("");
				parent.hide();*/

			} else {
				add_message(wrapper, data, "error");
				clean_file_input(wrapper);
			}
		})
		.fail(function(jqXHR, textStatus, error){
		    add_message(wrapper, data, "error");
		    clean_file_input(wrapper);
		})
		.always(function() {
		    wrapper.find('.thwcfe-file-upload-status').hide();
		});
	}

	function prepare_preview_html(uploaded, multi){
		//var prev_html = uploaded.name;
		var file_size = '';
		if($.isNumeric(uploaded.size)){
			file_size = uploaded.size/1000;
			file_size = Math.round(file_size);
			file_size = file_size+' KB';
		}
		
		if( multi == true ) {
			var prev_html  = '<span class="thwcfe-uloaded-file-list multiple-upload-content"><span class="thwcfe-uloaded-file-list-item">';
			$(uploaded.name).each(function(i) {
				prev_html += '<span class="thwcfe-columns">';

				if($.inArray(uploaded.type[i], IMG_FILE_TYPES) !== -1){
					prev_html += '<span class="thwcfe-column-thumbnail">';
					prev_html += '<img src="'+ uploaded.url[i] +'" >';
					prev_html += '</span>';
				}

				prev_html += '<span class="thwcfe-column-title">';
				prev_html += '<span title="'+uploaded.name[i]+'" class="title">'+uploaded.name[i]+'</span>';
				if(file_size){
					prev_html += '<span class="size">'+file_size[i]+'</span>';
				}
				prev_html += '</span>';

				prev_html += '</span>';
			});

			prev_html += '<span class="thwcfe-column-actions">';
			prev_html += '<a href="#" onclick="thwcfeRemoveUploaded(this, event); return false;" class="thwcfe-action-btn thwcfe-remove-uploaded" title="Remove">X</a>';
			prev_html += '</span>';

			prev_html += '</span></span>';
		} else {
			var prev_html  = '<span class="thwcfe-uloaded-file-list"><span class="thwcfe-uloaded-file-list-item">';		
			prev_html += '<span class="thwcfe-columns">';

			if($.inArray(uploaded.type, IMG_FILE_TYPES) !== -1){
				prev_html += '<span class="thwcfe-column-thumbnail">';
				prev_html += '<img src="'+ uploaded.url +'" >';
				prev_html += '</span>';
			}

			prev_html += '<span class="thwcfe-column-title">';
			prev_html += '<span title="'+uploaded.name+'" class="title">'+uploaded.name+'</span>';
			if(file_size){
				prev_html += '<span class="size">'+file_size+'</span>';
			}
			prev_html += '</span>';

			prev_html += '<span class="thwcfe-column-actions">';
			prev_html += '<a href="#" onclick="thwcfeRemoveUploaded(this, event); return false;" class="thwcfe-action-btn thwcfe-remove-uploaded" title="Remove">X</a>';
			prev_html += '</span>';

			prev_html += '</span>';
			prev_html += '</span></span>';
		}

		
		return prev_html;
	}

	function remove_uploaded(elm, event) {
		//var fileurl = $(event.target).attr("data-fileurl");
		var wrapper = $(elm).closest('.thwcfe-input-field-wrapper');
		var file = $(elm).data('file');
		
		var data = {
			action: 'thwcfe_remove_uploaded',
			file: file			 
		};

		$.ajax({
			type: 'POST',
			url: thwcfe_public_var.ajax_url,
			data: data,
			cache: false,
			dataType: 'json',
			beforeSend : function()    {           
				wrapper.find('.thwcfe-uloaded-files').hide();
				wrapper.find('.thwcfe-file-upload-status').show();
				clear_message(wrapper);
			},
		})
		.done(function(data, textStatus, jqXHR){
		    console.log(data);
		    if(data.response == "SUCCESS"){
		    	$(elm).data('file', '');
				$(elm).hide();
				wrapper.find('.thwcfe-upload-preview').html('');
				wrapper.find('.thwcfe-uloaded-files').hide();
				wrapper.find('.thwcfe-checkout-file').show();

				clean_file_input(wrapper);
				//wrapper.find('.thwcfe-checkout-file').val('');
				//wrapper.find('.thwcfe-checkout-file-value').val('');

				//$("#ibenic_file_upload_preview").hide();
				//$("#ibenic_file_upload").show();
				add_message(wrapper, data, "success");
			}else if(data.response == "ERROR" ){
				add_message(wrapper, data, "error");
			}
		})
		.fail(function(jqXHR, textStatus, error){
			wrapper.find('.thwcfe-uloaded-files').show();
		    add_message(wrapper, error, "error");
		})
		.always(function() {
		    wrapper.find('.thwcfe-file-upload-status').hide();
		});
	}

	function change_uploaded(elm, event){
		var wrapper = $(elm).closest('.thwcfe-input-field-wrapper');

		wrapper.find('.thwcfe-remove-uploaded').hide();
		wrapper.find('.thwcfe-input-file').show();
	}
	function cancel_change_uploaded(elm, event){
		var wrapper = $(elm).closest('.thwcfe-input-field-wrapper');

		wrapper.find('.thwcfe-remove-uploaded').show();
		wrapper.find('.thwcfe-cancel-change').show();
		wrapper.find('.thwcfe-input-file').hide();
	}

	function clean_file_input(wrapper){
		var input = wrapper.find('.thwcfe-checkout-file-value');

		wrapper.find('.thwcfe-checkout-file').val('');
		input.val('');
		input.data('file-name', '');
		input.trigger("change");
	}

	function add_message(wrapper, data, type){
		//add_message( "File successfully deleted", "success");
		//alert(msg.error);
		if(data.response && data.error){
			wrapper.find('.thwcfe-file-upload-msg').html(data.error);
			wrapper.find('.thwcfe-file-upload-msg').show();
		}else{
			clear_message(wrapper);
		}
	}

	function clear_message(wrapper){
		wrapper.find('.thwcfe-file-upload-msg').html('');
		wrapper.find('.thwcfe-file-upload-msg').hide();
	}
	
	return {
		setup_file_upload : setup_file_upload,
		remove_uploaded : remove_uploaded,
		change_uploaded : change_uploaded,
		prepare_preview_html : prepare_preview_html,
		clean_file_input : clean_file_input,
	};
}(window.jQuery, window, document));

function thwcfeRemoveUploaded(elm, event){
	thwcfe_public_file_upload.remove_uploaded(elm, event);
}

function thwcfeChangeUploaded(elm, event){
	thwcfe_public_file_upload.change_uploaded(elm, event);
}

var thwcfe_public_price = (function($, window, document) {
	'use strict';
	
	var currRequest = null;
	
	function setup_price_fields(wrapper, data){
		prepare_extra_cost_for_option_fields(wrapper);

		wrapper.find('.thwcfe-price-field').change(function(){
			//if(!$(this).hasClass("thwcfe-disabled-field")){																 
				var ftype = $(this).getType();	
				if(ftype == "select" || ftype == "radio"){
					prepare_extra_cost_from_selected_option($(this), ftype);
				}
				calculate_extra_cost(data);
			//}
			return false;
		});
	}
	
	function prepare_extra_cost_for_option_fields(wrapper){
		wrapper.find('.thwcfe-price-option-field').each(function(){										 
			prepare_extra_cost_from_selected_option($(this), 'select');
		});
	}
	
	function prepare_extra_cost_from_selected_option(elm, ftype){
		var option = elm.find(':selected');
		var oPrice = '';
		var oPriceType = '';
		
		if(elm.attr("multiple")){
			elm.find('option:selected').each(function(){
				var oprice = $(this).data('price');
				var opriceType = $(this).data('price-type');	
				if(oprice){
					opriceType = opriceType ? opriceType : 'normal';
					
					if(oPrice.trim()){
						oPrice += ',';
					}
					
					if(oPriceType.trim()){
						oPriceType += ',';
					}
					
					oPrice += oprice;
					oPriceType += opriceType;
				}
			});
		}else{
			oPrice = option.data('price');
			oPriceType = option.data('price-type');
			oPriceType = oPriceType ? oPriceType : 'normal';
		}
		
		if(oPrice){
			elm.data("price", oPrice);		
			elm.data("price-type", oPriceType);
		}else{
			if(ftype == "select"){
				elm.data("price", "");		
				elm.data("price-type", "");
			}
		}
	}

	function prepare_price_props_for_multiple_options(elm, type, options){
		var oPrice = '';
		var oPriceType = '';
		
		options.each(function(){
			var oprice = $(this).data('price');
			var opriceType = $(this).data('price-type');	

			if(oprice){
				opriceType = opriceType ? opriceType : 'normal';
				
				if(oPrice.trim()){
					oPrice += ',';
				}
				
				if(oPriceType.trim()){
					oPriceType += ',';
				}
				
				oPrice += oprice;
				oPriceType += opriceType;
			}
		});
		
		return {
			'price' : oPrice,
			'priceType' : oPriceType
		}
	}

	function prepare_price_props_for_multiple_option_field(elm, name, type){
		var price_props = false;
		if(type === 'select'){
			var options = elm.find('option:selected');
			price_props = prepare_price_props_for_multiple_options(elm, type, options);

		}else if(type === 'checkbox'){
			var options = $("input[type=checkbox][name='"+name+"[]']:checked");
			price_props = prepare_price_props_for_multiple_options(elm, type, options);
		}
		return price_props;
	}
		
	function calculate_extra_cost(args){
		var priceInfoArr = {};									 	
		$('.thwcfe-price-field:not(.thwcfe-disabled-field)').each(function(){	
			if(!$(this).hasClass("thwcfe-disabled-field") && !$(this).hasClass("thwcfe-disabled-shipping-field")){
				var ftype = $(this).getType();
				var multiple = 0;

				if($(this).hasClass("thwcfe-checkout-file-value")){
					ftype = 'file';
				}

				if(ftype == 'checkbox'){
					multiple = $(this).data("multiple");
				}else if(ftype == "select"){
					if($(this).attr("multiple")){
						multiple = 1;
					}
				}

				var id = $(this).prop("id");
				var name = thwcfe_public_base.get_field_name(ftype, $(this).prop("name"), id, multiple)
				var value = thwcfe_public_base.get_field_value(ftype, $(this), name); //$(this).val();

				if(ftype === 'radio'){
					value = $(this).is(':checked') ? value : '';

				}else if(ftype === 'file'){
					value = $(this).data('file-name');
					value = value ? value : '';
				}
				
				var label = $(this).data("price-label");
				var valueText = value;
				var qtyField = '';
				var price = $(this).data("price");		
				var priceType = $(this).data("price-type");
				var priceUnit = $(this).data("price-unit");
				var taxable = $(this).data("taxable");
				var tax_class = $(this).data("tax_class");

				if(ftype === 'checkbox' && multiple){
					var price_props = prepare_price_props_for_multiple_option_field($(this), name, ftype);
					if(price_props){
						price = price_props['price'];
						priceType = price_props['priceType'];
					}
				}
				
				if(priceType && priceType === 'dynamic'){
					if(!$.isNumeric(priceUnit) && $('#'+priceUnit).length){
						qtyField = $('#'+priceUnit).val();
						priceUnit = 1;
					}
				}else{
					priceUnit = 0;
				}
				
				if(value && name && (price || (priceType && priceType === 'custom'))){
					var priceInfo = {};
					priceInfo['name'] = name;
					//priceInfo['label'] = label+' ('+valueText+')';
					priceInfo['label'] = label;
					priceInfo['price'] = price;
					priceInfo['price_type'] = priceType;
					priceInfo['price_unit'] = priceUnit;
					priceInfo['value'] = value;
					priceInfo['qty_field'] = qtyField;
					priceInfo['taxable'] = taxable;
					priceInfo['tax_class'] = tax_class;
					priceInfo['multiple'] = multiple;
					
					priceInfoArr[name] = priceInfo;
				}
			}
		});
		
		var data = {
            action: 'thwcfe_calculate_extra_cost',
			price_info: JSON.stringify(priceInfoArr)
        };

        if($('.thwcfe-price-field').length){
	        currRequest = $.ajax({
	            type: 'POST',
	            url : args.ajax_url,
	            data: data,
				beforeSend : function(jqxhr){           
					if(currRequest != null) {
						currRequest.abort();

						if(currRequest.uid){
							this.data = this.data+'&abort_req='+currRequest.uid;
						}
					}

					jqxhr.uid = thwcfe_public_base.uniqueId(16);
					this.data = this.data+'&uid='+jqxhr.uid;
				},
	            success: function(code){
	            	$('body').trigger('update_checkout');
					//$( document.body ).trigger( 'update_checkout' );
					//wc_checkout_form.trigger_update_checkout();
	            }
	        });
	    }
	}
	
	return {
		setup_price_fields : setup_price_fields,
		calculate_extra_cost : calculate_extra_cost,
	};
}(window.jQuery, window, document));

var thwcfe_public_repeat = (function($, window, document) {
	'use strict';

	/*function setup_repeat_section_fields(wrapper){
		wrapper.find('.thwcfe-repeat-section').each(function(){										 
			prepare_extra_cost_from_selected_option($(this), 'select');
		});

		wrapper.find('.thwcfe-repeat-trigger').change(function(){
			
		});
	}
	
	function repeat_section(celm, validations, needSetup){
		
	}
	
	
	
	return {
		
	};*/
}(window.jQuery, window, document));

var thwcfe_public_myaccount = (function( $ ) {
	'use strict';

	function initialize_thwcfe_myaccount(){
		var form_wrapper = $('.woocommerce-MyAccount-content, .woocommerce-form-register');
		if(form_wrapper){
			thwcfe_public_base.setup_enhanced_select(form_wrapper, 'thwcfe-enhanced-select', thwcfe_public_var);
			thwcfe_public_base.setup_enhanced_select(form_wrapper, 'thwcfe-enhanced-multi-select', thwcfe_public_var);
		    thwcfe_public_base.setup_date_picker(form_wrapper, 'thwcfe-checkout-date-picker', thwcfe_public_var);
		    thwcfe_public_base.setup_time_picker(form_wrapper, 'thwcfe-checkout-time-picker', thwcfe_public_var);
		    thwcfe_public_base.setup_time_picker_linked_date(form_wrapper, 'thwcfe-checkout-date-picker', thwcfe_public_var);
		    
			thwcfe_public_file_upload.setup_file_upload(form_wrapper, thwcfe_public_var);

			/**** CONDITIONAL FIELD SETUP - START ****/
			thwcfe_public_conditions.validate_all_conditions(null);
						
			/**** CHARACTER COUNT - START -----****/
			/*$('.thwcfe-char-count .thwcfe-input-field').keyup(function(){
				thwcfe_public_base.display_char_count($(this), true);
			});
			
			$('.thwcfe-char-left .thwcfe-input-field').keyup(function(){
				thwcfe_public_base.display_char_count($(this), false);
			});*/
		}
	}
	
	/***----- INIT -----***/
	initialize_thwcfe_myaccount();

	return {
		initialize_thwcfe_myaccount : initialize_thwcfe_myaccount,
	};

})( jQuery );
