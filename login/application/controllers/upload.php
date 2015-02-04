<?php

/**
 * Class upload
 * The upload area
 */
class upload extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * This method controls what happens when you move to /help/index in your app.
     */
    function index()
    {
        $this->view->render('upload/index');
    }
	
	function upld()
    {
        $this->view->render('upload/upld');
    }
	
	function edit()
    {
        $this->view->render('upload/edit');
    }
}
