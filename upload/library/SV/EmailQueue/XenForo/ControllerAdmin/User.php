<?php

class SV_EmailQueue_XenForo_ControllerAdmin_User extends XFCP_SV_EmailQueue_XenForo_ControllerAdmin_User
{
    static $_deferMailTransport = null;
    
    protected function _getDeferMailTransport()
    {
        if(self::$_deferMailTransport === null)
        {
            $class = XenForo_Application::resolveDynamicClass("SV_EmailQueue_Transport_Defer");
            self::$_deferMailTransport = new $class();
        }
        return self::$_deferMailTransport;
    }

	protected function _sendEmail(array $user, array $email, Zend_Mail_Transport_Abstract $transport)
	{
        if (XenForo_Application::get('config')->enableMailQueue)
        {
            // use a tiny custom Mail transport which just posts to XenForo_Mail::insertMailQueue
            $transport = $this->_getDeferMailTransport();
        }
        return parent::_sendEmail($user, $email, $transport);
    }
}