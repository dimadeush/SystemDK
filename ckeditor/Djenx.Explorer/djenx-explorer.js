/**
 * Djenx.Explorer
 * http://djenx.ru/djenx-explorer
 *
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@djenx.ru so i can send you a copy immediately.
 *
 * @copyright	(c) 2009-2011 Viktorov Alexander aka Demphest Gorphek (http://www.demphest.ru)
 * @license		http://framework.zend.com/license/new-bsd		PHP License 3.0
 * @version		$Id: djenx-explorer.js 138 2011-01-03 16:05:23Z demphest $
 * @link				http://dev.djenx.ru/Djenx.Explorer/djenx-explorer.js
 * @since			File available since Release 2.0.0
*/

var DjenxExplorer = {
	version : '2.2.3b',
	argumentsUrl : '',

	init : function(params) {
		this.argumentsUrl = '';
		if ('undefined' == typeof(params)) params = {};
		
		if ('undefined' == typeof(params.path)) {
			params.path = function() {
				var s = document.getElementsByTagName('script');
				for (var i=-1; ++i<s.length;) {
					if (s[i].getAttribute('src') && -1 != (src = s[i].getAttribute('src')).indexOf('/djenx-explorer.js')) {
						return src.substring(0, src.lastIndexOf('/'));
					}
				}

				/*alert('Not defined parameter - "path" in DjenxExplorer.init({ path: "/path/to/DjenxExplorer/" });');*/
				return '';
			}();
		}
		if (params.path && '/' == params.path.substring(params.path.length-1)) {
			params.path = params.path.substring(0, params.path.length - 1);
		}

		this.width	= params.width || 1000;
		this.height	= params.height || 700;

		params.connector = params.connector && '' != params.connector || 'php';
		if ('object' == typeof(params.returnTo) && params.returnTo.id && -1 != params.returnTo.id.indexOf('cke_')) {
			params.editor = params.returnTo;
			params.returnTo = 'ckeditor';
		}

		params.width	= '';
		params.height	= '';

		if (params.post) {
			var pair = '';
			if ('object' == typeof(params.post)) {
				for (var i in params.post) {
					pair += i + ':' + params.post[i] + ',';
				}
			} else if ('array' == typeof(params.post)) {
				
			}
			params.post = '{' + pair.substring(0, pair.length-1) + '}';
		}

		for (var i in params) {
			if ('' != params[i] && i != 'editor') {
				this.argumentsUrl += '&' + i + '=' + params[i];
			}
		}

		this.path = params.path;
		this.returnTo = params.returnTo || '';
		
		switch(params.returnTo) {
			case 'ckeditor':
				if ('undefined' != typeof(params.editor)) {
					params.editor.config['filebrowserWindowWidth']	= params.width;
					params.editor.config['filebrowserWindowHeight']	= params.height;
					params.editor.config['filebrowserBrowseUrl']	= params.path + '/index.html?type=file' + this.argumentsUrl;
					params.editor.config['filebrowserUploadUrl']	= params.path + '/connector/' + params.connector + '/index.' + params.connector + '?action=ckQuickUpload&type=file' + this.argumentsUrl;

					var type = ['Flash', 'Image', 'Media'];
					for (var i in type) {
						params.editor.config['filebrowser' + type[i] + 'WindowWidth']	= params.width;
						params.editor.config['filebrowser' + type[i] + 'WindowHeight']	= params.height;
						params.editor.config['filebrowser' + type[i] + 'BrowseUrl']	= params.path + '/index.html?type=' + type[i].toLowerCase() + this.argumentsUrl;
						params.editor.config['filebrowser' + type[i] + 'UploadUrl']	= params.path + '/connector/' + params.connector + '/index.' + params.connector + '?action=ckQuickUpload&type=' + type[i].toLowerCase() + this.argumentsUrl;
					}

				} else {
					alert('You need to pass the object in the variable "editor" or "returnTo"');
				}
				break;

			case 'tinymce':
				break;

			default:
				this.type	= params.type || 'file';
				this.url		= this.path + '/index.html?type=' + this.type.toLowerCase() + this.argumentsUrl;
				this.args	= 'width=' + this.width +',height=' + this.height + 'resizable=1,menubar=0,scrollbars=yes,location=1,left=0,top=0,screenx=,screeny=';
				break;
		}

		return;
	},

	open: function(params, url, type, win) {
		if ('undefined' != typeof(params.returnTo)) {
			returnTo = params.returnTo;
		} else {
			returnTo = this.returnTo;
		}

		switch(returnTo) {
			case 'ckeditor':
				break;

			case 'tinymce':
			    tinyMCE.activeEditor.windowManager.open({
			        url:		this.path + '/index.html?type=' + type.toLowerCase() + this.argumentsUrl,
			        width:	this.width,
			        height:	this.height,
			        inline:		'yes',
			        close_previous: 'no'
			    }, {
			        window: win,
			        input: params
			    });
				break;

			default:
				var win = window.open(this.url + '&returnTo=' + returnTo, 'DjenxExplorer', this.args);
				win.focus();
				break;
		}

		return;
	}

};
