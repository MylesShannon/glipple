<?php

/**
 * Class test
 * The test area
 */
class Test extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * This method controls what happens when you move to /test/index in your app.
     */
    function index()
    {
        $this->view->render('test/index');
    }
	
	function upload()
    {
        $this->view->render('test/upload');
    }
}
