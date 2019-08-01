<?php

class SV_EmailQueue_XenForo_Deferred_UserEmail extends XFCP_SV_EmailQueue_XenForo_Deferred_UserEmail
{
    /** @var SV_EmailQueue_Transport_FailedDefer */
    protected $_deferMailTransport = null;

    protected function _getDeferMailTransport(Zend_Mail_Transport_Abstract $transport)
    {
        if($this->_deferMailTransport === null)
        {
            $class = XenForo_Application::resolveDynamicClass("SV_EmailQueue_Transport_FailedDefer");
            $this->_deferMailTransport = new $class();
            $this->_deferMailTransport->setWrappedTransport($transport);
        }
        return $this->_deferMailTransport;
    }

    protected function _sendEmail(array $email, array $user, Zend_Mail_Transport_Abstract $transport)
    {
        if (XenForo_Application::get('config')->enableMailQueue)
        {
            // use a tiny custom Mail transport which just posts to XenForo_Mail::insertFailedMailQueue on failure
            $transport = $this->_getDeferMailTransport($transport);
        }
        return parent::_sendEmail($email, $user, $transport);
    }
}