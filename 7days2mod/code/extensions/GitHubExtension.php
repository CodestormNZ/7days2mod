<?php

class GitHubExtension extends Extension
{
  public function curlRequest($url, $header = true, $app = '7days2mod-app')
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT,$app);
    if ($header) {
      curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_HEADER, 1);
    }
    
    $config = SiteConfig::current_site_config(); 
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $config->gituser.":".$config->gitpwd);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
  }
  
  public function getGitHubContent($file_path = '', $org = '7days2mod', $repo = 'Vanilla')
  {
    $repo_url = 'https://api.github.com/repos/'.$org.'/'.$repo.'/contents/';
    $url = $repo_url.$file_path;
    
    $response = $this->curlrequest($url, false);
//    $parts = explode("\r\n\r\nHTTP/", $response);
//    $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
//    list($headers, $body) = explode("\r\n\r\n", $parts, 2);
    $responseData = json_decode($response);
    
    return $responseData;
  }  
}
