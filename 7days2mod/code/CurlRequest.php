<?php

class CurlRequest
{
  private $user;
  private $pwd;
  private $app;
  
  public function __construct ($app, $user = '', $pwd = '')
  {
    $this->app = $app;
    $this->user = $user;
    $this->pwd = $pwd;
  }
  
  public function Request($url, $showheaders = false)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT,$this->app);
    if ($showheaders) {
      curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_HEADER, 1);
    }
    
    if ($this->user != '') {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $this->user.":".$this->pwd);
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
  }
}
