<?php

/**
 * Class: SystemController
 * @Purpose: Controller for Post related pages.
 * @Author: Manasi G
 * @Modified By:    Manasi G (manasi.ghangured@in.v2solutions.com), Soju C
 */

class SystemController extends Zend_Controller_Action {

    /** call at every initialisation **/
    public function init() {
        /* Initialize action controller here */
        $this->_options = $this->getInvokeArg('bootstrap')->getOptions();
    }


    /** index action **/
    public function indexAction() {
        // action body
	// 'DvInfo' is the register key from application.ini
        Zend_Registry::get('DvInfo')->adminDbClass();
	/** Code
	**/
        exit; //optional

    }
    
}
