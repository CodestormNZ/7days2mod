<?php

class modSiteConfig extends DataExtension
{
    private static $db = array(
        'gituser' => 'Text',
        'gitpwd' => 'Text'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
          "Root.Main", 
          new TextField("gituser", "GitHub Username or Email")
        );
        $fields->addFieldToTab(
          "Root.Main", 
          new PasswordField("gitpwd", "GitHub Password")
        );
    }
}
