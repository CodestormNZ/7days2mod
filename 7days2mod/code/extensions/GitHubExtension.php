<?php

class GitHubExtension extends Extension
{
  public function getGitHubContent($file_path = '', $org = '7days2mod', $repo = 'Vanilla')
  {
    $repo_url = 'https://api.github.com/repos/'.$org.'/'.$repo.'/contents/';
    $url = $repo_url.$file_path;
    
    $config = SiteConfig::current_site_config();
    $curl = new CurlRequest($config->gituser, $config->gitpwd);
    $response = $curl->Request($url);
    
//    $parts = explode("\r\n\r\nHTTP/", $response);
//    $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
//    list($headers, $body) = explode("\r\n\r\n", $parts, 2);
    $responseData = json_decode($response);
    
    return $responseData;
  }  
}
