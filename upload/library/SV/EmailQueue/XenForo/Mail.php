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

    protected $_mailQueue = null;

    protected function _getMailQueueModel()
    {
        if ($this->_mailQueue === null)
        {
            $this->_mailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        }
        return $this->_mailQueue;
    }
}