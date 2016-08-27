<?php

class RepoDataController extends Controller
{
  private static $allowed_actions = array(
    'showConfig',
    'showHeaders',
  );
  
  private static $url_handlers = array(
    'headers' => 'showHeaders',
    'Config/$x1/$x2/$x3/$x4/$x5' => 'showConfig',
  );

  public function init()
  {
    parent::init();
  }
  
  public function showHeaders()
  {
    $url = 'https://api.github.com/orgs/7days2mod';

    $config = SiteConfig::current_site_config();
    $curl = new CurlRequest($config->gituser, $config->gitpwd);
    $response = $curl->Request($url, true);

    return $this->customise(new ArrayData(array(
      'Content' => nl2br(htmlentities($response)),
    )))->renderWith("RepoData");
  }
  
  /*
    Data Functions
  */
  
  public function showConfig()
  {
    $url_path = $this->getRequest()->getURL();
    $org = '7days2mod';
    $repo = 'Vanilla';
    if (null !== $this->getRequest()->param('org')) {
      $org = $this->getRequest()->param('org');
      list($_org, $_repo, $file_path) = explode("/", $url_path, 3);
    } else {
      list($_repo, $file_path) = explode("/", $url_path, 2);
    }
    if (null !== $this->getRequest()->param('repo')) {
      $repo = $this->getRequest()->param('repo');
    } else {
      $file_path = $url_path;
    }
    $file_path = str_replace(" ", "%20", $file_path);
    
    $responseData = $this->getGitHubContent($file_path, $org, $repo);
    if (isset($responseData->content)) {
      $content = htmlentities(base64_decode($responseData->content));
    } else {
      $content = "File not found in repo: ".$file_path;
    }
    
    return $this->customise(new ArrayData(array(
      'Content' => $content,
    )))->renderWith("RepoData");
  }

}
