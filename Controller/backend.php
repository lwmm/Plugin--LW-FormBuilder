<?php

/**
 * The backend controller saves done changes and generate the backend
 * html output.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_formbuilder
 */

namespace LwFormBuilder\Controller;

class backend extends \lw_object
{
    protected $config;
    protected $request;
    protected $repository;
    protected $pluginname;
    protected $oid;
    protected $response;
    protected $db;
            
    function __construct($config, $request, $repository, $pluginname, $oid, $response, $db)
    {
        $this->config = $config;
        $this->request = $request;
        $this->repository = $repository;
        $this->pluginname = $pluginname;
        $this->oid = $oid;
        $this->response = $response;
        $this->db = $db;
    }

    function backend_save()
    {
       # print_r($this->request);die();
        $cH = new \LwI18n\Model\commandHandler($this->db);
        $langParams = $this->request->getRaw("lw_i18n");
        
        foreach($langParams as $lang => $plugindata) {
            foreach($plugindata as $pluginname => $entries) {
                foreach($entries as $key => $text) {
                    $cH->save($pluginname, $lang, $key, $text);
                }
            }
        }
        
        $parameter = array();
        $parameter["selectedLang"]          = $this->request->getAlnum("selectedLang");
        $parameter["baseData"] = array(
            "general" => array(
                "title" => $this->request->getAlnum("title"),
                "csv" => $this->request->getInt("csv"),
                "mail" => $this->request->getInt("mail"),
                "mail_sender" => $this->request->getRaw("mail_sender"),             #
                "mail_reciever" => $this->request->getRaw("mail_reciever"),         #
                "url_error" => $this->request->getRaw("url_error"),                 #
                "url_success" => $this->request->getRaw("url_success"),             #
                "max_registrations" => $this->request->getInt("max_registrations"), #
                "url_full" => $this->request->getRaw("url_full"),                   #
                "urls_target_top" => $this->request->getInt("urls_target_top")
            ),
            "emailResponse" => array(
                "formfieldname_email" => $this->request->getRaw("formfieldname_email"),
                "subject" => $this->request->getRaw("subject"),
                "return_mail" => $this->request->getRaw("return_mail")
            ),
            "confirmPage" => array(
                "use_confirm_page" => $this->request->getInt("use_confirm_page"),
                "confirm_text" => $this->request->getRaw("confirm_text")
            )
        );
        
        $content = serialize(json_decode(str_replace("'", "\"", $this->request->getRaw("formStructure")), true));
        
        $this->repository->plugins()->savePluginData($this->pluginname, $this->oid, $parameter, $content);

        $this->pageReload($this->config["url"]["client"] . "admin.php?obj=content");
    }
    
    function backend_view($errors = false)
    {
        $data = $this->repository->plugins()->loadPluginData($this->pluginname, $this->oid);
        $formStructure = "";
        if($data["content"]){
            $formStructure = unserialize($data["content"]);
        }
        
        if($errors){    
            $baseData = array(
                "general" => array(
                    "title" => $this->request->getAlnum("title"),
                    "csv" => $this->request->getInt("csv"),
                    "mail" => $this->request->getInt("mail"),
                    "mail_sender" => $this->request->getRaw("mail_sender"),             
                    "mail_reciever" => $this->request->getRaw("mail_reciever"),         
                    "url_error" => $this->request->getRaw("url_error"),                 
                    "url_success" => $this->request->getRaw("url_success"),             
                    "max_registrations" => $this->request->getInt("max_registrations"), 
                    "url_full" => $this->request->getRaw("url_full"),                   
                    "urls_target_top" => $this->request->getInt("urls_target_top")
                ),
                "emailResponse" => array(
                    "formfieldname_email" => $this->request->getRaw("formfieldname_email"),
                    "subject" => $this->request->getRaw("subject"),
                    "return_mail" => $this->request->getRaw("return_mail")
                ),
                "confirmPage" => array(
                    "use_confirm_page" => $this->request->getInt("use_confirm_page"),
                    "confirm_text" => $this->request->getRaw("confirm_text")
                )
            );
        }else{
            $baseData = false;
            if($data["parameter"]["baseData"]){
                $baseData = $data["parameter"]["baseData"];
            }
        }
        
        $Lw18nController        = new \LwI18n\Controller\I18nController($this->db, $this->response);
        $Lw18nController->execute( array($this->pluginname, "lw_formbuilder"), "de", $this->collectDataforLwI18nPlugin("de"));
        $Lw18nController->execute( array($this->pluginname, "lw_formbuilder"), "en", $this->collectDataforLwI18nPlugin("en"));
        
        
        $view                   = new \lw_view(dirname(__FILE__) . '/../Views/Templates/backendform.tpl.phtml');
        $view->actionUrl        = $this->buildUrl(array("pcmd" => "save"));
        
        $view->bootstrapCSS     = $this->config["url"]["media"] . "bootstrap/css/bootstrap.min.css";
        $view->bootstrapJS      = $this->config["url"]["media"] . "bootstrap/js/bootstrap.min.js";
        $view->urlJQmin         = $this->config["url"]["media"] . "jquery/jquery.min.js";
        $view->urlJQcore        = $this->config["url"]["media"] . "jquery/ui/ui.core.js";
        $view->urlJQsortable    = $this->config["url"]["media"] . "jquery/ui/ui.sortable.js";
        $view->urlJQUI          = $this->config["url"]["media"] . "jquery/ui/jquery-ui-1.8.7.custom.min.js";
        $view->urlJQUIcss       = $this->config["url"]["media"] . "jquery/ui/css/smoothness/jquery-ui-1.8.7.custom.css";
        $view->mce              = $this->config["url"]["media"] . "tinymce/jscripts/tiny_mce/tiny_mce.js";

        $view->iconArrows       = $this->config["url"]["media"] . "pics/fatcow_icons/16x16_0060/arrow_inout.png";
        $view->iconCursor       = $this->config["url"]["media"] . "pics/fatcow_icons/16x16_0320/cursor.png";
        $view->iconCross        = $this->config["url"]["media"] . "pics/fatcow_icons/16x16_0300/cross.png";
        $view->iconWrench       = $this->config["url"]["media"] . "pics/fatcow_icons/16x16_1000/wrench.png";
        $view->iconAdd          = $this->config["url"]["media"] . "pics/fatcow_icons/16x16_0020/add.png";

        $view->formArray        = $formStructure;
        $view->baseUrl          = \LwFormBuilder\Services\Page::getUrl();
        $view->urlResource      = $this->config["url"]["resource"];
        
        $view->de               = $this->response->getOutputByKey("i18n_de"); 
        $view->en               = $this->response->getOutputByKey("i18n_en"); 
        $view->params           = $data["parameter"];
        $view->baseData         = $baseData;
        $view->errors           = $errors;
        return $view->render();
    }
    
    public function collectDataforLwI18nPlugin($lang)
    {
        $de = array_merge(array($this->pluginname => $this->fillPlaceHolderWithSelectedLanguage("de")));
        $en = array_merge(array($this->pluginname => $this->fillPlaceHolderWithSelectedLanguage("en"))); 
        
        switch ($lang) {
            case "de":
                return array("de" => $de);
                break;
            case "en":
                return array("en" => $en);
                break;
        }
    }
    
    public function fillPlaceHolderWithSelectedLanguage($lang)
    {
        $languageDE = array(
            "errors_occured"                            => "Es sind Fehler aufgetreten.",
            "required"                                  => "Das folgende Feld muss ausgef&uuml;llt werden.",
            "max_length_with_placeholder"               => "Der Wert darf maximal {_data_} Zeichen lang sein!",
            "max_length_3999"                           => "Der Wert darf maximal 3999 Zeichen lang sein!",
            "error_email"                               => "Bitte eine g&uuml;ltige E-Mail eingeben!",
            "error_file"                                => "Bitte eine Datei ausw&auml;hlen!",
            "label_allowed_file_types"                  => "Nur folgende Datei-Typen sind erlaubt.",
            "label_error_date"                          => "Bitte ein g&uuml;ltiges Datum eingeben.",
            "label_error_date_from_with_placeholder"    => "Bitte ein Datum ab dem {_data_} eingeben.",
            "label_error_date_to_with_placeholder"      => "Bitte ein Datum bis zum {_data_} eingeben.",
            "label_cancle_button"                       => "zur&uuml;ck",
            "label_submit_button"                       => "absenden"
        );
        
        $languageEN = array(
        );
        
        if($lang == "de") {
            return $languageDE;
        } else {
            return $languageEN;
        }
    }
}
