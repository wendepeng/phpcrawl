<?php

// It may take a whils to crawl a site ...
set_time_limit(10000);

// Inculde the phpcrawl-mainclass
include("libs/PHPCrawler.class.php");

// Extend the class and override the handleDocumentInfo()-method 
class MyCrawler extends PHPCrawler 
{
  function handleDocumentInfo($DocInfo) 
  {
	  $url = $DocInfo->url;
    if(!preg_match('/http\:\/\/www\.nianhua123\.cc\/tuku\//i', $url)){
        return ;
    }
	  $content = $DocInfo->content;
	  preg_match_all('/<img src="(http\:\/\/s1kbfd\.df34d3f\.com\/(.*)?\.jpg)">/', $content, $result);
	  if(empty($result[0])){
		  return ;
	  }
	  
	  $img_data=file_get_contents($result[1][0]);
	  file_put_contents('/tmp/'.rand().'.jpg',$img_data);
  
    // Just detect linebreak for output ("\n" in CLI-mode, otherwise "<br>").
    if (PHP_SAPI == "cli") $lb = "\n";
    else $lb = "<br />";

    // Print the URL and the HTTP-status-Code
    echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")".$lb;
    
    // Print the refering URL
    echo "Referer-page: ".$DocInfo->referer_url.$lb;
    
    // Print if the content of the document was be recieved or not
    if ($DocInfo->received == true)
      echo "Content received: ".$DocInfo->bytes_received." bytes".$lb;
    else
      echo "Content not received".$lb; 
    
    // Now you should do something with the content of the actual
    // received page or file ($DocInfo->source), we skip it in this example 
    
    echo $lb;
    
    flush();
  } 
}

// Now, create a instance of your class, define the behaviour
// of the crawler (see class-reference for more options and details)
// and start the crawling-process.

$crawler = new MyCrawler();

// URL to crawl
$crawler->setURL("http://www.nianhua123.cc/tuku/");

// Only receive content of files with content-type "text/html"
#$crawler->addContentTypeReceiveRule("#text/html#");

// Ignore links to pictures, dont even request pictures
//$crawler->addURLFilterRule("#\.(jpg|jpeg|gif|png)$# i");
$crawler->addURLFilterRule("#\.(css|js|ico|jpg|jpeg|png)$# i");

// Store and send cookie-data like a browser doe
$crawler->enableCookieHandling(true);

// Set the traffic-limit to 1 MB (in bytes,
// for testing we dont want to "suck" the whole site)
#$crawler->setTrafficLimit(1000 * 1024);

// Thats enough, now here we go
$crawler->go();

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
?>
