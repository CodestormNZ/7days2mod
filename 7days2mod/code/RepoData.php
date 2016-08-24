<?php
class RepoData extends SiteTree {

  private static $db = array(
  );

  private static $has_one = array(
  );

}
class RepoData_Controller extends ContentController {

  /**
   * An array of actions that can be accessed via a request. Each array element should be an action name, and the
   * permissions or conditions required to allow the user to access it.
   *
   * <code>
   * array (
   *     'action', // anyone can access this action
   *     'action' => true, // same as above
   *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
   *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
   * );
   * </code>
   *
   * @var array
   */
  private static $allowed_actions = array (
    'showconfig',
    'showheaders',
  );
  private static $url_handlers = array(
    'headers' => 'showheaders',
    'Config/$x1/$x2/$x3/$x4/$x5' => 'showconfig',
  );

  public function init() {
    parent::init();
    // You can include any CSS or JS required by your project here.
    // See: http://doc.silverstripe.org/framework/en/reference/requirements
  }

  
  /*
    GitHub Functions
  */
  
  private function curl_request($url, $header = true, $app = '7days2mod-app') {
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
  
  private function get_github_content($file_path = '', $org = '7days2mod', $repo = 'Vanilla') {
    $repo_url = 'https://api.github.com/repos/'.$org.'/'.$repo.'/contents/';
    $url = $repo_url.$file_path;
    
    $response = $this->curl_request($url, false);
//    $parts = explode("\r\n\r\nHTTP/", $response);
//    $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
//    list($headers, $body) = explode("\r\n\r\n", $parts, 2);
    $responseData = json_decode($response);
    
    return $responseData;
  }

  public function showheaders() {
    $url = 'https://api.github.com/orgs/7days2mod';
    
    $response = $this->curl_request($url);

    return $this->customise(new ArrayData(array(
      'Content' => nl2br(htmlentities($response)),
    )))->renderWith("Page");
  }

  
  /*
    Data Functions
  */
  
  public function showconfig() {
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
    
    $responseData = $this->get_github_content($file_path, $org, $repo);
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
