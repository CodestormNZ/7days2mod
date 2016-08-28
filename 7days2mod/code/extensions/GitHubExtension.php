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
    $responseData = json_decode($response);
    
    return $responseData;
  }
  
  public function getGitHubOrgRequest($url, $org, $showheaders = false)
  {
    if (null === $org) {
      $org = '7days2mod';
    }
    $url = str_replace("{org}", $org, $url);

    $config = SiteConfig::current_site_config();
    $curl = new CurlRequest($config->gituser, $config->gitpwd);
    $response = $curl->Request($url, $showheaders);

    if ($showheaders) {
      $parts = explode("\r\n\r\nHTTP/", $response);
      $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
      list($headers, $details) = explode("\r\n\r\n", $parts, 2);
    } else {
      $headers = '';
      $details = $response;
    }
    return array($headers, $details);
  }
}
