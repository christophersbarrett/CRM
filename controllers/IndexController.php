<?php

class IndexController extends Zend_Controller_Action {

    private $title;   
    protected $_namespace;

    public function init() {
       $this->_namespace = new Zend_Session_Namespace();
       $this->_namespace->userId = 1; //obviously this needs to come in from moodle eventually

    }

    public function indexAction() {
       $var = $this->getInvokeArg('bootstrap')->getOptions();
        if ($this->getRequest ()->isPost ()) {
            $formData = $this->getRequest ()->getPost ();
            if(isset($formData['Play'])  || isset($formData['Review'])) {//load up existing session
                $sessionId = $formData['sId'];               
                $this->_namespace->sId = $sessionId;
                $session = new Model_Session();
                $session->find($sessionId);
                $this->_namespace->date = $session->getTradingDate();//should be an offset
                $this->_namespace->profileId = $session->getProfileId();
                $this->_redirect ( $this->view->url(array('controller'=>'index', 'action'=>'play')) );
            }
            else {//create a new session
                $userId = $formData['userId'];
                $url = "index/createsession/userId/$userId";
				$this->_redirect( $url );
            }
        }
        else {
            $args = $this->getRequest()->getParams();
            $userId = $this->_namespace->userId;
            $this->_namespace->date = 0;
            
            $this->view->userId = $userId;
            $this->view->title = 'CRM Interfaces';
            $this->view->headTitle ( $this->view->title, 'PREPEND' );
            $url = $this->view->url(array('controller'=>'data', 'action'=>'getusersessions'));
            $this->view->store = $this->view->dataStore('store', 'dojox.data.QueryReadStore', array('url' => $url));

        $grid = $this->view->dataGrid('sessions',
            array(
            'selectionMode' => 'none',
            'style' => 'height: 500px; width: 100%;',
            'store' => 'store',
            'escapeHTMLInData' => false,
            'autoHeight' => false,
            'fields' => array(
            array('field' => 'name', 'label' => 'Name', 'options' => array('formatter' => 'formatPrimary', 'width' => '20%')),
            array('field' => 'traderType', 'label' => 'Trader Type', 'options' => array('formatter' => 'formatPrimary', 'width' => '10%')),
            array('field' => 'tradingDate', 'label' => 'Trading Date', 'options' => array('formatter' => 'formatDate', 'width' => '20%')),
            array('field' => 'assets', 'label' => 'Assets', 'options' => array('formatter' => 'formatAssets', 'width' => '20%')),
            array('field' => 'accounts', 'label' => 'Account Balances', 'options' => array('formatter' => 'formatAccounts', 'width' => '20%')),
            array('field' => 'form', 'label' => ' ', 'options' => array('formatter' => 'formatForm', 'width' => '10%')),
            )));
        $this->view->grid = $grid;
        }
    }

    public function playAction() {
                $sessionId = $this->_namespace->sId;
                $session = new Model_Session();
                $session->find($sessionId);
                $start = $session->getStartDateIndex();
                $years = $session->getYears();
                $session->loadVirtualDate($years,null,$start);
                $vd = $session->getVirtualDate();
                $this->_namespace->vdList = $vd->getRealDates();//this should store the array of real dates in the session data.  is this dangerous? I don't know, we'll find out
                $this->view->today = $vd->getVirtualDateFromOffset($this->_namespace->date);
                $this->view->offset = $this->_namespace->date;
                $this->view->real = $vd->getRealDateFromOffset($this->_namespace->date);
                $session->loadProfile();
                $profile = $session->getProfile();
                $this->view->profileName = $profile->getName();
                $this->view->tradingOffset = $this->_namespace->date;
                $this->view->list = $this->_namespace->vdList;
 /*//this code is for an loading overlay
       <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <title>iKarma - v0.0.3a</title>
        <link rel="shortcut icon" href="./img/iK_75.png" />
        <style type="text/css">
            @IMPORT url("./js/dojo/resources/dojo.css");
            @IMPORT url("./js/dijit/themes/soria/soria.css");
            @IMPORT url("./js/dojox/grid/resources/soriaGrid.css");
            @IMPORT url("./js/dojox/grid/resources/Grid.css");
            @IMPORT url("./js/dojox/editor/plugins/resources/css/FindReplace.css");
            @IMPORT url("./js/dojox/editor/plugins/resources/css/Save.css");
            @IMPORT url("./css/pdt.css");
            html, body {
                height: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            #overlay {
                background: #000000;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
            }

            #preloader {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: #fff url('./js/dojox/image/resources/images/loading.gif') no-repeat center center;
                position: absolute;
                z-index: 999;
            }

            #config {
                margin: 0;
                padding: 0;
                background: gray;
                position: absolute;
                overflow: hidden;
                z-index: 990;
                top: 0;
                left: 0;
                color: #292929;
                margin-top: 10px;
                width: 300px;
                width: 500px;
                border: 1px solid #BABABA;
                background-color: #ddd;
                padding-left: 10px;
                padding-right: 10px;
                margin-left: 10px;
                margin-bottom: 1em;
                -o-border-radius: 10px;
                -moz-border-radius: 12px;
                -webkit-border-radius: 10px;
                -webkit-box-shadow: 0px 3px 7px #adadad;
                border-radius: 10px;
                -moz-box-sizing: border-box;
                -opera-sizing: border-box;
                -webkit-box-sizing: border-box;
                -khtml-box-sizing: border-box;
                box-sizing: border-box;
                overflow: hidden;
            }

            #blocker {
                margin: 0;
                padding: 0;
                background: url('./img/test.jpg') repeat;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                // for IE
                filter: alpha(opacity = 60);
                // CSS3 standard
                opacity: 0.6;
                z-index: 800;
            }
        </style>
        <script type="text/javascript">

            djConfig = {
                isDebug: true,
                parseOnLoad: false,
                baseScriptUri: "../js/dojo/"
            };
        </script>
        <script language='JavaScript' type="text/javascript" src="./js/dojo/dojo.js">
        </script>
        <script type="text/javascript">

            var hideLoader = function(){
                dojo.fadeOut({
                    node: "preloader",
                    duration: 2000,
                    onEnd: function(){
                        dojo.style("preloader", "display", "none");
                    }
                }).play();
            }
            function level1(){
                //add requires here
                dojo.require("karma.controller.Main");
                dojo.addOnLoad(level2);
            }

            function level2(){
                var controller = new karma.controller.Main({
                    myAttachPoint: 'uiBorder',
                    myHotKeyPane: 'config',
                    myBlocker: 'blocker'
                });
                dojo.parser.parse();
                hideLoader();

            };
            dojo.addOnLoad(level1);
        </script>
    </head>
    <body class='soria'>
        <div id="preloader">
        </div>
        <div id="uiBorder" dojoAttachPoint="dapMain">
        </div>
        <div id="config" dojoAttachPoint="dapConfig">
            <table align='center'>
                <tr height='10px'>
                </tr>
                <tr>
                    <td class="label" colspan="2">
                        Show Tree Pane:<input dojoType="dijit.form.CheckBox" checked="true" dojoAttachPoint="dapShowTreePane"/>
                    </td>
                </tr>
                <tr>
                    <td class="label" colspan="2">
                        Show Dashboard Pane:<input dojoType="dijit.form.CheckBox" checked="true" dojoAttachPoint="dapShowDashPane"/>
                    </td>
                </tr>
                <tr height='10px'>
                	<td class="label" colspan="2">
                		Number of Dijits Loaded:<span id="numDigits"></span>
                		</td>
                </tr>
            </table>
        </div>
        <div id="blocker">
        </div>
    </body>
</html>
*/
    }

    public function testgridAction() {
        
        $this->view->title = "Test Grid";
        $this->view->headTitle ( $this->view->title, 'PREPEND' );
        $url = $this->view->url(array('controller'=>'data', 'action'=>'getprofiles'));
        $this->view->store = $this->view->dataStore('store', 'dojox.data.QueryReadStore', array('url' => $url));

        $grid = $this->view->dataGrid('myGrid',
            array(
            'selectionMode' => 'single',
            'style' => 'height: 110px; width: 100%;',
            //'store' => 'store',
            'rowSelector' => '10px',
            'fields' => array(
            array('field' => 'id', 'label' => 'Id', 'options' => array('formatter' => 'formatTitle', 'width' => '0%')),
            array('field' => 'name', 'label' => 'Name', 'options' => array('formatter' => 'formatPrimary', 'width' => '20%')),
            array('field' => 'product', 'label' => 'Product', 'options' => array('formatter' => 'formatPrimary', 'width' => '10%')),
            array('field' => 'traderType', 'label' => 'Type', 'options' => array('formatter' => 'formatPrimary', 'width' => '20%')),
            array('field' => 'commodities', 'label' => 'Commodities', 'options' => array('formatter' => 'formatPrimary', 'width' => '20%')),
            array('field' => 'startingAssets', 'label' => 'Starting Assets', 'options' => array('formatter' => 'formatPrimary', 'width' => '15%')),
            array('field' => 'operatingCosts', 'label' => 'Operating Costs', 'options' => array('formatter' => 'formatPrimary', 'width' => '15%')),
            )));
        $this->view->grid = $grid;
        


    }  
    
    public function moodleAction() {
		require_once 'ims-blti/blti.php';
		
		// Data from Moodle is sent in via POST.  The Moodle site's secret
		// must match ours, and the timestamp must be current, otherwise
		// we'll still create a context, but it's "valid" property will
		// be false.  Either way, we just pass it on to the view for further
		// processing and display.
		$context = new BLTI("secret", false, false);
		$this->view->context = $context;
		
		$this->view->title = 'Start From Moodle';
    	$this->view->headTitle( $this->view->title, 'PREPEND');
    }
}







