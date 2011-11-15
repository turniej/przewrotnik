#!/usr/bin/php
<?php
// Przewrotnik 
// Przewraca słowa w wierszach, poetów w mogiłach
// wymaga obecności w cwd katalogu 'txt-liryka' z wierszami i prawa zapisu do ./
//
// michal.szota@gmail.com
// 30.10.2011

/*
    Copyright (C) 2011 Michał Szota

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// init

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
$pustaki="(:|\n|;| |,|\.|\-|!|…|\?|—)";

// dane, buforujemy listę słów
$pliki=getFilesFromDir('txt-liryka');

@$korpus=unserialize(file_get_contents('wersy.db'));
if (!$korpus) {
	foreach ($pliki as $p) { 
		$k=preg_replace('/---.*/s','',file_get_contents($p));
		$korpus.=samelitery(mb_strtolower($k))."\n";
	}
	$korpus=array_unique(preg_split("/(\n| )/",$korpus));
	@file_put_contents('wersy.db',serialize($korpus));
}

// ale wiersz do obrócenia wczytujemy z odpowiednio małego pliku
while (!isset($zrodlo) || strlen($zrodlo)>3500) {
	$zrodlo=file_get_contents($pliki[array_rand($pliki)]);
}

$zrodloa=explode("\n",$zrodlo);
$autor=$zrodloa[0];
unset($zrodloa);

$zrodlo=preg_replace('/---.*/s','',$zrodlo);
$zrodlo = implode("\n", array_slice(explode("\n", $zrodlo), 4));
$zrodlo=preg_replace("/^\n/",'',$zrodlo);
$zrodloa=preg_split('/'.$pustaki.'/u',$zrodlo);
$zrodloa=array_unique($zrodloa);
foreach ($zrodloa as $k=>$v) {
	$zrodloa[$k]=mb_strtolower(samelitery($v));
}
$zrodloa=array_slice(array_unique($zrodloa),3);


//echo $zrodlo;

// podmieniamy słówka

foreach ($zrodloa as $zk=>$zv) {
	shuffle($korpus);
	foreach ($korpus as $k) {
	if (strlen($zv)<5) break;

//	if (metaphone($zv)==metaphone($k))  {
	if (substr($zv,-4)==substr($k,-4) )  {
		$zrodlo=preg_replace('/'.$pustaki.$zv.$pustaki.'/ui','${1}'.$k.'${2}',$zrodlo,mt_rand(1,2));
			break;
		}
	}
}

// ogarniamy długość i wywracamy do góry nogami

unset($zrodloa);
while (strlen($zrodlo)>900 || !isset($zrodloa)) {
	$zrodloa=explode("\n",$zrodlo);
	if (strlen($zrodlo)>900) unset($zrodloa[array_rand($zrodloa)]);
	foreach ($zrodloa as $zk=>$zv) {
		$zrodloa[$zk]=trim(mb_ucfirst($zrodloa[$zk]));
		if (strlen(trim($zv))<5 && strlen(trim($zv))>0) unset ($zrodloa[$zk]);
	}
//	$zrodloa=array_reverse($zrodloa);
	$zrodlo=implode("\n",$zrodloa);
}



$zrodlo=preg_replace("/\n{3,100}/s","\n",$zrodlo);
$zrodlo=preg_replace("/  /"," ",$zrodlo);
if (preg_match('/[A-Z].*[A-Z]/',$autor)) {
	$zrodlo=trim($autor).' przewraca się w grobie'."\n--------------------------------------------------------------\n".$zrodlo;
}
echo preg_replace('/[,;]\n/',"\n",$zrodlo);
echo "\n";
echo "\n";

// pomocnicze funkcje


function mb_ucfirst($string) {
	$string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	return $string;
}


function samelitery($txt) {
	return trim(preg_replace("/[^a-zéłąćżńóśćęźŁĄĆŻŃÓŚĆĘŹ\ \n']/ui",'',$txt));
}


function getFilesFromDir($dir) { 

  $files = array(); 
  if ($handle = opendir($dir)) { 
    while (false !== ($file = readdir($handle))) { 
        if ($file != "." && $file != "..") { 
            if(is_dir($dir.'/'.$file)) { 
                $dir2 = $dir.'/'.$file; 
                $files[] = getFilesFromDir($dir2); 
            } 
            else { 
              $files[] = $dir.'/'.$file; 
            } 
        } 
    } 
    closedir($handle); 
  } 

  return array_flat($files); 
} 

function array_flat($array) { 

  foreach($array as $a) { 
    if(is_array($a)) { 
      $tmp = array_merge($tmp, array_flat($a)); 
    } 
    else { 
      $tmp[] = $a; 
    } 
  } 

  return $tmp; 
} 

?>