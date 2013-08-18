<?php

function translit($str, $method = 'strstr')
{
	switch ($method) {
		case 'strstr':
		    $converter = array(
				'а' => 'a',	'б' => 'b',	'в' => 'v',
				'г' => 'g',		'д' => 'd',	'е' => 'e',
				'ё' => 'e',	'ж' => 'zh',	'з' => 'z',
				'и' => 'i',		'й' => 'y',	'к' => 'k',
				'л' => 'l',		'м' => 'm',	'н' => 'n',
				'о' => 'o',	'п' => 'p',	'р' => 'r',
				'с' => 's',	'т' => 't',		'у' => 'u',
				'ф' => 'f',	'х' => 'h',	'ц' => 'c',
				'ч' => 'ch',	'ш' => 'sh',	'щ' => 'sch',
				'ь' => "",		'ы' => 'y',	'ъ' => "",
				'э' => 'e',	'ю' => 'yu',	'я' => 'ya',

				'А' => 'A',	'Б' => 'B',	'В' => 'V',
				'Г' => 'G',	'Д' => 'D',	'Е' => 'E',
				'Ё' => 'E',	'Ж' => 'Zh',	'З' => 'Z',
				'И' => 'I',		'Й' => 'Y',	'К' => 'K',
				'Л' => 'L',	'М' => 'M',	'Н' => 'N',
				'О' => 'O',	'П' => 'P',	'Р' => 'R',
				'С' => 'S',	'Т' => 'T',	'У' => 'U',
				'Ф' => 'F',	'Х' => 'H',	'Ц' => 'C',
				'Ч' => 'Ch',	'Ш' => 'Sh',	'Щ' => 'Sch',
				'Ь' => "'",	'Ы' => 'Y',	'Ъ' => "'",
				'Э' => 'E',	'Ю' => 'Yu',	'Я' => 'Ya',
			);

			return iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", strtr($str, $converter));
			break;

		default:
			$trans = array();
			$ch1 = "/\r\n-абвгдеёзийклмнопрстуфхцыэАБВГДЕЁЗИЙКЛМНОПРСТУФХЦЫЭABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$ch2 = "    abvgdeeziyklmnoprstufhcyeabvgdeeziyklmnoprstufhcyeabcdefghijklmnopqrstuvwxyz";
			for ($i=-1, $iCount=$djenx->_strlen($ch1); ++$i<$iCount;) {
				$trans[$djenx->_substr($ch1, $i, 1)] = $djenx->_substr($ch2, $i, 1);
			}
			$trans["Ж"] = "zh";	$trans["ж"] = "zh";
			$trans["Ч"] = "ch";	$trans["ч"] = "ch";
			$trans["Ш"] = "sh";	$trans["ш"] = "sh";
			$trans["Щ"] = "sch";	$trans["щ"] = "sch";
			$trans["Ъ"] = "";		$trans["ъ"] = "";
			$trans["Ь"] = "";		$trans["ь"] = "";
			$trans["Ю"] = "yu";	$trans["ю"] = "yu";
			$trans["Я"] = "ya";	$trans["я"] = "ya";
			$trans["\\\\"] = " ";
			$trans["[^\. a-z0-9]"] = " ";
			$trans["^[ ]+|[ ]+$"] = "";
			$trans["[ ]+"] = "_";
			foreach($trans as $from => $to) {
				$str = $djenx->_ereg_replace($from, $to, $str);
			}
			return $str;
	}
}
