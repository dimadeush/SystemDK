<?php
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
 * @link		http://dev.djenx.ru/Djenx.Explorer/djenx-explorer.js
 * @since		File available since Release 2.0.0
*/


class Djenx_Explorer
{
	public		$cfg = array();
	public		$options = array();
	protected	$_sys	= array();

	private	$_data = array();
	private	$_stat = array();

	public function  __construct($options = array())
	{
		$response = array();

		!defined('DIR_SEP')? define('DIR_SEP', '/') : null;
		!defined('DOCUMENT_ROOT')? define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']) : null;
		if (!empty($options)) {
			$this->options = $options;
		}

		$this->_sys['dir'] = isset($this->options['dir'])? rtrim($this->options['dir'], '\\/') . DIR_SEP : realpath(dirname(__FILE__) . '/../..') . DIR_SEP;
		$this->_sys['dir'] = str_replace(array('\\', '/'), DIR_SEP, $this->_sys['dir']);

		$this->_sys['cacheTree']	= $this->_sys['dir'] . 'cache' . DIR_SEP . (empty($this->options['cachePrefix'])? '' : $this->options['cachePrefix']) . 'tree.php';
		$this->_sys['cacheStat']	= $this->_sys['dir'] . 'cache' . DIR_SEP . (empty($this->options['cachePrefix'])? '' : $this->options['cachePrefix']) . 'statistic.php';

		empty($this->options['iniFile'])? $this->options['iniFile'] = $this->_sys['dir'] . '..' . DIR_SEP . 'config.ini' : null;
		$this->_parseCfg();

		if (isset($options['allowFunction'])) {
			if (function_exists($options['allowFunction'])) {
				if (true !== $options['allowFunction']()) {
					$response['accessDenied'] = 'You do not have access';
					exit($this->_json_encode($response));
				}
			} else {
				$response['accessDenied'] = 'A security issue has not found the function of authentication';
				exit($this->_json_encode($response));
			}
		}

		if (!is_writable($cacheDir = dirname($this->_sys['cacheTree'])) && !file_exists($this->_sys['cacheTree']) && !file_exists($this->_sys['cacheStat']) || !is_writable($this->_sys['cacheTree']) && !is_writable($this->_sys['cacheStat'])) {
			if (!$this->mkdirs($cacheDir, 0777) || !is_writable($cacheDir)) {
				if (function_exists('sys_get_temp_dir')) {
					$tmpDir = sys_get_temp_dir();
					$tmpDir = rtrim($tmpDir, '\\/') . DIR_SEP;

					$this->_sys['cacheTree'] = $tmpDir . (empty($this->options['cachePrefix'])? '' : $this->options['cachePrefix']) . 'tree.php';
					$this->_sys['cacheStat'] = $tmpDir . (empty($this->options['cachePrefix'])? '' : $this->options['cachePrefix']) . 'statistic.php';
				}
			}
		}

		//	===

		$this->_data = array(
			'lang'	=> isset($_POST['lang'])? $_POST['lang'] : 'en',
			'type'	=> isset($_POST['type']) && isset($this->cfg['resource'][$_POST['type']]['allow'])? $_POST['type'] : 'file',
			'sort'	=> array(
							'type'			=> isset($_POST['sort']['type'])? $_POST['sort']['type'] : 'name',
							'direction'	=> isset($_POST['sort']['direction'])? $_POST['sort']['direction'] : 'asc'
						),
			'file'		=> isset($_POST['file'])? $_POST['file'] : '',
			'dirPath'	=> (isset($_POST['dirPath'])? urldecode($_POST['dirPath']) : ''),
			'rename'	=> array(
								'_old'		=> isset($_POST['rename']['_old'])? $_POST['rename']['_old'] : '',
								'_new'	=> isset($_POST['rename']['_new'])? $_POST['rename']['_new'] : '',
							),
			'upload'	=> array(
								'width'	=> isset($_POST['upload_width'])?	intval($_POST['upload_width']) : 0,			//isset($_POST['upload']['width'])? intval($_POST['upload']['width']) : 0,
								'height'	=> isset($_POST['upload_height'])?	intval($_POST['upload_height']) : 0,			//isset($_POST['upload']['height'])? intval($_POST['upload']['height']) : 0,
								'crop'		=> isset($_POST['upload_crop'])?	(bool) $_POST['upload_crop'] : false,
								'replace'	=> isset($_POST['upload_replace'])?	(bool) $_POST['upload_replace'] : false	//isset($_POST['upload']['replace'])? (bool) $_POST['upload']['replace'] : false
							)
		);
		!empty($options['_data'])? $this->_data = $this->mergeData($this->_data, $options['_data']) : null;


		$this->mkdirs($this->cfg['common']['path']['absolute'] . DIR_SEP . $this->cfg['common']['path']['relative']);
		$this->_data['dirPath'] = $this->_str_replace('\\', '/', rtrim($this->_data['dirPath'], '\\/')) . DIR_SEP;

		$path = $this->cfg['common']['path']['absolute'] . DIR_SEP . trim($this->cfg['common']['path']['relative'] . DIR_SEP . $this->_data['dirPath'], '\\/') . DIR_SEP;
		$realPath = $this->_str_replace('\\', DIR_SEP, realpath($path)) . DIR_SEP;

		if ($realPath !== $this->cfg['path'] && false === $this->_strpos($realPath, $this->cfg['path'])) {
			exit('fake');
		}


		$action = isset($options['action'])? $options['action'] : (isset($_GET['action'])? $_GET['action'] : '');
		return isset($options['main']) && false === $options['main']? true : $this->main($action);
	}

	public function main($action)
	{

		if (method_exists($this, $action)) {

			if (isset($this->options['main']) && true === $this->options['main']) {
				if (isset($this->options['json']) && false === $this->options['json']) {
					return $this->$action();
				} else {
					return $this->_json_encode($this->$action());
				}

			} else {
				header('Expires: Sun, 10 Oct 2010 10:10:10 GMT');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', false);
				header('Pragma: no-cache');

				if (!isset($_GET['noJsonHeader'])) {
					header('Content-Type: text/' . ($this->cfg['php']['json_header']? 'json' : 'plain') . '; charset=' . $this->cfg['common']['encoding']);
				}

				echo $this->_json_encode($this->$action());
			}

		}

		return true;
	}

	public function getConfig()
	{
		$extensions = '';
		$ext = $this->cfg['resource'][$this->_data['type']]['allow'];
		for ($i=-1, $iCount=count($ext); ++$i<$iCount;) {
			$extensions .= '*.' . $ext[$i] . ';';
		}

		$maxSize = $this->cfg['php']['upload_max_filesize'] > $this->cfg['resource'][$this->_data['type']]['maxsize'] && 0 != $this->cfg['resource'][$this->_data['type']]['maxsize']?
			  $this->cfg['resource'][$this->_data['type']]['maxsize']
			: $this->cfg['php']['upload_max_filesize'];

		$archive = array(
			'zip'	=> extension_loaded('zip'),
			'rar'	=> extension_loaded('rar'),
			'bz2'	=> extension_loaded('bz2'),
			'gz'	=> extension_loaded('zlib'),
			'lzf'	=> function_exists('lzf_compress')
		);

		return array(
			'misc' => array(
							'file_uploads'	=> $this->cfg['php']['file_uploads'],
							'file_extension'	=> $extensions,
							'allowed_symbol'	=> $this->cfg['resource']['allowed_symbol'],
							'path' => array(
											'upload' => '/' . $this->cfg['common']['path']['relative'] . '/'
							),
							'archive'	=> $archive
			),
			'thumb'	=> array(
								'dirname'	=> $this->cfg['thumb']['dirname'],
								'width'	=> $this->cfg['thumb']['maxwidth'],
								'height'	=> $this->cfg['thumb']['maxheight']
			),
			'maxsize' => array(
						'byte'	=> floatval($maxSize),
						'text'	=> $this->sizeFormat($maxSize)
			)
		);
	}

	public function getTreeDirectory($getRoot = true)
	{

		if ($getRoot && $this->cfg['dir']['scan_all'] && file_exists($this->_sys['cacheTree'])) {
			$stat = stat($this->_sys['cacheTree']);
			if (($stat['mtime'] + $this->cfg['dir']['cache_expire']) > time()) {
				require_once $this->_sys['cacheTree'];
				if (!empty($d)) {
					return $d;
				}
			}
		}

		$d = array();
		if ($getRoot && '/' == $this->_data['dirPath']) {
				$info = '';
				if ($this->cfg['dir']['calculating']) {
					$stat	= $this->getSize($this->cfg['path'] . $this->_data['dirPath']);
					$info	= ' <b class="size">[' . $this->sizeFormat($stat['size']) . ']</b> <i class="files">' . $stat['files'] . ' <span lang="files">files</span></i>';
				}

				$d[] = array(
					'data'	=> array(
									'title'	=> trim($this->cfg['common']['path']['relative'], '\\/') . $info,
									'icon'	=> 'folder-a',
									'attr'	=> array(
													'href'	=> '#',
												)
								),
					'attr'	=> array(
									'id'		=> 'root',
									'rel'	=> 'root',
									'path'	=> '',
								),
					'children'	=> $this->getTreeDirectory(false)
				);

				if ($this->cfg['dir']['scan_all'] && $this->cfg['dir']['cache_expire']) {
					file_put_contents($this->_sys['cacheTree'], '<?php' . PHP_EOL . '$d = ' . var_export($d, true) . ';');
				}

				return $d;
		}

		$dir	= $this->_data['dirPath'];
		$list	= scandir($this->cfg['path'] . $dir);

		for ($i=-1, $iCount=count($list); ++$i<$iCount;) {
			if (!in_array($list[$i], $this->cfg['permission']['hide']['folder']) && is_dir($this->cfg['path'] . $dir . $list[$i])) {

				$info = '';
				if ($this->cfg['dir']['calculating']) {
					$stat = $this->getSize($this->cfg['path'] . $dir . $list[$i]);
					$info = ' <b class="size">[' . $this->sizeFormat($stat['size']) . ']</b> <i class="files">' . $stat['files'] . ' <span lang="files">files</span></i>';
				}

				$d[] = array(
					'data'	=> array(
									'title'	=> $list[$i] . $info,
									'icon'	=> 'folder-a',
									'attr'	=> array(
													'href'	=> '#',
													'title'	=> isset($stat['folders'])? $stat['folders'] . ' folders' : ''
												)
								),
					'attr'	=> array(
									'id'		=> '_' . urlencode($list[$i]),
									'path'	=> urlencode(trim($dir . $list[$i], '\\/')),
								),
				);

				$this->_data['dirPath'] = $dir . $list[$i] . DIR_SEP;
				$subList = $this->getTreeDirectory(false);

				if (count($subList)) {
					$index = count($d) - 1;
					$d[$index]['state'] = 'closed';
					if ($this->cfg['dir']['scan_all']) {
						$d[$index]['children'] = $subList;
					}
				}

			}
		}

		return $d;
	}


	public function getFiles()
	{
		$response = array(
			'size'			=> 0,
			'elements'	=> 0,
			'folders'		=> array(),
			'files'			=> array()
		);

		$files = array();
		$folders = array();

		$dir		= $this->_data['dirPath'];
		$full		= $this->cfg['path'] . $dir;
		$thumb	= $this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $dir;

		$list = scandir($full);
		switch ($this->_data['sort']['type']) {
			case 'date':
				break;
			case 'size':
				break;
			case 'name':
			default:
				natcasesort($list);
				$list = array_values($list);
				break;
		}

		for ($i=-1, $iCount=count($list); ++$i<$iCount;) {
			$ext = $this->_substr($list[$i], $this->_strrpos($list[$i], '.') + 1);
			$ext = $this->_strtolower($ext);

			if (!in_array($list[$i], $this->cfg['permission']['hide']['file']) && !in_array($ext, $this->cfg['resource'][$this->_data['type']]['denied']) && in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow']) && is_file($full . $list[$i])) {

				$imgSize = array(0, 0);
				$thumbFile = $list[$i];
				if (in_array($ext, $this->cfg['resource']['image']['allow'])) {
					//$this->cfg['thumb']['method'] == 'imagick' && $this->cfg['thumb']['imagick_jpg']? $thumbFile = $this->_substr($thumbFile, 0, $this->_strrpos($thumbFile, '.')) . '.jpg' : null;
					if ($this->cfg['thumb']['enabled'] && !file_exists($thumb . $thumbFile)) {
						$imgSize = $this->resizeImage($full . $list[$i], $thumb . $list[$i], 0, 0, array('setFormat' => 'jpg'));
					}
					0 === $imgSize[0]? $imgSize = getimagesize($full . $list[$i]) : null;
				}

				$stat = stat($full . $list[$i]);
				$response['size'] += $stat['size'];

				$files[] = array(
								'name'		=> $list[$i],
								'ext'		=> $ext,
								'width'		=> round($imgSize[0]),
								'height'	=> round($imgSize[1]),
								'size'		=> $this->sizeFormat($stat['size']),
								'date'		=> date($this->cfg['common']['date_format'], $stat['mtime']),
								'thumb'	=> '/' . $this->cfg['common']['path']['relative'] . (isset($imgSize['min'])? '' : '/' . $this->cfg['thumb']['dirname']) . '/' . $dir . $thumbFile,
								'sized'		=> $stat['size'],
								'mtime'	=> $stat['mtime'],
								'format'	=> $this->getFormat($ext)
							);

			} elseif (!in_array($list[$i], $this->cfg['permission']['hide']['folder']) && is_dir($full . $list[$i])) {

				$stat = stat($full . $list[$i]);
				$folders[] = array(
									'name'	=> $list[$i],
									'path'		=> urlencode($dir . $list[$i]),
									'date'		=> date($this->cfg['common']['date_format'], $stat['mtime']),
									'size'		=> '&nbsp;',
									'mtime'	=> $stat['mtime'],
								);

			}
		}

		switch ($this->_data['sort']['type']) {
			case 'date':
				usort($files, '_sortDate');
				usort($folders, '_sortDate');
				break;
			case 'size':
				usort($files, '_sortSize');
				break;
			case 'name':
			default:
				break;
		}

		if ('desc' == $this->_data['sort']['direction']) {
			$files = array_reverse($files);
			$folders = array_reverse($folders);
		}

		$response['size'] = $this->sizeFormat($response['size']);
		$response['count_files'] = count($files);
		$response['count_folders'] = count($folders);

		for ($i=-1, $iCount=count($files); ++$i<$iCount;) {
			unset($files[$i]['sized'], $files[$i]['mtime']);
		}
		$response['files']		= $files;
		$response['folders']	= $folders;

		$dir = rtrim($dir, '\\/');
		$response['parent_folder'] = urlencode( $this->_substr($dir, 0, $this->_strrpos($dir, DIR_SEP)) );

		return $response;
	}


	public function upload($keyFile = 'Filedata')
	{
		$response = array(
							'success' => 'false'
						);

		if ('/' == $this->_data['dirPath'] || empty($this->_data['dirPath'])) {
			exit('choose_dir');
		}

		if ('on' == $this->cfg['php']['file_uploads'] && isset($_FILES[$keyFile])) {
			$dir = $this->cfg['path'] . $this->_data['dirPath'];

			$replace	= $this->_data['upload']['replace'];
			$width	= $this->_data['upload']['width'];
			$height	= $this->_data['upload']['height'];
			$crop		= $this->_data['upload']['crop'];

			$files = $_FILES[$keyFile];
			$maxSize = $this->cfg['php']['upload_max_filesize'] > $this->cfg['resource'][$this->_data['type']]['maxsize'] && 0 != $this->cfg['resource'][$this->_data['type']]['maxsize']?
				  $this->cfg['resource'][$this->_data['type']]['maxsize']
				: $this->cfg['php']['upload_max_filesize'];

			if ($maxSize > $files['size']) {
				$ext = $this->_substr($files['name'], $this->_strrpos($files['name'], '.') + 1);
				$ext = $this->_strtolower($ext);
				if (!in_array($ext, $this->cfg['resource'][$this->_data['type']]['denied']) && in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow'])) {

					$replace && file_exists($dir . $files['name'])? unlink($dir . $files['name']) : null;
					$fileName = $this->getFreeFileName($files['name'], $dir);

					if (in_array($ext, $this->cfg['resource']['image']['allow']) && ($width || $height)) {
						$this->resizeImage($files['tmp_name'], $dir . $fileName, $width, $height, array('upload' => true, 'crop' => $crop));
					} else {
						move_uploaded_file($files['tmp_name'], $dir . $fileName);
					}

					if (file_exists($dir . $fileName)) {
						chmod($dir . $fileName, $this->cfg['permission']['chmod']['file']);
						$this->statistic(array(
							'type'	=> 'UPLOAD_FILE',
							'name'	=> $fileName,
							'path'	=> $this->_data['dirPath']
						));

						exit('true');
					} else {
						exit('tech_error');
					}

				}

			} else {
				//$response['error'] = 'filesize';
				exit('max_filesize');
			}

		}

		exit('false');
		return $response;
	}

	public function ckQuickUpload()
	{
		header('Content-Type: text/html; charset=' . $this->cfg['common']['encoding']);

		$quickDir = $this->cfg['path'] . $this->_data['type'] . DIR_SEP . $this->cfg['ckeditor']['quick_dir'] . DIR_SEP;
		$this->mkdirs($quickDir);

		$script = '<script type="text/javascript">/*alert("error");*/</script>';
		if (0 == $_FILES['upload']['error']) {
			$fileName = $this->getFreeFileName($_FILES['upload']['name'], $quickDir);
			if (false !== ($pos = strrpos($fileName, '.'))) {
				$ext = substr($fileName, $pos + 1);
				$ext = $this->_strtolower($ext);
				if (!in_array($ext, $this->cfg['resource'][$this->_data['type']]['denied']) && in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow']) && move_uploaded_file($_FILES['upload']['tmp_name'], $quickDir . $fileName)) {
					$url = '/' . $this->cfg['common']['path']['relative'] . '/'.  $this->_data['type'] . '/' . $this->cfg['ckeditor']['quick_dir'] . '/' . $fileName;
					$funcNum = isset($_GET['CKEditorFuncNum'])? intval($_GET['CKEditorFuncNum']) : 2;
					$script = "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction(" . $funcNum . ", '" . $url . "', '');</script>";
				}
			}
		}

		exit($script);
		return;
	}

	public function delete()
	{
		$response = array(
							'success'	=> false,
							'unlink'	=> array()
		);

		$path		= $this->cfg['path'] . $this->_data['dirPath'];
		$thumb	= $this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $this->_data['dirPath'] . DIR_SEP;

		$files	= explode(':', $this->_data['file']);
		for ($i=-1, $iCount=count($files); ++$i<$iCount;) {
			$ext	= $this->_substr($files[$i], $this->_strrpos($files[$i], '.') + 1);
			$ext	= $this->_strtolower($ext);

			if (is_dir($path . $files[$i])) {
				$this->rmdirs($thumb . $files[$i]);
				$success = $this->rmdirs($path . $files[$i]);
				$response['unlink'][] = array(
												'folder'	=> $files[$i],
												'success'	=> $success
				);
				$success? $this->statistic(array(
									'type'	=> 'DELETE_FOLDER',
									'name'	=> $files[$i],
									'path'	=> $this->_data['dirPath']
				)) : null;

				$this->cleaning('tree');
			} else {
				if (!in_array($ext, $this->cfg['resource'][$this->_data['type']]['denied']) && in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow'])) {
					file_exists($thumb . $files[$i])?	unlink($thumb . $files[$i]) : null;
					if (file_exists($path . $files[$i])) {
						$success = unlink($path . $files[$i]);
						$response['unlink'][] = array(
															'file'		=> $files[$i],
															'success'	=> $success);

						$success? $this->statistic(array(
											'type'	=> 'DELETE_FILE',
											'name'	=> $files[$i],
											'path'	=> $this->_data['dirPath']
						)) : null;
					}

				}
			}

		}

		return $response;
	}

	public function createFolder()
	{
		$response = array(
							'success'		=> false,
							'name'		=> '',
		);

		$_new	= $this->_data['rename']['_new'];
		preg_match($this->cfg['resource']['allowed_symbol'], $_new, $matches);
		$new = $matches[0];

		$path = $this->cfg['path'] . $this->_data['dirPath'];
		if (is_dir($path)) {
			$this->cfg['resource']['foldername_tolowercase']? $new = $this->_strtolower($new) : null;
			if ($response['success'] = $this->mkdirs($path . $new)) {
				$response['name'] = $new;

				chmod($path . $new, $this->cfg['permission']['chmod']['folder']);
				$this->cleaning('tree');
				$this->statistic(array(
								'type'	=> 'CREATE_FOLDER',
								'name'	=> $response['name'],
								'path'	=> $this->_data['dirPath']
						));
			}
		}

		return $response;
	}

	public function rename()
	{
		$response = array(
							'success'		=> false,
							'name'		=> '',
							'type'			=> ''
		);

		$_old		= $this->_data['rename']['_old'];
		$_new	= $this->_data['rename']['_new'];

		preg_match($this->cfg['resource']['allowed_symbol'], $_new, $matches);
		$new = $matches[0];


		$path		= $this->cfg['path'] . $this->_data['dirPath'];

		if (!empty($new)) {
			$posDot = $this->_strrpos($new, '.');
			if (false !== $posDot) {
				$ext = $this->_substr($new, $posDot + 1);
				if (in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow'])) {
					$new = $this->_substr($new, 0, $posDot);
				}
			}

			$_ext = $this->_substr($_old, $this->_strrpos($_old, '.') + 1);
			$_ext = $this->_strtolower($_ext);

			if (is_file($path . $_old) && !empty($_ext)) {
				$this->cfg['resource']['filename_tolowercase']? $new = $this->_strtolower($new) : null;
				if ($response['success'] = rename($path . $_old, $path . $new . '.' . $_ext)) {
					$response['type']		= 'file';
					$response['name']	= $new . '.' . $_ext;

					rename($this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $this->_data['dirPath'] . $_old, $this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $this->_data['dirPath'] . $new . '.' . $_ext);
					chmod($path . $new . '.' . $_ext, $this->cfg['permission']['chmod']['file']);
					chmod($this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $this->_data['dirPath'] . $new . '.' . $_ext, $this->cfg['permission']['chmod']['file']);

					$this->statistic(array(
								'type'	=> 'RENAME_FILE',
								'name'	=> $response['name'],
								'path'	=> $this->_data['dirPath'],
								'old'	=> $_old
					));
				}

			} elseif (is_dir($this->cfg['path'] . $this->_data['dirPath']) && false !== $this->_strpos($this->_data['dirPath'], $_old)) {

				$dirPath = rtrim($this->_data['dirPath'], '/');
				if (false !== ($pos = $this->_strrpos($dirPath, '/'))) {
					$dirPath = $this->_substr($dirPath, 0, $pos);
				}

				$this->cfg['resource']['foldername_tolowercase']? $new = $this->_strtolower($new) : null;
				if ($response['success'] = rename($this->cfg['path'] . $this->_data['dirPath'], $this->cfg['path'] . $dirPath . DIR_SEP . $new)) {
					$response['type']		= 'folder';
					$response['name']	= $new;

					rename($this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $this->_data['dirPath'], $this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $dirPath . DIR_SEP . $new);
					chmod($this->cfg['path'] . $dirPath . DIR_SEP . $new, $this->cfg['permission']['chmod']['folder']);
					chmod($this->cfg['path'] . $this->cfg['thumb']['dirname'] . DIR_SEP . $dirPath . DIR_SEP . $new, $this->cfg['permission']['chmod']['folder']);

					$this->cleaning('tree');
					$this->statistic(array(
								'type'	=> 'RENAME_FOLDER',
								'name'	=> $response['name'],
								'path'	=> $this->_data['dirPath'],
								'old'	=> $_old
					));
				}

			}

		}

		return $response;
	}

	public function cleaning()
	{
		$response = array('success' => false);
		$obj = isset($_GET['object'])? $_GET['object'] : 'tree';

		switch ($obj) {
			case 'tree':
				if (file_exists($this->_sys['cacheTree'])) {
					$response['success'] = unlink($this->_sys['cacheTree']);
					$response['success']? $this->statistic(array('type'	=> 'CACHE_CLEAR_TREE')) : null;
				}
				break;

			case 'thumbnail':
				$response['success'] = $this->rmdirs($this->cfg['common']['path']['absolute'] . DIR_SEP . $this->cfg['common']['path']['relative'] . DIR_SEP . $this->cfg['thumb']['dirname']);
				$response['success']? $this->statistic(array('type'	=> 'CACHE_CLEAR_THUMBNAIL')) : null;
				break;

			case 'statistic':
				if (file_exists($this->_sys['cacheStat'])) {
					$response['success'] = unlink($this->_sys['cacheStat']);
					$response['success']? $this->statistic(array('type'	=> 'CACHE_CLEAR_STATISTIC')) : null;
				}
				break;

			default:
				break;
		}

		return $response;
	}

	public function pack()
	{
		$response = array('success' => false);

		$files	= explode(':', $this->_data['file']);
		$path	= $this->cfg['path'] . $this->_data['dirPath'];

		$archiveName	= trim($_POST['archiveName']);
		$archiveFormat	= $this->_strtolower($_POST['archiveFormat']);

		if (!in_array($archiveFormat, array('zip', 'rar', 'bz2', 'gz', 'lzf', 'tar'))) {
			$archiveFormat = 'Zip';
		}


		$list = $path . $files[0];
		$list = rtrim($list, '\\/');
/*
		$list	= array();

		for ($i=-1, $iCount=count($files); ++$i<$iCount;) {
			$ext	= $this->_substr($files[$i], $this->_strrpos($files[$i], '.') + 1);

			if (is_dir($path . $files[$i])) {
				$list[] = $path . $files[$i];

			} elseif (!in_array($ext, $this->cfg['resource'][$this->_data['type']]['denied']) && in_array($ext, $this->cfg['resource'][$this->_data['type']]['allow'])) {
				$list[] = $path . $files[$i];

			}
		}
*/
		require_once 'Zend' . DIR_SEP . 'Filter.php';
		require_once 'Zend' . DIR_SEP . 'Filter' . DIR_SEP . 'Compress.php';

		$archiveFile = $archiveName . '.' . strtolower($archiveFormat);
		$archiveFile = $this->getFreeFileName($archiveFile, $path);

		$filter = new Zend_Filter_Compress(array(
			'adapter' => ucfirst($archiveFormat),
			'options' => array(
				'archive' => $path . $archiveFile
			)
		));

		$result = $filter->filter($list);
		false === $result? $response['success'] = false : $response['success'] = true;

		return $response;
	}

	public function unPack()
	{
		$response = array('success' => false);

		$files	= explode(':', $this->_data['file']);
		$path	= $this->cfg['path'] . $this->_data['dirPath'];

		//

		require_once 'Zend' . DIR_SEP . 'Filter.php';
		require_once 'Zend' . DIR_SEP . 'Filter' . DIR_SEP . 'Decompress.php';

		$ext = $this->_substr($files[0], $this->_strrpos($files[0], '.') + 1);
		$ext = $this->_strtolower($ext);

		$filter = new Zend_Filter_Decompress(array(
			'adapter' => ucfirst($ext),
			'options' => array(
				'target' => $path,
			)
		));

		$result = $filter->filter($path . $files[0]);
		false === $result? $response['success'] = false : $response['success'] = true;

		return $response;
	}

	public function download($file = null)
	{
		is_null($file) && isset($_GET['file'])? $file = $_GET['file'] : null;

		$file = realpath($this->cfg['path'] . trim($file, '\\/'));
		$file = str_replace('\\', DIR_SEP, $file);

		if (empty($file) || false === $this->_strpos($file, $this->cfg['path'])) {
			header("HTTP/1.0 404 Not Found");
			exit;
		}

		$inf = pathinfo($file);
		$inf['extension'] = $this->_strtolower($inf['extension']);

		if (in_array($inf['extension'], $this->cfg['resource']['file']['denied']) || !in_array($inf['extension'], $this->cfg['resource']['file']['allow'])) {
			header("HTTP/1.0 404 Not Found");
			exit;
		}

		$inf['size'] = filesize($file);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: private', false);
		header('Content-Disposition: attachment; filename=' . urlencode($inf['basename']));
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Description: File Transfer');
		header('Content-Length: ' . $inf['size']);

		if (50000000 > $inf['size']) {
			flush();
			$fp = fopen($file, "r");
			while (!feof($fp)) {
		    	echo fread($fp, 65535);
		    	flush();
			}
			fclose($fp);

		} else {
			readfile($file);
		}

		return;
	}


	public function getFreeFileName($file, $dir)
	{
		$dir = rtrim($dir, DIR_SEP) . DIR_SEP;

		$ext = $this->_substr($file, $this->_strrpos($file, '.') + 1);
		$ext = $this->_strtolower($ext);

		$this->cfg['resource']['filename_tolowercase']?		$file = $this->_strtolower($file) : null;
		!empty($this->cfg['resource']['replace_spaces'])?	$file = $this->_str_replace(' ', $this->cfg['resource']['replace_spaces'], $file) : null;

		$fileName = $this->_substr($file, 0, $this->_strrpos($file, '.'));

		if (file_exists($localePhp = dirname(__FILE__) . DIR_SEP . 'Locale' . DIR_SEP . $this->_data['lang'] . '.php')) {
			require_once $localePhp;
		}

		if (function_exists('translit')) {
			$fileName = translit($fileName, $this->cfg['php']['translit_method']);
		}

		$f = $fileName . '.' . $ext;
		if (file_exists($dir . $f)) {

			if (false !== ($pos = $this->_strrpos($f, '_')) && '(' == $f{$pos + 1}) {
				$symname = $this->_substr($f, 0, $pos);
			} else {
				$symname = $fileName;
			}

			$i = 0;
			$exist = true;
			while ($exist && ++$i < 500) {
				$fileNewName = $symname . '_(' . $i . ').' . $ext;
				if (!file_exists($dir . $fileNewName)) {
					$f = $fileNewName;
					$exist = false;
				}
			}
		}

		return $f;
	}

	public function resizeImage($source, $dest = null, $maxWidth = 0, $maxHeight = 0, $param = array())
	{

		if (!$size = getimagesize($source))
			return null;

		$width = $size[0];
		$height= $size[1];

		null === $dest? $dest = $source : null;
		if (!$this->mkdirs(dirname($dest))) {
			return false;
		}

		if (isset($param['upload'])) {
			$maxWidth	= $maxWidth?	$maxWidth	: 10000;
			$maxHeight	= $maxHeight?	$maxHeight	: 10000;
		} else {
			$maxWidth	= $maxWidth?	$maxWidth	: $this->cfg['thumb']['maxwidth'];
			$maxHeight	= $maxHeight?	$maxHeight	: $this->cfg['thumb']['maxheight'];
		}

		$param['gd']		= ('gd' == $this->cfg['thumb']['method'])? true : false;
		$param['crop']		= isset($param['crop'])? $param['crop'] : $this->cfg['thumb']['crop'];
		$param['quality']	= $this->cfg['thumb']['jpg_quality'];

		$src_x = $src_y = 0;
		if (($size[0] <= $maxWidth) && ($size[1] <= $maxHeight)) {
			$width  = $size[0];
			$height = $size[1];

			if (isset($param['upload'])) {
				return copy($source, $dest);
			} else {
				return array_merge($size, array('min' => true));
			}

		} else {
			$width  = $maxWidth;
			$height = $maxHeight;

			if (!$height) {
				$ratio = $size[0] / $width;
				$height = $maxHeight = round($size[1] / $ratio);
			}

			$ratio_width  = $size[0] / $maxWidth;
			$ratio_height = $size[1] / $maxHeight;

			if ($ratio_width < $ratio_height) {
				if ($param['crop']) {
					$src_y = ($size[1] - $maxHeight * $ratio_width) / 2;
					$size[1] = $maxHeight * $ratio_width;
				} else {
					$width  = $size[0] / $ratio_height;
					$height = $maxHeight;
				}

			} else {
				if ($param['crop']) {
					$src_x = ($size[0] - $maxWidth * $ratio_height) / 2;
					$size[0] = $maxWidth * $ratio_height;
				} else {
					$width  = $maxWidth;
					$height = $size[1] / $ratio_width;
				}
			}
		}

		if (!$param['gd'] && class_exists('Imagick')) {
			$img = new Imagick();
			$img->readImage($source);

			$format = $this->_strtolower($img->getImageFormat());
			if (!in_array($format, array('png', 'jpg', 'jpeg', 'gif')) || isset($param['setFormat']) && 'jpg' == $param['setFormat']) {
				$img->setCompression(Imagick::COMPRESSION_JPEG);
				$img->setCompressionQuality($param['quality']);
				$img->setImageFormat('jpeg');

				$dest = $this->_substr($dest, 0, $this->_strrpos($dest, '.'));
				$dest.= '.jpg';
			}

			//$img->resizeImage($width, $height, Imagick::FILTER_GAUSSIAN, 1);
			$param['crop']?
					$img->cropThumbnailImage($width, $height)
				:	$img->resizeImage($width, $height, Imagick::FILTER_GAUSSIAN, 1);
			$img->writeImage($dest);
			$img->clear();
			$img->destroy();

		} else {

			$rgb		= 0xFFFFFF;
			$format	= $this->_strtolower($this->_substr($size['mime'], $this->_strpos($size['mime'], '/') + 1));
			$icfunc	= 'imagecreatefrom' . $format;
			if (!function_exists($icfunc)) {
				return null;
			}

			$imgFrom = $icfunc($source);

			$img = imagecreatetruecolor($width, $height);
			imagefill($img, 0, 0, $rgb);
			//imagealphablending($img, false);
			//imagesavealpha($img, true);
			//$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
			//imagefill($img, 0, 0, $transparent);
			imagecopyresampled($img, $imgFrom, 0, 0, $src_x, $src_y, $width, $height, $size[0], $size[1]);

			if ('png' == $format) {
				//imagepng($thum_img, $thum_fname, $quality, PNG_ALL_FILTERS);
				imagejpeg($img, $dest, $param['quality']);
			} else {
				imagejpeg($img, $dest, $param['quality']);
			}

			imagedestroy($imgFrom);
			imagedestroy($img);
		}

	    return $size;
	}

	public function statistic($options = array())
	{
		if (!$this->cfg['extension']['statistic_limit']) {
			return array();
		}

		if (empty($this->_stat) && file_exists($this->_sys['cacheStat'])) {
			require_once $this->_sys['cacheStat'];
			$this->_stat = $stat;
			if (!isset($options['type'])) {
				for ($i=-1, $iCount=count($this->_stat); ++$i<$iCount;) {
					$this->_stat[$i]['date'] = date($this->cfg['common']['date_format'], $this->_stat[$i]['date']);
					unset($this->_stat[$i]['remote_addr']);
				}
				return $this->_stat;
			}
		}

		if (count($this->_stat) > $this->cfg['extension']['statistic_limit']) {
			$this->_stat = array_slice($this->_stat, 0, $this->cfg['extension']['statistic_limit']);
		}

		if (!isset($options['type'])) {
			return array();
		}

		$rec = array(
			'type'	=> $options['type'],
			'date'	=> time(),
			'remote_addr'	=> $_SERVER['REMOTE_ADDR']
		);

		isset($options['name'])?	$rec['name']	= $options['name'] : null;
		isset($options['path'])?	$rec['path']	= trim($options['path'], '\\/') : null;

		switch ($options['type']) {
			case 'UPLOAD_FILE':
			case 'DELETE_FILE':
			case 'CREATE_FOLDER':
			case 'CACHE_CLEAR_TREE':
			case 'CACHE_CLEAR_THUMBNAIL':
			case 'CACHE_CLEAR_STATISTIC':
				break;
			case 'DELETE_FOLDER':
				if (false !== ($pos = $this->_strrpos($rec['path'], DIR_SEP))) {
					$rec['name']= $this->_substr($rec['path'], $pos + 1);
					$rec['path'] = $this->_substr($rec['path'], 0, $pos);
					$rec['path'] = trim($rec['path'], '\\/');
				}
				break;
			case 'RENAME_FOLDER':
				if (false !== ($pos = $this->_strrpos($rec['path'], DIR_SEP))) {
					$rec['path'] = $this->_substr($rec['path'], 0, $pos);
					$rec['path'] = trim($rec['path'], '\\/');
				}
				if (false !== ($pos = $this->_strrpos($options['old'], DIR_SEP))) {
					$options['old'] = $this->_substr($options['old'], $pos + 1);
				}
				// break intentionally omitted
			case 'RENAME_FILE':
				$rec['name'] = trim($rec['name'], '\\/');
				$rec['old'] = trim($options['old'], '\\/');
				break;

			default:
				return false;
		}

		array_unshift($this->_stat, $rec);
		file_put_contents($this->_sys['cacheStat'], '<?php' . PHP_EOL . '$stat = ' . var_export($this->_stat, true) . ';');
		return true;
	}

	public function getSize($path)
	{
		$stat = array(
					'size'	=> 0,
					'files'	=> 0,
					'folders' => 0
		);

		$path = rtrim($path, '\\/');
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				$next = $path . '/' . $file;
				if ($file != '.' && $file != '..' && !is_link($next)) {
					if (is_dir($next)) {
						$stat['folders']++;
						$__stat = $this->getSize($next);
						$stat['size']		+= $__stat['size'];
						$stat['files']		+= $__stat['files'];
						$stat['folders']	+= $__stat['folders'];

					} elseif (is_file($next)) {
						$stat['size']		+= filesize($next);
						$stat['files']++;
					}
				}
			}
		}

		closedir($handle);
		return $stat;
	}


	public function sizeFormat($size)
	{
		if ($size < 1024) {
			return $size . ' bytes';
		} else if ($size < (1024*1024)) {
			$size = round($size / 1024, 1);
			return $size . ' KB';
		} else if ($size < (1024*1024*1024)) {
			$size = round($size / (1024*1024), 1);
			return $size . ' MB';
		} else {
			$size = round($size / (1024*1024*1024), 1);
			return $size . ' GB';
		}

	}


	public function mkdirs($dir)
	{
		if (empty($dir)) {
			return false;
		}

		if (is_dir($dir) || '/' === $dir || DIR_SEP === $dir) {
			chmod($dir, $this->cfg['permission']['chmod']['folder']);
			return true;
		}

		if ($this->mkdirs(dirname($dir))) {
			$is = mkdir($dir, $this->cfg['permission']['chmod']['folder']);
			$is? chmod($dir, $this->cfg['permission']['chmod']['folder']) : null;
			return $is;
		}

		return false;
	}

	public function rmdirs($name)
	{
		if (!file_exists($name)) {
			return false;
		}

		if (is_file($name)) {
			return unlink($name);
		}

		$dir = dir($name);
		while (false !== ($entry = $dir->read())) {
			if ('.' == $entry || '..' == $entry) {
				continue;
			}
			$this->rmdirs($name . DIR_SEP . $entry);
		}
		$dir->close();

		return rmdir($name);
	}

	//	---

	private function _parseCfg()
	{

		if (floatval(phpversion()) >= 5.3) {
			$this->cfg = parse_ini_file($this->options['iniFile'], true);
		} else {
			$this->cfg = $this->_parseVarForIni($this->options['iniFile']);
		}

		$this->cfg['php']['upload_max_filesize'] = intval($this->cfg['php']['upload_max_filesize']);
		//	---

		$this->cfg['common']['path']['relative'] = trim($this->cfg['common']['path']['relative'], '/\\');
		$this->cfg['common']['path']['absolute'] = rtrim($this->cfg['common']['path']['absolute'], '/\\');

		$this->cfg['path'] = $this->cfg['common']['path']['absolute'] . DIR_SEP . $this->cfg['common']['path']['relative'] . DIR_SEP;

		$this->cfg['permission']['hide']['file']		= explode(',', $this->cfg['permission']['hide']['file']);
		$this->cfg['permission']['hide']['folder']	= explode(',', $this->cfg['permission']['hide']['folder']);
		$this->cfg['permission']['hide']['folder'][] = $this->cfg['thumb']['dirname'];

		foreach (array('file', 'flash', 'image') as $k => $v) {
			$this->cfg['resource'][$v]['allow']	= explode(',', trim($this->cfg['resource'][$v]['allow']));
			$this->cfg['resource'][$v]['denied']	= explode(',', trim($this->cfg['resource'][$v]['denied']));
		}
		$this->cfg['resource']['file']['allow']		= array_merge($this->cfg['resource']['file']['allow'], $this->cfg['resource']['flash']['allow'], $this->cfg['resource']['image']['allow']);

		foreach ($this->cfg['extension']['format'] as $k => $v) {
			$this->cfg['extension']['format'][$k] = explode(',', trim($v));
		}

		$this->cfg['thumb']['dirname'] = trim($this->cfg['thumb']['dirname'], '\\/');

		$this->cfg['permission']['chmod']['file'] = octdec($this->cfg['permission']['chmod']['file']);
		$this->cfg['permission']['chmod']['folder'] = octdec($this->cfg['permission']['chmod']['folder']);

		$this->cfg['dir']['scan_all'] = (bool) $this->cfg['dir']['scan_all'];
		$this->cfg['resource']['filename_tolowercase'] = (bool) $this->cfg['resource']['filename_tolowercase'];
		$this->cfg['resource']['foldername_tolowercase'] = (bool) $this->cfg['resource']['foldername_tolowercase'];


		ini_set('file_uploads', $this->cfg['php']['file_uploads']);
		ini_set('upload_max_filesize', $this->cfg['php']['upload_max_filesize'] . 'M');
		ini_set('post_max_size', $this->cfg['php']['upload_max_filesize'] . 'M');

		$this->cfg['resource']['file']['maxsize']		*= 1024 * 1024;
		$this->cfg['resource']['flash']['maxsize']	*= 1024 * 1024;
		$this->cfg['resource']['image']['maxsize']	*= 1024 * 1024;

		$this->cfg['php']['upload_max_filesize']		*= 1024 * 1024;

		//	---

		empty($this->cfg['php']['memory_limit'])? 				null : ini_set('memory_limit', $this->cfg['php']['memory_limit']);
		empty($this->cfg['php']['max_execution_time'])?	null : ini_set('max_execution_time', $this->cfg['php']['max_execution_time']);

		empty($this->cfg['php']['setlocale'])?	null : setlocale(LC_ALL, $this->cfg['php']['setlocale']);
		date_default_timezone_set($this->cfg['common']['date_timezone']);

		if (function_exists('mb_internal_encoding')) {
			define('MB_STRING', true);
			mb_internal_encoding($this->cfg['common']['encoding']);
			mb_language($this->cfg['common']['lang']);
		} else {
			define('MB_STRING', false);
		}

		ini_set('include_path',
				  $this->_sys['dir'] . 'library' . PATH_SEPARATOR
				. $this->_sys['dir'] . 'library' . DIR_SEP . 'Zend' . PATH_SEPARATOR
				. $this->_sys['dir'] . 'library' . DIR_SEP . 'PEAR' . PATH_SEPARATOR
				. ini_get('include_path'));

		if ('zend' == $this->cfg['php']['json_method']) {
			define('JSON_PHP5', false);
			require_once 'Zend' . DIR_SEP . 'Json.php';
		} else {
			define('JSON_PHP5', true);
		}

		return true;
	}

	//	---

	public function getFormat($ext)
	{
		foreach ($this->cfg['extension']['format'] as $k => $v) {
			if (in_array($ext, $this->cfg['extension']['format'][$k])) {
				return $k;
			}
		}

		return 'unknown';
	}

	public function mergeData($to, $from)
	{
		foreach ($from as $key => $value) {
			is_array($value)? $to[$key] = $this->mergeData($to[$key], $from[$key]) : $to[$key] = $value;
		}

  		return $to;
	}

	private function _parseVarForIni($iniFile)
	{
		$ini = file_get_contents($iniFile);

		$replace = array(
			'format[image]',
			'format[archive]',
			'format[audio]',
			'format[video]',

			'path[relative]',
			'path[absolute]',

			'chmod[file]',
			'chmod[folder]',

			'hide[file]',
			'hide[folder]',

			'file[allow]',
			'file[denied]',
			'file[maxsize]',
			'flash[allow]',
			'flash[allow]',
			'flash[denied]',
			'flash[maxsize]',
			'image[allow]',
			'image[denied]',
			'image[maxsize]',

			'file[view]',
			'file[create]',
			'file[rename]',
			'file[delete]',

			'folder[view]',
			'folder[create]',
			'folder[rename]',
			'folder[delete]',
		);

		for ($i=-1, $iCount=count($replace); ++$i<$iCount;) {
			$part = explode('[', trim($replace[$i], '[]'));
			$ini = str_replace($replace[$i], $part[0] . '__' . $part[1], $ini);
		}

		$cfg = parse_ini_string($ini, true);

		$cfg['extension']['format']['image']	= $cfg['extension']['format__image'];
		$cfg['extension']['format']['archive']	= $cfg['extension']['format__archive'];
		$cfg['extension']['format']['audio']	= $cfg['extension']['format__audio'];
		$cfg['extension']['format']['video']	= $cfg['extension']['format__video'];

		$cfg['common']['path']['relative']		= $cfg['common']['path__relative'];
		$cfg['common']['path']['absolute']	= str_replace('DOCUMENT_ROOT', DOCUMENT_ROOT, $cfg['common']['path__absolute']);

		$cfg['permission']['chmod']['file']		= $cfg['permission']['chmod__file'];
		$cfg['permission']['chmod']['folder']	= $cfg['permission']['chmod__folder'];
		$cfg['permission']['hide']['file']		= $cfg['permission']['hide__file'];
		$cfg['permission']['hide']['folder']		= $cfg['permission']['hide__folder'];

		$cfg['resource']['file']['allow']		= $cfg['resource']['file__allow'];
		$cfg['resource']['file']['denied']		= $cfg['resource']['file__denied'];
		$cfg['resource']['file']['maxsize']		= $cfg['resource']['file__maxsize'];
		$cfg['resource']['flash']['allow']		= $cfg['resource']['flash__allow'];
		$cfg['resource']['flash']['denied']		= $cfg['resource']['flash__denied'];
		$cfg['resource']['flash']['maxsize']	= $cfg['resource']['flash__maxsize'];
		$cfg['resource']['image']['allow']		= $cfg['resource']['image__allow'];
		$cfg['resource']['image']['denied']	= $cfg['resource']['image__denied'];
		$cfg['resource']['image']['maxsize']	= $cfg['resource']['image__maxsize'];

		$cfg['access']['file']['view']		= $cfg['access']['file__view'];
		$cfg['access']['file']['create']	= $cfg['access']['file__create'];
		$cfg['access']['file']['rename']	= $cfg['access']['file__rename'];
		$cfg['access']['file']['delete']	= $cfg['access']['file__delete'];
		$cfg['access']['folder']['view']		= $cfg['access']['folder__view'];
		$cfg['access']['folder']['create']	= $cfg['access']['folder__create'];
		$cfg['access']['folder']['rename']	= $cfg['access']['folder__rename'];
		$cfg['access']['folder']['delete']	= $cfg['access']['folder__delete'];


		return $cfg;
	}


	//	---

	public function _substr($string, $start, $length = null)
	{
		if (MB_STRING) {
				return (null === $length)? mb_substr($string, $start) : mb_substr($string, $start, $length);
		}
		return (null === $length)? substr($string, $start) : substr($string, $start, $length);
	}

	public function _strpos($string, $needle)
	{
		if (MB_STRING) {
			return mb_strpos($string, $needle);
		}
		return strpos($string, $needle);
	}

	public function _strrpos($string, $needle)
	{
		if (MB_STRING) {
			return mb_strrpos($string, $needle);
		}
		return strrpos($string, $needle);
	}

	public function _strtolower($string)
	{
		if (MB_STRING) {
			return mb_strtolower($string);
		}
		return strtolower($string);
	}

	public function _strlen($string)
	{
		if (MB_STRING) {
			return mb_strlen($string);
		}
		return strlen($string);
	}

	public function _str_replace($search, $replace, $string)
	{
		return str_replace($search, $replace, $string);
	}

	public function _ereg_replace($pattern, $replace, $string)
	{
		if (MB_STRING) {
			mb_ereg_replace($pattern, $replace, $string);
		}
		return ereg_replace($pattern, $replace, $string);
	}


	public function _json_encode($context)
	{
		if (!JSON_PHP5) {
			return Zend_Json::encode($context);
		}
		return json_encode($context);
	}

	public function _json_decode($context)
	{
		if (!JSON_PHP5) {
			return Zend_Json::decode($context);
		}
		return json_decode($context);
	}

}


	function _sortDate($a, $b)
	{
		if ($a['mtime'] == $b['mtime'])		return -1;
		if ($a['mtime'] > $b['mtime'])		return 1;
		return 0;
	}

	function _sortSize($a, $b)
	{
		if ($a['sized'] == $b['sized'])	return -1;
		if ($a['sized'] > $b['sized'])		return 1;
		return 0;
	}


if (!function_exists('parse_ini_string')) {

	function parse_ini_string($str, $ProcessSections=false)
	{
		$lines		= explode("\n", $str);
		$return	= array();
		$inSect	= false;

		foreach ($lines as $line) {
			$line = trim($line);
			if (!$line || $line[0] == "#" || $line[0] == ";")
				continue;

			if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
				$inSect = substr($line, 1, $endIdx-1);
				continue;
			}

			if (!strpos($line, '='))
				continue;

			$tmp = explode("=", $line, 2);
			$tmp[0] = trim($tmp[0]);

			if ($pos = strpos($tmp[1], ';')) {
				$tmp[1] = substr($tmp[1], 0, $pos);
			}
			$tmp[1] = trim($tmp[1], " 	\"\'");

			if ($ProcessSections && $inSect)
				$return[$inSect][$tmp[0]] = $tmp[1];
			else
				$return[$tmp[0]] = $tmp[1];
		}
		return $return;
	}

}
