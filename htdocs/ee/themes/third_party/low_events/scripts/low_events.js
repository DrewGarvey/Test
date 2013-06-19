/**
 * Low Events JS file
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */

// Make sure LOW namespace is valid
if (typeof LOW == 'undefined') var LOW = new Object;

(function($){

// --------------------------------------
// Create Low Date object
// --------------------------------------

LOW.Date = function(date, time) {

	// Private var of JS date object
	var _date = new Date();

	// Add padding to number
	var _pad = function(i) {
		return ('0' + i).slice(-2);
	};

	// Make sure date given is valid and JS understands it
	var _getDate = function(str) {
		var m;

		if ( ! str || ! (m = str.match(/^(19\d\d|20\d\d)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/))) {
			return null;
		}

		return [parseInt(m[1]), m[2] - 1, m[3] * 1];
	};

	// Make sure time is valid and in 24h format
	var _getTime = function(str) {
		var m;

		if ( ! str || ! (m = str.toLowerCase().match(/^(\d{1,2}):(\d{1,2})\s?([ap]m)?$/i))) {
			return null;
		}

		if ( ! m[3]) return [m[1], m[2]];

		var hour = parseInt(m[1] * 1);

		if (hour == 12) {
			var hours = (m[3] == 'pm') ? 12 : 0;
		} else {
			var hours = (hour + (m[3] == 'pm' ? 12 : 0));
		}

		return [hours, parseInt(m[2])];
	};

	var obj = {
		// Set given date and time to this object
		set: function(d, t) {
			if (d = _getDate(d)) {
				_date.setFullYear(d[0]);
				_date.setMonth(d[1]);
				_date.setDate(d[2]);
			}

			if (t = _getTime(t)) {
				_date.setHours(t[0]);
				_date.setMinutes(t[1]);
			}
		},

		// Add amount of milliseconds to this object's date
		add: function(ms) {
			_date.setTime(_date.getTime() + ms);
		},

		// Return timestamp in milliseconds
		stamp: function() {
			return _date.getTime();
		},

		// Return the date in YYYY-MM-DD format
		getDate: function() {
			return [
				_date.getFullYear(),
				_pad(_date.getMonth() + 1),
				_pad(_date.getDate())
			].join('-');
		},

		// Return the time in HH:II format
		getTime: function() {
			return [
				_pad(_date.getHours()),
				_pad(_date.getMinutes())
			].join(':');
		},

		// Check if given date is valid
		isValidDate: function(d) {
			return _getDate(d);
		},

		// Check if given time is valid
		isValidTime: function(t) {
			return _getTime(t);
		}
	};

	obj.set(date, time);

	return obj;
};

// --------------------------------------
// Create Low Events object
// --------------------------------------

LOW.Events = function(el) {

	// jQuery objects
	var $el        = $(el),
		$startDate = $el.find('.start-date'),
		$startTime = $el.find('.start-time'),
		$endDate   = $el.find('.end-date'),
		$endTime   = $el.find('.end-time'),
		$allDay    = $el.find(':checkbox');

	// Translated data-attributes to settings
	var Settings = {
		timeFormat : ($el.attr('data-time-format') || 'eu'),
		timeInterval : ($el.attr('data-time-interval') || '30'),
		lang : {}
	};

	// Change timeFormat from 'us' or 'eu' to correct format
	Settings.timeFormat = (Settings.timeFormat == 'us') ? 'g:i a' : 'H:i';

	// Check language attributes
	var langKeys = ['decimal', 'mins', 'hr', 'hrs'];

	for (var i in langKeys) {
		var val = $el.attr('data-lang-'+langKeys[i]);
		if (val) {
			Settings.lang[langKeys[i]] = val;
		}
	}

	// Local Start date, End date and time difference
	var start = new LOW.Date($startDate.val(), $startTime.val()),
		end   = new LOW.Date($endDate.val(), $endTime.val()),
		diff  = end.stamp() - start.stamp(),
		day   = 60 * 60 * 24 * 1000,
		last  = {};

	// Keep track of last valid start/end times
	last.startTime = $startTime.val();
	last.endTime = $endTime.val();

	// --------------------------------------

	function validateTime() {
		var $obj  = $(this),
			time  = $obj.val().replace('.', ':'),
			which = (this.name.match(/\[start_time\]/)) ? 'startTime' : 'endTime',
			lowDate = new LOW.Date;

		// Check if time is valid, revert to last known valid time if not
		if (lowDate.isValidTime(time)) {
			last[which] = time;
		} else {
			time = last[which];
		}

		// Set it to be sure
		$obj.timepicker('setTime', time);
	};

	// When the start date/time changes, make sure the end date
	// also changes with it, depending on the recorded difference
	function keepDiff() {
		// set both start and end to start values
		start.set($startDate.val(), $startTime.val());
		end.set($startDate.val(), $startTime.val());

		// then add the current diff to the end date
		end.add(diff);

		// And set the end values
		$endDate.datepicker('setDate', end.getDate());
		$endTime.timepicker('setTime', end.getTime());

		// keep track of the last valid endtime
		last.endTime = end.getTime();

		// Verify that the difference is okay
		verifyDiff();
	};

	// See if time/date difference between start and end dates is not a negative value
	// end date should be later than start date
	function verifyDiff() {
		var newDiff = _getDiff();

		// If we have a negative difference on the same day,
		// add a day to the date, then calculate new diff
		if (newDiff < 0 && _isSameDay()) {
			end.add(day);
			$endDate.datepicker('setDate', end.getDate());
			newDiff = _getDiff();
		}

		if (newDiff < 0) {
			// If we still have a negative value, show error
			$endDate.addClass('low-error');
			$endTime.addClass('low-error');
		} else {
			// or else remove errors...
			$endDate.removeClass('low-error');
			$endTime.removeClass('low-error');

			// ...and set diff to the new diff...
			diff = newDiff;
		}

		// ...and alter the endTime timepicker's options
		_setEndTimepicker();
	};

	// Is the start and end date the same?
	function _isSameDay() {
		return start.getDate() == end.getDate();
	};

	// Get the current diff of the start/end dates
	function _getDiff() {
		start.set($startDate.val(), $startTime.val());
		end.set($endDate.val(), $endTime.val());
		return end.stamp() - start.stamp();
	};

	// Reset end timepicker to account for duration an minTime
	function _setEndTimepicker()
	{
		var options = {};

		if (_isSameDay()) {
			options.minTime = start.getTime();
			options.showDuration = true;
		} else {
			options.minTime = '00:00';
			options.showDuration = false;
		}

		$endTime.timepicker('option', options);
	};

	function toggleAllDay() {
		$el.toggleClass('low-all-day');
	};

	// --------------------------------------

	// Add datepicker to date fields
	$startDate.change(keepDiff).datepicker({
		showAnim: false,
		dateFormat: $.datepicker.W3C,
		defaultDate: start.getDate(),
		onSelect: keepDiff
	});

	$endDate.change(verifyDiff).datepicker({
		showAnim: false,
		dateFormat: $.datepicker.W3C,
		defaultDate: end.getDate(),
		onSelect: verifyDiff
	});

	// Add timepicker to time fields
	$startTime.timepicker({
		timeFormat: Settings.timeFormat,
		step: Settings.timeInterval
	});

	$endTime.timepicker({
		timeFormat: Settings.timeFormat,
		step: Settings.timeInterval,
		minTime: start.getTime(),
		showDuration: _isSameDay(),
		lang: Settings.lang
	});

	// Add checks on time fields to time fields
	$startTime.change(validateTime);
	$endTime.change(validateTime);
	$startTime.change(keepDiff);
	$endTime.change(verifyDiff);

	// All Day toggle Show/hide times based on checkbox
	$allDay.change(toggleAllDay);

};

// Execute onload
$(function(){
	$('.low-events').each(function(){
		new LOW.Events(this);
	});
});

})(jQuery);