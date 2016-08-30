<?php

class OrgController extends Controller
{
  private static $allowed_actions = array(
    'showOrganisation',
    'showRepos',
  );
  
  private static $url_handlers = array(
    'details/$org' => 'showOrganisation',
    'repos/$org' => 'showRepos',
  );

  public function init()
  {
    parent::init();
  }
  
  public function showOrganisation()
  {
    $url = 'orgs/{org}';
    list($headers, $details) = $this->getGitHubOrgRequest($url, $this->getRequest()->param('org'), true);
    
    $details = get_object_vars(json_decode($details));
    $body = var_export($details,true);
    
    
    return $this->customise(new ArrayData(array(
      'Headers' => nl2br(htmlentities($headers)),
      'Organization' => htmlentities($body),
    )))->renderWith("Organization");
  }
  
  public function showRepos()
  {
    $url = 'orgs/{org}/repos';
    list($headers, $details) = $this->getGitHubOrgRequest($url, $this->getRequest()->param('org'));
    
    $details = json_decode($details);
    $body = var_export($details,true);
    
    return $this->customise(new ArrayData(array(
      'Headers' => nl2br(htmlentities($headers)),
      'Organization' => htmlentities($body),
    )))->renderWith("Organization");
  }

}
