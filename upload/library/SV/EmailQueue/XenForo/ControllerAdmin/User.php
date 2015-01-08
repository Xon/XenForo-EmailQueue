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
        // use a tiny custom Mail transport which just posts to XenForo_Mail::insertMailQueue
        return parent::_sendEmail($user, $email, $this->_getDeferMailTransport());
    }
}