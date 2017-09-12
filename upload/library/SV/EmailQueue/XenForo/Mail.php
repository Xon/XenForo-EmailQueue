<?php

class SV_EmailQueue_XenForo_Mail extends XFCP_SV_EmailQueue_XenForo_Mail
{
    public function sendMail(Zend_Mail $mailObj)
    {
        if (!XenForo_Application::get('config')->enableMail)
        {
            return true;
        }

        if (XenForo_Application::getOptions()->sv_emailqueue_force)
        {
            $sv_emailqueue_exclude = XenForo_Application::getOptions()->sv_emailqueue_exclude;
            if (empty($sv_emailqueue_exclude[$this->_emailTitle]))
            {
                return $this->_getMailQueueModel()->insertMailQueue($mailObj);
            }
        }

        try
        {
            if (parent::sendMail($mailObj))
            {
                return true;
            }
        }
        catch(Exception $e)
        {
            $this->_getMailQueueModel()->insertFailedMailQueue($mailObj);
            $toEmails = implode(', ', $mailObj->getRecipients());
            XenForo_Error::logException($e, false, "Queued, Email to $toEmails failed: ");
            return true;
        }

        return $this->_getMailQueueModel()->insertFailedMailQueue($mailObj);
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