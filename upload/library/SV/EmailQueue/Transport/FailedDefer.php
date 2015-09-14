<?php

class SV_EmailQueue_Transport_FailedDefer extends Zend_Mail_Transport_Abstract
{
    protected $_mailQueue = null;
    protected $transport = null;

    public function setWrappedTransport(Zend_Mail_Transport_Abstract $transport)
    {
        $this->transport =  $transport;
    }

    public function send(Zend_Mail $mail)
    {
        try
        {
            $this->transport->send($mail);
        }
        catch (Exception $e)
        {
            $toEmails = implode(', ', $mail->getRecipients());
            XenForo_Error::logException($e, false, "Email to $toEmails failed: ");
            $this->_getMailQueueModel()->insertFailedMailQueue($mail);
        }
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