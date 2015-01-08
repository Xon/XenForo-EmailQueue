<?php

class SV_EmailQueue_XenForo_Mail extends XFCP_SV_EmailQueue_XenForo_Mail
{
    public function sendMail(Zend_Mail $mailObj)
    {
        if (!XenForo_Application::get('config')->enableMail)
        {
            return true;
        }
        if (XenForo_Application::get('config')->enableMailQueue)
        {
            return $this->_getMailQueueModel()->insertMailQueue($mailObj);
        }
        else
        {
            return parent::sendMail($mailObj);
        }
    }

    static $_mailQueue = null;

    protected function _getMailQueueModel()
    {
        if (self::$_mailQueue === null)
        {
            self::$_mailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        }
        return self::$_mailQueue;
    }
}