<?php
class Kwc_NewsletterCategory_Subscribe_Mail_Component extends Kwc_Newsletter_Subscribe_Mail_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['recipientSources']['sub'] = 'Kwc_NewsletterCategory_Subscribe_Model';
        return $ret;
    }
}
