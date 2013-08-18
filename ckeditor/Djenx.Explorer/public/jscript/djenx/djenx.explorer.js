/**
 * Djenx.Explorer (2.2.3b)
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


if ('undefined' == typeof($Djenx.Explorer) || 'Object' != typeof($Djenx.Explorer)) {
	$Djenx.Explorer = {};
}

var uriArgs = $Djenx.parseStr(location.search);
if ('undefined' == typeof(uriArgs.path)) {
	uriArgs.path = location.pathname;
}

$Djenx.Explorer = {
	$cfg : {
		path : {
			pub: uriArgs.path + '/public/',
			skin: uriArgs.path + '/public/skin/' + (uriArgs.skin || 'acdsee3') + '/',
			upload : '',
			connector : uriArgs.path + '/connector/' + (uriArgs.connector || 'php') + '/index.' + (uriArgs.connector || 'php')
		},
		/*skin			: uriArgs.skin || 'acdsee3',*/
		connector	: uriArgs.connector || 'php',
		returnTo		: uriArgs.returnTo || '',

		maxsize	: {
			byte	: 0,
			text	: ''
		},

		thumb : {
			dirname	: '',
			width		: 0,
			height	: 0
		},

		display : {
			list			: false,
			thumb	: true,

			fileName : true,
			fileDate	: true,
			fileSize	: true,
			folder		: true,

			treeSize	: true,
			treeFiles	: true,

			previewBox		: true,
			confirmDelete	: true,
			contextmenu	: true
		},

		cookies : {
			expires	: 30,
			path		: '/'
		},

		isFlash		: true
	},

	css	: {
		boxfolders	: {},
		downloads	: {},
		preview		: {}
	},

	data	: {
		lang	: uriArgs.lang || function() {
					if (navigator.appName == 'Netscape') {
						var language = navigator.language;
					} else {
						var language = navigator.browserLanguage;
					}
					return language.substring(0, 2);
			}() || 'en',

		type	: uriArgs.type || 'file',
		sort	: {
			type		: 'name',
			direction	: 'asc'
		},

		file			: '',
		dirPath	: '',
		rename	: {
			_old	: '',
			_new	: ''
		},
		upload_width	: 0,
		upload_height	: 0,
		upload_crop	: false,
		upload_replace	: false
		/*upload	: {
			width		: 0,
			height	: 0,
			crop		: false,
			replace	: false
		}*/
	},

	temp	: {
		htmlList		: '',
		htmlThumb	: ''
	},

	list : {
		onPage	: 0,
		count		: 0,
		response	: {},
		pages		: {}
	},

	operation : '',
	lang : {},
	allowSymbol : null,

	openPath : function(path) {
		if ('' == path) {
			return false;
		}

		$('#treeFolders li[path="' + path + '"]')
			.parentsUntil('.jstree', '.jstree-closed')
			.andSelf()
			.each(function () {
					$('#treeFolders').jstree('open_node', this);
				});

	/*
		$('#treeFolders').jstree("select_node", $('#treeFolders li[path="' + $(this).attr('path') + '"]'));
	*/
		$('#treeFolders li[path="' + path + '"] > a').click();
		return false;
	},

	fileListing : function(response) {
		$Djenx.Explorer.list.onPage = $('#file_listing').val();
		if ('' == $Djenx.Explorer.list.onPage || 0 == $Djenx.Explorer.list.onPage) {
			$Djenx.Explorer.appendFiles(response);
			$('#listing').fadeOut();

		} else {
			$Djenx.Explorer.list.response = response;
			$Djenx.Explorer.list.count = response.files.length;
			var pages = $Djenx.Explorer.list.count / $Djenx.Explorer.list.onPage;
			pages = Math.ceil(pages * Math.pow(10, 0)) / Math.pow(10, 0);

			$('#page').attr('maxval', pages);
			$('b.pageCount', '#listing').text(pages);
			$('#listing').fadeIn();

			$Djenx.Explorer.listing(1);
		}

		return;
	},

	listing		: function(page) {
		page--;
		var start= $Djenx.Explorer.list.onPage * page;
		var end	= $Djenx.Explorer.list.onPage * (page + 1);

		var index = 0;
		var interval = {};
		for (var i=start; i<end && i<$Djenx.Explorer.list.count; i++) {
			interval[index++] = $Djenx.Explorer.list.response.files[i];
		}

		var temp = clone($Djenx.Explorer.list.response);
		temp.files = interval;

		$Djenx.Explorer.appendFiles(temp);
		return true;
	},

	getFiles	: function(dirPath) {
		if ('' ==dirPath) {
			return false;
		}

		$Djenx.Explorer.data.file = '';
		$Djenx.Explorer.data.dirPath = decodeURIComponent(dirPath);

		$.post($Djenx.Explorer.$cfg.path.connector + '?action=getFiles', $Djenx.Explorer.data,
			function(response) {
				$('div.allElement', _$bottom).html($Djenx.Explorer.lang.common.totalItems + ': ' + response.count_files + (0 == response.size? '' : ' (' + response.size + ')') + (response.count_folders? ', ' + response.count_folders + ' ' + $Djenx.Explorer.lang.common.folders5 : ''));
				$Djenx.Explorer.fileListing(response);
			},
			'json'
		);

		return true;
	},

	appendFiles : function(response) {

		var files = response.files;
		var folders = response.folders;
		var thumb = '', list = '', w_h, attr;


		list += '<table class="list">';
		list += '<thead><tr>';
			list += '<td class="format"></td>'
			list += '<td class="checkbox"><input type="checkbox" id="selectAll" value="" /></td>'
			list += '<td class="name">' + $Djenx.Explorer.lang.common.name + '</td>';
			list += '<td class="date">' + $Djenx.Explorer.lang.common.date + '</td>';
			list += '<td class="size">' + $Djenx.Explorer.lang.common.size + '</td>';
		list += '</tr></thead><tbody>';

		if ('' != response.parent_folder) {
			thumb += '<div class="folder"><div class="_type _dir"></div>';
			thumb += '<div class="image"><a href="#" path="' + response.parent_folder + '"><img src="public/image/blank.gif" alt="" /></a></div>';
			thumb += '<div class="checkbox"><input type="checkbox" name="folder[]" value="" style="visibility:hidden" /></div>';
			thumb += '<div class="name">[ .. ]</div>';
			thumb += '<div class="date">&nbsp;</div>';
			thumb += '<div class="size">&nbsp;</div>';
			thumb += '</div>';
		}

		for (var i in folders) {
			thumb += '<div class="folder"><div class="_type _dir"></div>';
			thumb += '<div class="image"><a href="#" path="' + folders[i].path + '"><img src="public/image/blank.gif" alt="" /></a></div>';
			thumb += '<div class="checkbox"><input type="checkbox" name="folder[]" value="' + folders[i].name + '" /></div>';
			thumb += '<div class="name">' + folders[i].name + '</div>';
			thumb += '<div class="date">' + folders[i].date + '</div>';
			thumb += '<div class="size">' + folders[i].size + '</div>';
			thumb += '</div>';

			list += '<tr data-type="folder">';
				list += '<td class="_type _dir"></td>'
				list += '<td class="checkbox"><input type="checkbox" name="folder[]" value="' + folders[i].name + '" /></td>'
				list += '<td class="name"><a href="#" path="' + folders[i].path + '">' + folders[i].name + '</a></td>';
				list += '<td class="date">' + folders[i].date + '</td>';
				list += '<td class="size">' + folders[i].size + '</td>';
			list += '</tr>';

		}


		for (var i in files) {
			attr = 'file="' + ($Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + files[i].name) + '" thumb="' + (files[i].width? ($Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.$cfg.thumb.dirname + '/' + $Djenx.Explorer.data.dirPath + '/' + files[i].name) : '') + '"';
			thumb += '<div data-type="file" data-format="' + files[i].format + '" class="thumb" ' + attr + ' ext="' + files[i].ext + '"><div class="_type _' + files[i].ext + '"></div><div class="image ext_' + files[i].ext + '" ' + (files[i].width? 'style="background-image: url(\'' + files[i].thumb + '\')"' : '') + '>';

			if (files[i].width) {
				w_h = '(' + files[i].width + ' x ' + files[i].height + ') ';
			} else {
				w_h = '';
			}
			thumb += '<img src="public/image/blank.gif" />';

			thumb += '</div><div class="checkbox"><input type="checkbox" name="file[]" value="' + files[i].name + '" /></div>';
			thumb += '<div class="name" >' + files[i].name + '</div>';
			thumb += '<div class="date" >' + files[i].date + '</div>';
			thumb += '<div class="size" >' + w_h + files[i].size + '</div>';
			thumb += '</div>';

			list += '<tr ' + attr + '  data-format="' + files[i].format + '" ext="' + files[i].ext + '">';
				list += '<td class="format _' + files[i].ext + '"></td>'
				list += '<td class="checkbox"><input type="checkbox" name="file[]" value="' + files[i].name + '" /></td>'
				list += '<td class="name"><a href="">' + files[i].name + '</a></td>';
				list += '<td class="date">' + files[i].date + '</td>';
				list += '<td class="size">' + w_h + files[i].size + '</td>';
			list += '</tr>';

		}

		list += '</tbody></table>';

		$Djenx.Explorer.temp.htmlList = list;
		$Djenx.Explorer.temp.htmlThumb = thumb;

		_$fileThumb.html($Djenx.Explorer.$cfg.display.thumb? thumb : list);


		if ($Djenx.Explorer.$cfg.display.contextmenu) {

			$('>div.folder', _$fileThumb).contextMenu({
					menu: 'contextMenuToolBox'
				},
				function(action, el, pos) {
					$Djenx.Explorer.data.file = $('div.name', el).text();

					switch(action) {
						case 'archivePack':
							$('input', '#archivePack').val($Djenx.Explorer.data.file);
							$('#archivePack').dialog('open');
							break;

						default:
							break;
					}
				return;
			});

			$('>div.thumb', _$fileThumb).contextMenu({
					menu: 'contextMenuFile',
					onShow: function(el, pos) {
						$('li.archivePack', '#contextMenuFile').removeClass('disabled');
						$('li.chooseThumb', '#contextMenuFile').addClass('disabled');
						$('li.archiveUnpack', '#contextMenuFile').addClass('disabled');
						switch(el.data('format')) {
							case 'image':
								$('li.archiveUnpack', '#contextMenuFile').addClass('disabled');
								$('li.chooseThumb', '#contextMenuFile').removeClass('disabled');
								break;
							case 'archive':
								$('li.archivePack', '#contextMenuFile').addClass('disabled');
								$('li.archiveUnpack', '#contextMenuFile').removeClass('disabled');
								break;
							case 'audio':
								break;
							case 'video':
								break;
							case 'unknown':
							default:
								break;
						}

						return;
					}
				},
				function(action, el, pos) {
					$Djenx.Explorer.data.file = $('div.name', el).text();

					switch(action) {
						case 'fileChoose':
							$Djenx.Explorer.action.chooseFile();
							break;
						case 'fileChooseThumb':
							$Djenx.Explorer.action.chooseThumb();
							break;

						case 'fileOpenWindow':
							$Djenx.Explorer.action.fileNewWindow();
							break;
						case 'fileDownload':
							$Djenx.Explorer.action.download();
							break;
						case 'fileRename':
							$Djenx.Explorer.operation = 'renameFolder';
							$Djenx.Explorer.action.renameFile();
							break;

						case 'fileRemoveSelected':
							$Djenx.Explorer.action.remove(false);
							break;
						case 'fileRemoveChecked':
							$Djenx.Explorer.action.remove(true);
							break;

						case 'archivePack':
							var pos, name = $Djenx.Explorer.data.file;
							if (-1 !== (pos = name.lastIndexOf('.'))) {
								name = name.substr(0, pos);
							}
							$('input', '#archivePack').val(name);
							$('#archivePack').dialog('open');
							break;

						case 'archiveUnpack':
							$Djenx.Explorer.action.unpack();
							break;

						default:
							break;
					}
				return;
			});
		}

		return;
	},

	returnData : function(input, data) {

		switch($Djenx.Explorer.$cfg.returnTo) {
			case 'ckeditor':
				var funcNum = uriArgs.CKEditorFuncNum;
				window.top.opener['CKEDITOR'].tools.callFunction(funcNum, input, data);
				window.top.close();
				window.top.opener.focus();
				break;

			case 'tinymce':
				var win = window.dialogArguments || opener || parent || top;
				tinyMCE = win.tinyMCE;
				var params = tinyMCE.activeEditor.windowManager.params;
				params.window.document.getElementById(params.input).value = input;
				try {
					params.window.ImageDialog.showPreviewImage(input);
				} catch(e) {}
				window.close();
				break;

			default:
				try {
					if ('$' == $Djenx.Explorer.$cfg.returnTo.substr(0, 1)) {
						var objInput = $Djenx.Explorer.$cfg.returnTo.substr(1);
						window.top.opener.document.getElementById(objInput).value = input;
					} else if ('' != $Djenx.Explorer.$cfg.returnTo) {
						window.top.opener[$Djenx.Explorer.$cfg.returnTo](input);
					} else {
/*
		TODO	view	path in window
*/
						return;
					}

				} catch(e) {
					alert('Function is not available or does not exist: ' + $Djenx.Explorer.$cfg.returnTo + "\r" + e.message);
				}
		}

		window.top.close();
		window.top.opener.focus();
		return;
	},

	action: {
		chooseFile : function() {
			$Djenx.Explorer.returnData($Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file);
			return;
		},

		chooseThumb : function() {
			$Djenx.Explorer.returnData($Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.$cfg.thumb.dirname + '/' + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file);
			return;
		},

		fileNewWindow : function() {
			$Djenx.openWindow($Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file, {
					'name': 'winExternal'
			});
		},

		download : function() {
			if ('' == $Djenx.Explorer.data.file) {
				return;
			}
			location.replace($Djenx.Explorer.$cfg.path.connector + '?action=download&file=' + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file);
		},

		removeFolder : function(confirm) {

			if ('undefined' == typeof(confirm) && $Djenx.Explorer.$cfg.display.confirmDelete) {
				var text = '<span class="ui-icon ui-icon-alert"></span>' + $Djenx.Explorer.lang.dialog.confirmRemove + ':<br /><br /><b>' + $Djenx.Explorer.data.dirPath + '</b>';
				$('#confirmation')
					.html(text)
					.dialog('open');
			} else {
				confirm = true;
			}

			if (confirm) {
				var data = $Djenx.Explorer.data;
				data.file = '';
				$.post($Djenx.Explorer.$cfg.path.connector + '?action=delete', data,
						function(response) {
							var i = 0;
							if ( response.unlink[i].success ) {
								/*var node = $('#treeFolders li[path="' + encodeURIComponent($Djenx.Explorer.data.dirPath) + '"]');
								$('#treeFolders').jstree('remove', node);*/
								$('li[path="' + encodeURIComponent($Djenx.Explorer.data.dirPath) + '"]', '#treeFolders').remove();
							} else {
								$.pnotify({
									pnotify_title: $Djenx.Explorer.lang.errors.occured,
									pnotify_text: '<b>' + response.unlink[i].folder + '</b><br />' + $Djenx.Explorer.lang.errors.removePossibleReason,
									pnotify_type: 'error',
									pnotify_opacity: .8
								});
							}
						},
						'json'
				);

				$Djenx.Explorer.operation = false;
			}

			return true;
		},

		remove : function(isAll, confirm) {
			var files = [];
			if ( isAll ) {
				$('input:checked', _$fileThumb).each(function() {
					files.push(this.value);
				});
			} else if ( '' != $Djenx.Explorer.data.file ) {
				files.push($Djenx.Explorer.data.file);
			}

			if ( !files.length ) {
				return false;
			}

			if ('undefined' == typeof(confirm) && $Djenx.Explorer.$cfg.display.confirmDelete) {
				$Djenx.Explorer.temp.removeIsAll = isAll;
				var text = '<span class="ui-icon ui-icon-alert"></span>' + $Djenx.Explorer.lang.dialog.confirmRemove + ':<br /><br /><b>' + files.join(', ') + '</b>';
				$('#confirmation')
					.html(text)
					.dialog('open');
			} else {
				confirm = true;
			}

			if (confirm) {
				var data = $Djenx.Explorer.data;
				data.file = files.join(':');
				$.post($Djenx.Explorer.$cfg.path.connector + '?action=delete', data,
						function(response) {
							for (var i=-1, iCount=response.unlink.length; ++i<iCount;) {
								if (response.unlink[i].folder) {
									var div = encodeURIComponent($Djenx.Explorer.data.dirPath + '/' + response.unlink[i].folder);
									if ( response.unlink[i].success ) {
										$('a[path="'+div+'"]', _$fileThumb).parent().parent().fadeOut('slow', function() {$(this).remove();});
										$('#treeFolders').jstree('remove', 'li[path="' + div + '"]');
									} else {
										$('a[path="'+div+'"]', _$fileThumb).parent().parent().addClass('unlinkError');
										$.pnotify({
											pnotify_title: $Djenx.Explorer.lang.errors.occured,
											pnotify_text: '<b>' + response.unlink[i].folder + '</b><br />' + $Djenx.Explorer.lang.errors.removePossibleReason,
											pnotify_type: 'error',
											pnotify_opacity: .8
										});
									}
								} else {
									var div = $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + response.unlink[i].file;
									if (response.unlink[i].success) {
										$('div[file="' + div + '"]', _$fileThumb).fadeOut('slow', function() {$(this).remove();});
									} else {
										$('div[file="' + div + '"]', _$fileThumb).addClass('unlinkError');
										$.pnotify({
											pnotify_title: $Djenx.Explorer.lang.errors.occured,
											pnotify_text: '<b>' + response.unlink[i].file + '</b><br />' + $Djenx.Explorer.lang.errors.removePossibleReason,
											pnotify_type: 'error',
											pnotify_opacity: .8
										});
									}
								}
							}
						},
						'json'
				);

				$Djenx.Explorer.operation = false;
			}

			return;
		},

		createFolder : function() {
			$Djenx.Explorer.data.rename._new = '';
			var op = $('#operation');
			$('span.info', op).html($Djenx.Explorer.lang.dialog.enterCreateFolderName + '<br /><br />' + $Djenx.Explorer.data.dirPath + '<br />');
			op.dialog({title: $Djenx.Explorer.lang.title.folderCreate});
			op.dialog('open');
		},

		renameFolder : function() {
			$Djenx.Explorer.data.rename._new = '';
			$Djenx.Explorer.data.rename._old = $Djenx.Explorer.data.dirPath;

			var op = $('#operation');
			$('span.info', op).html($Djenx.Explorer.lang.dialog.enterFolderName + '<br /><br />' + $Djenx.Explorer.data.dirPath + '<br />');
			op.dialog({title: $Djenx.Explorer.lang.title.folderRename});
			op.dialog('open');
		},

		renameFile: function() {
			$Djenx.Explorer.data.rename._new = '';
			$Djenx.Explorer.data.rename._old = $Djenx.Explorer.data.file;

			var op = $('#operation');
			$('span.info', op).html($Djenx.Explorer.lang.dialog.enterFileName + '<br /><br />' + $Djenx.Explorer.data.file + '<br />');
			op.dialog({title: $Djenx.Explorer.lang.title.fileRename});
			op.dialog('open');
		},

		setPermission : function() {

		},

		pack : function() {

		},

		unpack : function() {
			$.post($Djenx.Explorer.$cfg.path.connector + '?action=unPack', $Djenx.Explorer.data,
					function(response) {
						if (true == response.success) {
							$Djenx.Explorer.getFiles($Djenx.Explorer.data.dirPath);
						}
						$.pnotify({pnotify_text: (response.success? $Djenx.Explorer.lang.operation.successComplete : $Djenx.Explorer.lang.errors.occured)});
					},
				'json');
		},

		edit : function() {

		}
	},

	returnSource : function() {

	},

	winResize : function() {

		var bodyHeight = $('#lft').height();
		$Djenx.Explorer.css.boxfolders.height	= parseInt(bodyHeight / 100 * 60);
		$Djenx.Explorer.css.downloads.height	= parseInt(bodyHeight / 100 * 10);
		$Djenx.Explorer.css.preview.height		= parseInt(bodyHeight / 100 * 30) - 70;

		$('#boxfolders').height($Djenx.Explorer.css.boxfolders.height);
		$('#uploads').height($Djenx.Explorer.css.downloads.height);
		$('#preview').height($Djenx.Explorer.css.preview.height);
		$('#statistic').height($Djenx.Explorer.css.downloads.height - 22);

		$('#previewImage').css('max-height', $Djenx.Explorer.css.preview.height + 'px');

		var uWidth = $('#uploads').width();
		if (uWidth >= 280) {
			$('div.head div.l span').show();
		} else {
			$('div.head div.l span').hide();
		}

		return;
	},

	postLoad : function() {

		$Djenx.Explorer.winResize();

		var _$preview = $('#preview');
		var a = $('div.head div.r a', _$preview);

		$('img', a).attr('src', ($Djenx.Explorer.$cfg.display.previewBox? $Djenx.Explorer.$cfg.path.skin + 'arrow-down.gif' : $Djenx.Explorer.$cfg.path.skin + 'arrow-up.gif') )
		a.click(function() {

					if ($Djenx.Explorer.$cfg.display.previewBox) {

						$('#previewImage').fadeOut();
						var setHeight = $Djenx.Explorer.css.boxfolders.height + $Djenx.Explorer.css.preview.height;

						$('#boxfolders').animate({
									height: setHeight
								}, {
									duration: 1000,
									complete: function() {
										$('img', a).attr('src', $Djenx.Explorer.$cfg.path.skin + 'arrow-up.gif');
									}
						});

					} else {
						$('#boxfolders').animate({
									height: $Djenx.Explorer.css.boxfolders.height
								}, {
									duration: 1000,
									complete: function() {
										$('#previewImage').fadeIn();
										$('img', a).attr('src', $Djenx.Explorer.$cfg.path.skin + 'arrow-down.gif');
									}
						});

					}

					$Djenx.Explorer.$cfg.display.previewBox = !$Djenx.Explorer.$cfg.display.previewBox;
					return false;
		});

		if ($Djenx.Explorer.$cfg.isFlash) {
			$('#uploadify').uploadifySettings('sizeLimit', $Djenx.Explorer.$cfg.maxsize.byte);
			$('#uploadify').uploadifySettings('fileExt', $Djenx.Explorer.temp.misc.file_extension);
			$('#uploadify').uploadifySettings('fileDesc', $Djenx.Explorer.temp.misc.file_extension);
			$('#uploadify').uploadifySettings('scriptData', $Djenx.Explorer.data);
		}

		return;
	},

	updateCookie : function() {
		for (var i in $Djenx.Explorer.$cfg.display) {
			$.cookie('$Djenx[explorer][$cfg][display][' + i + ']', $Djenx.Explorer.$cfg.display[i], $Djenx.Explorer.$cfg.cookies);
		}

		$.cookie('$Djenx[explorer][$cfg][setting][replace_file]', $('#replace_file').is(':checked'), $Djenx.Explorer.$cfg.cookies);
		$.cookie('$Djenx[explorer][$cfg][setting][image_crop]', $('#image_crop').is(':checked'), $Djenx.Explorer.$cfg.cookies);

		$Djenx.Explorer.data.sort.type = $('input[name="sort_method"]:checked', '#tabGeneral').val();
		$Djenx.Explorer.data.sort.direction = $('input[name="sort_type"]:checked', '#tabGeneral').val();
		$.cookie('$Djenx[explorer][$cfg][setting][sort_method]', $Djenx.Explorer.data.sort.type, $Djenx.Explorer.$cfg.cookies);
		$.cookie('$Djenx[explorer][$cfg][setting][sort_type]', $Djenx.Explorer.data.sort.direction, $Djenx.Explorer.$cfg.cookies);

		$.cookie('$Djenx[explorer][$cfg][setting][file_listing]', $('#file_listing').val(), $Djenx.Explorer.$cfg.cookies);
		return true;
	},

	setSettings : function() {
		for (var i in $Djenx.Explorer.$cfg.display) {
			var value = $.cookie('$Djenx[explorer][$cfg][display][' + i + ']');
			if (null !== value) {
				$Djenx.Explorer.$cfg.display[i] = ('true' == value? true : false);
			}
		}

		if ($Djenx.Explorer.$cfg.display.thumb) {
			$('#view_thumb').attr('checked', 'checked');
			$('#view_list').removeAttr('checked');
		} else {
			$('#view_thumb').removeAttr('checked');
			$('#view_list').attr('checked', 'checked');
		}

		var _set = ['fileName', 'fileDate', 'fileSize', 'folder', 'contextmenu', 'treeSize', 'treeFiles', 'confirmDelete'];
		for (var i in _set) {
			$Djenx.Explorer.$cfg.display[_set[i]]?	$('#display_' + _set[i]).attr('checked', 'checked') : $('#display_' + _set[i]).removeAttr('checked');
		}

		$.cookie('$Djenx[explorer][$cfg][setting][replace_file]')? $('#replace_file').attr('checked', 'checked') : $('#replace_file').removeAttr('checked');
		$.cookie('$Djenx[explorer][$cfg][setting][image_crop]')? $('#image_crop').attr('checked', 'checked') : $('#image_crop').removeAttr('checked');

		$('#file_listing').val($.cookie('$Djenx[explorer][$cfg][setting][file_listing]'));

		var sort = $.cookie('$Djenx[explorer][$cfg][setting][sort_method]');
		if ('date' == sort) {
			$('#sort_date').attr('checked', 'checked');
		} else if('size' == sort) {
			$('#sort_size').attr('checked', 'checked');
		}

		'desc' == $.cookie('$Djenx[explorer][$cfg][setting][sort_type]')? $('#sort_type_desc').attr('checked', 'checked') : null;


		$Djenx.Explorer.cssSettings();
		return;
	},

	cssSettings: function() {
		var style = '';
		$Djenx.Explorer.$cfg.display.fileName?	style += '#fileThumb .name {display:block;} #fileThumb table td.name {display: table-cell;}'	: style += '#fileThumb .name {display:none;}';
		$Djenx.Explorer.$cfg.display.fileDate?	style += '#fileThumb .date {display:block;} #fileThumb table td.date {display: table-cell;}'	: style += '#fileThumb .date {display:none;}';
		$Djenx.Explorer.$cfg.display.fileSize?	style += '#fileThumb .size {display:block;} #fileThumb table td.size {display: table-cell;}'		: style += '#fileThumb .size {display:none;}';

		$Djenx.Explorer.$cfg.display.folder?		style += '#fileThumb .folder {display:block;}'		: style += '#fileThumb .folder {display:none;}';
		$Djenx.Explorer.$cfg.display.treeSize?	style += '#treeFolders b.size {display:inline;}'	: style += '#treeFolders b.size {display:none;}';
		$Djenx.Explorer.$cfg.display.treeFiles?	style += '#treeFolders i.files {display:inline;}'	: style += '#treeFolders i.files {display:none;}';

		/*if ($.browser.msie) {*/
			if ($('#cssSettings').length) {
				$('#cssSettings').text(style);
			} else {
				$('head').append('<style id="cssSettings" type="text/css">' + style + '</style>');
			}
		/*} else {
			$('#cssSettings').text(style);
		}*/

		return;
	}

};

/* === === === */

/*
if ($.browser.msie && -1 != $.browser.version.indexOf('7.')) {
}
*/
var _$bottom, _$fileThumb;
$Djenx.setURL();
window.onresize = $Djenx.Explorer.winResize;
$(document).ready(function() {
	if ($.url.post) for (var i in $.url.post) {$Djenx.Explorer.data[i] = $.url.post[i];}
	var playerVersion = swfobject.getFlashPlayerVersion();
	if (0 === playerVersion.major) {
		$Djenx.Explorer.$cfg.isFlash = false;
	}

	$('head')
		.prepend('<script type="text/javascript" src="' + $Djenx.Explorer.$cfg.path.pub + 'lang/' + $Djenx.Explorer.data.lang + '.js"></script>')
		.append('<link type="text/css" rel="stylesheet" href="' + $Djenx.Explorer.$cfg.path.skin + 'style.css" />')
		.append('<script type="text/javascript" src="' + $Djenx.Explorer.$cfg.path.skin + 'extend.js"></script>');
		/*.append('<style id="cssSettings" type="text/css"></style>');*/

	_$bottom	= $('#bottom');
	_$fileThumb	= $('#fileThumb');

	$(document)
		.ajaxStart(function() {
				$('div.ajaxLoading', _$bottom).css('background-image', "url('" + $Djenx.Explorer.$cfg.path.skin + "ajax-loading.gif')");
			})
		.ajaxStop(function() {
				$('div.ajaxLoading', _$bottom).css('background-image', 'none');
			});

	$Djenx.Explorer.setSettings();

	if ($Djenx.Explorer.$cfg.isFlash) {
		$('#uploadify').uploadify({
			'uploader'	: $Djenx.Explorer.$cfg.path.pub + 'jscript/jquery/uploadify/uploadify.swf',
			'script'		: $Djenx.Explorer.$cfg.path.connector + '?action=upload',
			'buttonImg'	: $Djenx.Explorer.$cfg.path.skin + 'browse.png',
			'buttonText': '',
			'cancelImg'	: $Djenx.Explorer.$cfg.path.pub + 'jscript/jquery/uploadify/cancel.png',
			'method'	: 'post',
			//'checkScript' : true,
			'folder'	: 'upload',
			'queueID': 'uploadContent',
			'auto'		: true,
			'multi'		: true,
			'fileExt'	: '',
			'fileDesc'	: '',
			'width'	: 80,
			'height'	: 16,
			'scriptData'		: {},
			'onSWFReady'	: function() {
			},
			'onOpen'			: function(event, queue, file) {
				$('#uploadify').uploadifySettings('scriptData', $Djenx.Explorer.data);
			},
			'onError'			: function(event, queue, file, error) {
			},
			'onComplete'	: function(event, queue, file, response, data) {
					if ('true' != response) {
						var o = $('#uploadify' + queue);
						o.addClass('uploadifyError');
						$('span.percentage, .uploadifyProgress').hide();
						$('span.fileName', o).html($Djenx.Explorer.lang.errors.uploadTech + ': <b>' + file.name + '</b>');
						o.click(function() {o.remove();});

						if ('max_filesize' == response) {
							$('span.fileName', o).html($Djenx.Explorer.lang.errors.uploadFileMaxSize + ': <b>' + file.name + '</b>');
						} else if ('choose_dir' == response) {
							$('span.fileName', o).html($Djenx.Explorer.lang.errors.uploadEmpty);
						} else {
							$('span.fileName', o).html($Djenx.Explorer.lang.errors.uploadTech + ': <b>' + file.name + '</b>');
						}
						return false;
					}
					return true;
				},
			'onAllComplete'	: function(event, data) {
					$Djenx.Explorer.getFiles($Djenx.Explorer.data.dirPath);
					return true;
				}
		});
	} else {
		$('head').prepend('<script type="text/javascript" src="' + $Djenx.Explorer.$cfg.path.pub + 'jscript/jquery/jquery.form.js"></script>');
		var s = '<div id="uploadSimple"><form id="fileSend" method="post" action=""><input type="file" name="Filedata" id="uploadify" /></form></div>';
		$('#uploadify').replaceWith(s);
		$('#uploadify').change(function() {
			$('#fileSend').ajaxSubmit({
				url:	$Djenx.Explorer.$cfg.path.connector + '?action=upload&noJsonHeader',
				type:	'post',
				data: $Djenx.Explorer.data,
				/*dataType: 'json',*/
				beforeSubmit: function() {
				},
				success : function showResponse(response, status) {
					if (-1 == response.indexOf('true')) {
						if (-1 != response.indexOf('max_filesize')) {
							$.pnotify({pnotify_text: $Djenx.Explorer.lang.errors.uploadFileMaxSize, pnotify_type: 'error'});
						} else if (-1 != response.indexOf('choose_dir')) {
							$.pnotify({pnotify_text: $Djenx.Explorer.lang.errors.uploadEmpty, pnotify_type: 'error'});
						} else {
							$.pnotify({pnotify_text: $Djenx.Explorer.lang.errors.uploadTech, pnotify_type: 'error'});
						}
						return false;
					}
					$Djenx.Explorer.getFiles($Djenx.Explorer.data.dirPath);
				}
			});
		});
	}

	$.post($Djenx.Explorer.$cfg.path.connector + '?action=getConfig', $Djenx.Explorer.data,
			function(reply) {
				if (reply.accessDenied) {
					alert( reply.accessDenied );
					return;
				}

				for (var i in reply) {
					if ('misc' != i) {
						$Djenx.Explorer.$cfg[i] = reply[i];
					}
				}

				$Djenx.Explorer.allowSymbol = (reply.misc.allowed_symbol);
				$Djenx.Explorer.temp.misc = reply.misc;
				$Djenx.Explorer.$cfg.path.upload = reply.misc.path.upload;

				if ( 'on' != reply.misc.file_uploads ) {
					$('#uploads').hide();
					$('#boxfolders').css('height', '70%');
				} else {
					$('div.head div.r a', '#uploads')
						.click(function() {
							$('#setting').dialog('open');
							$('#tabs').tabs('select', 1);
							return false;
						})
					.attr('title', $Djenx.Explorer.lang.options.uploadFile);

					$('div.head div.l', '#uploads').append(' (' + $Djenx.Explorer.lang.noMore + ' ' + reply.maxsize.text + ') ');
				}

				var option = '';
				for (var i in reply.misc.archive) {
					reply.misc.archive[i]? option += '<option value="' + i + '">' + i + '</option>' : null;
				}
				$('select[name="archiveFormat"]', '#archivePack').html(option);


				/* --- */
				$('head').append('\
						<style type="text/css">\
							#fileThumb .thumb .image, #fileThumb .folder .image, #fileThumb .folder .image a {\
								width: ' + $Djenx.Explorer.$cfg.thumb.width + 'px;\
								height:' + $Djenx.Explorer.$cfg.thumb.height + 'px;\
								max-width: ' + $Djenx.Explorer.$cfg.thumb.width + 'px;\
								max-height:' + $Djenx.Explorer.$cfg.thumb.height + 'px;\
							}\
							#fileThumb .name, #fileThumb .size, #fileThumb .date {\
								width: ' + $Djenx.Explorer.$cfg.thumb.width + 'px;\
								max-width: ' + $Djenx.Explorer.$cfg.thumb.width + 'px;\
							}\
							#header #fileOperation {\
								width: ' + (($Djenx.Explorer.$cfg.thumb.width * 2) + 47) + 'px;\
							}\
							#fileThumb .thumb, #fileThumb .folder {\
								width: ' + (parseInt($Djenx.Explorer.$cfg.thumb.width)) + 'px\
							}\
							#fileSearch {\
								width: ' + (parseInt($Djenx.Explorer.$cfg.thumb.width) + 12) + 'px\
							}\
						</style>');


				setTimeout('$Djenx.Explorer.postLoad()', 1500);
				return;
			},
			'json'
	);

	$('#treeFolders')
		.bind('loaded.jstree', function (event, data) {
				return;
			})
		.bind('select_node.jstree', function(event, data) {
				$Djenx.Explorer.data.dirPath = decodeURIComponent( $(data.rslt.obj).attr('path') );
				$Djenx.Explorer.getFiles($Djenx.Explorer.data.dirPath);
				try {
					$Djenx.Explorer.$cfg.isFlash? $("#uploadify").uploadifySettings('scriptData', $Djenx.Explorer.data) : null;
				} catch(e) {};

				var url = '';
				var uri = $Djenx.Explorer.data.dirPath.split('/');
				var paths = '<span>' + $Djenx.Explorer.$cfg.path.upload + '</span>';
				for (var i=-1, iCount=uri.length; ++i<iCount;) {
					url += (0 == i? '' : '%2F');
					url += uri[i];
					paths += '<a href="" path="' + url + '">' + uri[i] + '</a><span>/</span>';
				}
				$('#address').html(paths);

				return;
			})
		.jstree({
			'core': {
				'html_titles': true,
				'initially_open' : ['root']
			},
			'json_data' : {
				'ajax' : {
					'url' : $Djenx.Explorer.$cfg.path.connector + '?action=getTreeDirectory',
					'type'	: 'post',
					'data' : function (n) {
						$Djenx.Explorer.data.dirPath = n.attr? n.attr('path') : '';
						return $Djenx.Explorer.data;
					}
				}
			},
			"types" : {
				"types" : {
					"valid_children" : [ "root" ],
					"root" : {
						"icon" : {
							"image" : $Djenx.Explorer.$cfg.path.pub + 'image/ico/drive.png'
						},
						"valid_children" : [ "default" ],
						"hover_node" : false,
						"select_node" : function () {
							return false;
						}
					}/*,
					"default" : {
						"max_children"	: -1,
						"max_depth"	: -1,
						"valid_children": "all",
						//"open_node"	: true,
						//"close_node"	: true,
						//"create_node"	: true,
						//"delete_node"	: true
					}*/
				}
			}/*,
			"ui" : {
				"select_limit" : 1,
				"select_multiple_modifier" : "alt",
				"selected_parent_close" : "select_parent",
				'disable_selecting_children' : true
			}*/,
			'plugins' : ["themes", "html_data","json_data", "ui","crrm","hotkeys", 'dnd', 'types', 'cookies']
		});

	$('span[lang]').each(function() {
		var o = $(this).attr('lang');
		if (-1 != o.indexOf('.')) {
			var p	= o.split('.');
			var t	= $Djenx.Explorer.lang[p[0]][p[1]];
		} else {
			var t = $Djenx.Explorer.lang[o];
		}

		if ('undefined' != typeof(t)) {
			$(this).text(t);
		}
		return;
	});


	_$fileThumb
		.delegate('div.thumb, div.folder', 'mouseover', function() {
				$(this).addClass('thumbOver');
			})
		.delegate('div.thumb, div.folder', 'mouseout', function() {
				$(this).removeClass('thumbOver');
			})
		.delegate('div.thumb', 'click', function(e) {
				$Djenx.Explorer.data.file = $('div.name', $(this)).text();
				if ($Djenx.Explorer.$cfg.display.previewBox && 'image' == $(this).data('format')) {
					$('#previewImage').attr('src', '' + $Djenx.Explorer.$cfg.path.skin + 'throbber.gif');
				}

				$('div.pressed', _$bottom)
					.removeClass()
					.addClass('pressed _' + $(this).attr('ext'))
					.html('<a href="' + $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file + '" target="winExternal">' + $Djenx.Explorer.data.file + '</a>' );
				$('div.stats', _$bottom).html( $('div.size', $(this)).text() );

				$('div.thumbClick', _$fileThumb).removeClass('thumbClick');
				$(this).addClass('thumbClick');

				if ($Djenx.Explorer.$cfg.display.previewBox && 'image' == $(this).data('format')) {
					$('#previewImage').attr('src', $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file);
				}

			})
		.delegate('div.thumb', 'dblclick', function() {
				$Djenx.Explorer.data.file = $('div.name', $(this)).text();
				$Djenx.Explorer.action.chooseFile();
			})
		.delegate('div.folder a, table.list tbody tr[folder] td.name a', 'click', function() {
				$Djenx.Explorer.openPath($(this).attr('path'));
				return false;
			})
		.delegate('table.list tbody tr', 'mouseover', function() {
				$(this).addClass('mouseOver');
			})
		.delegate('table.list tbody tr', 'mouseout', function() {
				$(this).removeClass('mouseOver');
			})
		.delegate('table.list tbody tr[ext] td.name a', 'click', function() {
				var tr = $(this).parent().parent();

				$Djenx.Explorer.data.file = $(this).text();
				$('div.pressed', _$bottom)
					.removeClass()
					.addClass('pressed _' + tr.attr('ext'))
					.html('<a href="' + $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file + '" target="winExternal">' + $Djenx.Explorer.data.file + '</a>' );
				$('div.stats', _$bottom).html( $('td.size', tr).text() );

				if ($Djenx.Explorer.$cfg.display.previewBox && 'image' == tr.data('format')) {
					$('#previewImage')
						.attr('src', '' + $Djenx.Explorer.$cfg.path.skin + 'throbber.gif')
						.attr('src', $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/' + $Djenx.Explorer.data.file);
				}

				return false;
			})
		.delegate('div.checkbox input, td.checkbox input', 'click', function() {
				var value = $(this).attr('value');
				if ( $(this).is(':checked') ) {
					$(this).parent().parent().addClass('checked');

				} else {
					$(this).parent().parent().removeClass('checked');

				}
			});

	$('#listing').delegate('a', 'click', function() {
		var p = $('#page');
		var page = p.val();
		var max = p.attr('maxval');

		'NaN' == typeof(page)? page = 1 : null;

		if ('pageBack' == $(this).attr('class')) {
			--page > 0? $Djenx.Explorer.listing(page) : null;
		} else {
			++page <= max? $Djenx.Explorer.listing(page) : null;
		}
		p.val(page>0 && page<=max? page : 1);
		return false;
	});
	$('#page').keyup(function() {
		var val = $(this).val();
		var max = $(this).attr('maxval');
		if (0 < val && val >= max) {
			$Djenx.Explorer.listing(val);
		} else {
			$(this).val(1);
			$Djenx.Explorer.listing(1);
		}
		return;
	});

	$('#address')
		.delegate('a', 'click', function() {
				$Djenx.Explorer.openPath($(this).attr('path'));
				return false;
		});

	$('#author').dialog({
			autoOpen : false,
			show: "blind",
			hide: "explode",
			resizable : false,
			width: 400,
			height: 350
	});
	$('.copyright a', '#header').click(function() {
		$('#author').dialog('open');
		return false;
	});
	$('a', '#author').attr('target', '_blank');

	/* == Settings == */

	$('#setting').dialog({
		autoOpen : false,
		width: 450,
		height: 500,
		'title' : $Djenx.Explorer.lang.settings
	});

	$('#tabs').tabs({
		cookie: $Djenx.Explorer.$cfg.cookies
	});

	$('div.settings a', '#header').click(function() {
		$('#setting').dialog('open');
		return false;
	});


	$('#view_thumb').click(function() {
		_$fileThumb.html($Djenx.Explorer.temp.htmlThumb);

		$Djenx.Explorer.$cfg.display.list		= false;
		$Djenx.Explorer.$cfg.display.thumb	= true;
	});
	$('#view_list').click(function() {
		_$fileThumb.html($Djenx.Explorer.temp.htmlList);

		$Djenx.Explorer.$cfg.display.list		= true;
		$Djenx.Explorer.$cfg.display.thumb	= false;
	});

	$('#display_fileName, #display_fileDate, #display_fileSize, #display_folder, #display_contextmenu, #display_treeSize, #display_treeFiles, #display_confirmDelete').click(function() {
		$Djenx.Explorer.$cfg.display[$(this).attr('id').substr(8)] = $(this).is(':checked');
	});

	$('#view_thumb, #view_list, #display_fileName, #display_fileDate, #display_fileSize, #display_folder, #display_contextmenu, #display_treeSize, #display_treeFiles, #display_confirmDelete, #replace_file, #image_crop, #sort_name, #sort_date, #sort_size, #sort_type_asc, #sort_type_desc').bind('click', function() {
		$Djenx.Explorer.updateCookie();
		$Djenx.Explorer.cssSettings();
	});

	$('#display_contextmenu').click(function() {
		$(this).is(':checked')? null : $('div.thumb', _$fileThumb).disableContextMenu();
	});

	$('#replace_file').bind('click', function() {
		$Djenx.Explorer.data.upload_replace = $(this).is(':checked');
	});
	$('#image_crop').bind('click', function() {
		$Djenx.Explorer.data.upload_crop = $(this).is(':checked');
	});

	$('#resize_width, #resize_height, #file_listing, #page')
		.keypress(function(e) {
				var key = (typeof e.charCode == 'undefined' ? e.keyCode : e.charCode);
				if (e.ctrlKey || e.altKey || key < 32)
					return true;

				key = String.fromCharCode(key);
				return /^[0-9]+/i.test(key);
			})
		.blur(function() {
			switch($(this).attr('id')) {
				case 'resize_width':
					$Djenx.Explorer.data.upload_width = $(this).val();
					break;
				case 'resize_height':
					$Djenx.Explorer.data.upload_height = $(this).val();
					break;
				case 'file_listing':
					$Djenx.Explorer.updateCookie();

				default:
					return;
					break;
			}
			$Djenx.Explorer.$cfg.isFlash? $('#uploadify').uploadifySettings('scriptData', $Djenx.Explorer.data) : null;
			return;
		});

	/* === */

	var buttons = {};
	buttons[$Djenx.Explorer.lang.common.remove] = function() {
		if ('removeFolder' == $Djenx.Explorer.operation) {
			$Djenx.Explorer.action.removeFolder(true);
		} else {
			$Djenx.Explorer.temp.removeIsAll? $Djenx.Explorer.action.remove(true, true) : $Djenx.Explorer.action.remove(false, true);
		}
		$('#confirmation').dialog('close');
	};
	buttons[$Djenx.Explorer.lang.cancel] = function() {
		$('#confirmation').dialog('close');
	};

	$('#confirmation').dialog({
		autoOpen: false,
		resizable: false,
		height: 200,
		modal: true,
		title: $Djenx.Explorer.lang.confirmation,
		buttons: buttons
	});

	$('#folderOperation a').click(function() {
		var action = $(this).attr('class');

		switch(action) {
			case 'create':
				$Djenx.Explorer.operation = 'createFolder';
				$Djenx.Explorer.action.createFolder();
				break;
			case 'rename':
				$Djenx.Explorer.operation = 'renameFolder';
				$Djenx.Explorer.action.renameFolder();
				break;
			case 'delete':
				$Djenx.Explorer.operation = 'removeFolder';
				$Djenx.Explorer.action.removeFolder();
				break;

			default:
				break;
		}

		return false;
	});

	$('#folderOperation a.create').attr('title', $Djenx.Explorer.lang.title.folderCreate);
	$('#folderOperation a.rename').attr('title', $Djenx.Explorer.lang.title.folderRename);
	$('#folderOperation a.delete').attr('title', $Djenx.Explorer.lang.title.folderRemove);

	$('#fileOperation a').click(function() {
		var action = $(this).attr('class');

		switch(action) {
			case 'searchFile':
				if ($('#fileSearch').is(':visible')) {
					$('#fileSearch').fadeOut();
					return false;
				}

				var pos = _$fileThumb.position();
				$('#fileSearch')
					.css({
							left: pos.left,
							top: pos.top
						})
					.fadeIn();
				$('input', '#fileSearch').focus();
				break;
			case 'chooseFile':
				$Djenx.Explorer.action.chooseFile();
				break;
			case 'chooseThumb':
				$Djenx.Explorer.action.chooseThumb();
				break;
			case 'download':
				$Djenx.Explorer.action.download();
				break;
			case 'fileNewWindow':
				$Djenx.Explorer.action.fileNewWindow();
				break;
			case 'delete':
				$Djenx.Explorer.action.remove(false);
				break;
			case 'deleteChecked':
				$Djenx.Explorer.action.remove(true);
				break;
			case 'rename':
				$Djenx.Explorer.operation = 'renameFolder';
				$Djenx.Explorer.action.renameFile();
				break;

			default:
				break;
		}

		return false;
	});

	$('#fileOperation a.searchFile').attr('title', $Djenx.Explorer.lang.title.fileSearch);
	$('#fileOperation a.chooseFile').attr('title', $Djenx.Explorer.lang.title.fileChoose);
	$('#fileOperation a.chooseThumb').attr('title', $Djenx.Explorer.lang.title.fileChooseThumb);
	$('#fileOperation a.fileNewWindow').attr('title', $Djenx.Explorer.lang.title.fileOpenWindow);
	$('#fileOperation a.download').attr('title', $Djenx.Explorer.lang.title.fileDownload);
	$('#fileOperation a.rename').attr('title', $Djenx.Explorer.lang.title.fileRename);
	$('#fileOperation a.delete').attr('title', $Djenx.Explorer.lang.title.fileRemoveSelected);
	$('#fileOperation a.deleteChecked').attr('title', $Djenx.Explorer.lang.title.fileRemoveChecked);
	$('#fileOperation a.toolBox').attr('title', $Djenx.Explorer.lang.title.additionally);

	$('#dialog').dialog({
		autoOpen: false,
		resizable: true,
		width: 525,
		height: 320,
		title: $Djenx.Explorer.lang.information
	});

	$('#operation').dialog({
		autoOpen: false,
		resizable: true,
		height: 200,
		modal: true,
		title: '',
		buttons: {
			OK : function() {
					$('#operationInput').attr('readonly', 'readonly');
					$Djenx.Explorer.data.rename._new = $('#operationInput').val();
					if ('' == $Djenx.Explorer.data.rename._new) {
						return false;
					}

					switch($Djenx.Explorer.operation) {
						case 'createFolder':
							$.post($Djenx.Explorer.$cfg.path.connector + '?action=createFolder', $Djenx.Explorer.data,
									function(response) {
										if (response.success) {
											var node = $('#treeFolders li[path="' + encodeURIComponent($Djenx.Explorer.data.dirPath) + '"]');
											$('#treeFolders').jstree('refresh', node);
											/*$('#treeFolders').jstree('create', node, 'first', response.name);*/
											$('#operation').dialog('close');
										}
							});
							break;

						case 'renameFile':
						case 'renameFolder':

							$.post($Djenx.Explorer.$cfg.path.connector + '?action=rename', $Djenx.Explorer.data,
									function(response) {
										if (response.success) {
											if ('file' == response.type) {
												var p = $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.data.dirPath + '/';
												var d = $('div[file="' + p + $Djenx.Explorer.data.file + '"]', _$fileThumb);
												if (d.length) {
													d.attr('thumb', p + $Djenx.Explorer.$cfg.path.upload + $Djenx.Explorer.$cfg.thumb.dirname + '/' + $Djenx.Explorer.data.dirPath + '/' + response.name).attr('file', p + response.name);
													$('div.checkbox input', d).val(response.name);
													$('div.name', d).text(response.name);
												}
												$Djenx.Explorer.data.file = response.name;
											} else {
												var nodeNewName = $Djenx.Explorer.data.dirPath;
												nodeNewName = nodeNewName.substr(0, nodeNewName.lastIndexOf('/'));

												var node = $('#treeFolders li[path="' + encodeURIComponent($Djenx.Explorer.data.dirPath) + '"]');
												node.attr('path', encodeURIComponent(nodeNewName + '/' + response.name));

												var s = ' <b class="size">' + $('a:first b', node).text() + '</b> <i class="files">' + $('a:first i', node).html() + '</i>';
												$.jstree._focused().set_text(node, response.name);
												$('a', node).append(s);
												$('ul', node).remove();
												$('#treeFolders').jstree('refresh', node);

												$Djenx.Explorer.getFiles(nodeNewName + '/' + response.name);
											}

											$('#operation').dialog('close');

										} else {
											$.pnotify({
												pnotify_title: $Djenx.Explorer.lang.errors.occured,
												pnotify_text: '<b>' + $Djenx.Explorer.data.rename._old + '</b><br />' + $Djenx.Explorer.lang.errors.renamePossibleReason,
												pnotify_type: 'error',
												pnotify_opacity: .8
											});
										}
									},
									'json'
								);

							break;

						default:
							break;
					}

					$Djenx.Explorer.operation = false;
					$('#operationInput').removeAttr('readonly').val('');
					return true;
			},
			Cancel : function() {
				$('#operation').dialog('close');
				$('#operationInput').removeAttr('readonly').val('');
			}
		}
	});

	$('#operationInput').keypress(function(e) {
		var key = (typeof e.charCode == 'undefined' ? e.keyCode : e.charCode);
		if (e.ctrlKey || e.altKey || key < 32)
			return true;

		key = String.fromCharCode(key);
		return eval($Djenx.Explorer.allowSymbol + '.test(key)');
		/*return /^[a-z0-9-_~\$()\[\]&=]+/i.test(key);*/
	});

	$('a[class*="clear"]', '#tabAdditionally').click(function() {
		var obj = $(this).attr('class');
		obj = obj.substr(obj.indexOf('clear') + 5).toLowerCase();

		$.post($Djenx.Explorer.$cfg.path.connector + '?action=cleaning&object=' + obj, $Djenx.Explorer.data,
				function(response) {
					$.pnotify({pnotify_text: (response.success? $Djenx.Explorer.lang.operation.successComplete : $Djenx.Explorer.lang.errors.occured)});
				},
			'json');
		return false;
	});




	$.post($Djenx.Explorer.$cfg.path.connector + '?action=statistic', $Djenx.Explorer.data,
			function(response) {
				if (response.length) {
					var ul = '<ul>';
					var s = '';
					for (i=-1, iCount=response.length; ++i<iCount;) {
						s = $Djenx.Explorer.lang.stat[response[i].type];
						s = s.replace('%name%', '<b>' + response[i].name + '</b>');
						s = s.replace('%path%', '<i>' + response[i].path + '</i>');
						switch(response[i].type) {
							case 'RENAME_FILE':
							case 'RENAME_FOLDER':
								s = s.replace('%old%', '<i>' + response[i].old + '</i>');
								break;
						}
						ul += '<li><u>' + response[i].date + '</u> ' + s + '</li>';
					}

					ul += '</ul>';
					$('#statistic').append(ul);

					$('div.statisticLog a', '#uploads').click(function() {
						$('#dialog')
							.html('<ul class="statistic">' + $('ul', '#statistic').html() + '</ul>')
							.dialog('open');
						return false;
					});
				} else {
					$('div.statisticLog', '#uploads').hide();
				}

			},
		'json');



	$('a', '#fileSearch').click(function() {
		$Djenx.Explorer.$cfg.display.folder? $('>div', _$fileThumb).show() : $('>div.thumb', _$fileThumb).show();
		$('#fileSearch').fadeOut();
		return false;
	});
	$('input', '#fileSearch').keyup(function() {
		var val = $(this).val();
		if ('' != val) {
			var o = $('>div[file*="'+val+'"]', _$fileThumb);
			if (o.length) {
				$(o).show();
				$('>div', _$fileThumb).not(o).hide();
			}
		}
	});


	var buttons = {};
	buttons[$Djenx.Explorer.lang.ok] = function() {
		var data = $Djenx.Explorer.data;
		data.archiveName = $('input', '#archivePack').val();
		data.archiveFormat = $('select option:selected', '#archivePack').val();
		$.post($Djenx.Explorer.$cfg.path.connector + '?action=pack', data,
				function(response) {
					$('#archivePack').dialog('close');
					if (true == response.success) {
						$Djenx.Explorer.getFiles($Djenx.Explorer.data.dirPath);
					}
					$.pnotify({pnotify_text: (response.success? $Djenx.Explorer.lang.operation.successComplete : $Djenx.Explorer.lang.errors.occured)});
				},
			'json');
	};
	buttons[$Djenx.Explorer.lang.cancel] = function() {
		$('#archivePack').dialog('close');
	};

	$('#archivePack').dialog({
		autoOpen: false,
		resizable: false,
		modal: true,
		width: 300,
		height: 200,
		title: $Djenx.Explorer.lang.title.archivePack,
		buttons: buttons
	});


	return;
});

function clone(o) {
	if (!o || 'object' !== typeof(o)) {
		return o;
	}

	var c = 'function' === typeof(o.pop)? [] : {};
	var p, v;
	for (p in o) {
		if (o.hasOwnProperty(p)) {
			v = o[p];
			if (v && 'object' === typeof(v)) {
				c[p] = clone(v);
			} else {
				c[p] = v;
			}
		}
	}

	return c;
}