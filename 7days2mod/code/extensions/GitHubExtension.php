<?php

class GitHubExtension extends Extension
{
  const APIURL = 'https://api.github.com/';
  const APPNAME = '7days2mod-app';
  private $githubUser;
  private $githubPwd;
  
  public function __construct()
  {
    parent::__construct();
    $config = SiteConfig::current_site_config();
    $this->githubUser = $config->gituser;
    $this->githubPwd = $config->gitpwd;
  }
  
  public function getGitHubContent($file_path = '', $org = '7days2mod', $repo = 'Vanilla')
  {
    $repo_url = self::APIURL.'repos/'.$org.'/'.$repo.'/contents/';
    $url = $repo_url.$file_path;
    
    $curl = new CurlRequest(self::APPNAME, $this->githubUser, $this->githubPwd);
    $response = $curl->Request($url);
    $responseData = json_decode($response);
    
    return $responseData;
  }
  
  public function getGitHubOrgRequest($url, $org, $showheaders = false)
  {
    if (null === $org) {
      $org = '7days2mod';
    }
    $url = self::APIURL.str_replace("{org}", $org, $url);

    $curl = new CurlRequest(self::APPNAME, $this->githubUser, $this->githubPwd);
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
