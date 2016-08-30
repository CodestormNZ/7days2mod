<?php

class DataController extends Controller
{
  private static $allowed_actions = array(
    'showGUI',
    'showCodemirror',
    'showNode',
  );
  
  private static $url_handlers = array(
    'Config/$folder/$file/gui' => 'showGUI',
    'Config/$file/gui' => 'showGUI',
    'Config/$folder/$file/code' => 'showCodemirror',
    'Config/$file/code' => 'showCodemirror',
    'Config/$folder/$file/node' => 'showNode',
    'Config/$file/node' => 'showNode',
//    'Config/$folder/!$file' => 'showGUI', //does not match on Config/XUi/filename.xml !!!!!!!!!
//    'Config/$file' => 'showGUI',
  );

  public function init()
  {
    parent::init();
    Requirements::javascript("framework/thirdparty/jquery/jquery.js");
    Requirements::javascript("themes/7days2mod/javascript/script.js");
    Requirements::css("themes/7days2mod/css/reset.css");
    Requirements::css("themes/7days2mod/css/typography.css");
    Requirements::css("themes/7days2mod/css/form.css");
    Requirements::css("themes/7days2mod/css/layout.css");
  }
  
  private function loadConfigParams() {
    $params = new stdClass();
    $params->org = '7days2mod';
    $params->repo = 'Vanilla';
    $params->folder = 'Data/Config/';
    $params->extension = '';
    if (null !== $this->getRequest()->param('org')) {
      $params->org = $this->getRequest()->param('org');
    }
    if (null !== $this->getRequest()->param('repo')) {
      $params->repo = $this->getRequest()->param('repo');
    }
    if (null !== $this->getRequest()->param('folder')) {
      $params->folder = $params->folder.$this->getRequest()->param('folder')."/";
    }
    if (null !== $this->getRequest()->getExtension()) {
      $params->extension = ".".$this->getRequest()->getExtension();
    }
    $params->file = $this->getRequest()->param('file').$params->extension;
    $params->file_path = str_replace(" ", "%20", $params->folder.$params->file);
    
    return $params;
  }
  
  public function showGUI()
  {
    $params = $this->loadConfigParams();
    $responseData = $this->getGitHubContent($params->file_path, $params->org, $params->repo);
    if (isset($responseData->content)) {
      $content = htmlentities(base64_decode($responseData->content));
    } else {
      $content = "File not found in repo: ".$params->file_path;
    }
    
    return $this->customise(new ArrayData(array(
      'Content' => $content,
    )))->renderWith("RepoData_gui");
  }
  
  public function showCodemirror()
  {
    $params = $this->loadConfigParams();
    $responseData = $this->getGitHubContent($params->file_path, $params->org, $params->repo);
    if (isset($responseData->content)) {
      $content = htmlentities(base64_decode($responseData->content));
    } else {
      $content = "File not found in repo: ".$params->file_path;
    }
    
    Requirements::javascript("//codemirror.net/lib/codemirror.js");
    Requirements::css("//codemirror.net/lib/codemirror.css");
    Requirements::javascript("//codemirror.net/mode/xml/xml.js");    
    Requirements::javascript("//codemirror.net/addon/dialog/dialog.js");
    Requirements::css("//codemirror.net/addon/dialog/dialog.css");
    Requirements::javascript("//codemirror.net/addon/search/searchcursor.js");
    Requirements::javascript("//codemirror.net/addon/search/search.js");
    Requirements::javascript("//codemirror.net/addon/fold/foldcode.js");
    Requirements::javascript("//codemirror.net/addon/fold/foldgutter.js");
    Requirements::css("//codemirror.net/addon/fold/foldgutter.css");
    Requirements::javascript("//codemirror.net/addon/fold/brace-fold.js");
    Requirements::javascript("//codemirror.net/addon/fold/xml-fold.js");

    $js = <<<JS
    var myCodeMirror = CodeMirror.fromTextArea(xmlcode, {
      lineNumbers: true,
      autofocus: true,
      foldGutter: true,
      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
    })
    var browserHeight = document.documentElement.clientHeight;
    myCodeMirror.getWrapperElement().style.height = (0.65 * browserHeight) + 'px';
    myCodeMirror.refresh();
JS;
    Requirements::customScript($js);
    $css = <<<CSS
    .CodeMirror-scroll {height: 100%; overflow-y: auto; overflow-x: auto;}
CSS;
    Requirements::customCSS($css);
    
    return $this->customise(new ArrayData(array(
      'Content' => $content,
    )))->renderWith("RepoData_code");
  }

  private function childnodes($nodes, &$uniqueNodes) {
    foreach($nodes as $node) {
      if ($node->nodeName == "#text") {
      } else {
        foreach ($node->attributes as $attr) {
          $text = $attr->getNodePath();
          $text = preg_replace("/\[.*?\]/","",$text);
          if (isset($uniqueNodes[$text])) {
            $uniqueNodes[$text] += 1;
          } else {
            $uniqueNodes[$text] = 1;
          }
        }
        $this->childnodes($node->childNodes, $uniqueNodes);
      }
    }
  }


  public function showNode()
  {
    $content = '';
    $params = $this->loadConfigParams();
    $responseData = $this->getGitHubContent($params->file_path, $params->org, $params->repo);
    if (isset($responseData->content)) {
      $xml = base64_decode($responseData->content);
      $doc = new \DOMDocument('1.0', 'UTF-8');
      $doc->preserveWhiteSpace = false;
      $doc->formatOutput = true;
      $doc->loadXML($xml);
      $xpath = new DOMXpath($doc);
      $text_nodes = $xpath->query('//text()');
      foreach ($text_nodes as $text_node) {
        $text_node->deleteData(0, $text_node->length+1);
      }
      
      $nodes = $xpath->query('/*');
      $uniqueNodes = array();
      foreach($nodes as $node) {
        if ($node->nodeName == "#text") {
        } else {
          foreach ($node->attributes as $attr) {
            $text = preg_replace("/\[.*?\]/","",$attr->getNodePath());
            if (isset($uniqueNodes[$text])) {
              $uniqueNodes[$text] += 1;
            } else {
              $uniqueNodes[$text] = 1;
            }
          }
          $this->childnodes($node->childNodes, $uniqueNodes);
        }
      }
      ksort($uniqueNodes);
    } else {
      $content = "File not found in repo: ".$params->file_path;
    }
    foreach ($uniqueNodes as $node=>$count) {
      $content .= $this->customise(new ArrayData(array(
        'Node' => $node,
        'Count' => $count,
      )))->renderWith("RepoData_node_line");
    }
    
    return $this->customise(new ArrayData(array(
      'uniqueNodes' => $content,
    )))->renderWith("RepoData_node");
  }

}
