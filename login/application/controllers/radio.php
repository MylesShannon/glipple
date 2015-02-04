<?php

/**
 * Class radio
 * The radio area
 */
class Radio extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * This method controls what happens when you move to /radio/index in your app.
     */
    function index()
    {
        $this->view->render('radio/index');
    }
}
