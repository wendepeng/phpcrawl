<?php

//妮图网 大图抓取
set_time_limit(0);
include("libs/PHPCrawler.class.php");

define('WEBSITE', '趣图网');
define('IMG_DIR', '/tmp/');

class MyCrawler extends PHPCrawler 
{
  public $filter_url = array(
    'http://www.qutuw.com/tag_xinggan.html',
    'http://www.qutuw.com/tag_mingxing.html',
    'http://www.qutuw.com/tag_aiqing.html',
    'http://www.qutuw.com/tag_dongwu.html',
    'http://www.qutuw.com/tag_dongman.html',
    'http://www.qutuw.com/tag_chuangyi.html',
    'http://www.qutuw.com/tag_sheying.html',
    'http://www.qutuw.com/tag_ertong.html'
    );

  function handleHeaderInfo(PHPCrawlerResponseHeader $header)
  {
    if ($header->content_type != "text/html"){
      return -1;
    }   

    if(in_array($header->source_url, $this->filter_url)){
      return -1;
    }
  }

  function handleDocumentInfo(PHPCrawlerDocumentInfo $DocInfo) 
  {
	  $url = $DocInfo->url;
    $content = $DocInfo->content;
    
	  preg_match('/(Uploads\/Images\/\d+\/\d+\/\d+\/[0-9a-z]+\.jpg)" alt="([^"]+)" title/s',$content,$result);
	  if(empty($result)){
      echo $url." ==== not preg_match the url\n";
      return ;
	  }

    //获取图片并存入本地
    $img_url = 'http://www.qutuw.com/'.$result[1];
    $img_title = $result[2];
    if(!is_dir(IMG_DIR.WEBSITE)){
      @mkdir(IMG_DIR.WEBSITE) or die('不能创建网站目录');
    }
    $img_file = IMG_DIR.WEBSITE.'/'.$img_title.'.jpg';

	  $img_data=file_get_contents($img_url);
    file_put_contents($img_file, $img_data);
    
    //输出提示
    echo "download img : $img_url\n";
    flush();
  } 
}

// Now, create a instance of your class, define the behaviour
// of the crawler (see class-reference for more options and details)
// and start the crawling-process.

$crawler = new MyCrawler();
$crawler->enableResumption();
$crawler->setWorkingDirectory(IMG_DIR.WEBSITE.'/');
$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

if(!is_dir(IMG_DIR.WEBSITE)){
      @mkdir(IMG_DIR.WEBSITE) or die('不能创建网站目录');
}

$guid_file = IMG_DIR.WEBSITE."/guid.tmp";
if (!file_exists($guid_file))
{
  $crawler_id = $crawler->getCrawlerId();
  file_put_contents($guid_file, $crawler_id);
}else
{
  $crawler_id = file_get_contents($guid_file);
  $crawler->resume($crawler_id);
}

$crawler->setURL("http://www.qutuw.com");

$crawler->addContentTypeReceiveRule("#text/html#");

$crawler->addURLFilterRule("#\.(css|js|ico|jpg|jpeg)$# i");

$crawler->enableCookieHandling(true);

$crawler->go();
unlink($guid_file);
// At the end, after the process is finished, we print a short
// report (see method getProcessReport() for more information)
$report = $crawler->getProcessReport();

if (PHP_SAPI == "cli") $lb = "\n";
else $lb = "<br />";
    
echo "Summary:".$lb;
echo "Links followed: ".$report->links_followed.$lb;
echo "Documents received: ".$report->files_received.$lb;
echo "Bytes received: ".$report->bytes_received." bytes".$lb;
echo "Process runtime: ".$report->process_runtime." sec".$lb; 

if ($report->abort_reason == 1){
  echo "Abort reason: ABORTREASON_PASSEDTHROUGH"; 
}else{
  echo "Abort reason: ".$report->abort_reason; 
}

?>
