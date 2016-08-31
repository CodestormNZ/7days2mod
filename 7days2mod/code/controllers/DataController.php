<?php

class DataController extends Controller
{
  private static $allowed_actions = array(
    'showGUI',
    'showCodemirror',
    'showNode',
    'showCompare',
  );
  
  private static $url_handlers = array(
    'Config/$folder/$file/gui' => 'showGUI',
    'Config/$file/gui' => 'showGUI',
    'Config/$folder/$file/code' => 'showCodemirror',
    'Config/$file/code' => 'showCodemirror',
    'Config/$folder/$file/node' => 'showNode',
    'Config/$file/node' => 'showNode',
    'Config/$folder/$file/compare/$org2/$repo2/code' => 'showCompare',
    'Config/$file/compare/$org2/$repo2/code' => 'showCompare',
    'Config/$folder/$file/compare/$repo2/code' => 'showCompare',
    'Config/$file/compare/$repo2/code' => 'showCompare',
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
    $params->org2 = '7days2mod';
    $params->repo2 = 'Vanilla';
    $params->folder = 'Data/Config/';
    $params->extension = '';
    if (null !== $this->getRequest()->param('org')) {
      $params->org = $this->getRequest()->param('org');
    }
    if (null !== $this->getRequest()->param('repo')) {
      $params->repo = $this->getRequest()->param('repo');
    }
    if (null !== $this->getRequest()->param('org2')) {
      $params->org2 = $this->getRequest()->param('org2');
    }
    if (null !== $this->getRequest()->param('repo2')) {
      $params->repo2 = $this->getRequest()->param('repo2');
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
  
  public function showCompare()
  {
    $params = $this->loadConfigParams();
    $responseData = $this->getGitHubContent($params->file_path, $params->org, $params->repo);
    $responseData2 = $this->getGitHubContent($params->file_path, $params->org2, $params->repo2);
    if (isset($responseData->content)) {
      $content = str_replace('"', '\"', base64_decode($responseData->content));
      $content = str_replace("\n", '\n', $content);
    } else {
      $content = "File not found in repo: ".$params->file_path;
    }
    if (isset($responseData2->content)) {
      $content2 = str_replace('"', '\"', base64_decode($responseData2->content));
      $content2 = str_replace("\n", '\n', $content2);
    } else {
      $content2 = "File not found in repo2: ".$params->file_path;
    }
    
    Requirements::javascript("//codemirror.net/lib/codemirror.js");
    Requirements::css("//codemirror.net/lib/codemirror.css");
    Requirements::javascript("//codemirror.net/mode/xml/xml.js");    
    Requirements::javascript("//cdnjs.cloudflare.com/ajax/libs/diff_match_patch/20121119/diff_match_patch.js");
    Requirements::javascript("//codemirror.net/addon/merge/merge.js");
    Requirements::css("//codemirror.net/addon/merge/merge.css");

    $js = <<<JS
      var repo1, repo2, dv, panes = 2, highlight = true, connect = null, collapse = false;
      function initUI() {
        if (repo1 == null) return;
        var target = document.getElementById("xmlcode");
        target.innerHTML = "";
        dv = CodeMirror.MergeView(target, {
          value: repo1,
          orig: repo2,
          lineNumbers: true,
          highlightDifferences: highlight,
          connect: connect,
          collapseIdentical: collapse
        });
      }

      function toggleDifferences() {
        dv.setShowDifferences(highlight = !highlight);
      }

      window.onload = function() {
        repo1 = "$content";
        repo2 = "$content2";
        initUI();
      };

      function mergeViewHeight(mergeView) {
        function editorHeight(editor) {
          if (!editor) return 0;
          return editor.getScrollInfo().height;
        }
        return Math.max(editorHeight(mergeView.leftOriginal()),
                        editorHeight(mergeView.editor()),
                        editorHeight(mergeView.rightOriginal()));
      }

      function resize(mergeView) {
        var height = mergeViewHeight(mergeView);
        for(;;) {
          if (mergeView.leftOriginal())
            mergeView.leftOriginal().setSize(null, height);
          mergeView.editor().setSize(null, height);
          if (mergeView.rightOriginal())
            mergeView.rightOriginal().setSize(null, height);

          var newHeight = mergeViewHeight(mergeView);
          if (newHeight >= height) break;
          else height = newHeight;
        }
        mergeView.wrap.style.height = height + "px";
      }
JS;
    Requirements::customScript($js);

    $css = <<<CSS
      .CodeMirror { line-height: 1.2;}
      .CodeMirror-merge, .CodeMirror-merge .CodeMirror {
        height: 650px;
      }
      @media screen and (min-width: 1300px) {
        article { max-width: 100%; }
        #nav { border-right: 499px solid transparent; }
      }
      span.clicky {
        cursor: pointer;
        background: #d70;
        color: white;
        padding: 0 3px;
        border-radius: 3px;
      }
CSS;
    Requirements::customCSS($css);
    
    return $this->customise(new ArrayData(array(
      'Content' => "",
    )))->renderWith("RepoData_compare");
  }

}
