<?php

class SV_EmailQueue_Transport_Defer extends Zend_Mail_Transport_Abstract
{
    static $_mailQueue = null;

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
        if (self::$_mailQueue === null)
        {
            self::$_mailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        }
        return self::$_mailQueue;
    }
}