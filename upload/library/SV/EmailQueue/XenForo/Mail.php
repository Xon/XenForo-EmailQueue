<?php

class SV_EmailQueue_XenForo_Mail extends SV_CanWarnStaff_XenForo_Mail
{
    public function sendMail(Zend_Mail $mailObj)
    {
        if (!XenForo_Application::get('config')->enableMail)
        {
            return true;
        }
        if (XenForo_Application::get('config')->enableMailQueue)
        {
            return XenForo_Model::create('XenForo_Model_MailQueue')->insertMailQueue($mailObj);
        }
        else
        {
            return parent::sendMail($mailObj);
        }
    }
}