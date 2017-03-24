<?php
/**
 * Clawer.php
 *
 * @author    IanGely <anguolei@gmail.com>
 * @copyright Copyright (c) 2010-2016
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      https://github.com/IanGely/github-trending
 * @version   0.0.1 first version
 */

require __DIR__ . '/vendor/autoload.php';

use Sunra\PhpSimple\HtmlDomParser;

error_reporting(0);
while (true) {
    job();
    sleep(24 * 60 * 60);
}

function gitAddCommitPush($date, $filename)
{
    $cmdAdd = "git add {$filename}";
    $cmdCommit = "git commit -m '" . $date . "'";
    $cmdPush = 'git push -u origin master';
    exec($cmdAdd);
    exec($cmdCommit);
    exec($cmdPush);
}

function createMarkdown($date, $filename)
{
    $fn = fopen($filename, "w") or die("Unable to open file!");
    $txt = "# " . $date;
    fwrite($fn, $txt);
    return $fn;
}

function grasp($language, $fn)
{
    logUtil("[grasp] {$language} start ...");
    $url = "https://github.com/trending/{$language}";
    $html = HtmlDomParser::file_get_html($url);
    $list = $html->find(".repo-list li");
    fwrite($fn, "\n## {$language} \n");
    foreach ($list as $li) {
        $a = $li->find("div", 0)->find("h3 a", 0);
        $desc = trim($li->find(".py-1 .col-9", 0)->plaintext);
        $stargazers = trim($li->find(".f6 a[aria-label=Stargazers]", 0)->plaintext);
        $title = trim($a->plaintext);
        $url = trim($a->href);
        $url = "https://github.com/" . $url;
        fwrite($fn, "* [{$title}]({$url}): {$desc} \n");
    }
    logUtil("[grasp] {$language} end");
}

function logUtil($msg, $level = "INFO")
{
    $cost_mem = memory_usage();
    $log = "[{$level}][" . date('Y-m-d H:i:s') . "][{$cost_mem}]\t{$msg}\n";
    echo $log;
    unset($log);
}


function memory_usage()
{
    $memory = ( !function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
    return $memory;
}

function job()
{
    logUtil("[job] start");
    $strDate = date("Y-m-d");
    $filename = $strDate . ".md";
    //创建文件
    createMarkdown($strDate, $filename);
    $fn = fopen($filename, "a");
    //抓取内容解析
    grasp("php", $fn);
    grasp("go", $fn);
    grasp("java", $fn);
    grasp("javascript", $fn);
    grasp("html", $fn);
    grasp("python", $fn);
    grasp("swift", $fn);
    grasp("ruby", $fn);

    //提交github
    gitAddCommitPush($strDate, $filename);
    fclose($fn);
    logUtil("[job] end");
}
