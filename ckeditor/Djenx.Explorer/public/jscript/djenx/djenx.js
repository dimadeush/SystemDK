/**
 *
 * Collection of useful functions v1.1
 * 2009 Demphest Gorphek
 *
 * Date: 2011-01-28 03:24:11 +0300
 * Revision: 1024
 */

var _$Djenx = function() {};

_$Djenx.prototype = {
	version: '1.0',
	
	add: function(attr, obj) {
		this[attr] = obj;
	},

	merge: function(attr, obj) {
		if (this.empty(this[attr])) {
			this.add(attr, obj);
			return;
		}

		for (var i in obj) {
			this[attr][i] = obj[i];
		}
	},

	require: function(src, pos) {
		if (!this.empty(pos) && 'prepend' == pos) {
			$('head').prepend('<script type="text/javascript" src="' + src + '"></script>');
		} else {
			$('head').append('<script type="text/javascript" src="' + src + '"></script>');
		}
	},

	/* === */

	refresh: function() {
		var link = location.href;
		this.redirect(link);
	},

	redirect: function(link) {
		try {
			location.replace(link);
		} catch(e) {
			try {location.href = link;} catch(e2) {}
		}
		return;
	},

	empty: function(o, p) {
		if ('undefined' === typeof (o) || null === o || false === o || '' === o || o instanceof Object && 0 == o.length) {
			return true;
		}
		return false;
	},

	isset: function() {	/* http://kevin.vanzonneveld.net */
		var a = arguments, count = a.length, i = -1;
		if (0 === count) {
			throw new Error('Empty isset');
		}
		while (++i !== count) {
			if ('undefined' == typeof(a[i]) || null === a[i]) {
				return false;
			}
		}
		return true;
	},

	/* === */

	dump: function(arr, level, dumpText) {
		if (!level) level = 0;
		if (!dumpText) dumpText = '';

		var level_padding = "";
		for(var j=0; j<level+1; j++) level_padding += "    ";

		if (typeof(arr) == 'object') {
			for(var item in arr) {
				var value = arr[item];

				if(typeof(value) == 'object') {
					dumpText += level_padding + "'" + item + "' ...\n";
					dumpText += this.dump(value, level+1, dumpText);
				} else {
					dumpText += level_padding + "'" + item + "' => \"" + value + "\"\n";
				}
			}
		} else {
			dumpText = "==>" + arr + "<==(" + typeof(arr) + ")";
		}
		return dumpText;
	},

	setURL: function() {
		$.url = {};
		$.urlParams = [];
		var
			s = self.location.search.substr(1)
		,	p	// params = s.split("&") 
		,	d	// data ('key=value') = p[index]
		,	k	// key   = d[0]
		,	v	// value = d[1]
		,	A	// isArray(value)
		,	i	// index
		;
		// parse the URL and create $.url hash
		if (!s) return;
		p = s.split("&");
		for (i=0; i < p.length; i++) {
			d = p[i].split("=");
			k = $.trim(d[0]);
			if (k) {
				v = d[1]==undefined ? true : parse(d[1]);
				if (!$.url[k]) {
					$.url[k] = v;
					$.urlParams.push(k);
				}
				else {
					if (!$.isArray($.url[k]) || ($.isArray(v) && typeof $.url[k][0] != 'object'))
						$.url[k] = [ $.url[k] ];
					$.url[k].push(v);
				}
			}
		}

		function parse (x) {
			x = $.trim(x);
			if (!x) return "";
			var
				c = x.length-1			// characters (length)
			,	f = x.charAt(0)			// first character
			,	l = x.charAt(c)			// last character
			,	A = f=="[" && l=="]"	// is an Array
			,	H = f=="{" && l=="}"	// is a Hash
			,	d						// data = v.split(",") = [v1,v2,...]
			,	h						// hash data-array = d.split(":") = [key,value]
			,	k						// key = h[0]
			,	o						// object - array or hash
			,	i						// index
			;
			if (A || H) {
				o = A ? [] : {}; // init return value
				d = x.substr(1,c-1).split(",")
				for (i=0; i < d.length; i++) {
					if (A)
						o[i] = parse(d[i]);
					else if (d[i]) {
						h = d[i].split(":");
						k = $.trim(h[0]);
						if (k) o[k] = parse(h[1]);
					}
				}
				return o;
			}
			else if (!isNaN(x))
				return Number(x);
			else if (x==="true")
				return true;
			else if (x==="false")
				return false;
			else
				return x;
		}
	},

	trim: function(str) {
		str = str.replace(/^\s+/, '');
		for (var i = str.length - 1; i >= 0; i--) {
			if (/\S/.test(str.charAt(i))) {
				str = str.substring(0, i + 1);
				break;
			}
		}
		return str;
	},

	parseStr: function(s) {	/*	v1.2;		http://design-noir.de/webdev/JS/parseStr/	*/
		var rv = {}, decode = window.decodeURIComponent || window.unescape;
		(null == s? location.search : s).replace(/^[?#]/, "").replace(/([^=&]*?)((?:\[\])?)(?:=([^&]*))?(?=&|$)/g,
				function($, n, arr, v) {
					if ('' == n) {
						return;
					}
					n = decode(n);
					v = decode(v);
					if (arr) {
						if (typeof rv[n] == "object")
							rv[n].push(v);
						else
							rv[n] = [v];
					} else {
						rv[n] = v;
					}
				});
		return rv;
	},

	openWindow: function(link, param) {
		if (this.empty(param)) {
			var param = new Object();
		}

		if (this.empty(param.name)) {param.name = 'window_' + Math.round(Math.random()*100000);}

		var arg = '';
		if (this.empty(param.fullscreen)) {
			if (!this.empty(param.width))	{arg += "width=" + param.width + ",";	}
			if (!this.empty(param.height))	{arg += "height=" + param.height + ",";}
		}
		if (!this.empty(param.fullscreen)) {
			arg += "width=" + screen.availWidth + ",";
			arg += "height=" + screen.availHeight + ",";
		}

		if (this.empty(param.center)) {
			param.x = 0;
			param.y = 0;
			arg += "screenx=" + param.x + ",";
			arg += "screeny=" + param.y + ",";
			arg += "left=" + param.x + ",";
			arg += "top=" + param.y + ",";
		}

		if (!this.empty(param.center) && this.empty(param.fullscreen)) {
			param.x = Math.floor((screen.availWidth-(param.width || screen.width))/2)-(screen.width-screen.availWidth);
			param.y = Math.floor((screen.availHeight-(param.height || screen.height))/2)-(screen.height-screen.availHeight);
			arg += "screenx=" + param.x + ",";
			arg += "screeny=" + param.y + ",";
			arg += "left=" + param.x + ",";
			arg += "top=" + param.y + ",";
		}

		if (!this.empty(param.scrollbars)) {arg += "scrollbars=1,"; }
		if (!this.empty(param.menubar)) {arg += "menubar=1,"; }
		if (!this.empty(param.location)) { arg += "location=1,"; }
		if (!this.empty(param.resizable))	{ arg += "resizable=1,"; }

		var win = window.open(link, param.name, arg);
		try {win.focus();}catch(e) {};

		return false;
	},

	/* --- */

	isEmail: function(email) {
		var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		return emailReg.test(email);
	}


}

$Djenx = new _$Djenx();

var s = $('script[src*=djenx.js]', 'head');
if (s.length) {
	var path = s.attr('src').replace(/djenx\.js(\?.*)?$/, '');
	var includes = s.attr('src').match(/\?.*load=([a-z,.\/]*)/);
	if (includes && includes[1]) {
		includes = includes[1].split(',');
		for (var i=-1, iCount=includes.length; ++i<iCount;) {
			$Djenx.require(path + includes[i] + '.js');
		}
	}
}



var cssFix = function() {
	var u = navigator.userAgent.toLowerCase(),
	is = function(is) {
		return (-1 != u.indexOf(is));
	};

	$('html').addClass([(!(/opera|webtv/i.test(u)) && /msie (\d)/.test(u)) ? ('ie ie' + RegExp.$1)
		: is('firefox/2') ? 'gecko ff2'	
		: is('firefox/3') ? 'gecko ff3'
		: is('firefox/4') ? 'gecko ff4'
		: is('minefield/3') ? 'gecko ff3'
		: is('minefield/4') ? 'gecko ff4'
		: is('gecko/') ? 'gecko'
		: is('chrome/') ? 'chrome'
		: is('opera/9') ? 'opera opera9'	: /opera (\d)/.test(u) ? 'opera opera' + RegExp.$1
		: is('konqueror') ? 'konqueror'
		: is('applewebkit/') ? 'webkit safari'
		: is('mozilla/') ? 'gecko'
		: '',
		(is('x11') || is('linux'))? ' linux'
			: is('mac')? ' mac'
			: is('win') ? ' win'
	: ''].join(''));
}();



