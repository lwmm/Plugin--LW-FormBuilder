<?php

/**
 * This plugin supports the creation of a tag cloud.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_formbuilder
 */
class lw_formbuilder extends lw_plugin
{

    protected $db;
    protected $repository;
    protected $response;

    /**
     * For the functionality of this plugin is it necessary to include
     * the "Autoloader" and the instances of "in_auth" and "auth"
     * objects.
     */
    public function __construct()
    {
        parent::__construct();
        include_once(dirname(__FILE__) . '/Services/Autoloader.php');
        $autoloader = new \LwFormBuilder\Services\Autoloader();
        $this->response = new \LwFormBuilder\Services\Response();
    }

    /**
     * The HTML frontend output will be build for logged in user. Not logged in
     * user will be redirected to the login page. 
     * 
     * @return string
     */
    public function buildPageOutput()
    {
        return "";
        $response = new \LwFormBuilder\Services\Response();
        return $response->getOutputByKey("LwFormBuilder");
    }

    /**
     * The HTML backend output will be build.
     * 
     * @return string
     */
    public function getOutput()
    {
        $backend = new \LwFormBuilder\Controller\backend($this->config, $this->request, $this->repository, $this->getPluginName(), $this->getOid(), $this->response, $this->db);
        if ($this->request->getAlnum("pcmd") == "save") {
            $validate = new \LwFormBuilder\Controller\Service\BackendBaseDataValidate();
            $validate->setValues($this->getArrayForValidation());
            
            if($validate->validate()){
                $backend->backend_save();
            }else{
                return $backend->backend_view($validate->getErrors());
            }
        }
        return $backend->backend_view();
    }

    /**
     * Here will be set if the plugin-conetentbox is deleteable from a page.
     * 
     * @return boolean
     */
    function deleteEntry()
    {
        return true;
    }
    
    private function getArrayForValidation()
    {
        return array(
            "mail_sender" => $this->request->getRaw("mail_sender"),             
            "mail_reciever" => $this->request->getRaw("mail_reciever"),         
            "url_error" => $this->request->getRaw("url_error"),                 
            "url_success" => $this->request->getRaw("url_success"),             
            "max_registrations" => $this->request->getInt("max_registrations"), 
            "url_full" => $this->request->getRaw("url_full")
        );
    }

}