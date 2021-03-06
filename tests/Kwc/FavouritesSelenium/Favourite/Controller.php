<?php
class Kwc_FavouritesSelenium_Favourite_Controller extends Kwc_Favourites_Controller
{
    public function preDispatch()
    {
        parent::preDispatch();
        //use custom user model
        Kwf_Registry::get('config')->user->model = 'Kwc_FavouritesSelenium_UserModel';

        //unset existing userModel instance to get new one
        $reg = Kwf_Registry::getInstance()->set('userModel',
            Kwf_Model_Abstract::getInstance('Kwc_FavouritesSelenium_UserModel')
        );
    }
}