<?php

class SV_EmailQueue_Transport_Defer extends Zend_Mail_Transport_Abstract
{
    /** @var XenForo_Model_MailQueue */
    protected $_mailQueue = null;

    public function send(Zend_Mail $mail)
    {
        $this->_getMailQueueModel()->insertMailQueue($mail);
    }

    protected function _sendMail()
    {
        throw new Exception("This email transport doesn't actually send email");
    }

    protected function _getMailQueueModel()
    {
        if ($this->_mailQueue === null)
        {
            $this->_mailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        }
        return $this->_mailQueue;
    }
}