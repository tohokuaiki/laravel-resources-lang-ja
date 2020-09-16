<?php
/**
 * 現在の言語ファイルと元となる英語ファイルの差分を調べる
 */
$usage = "\nUsage:\n => php ./checker.php --ja /resources/lang/ja/ --en /your_target_laravel/resources/lang/en\n";
$argv = $_SERVER['argv'];
if (count($argv) === 5 &&
    ($ja_key = array_search('--ja', $argv)) !== false &&
    ($en_key = array_search('--en', $argv)) !== false ){
    $ja_dir = rtrim($argv[$ja_key + 1] ?? '', '/');
    $en_dir = rtrim($argv[$en_key + 1] ?? '', '/');
    if (!file_exists($ja_dir)) {
        exit ($ja_dir . "が存在しません。\n");
    }
    if (!file_exists($en_dir)) {
        exit ($en_dir . "が存在しません。\n");
    }
    $en_files = glob($en_dir. '/*.php');
    foreach ($en_files as $en_file){
        $en_base = basename($en_file);
        $ja_file = $ja_dir . '/' . $en_base;
        if (!file_exists($ja_file)){
            printf("%sが存在しません。", $ja_file);
        }
        else {
            $en_lang = include($en_file);
            $ja_lang = include($ja_file);
            $diff_func = function($lang1, $lang2){
                $diff = [];
                foreach ($lang1 as $key =>$lang){
                    if (!isset($lang2[$key])){
                        $diff[] = $key;
                    }
                    else {
                        if (is_array($lang)){
                            foreach (array_keys($lang) as $l_key){
                                if (!isset($lang2[$key][$l_key])){
                                    if (isset($diff[$key])){
                                        $diff[$key] = [];
                                    }
                                    $diff[$key][] = $l_key;
                                }
                            }
                        }
                    }
                }
                return $diff;
            };
            $diff_print = function($diff){
                foreach ($diff as $k=>$v){
                    if (is_array($v)){
                        printf("\t%s => \n", $k);
                        foreach ($v as $_k){
                            printf("\t\t%s\n", $_k);
                        }
                    }
                    else {
                        printf("\t%s\n", $v);
                    }
                }
            };
            $en_diff = $diff_func($en_lang, $ja_lang);
            $ja_diff = $diff_func($ja_lang, $en_lang);
            if ($en_diff){
                printf("%s に新しいキーが含まれています。=>\n", $en_file);
                $diff_print($en_diff);
            }
            if ($ja_diff){
                printf("%s に使われてないキーが含まれています。=>\n", $ja_file);
                $diff_print($ja_diff);
            }
        }
    }
    exit;
}

echo '引数の指定が正しくありません。'. $usage;
