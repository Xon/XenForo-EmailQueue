<?php

class SV_EmailQueue_XenForo_Model_EmailBounce extends XFCP_SV_EmailQueue_XenForo_Model_EmailBounce
{
    protected $sv_bounceType = null;

    public function takeBounceAction($userId, $bounceType, $bounceDate)
    {
        $this->sv_bounceType = $bounceType;
        $ret = parent::takeBounceAction($userId, $bounceType, $bounceDate);
        $this->sv_bounceType = null;
        return  $ret;
    }

    public function hasSoftBouncedTooMuch($userId)
    {
        if (self::$sv_disableEmailOnBounce === null)
        {
            self::$sv_disableEmailOnBounce = XenForo_Application::getOptions()->sv_disableEmailOnBounce;
            if (empty(self::$sv_disableEmailOnBounce))
            {
                self::$sv_disableEmailOnBounce = array();
            }
        }

        if (!empty(self::$sv_disableEmailOnBounce['any_soft']))
        {
            return true;
        }
        return parent::hasSoftBouncedTooMuch($userId);
    }

    static $sv_disableEmailOnBounce = null;

    public function triggerUserBounceAction($userId)
    {
        if (self::$sv_disableEmailOnBounce === null)
        {
            self::$sv_disableEmailOnBounce = XenForo_Application::getOptions()->sv_disableEmailOnBounce;
            if (empty(self::$sv_disableEmailOnBounce))
            {
                self::$sv_disableEmailOnBounce = array();
            }
        }

        if ($this->sv_bounceType && (
            (!empty(self::$sv_disableEmailOnBounce['any_soft']) && $this->sv_bounceType == 'soft') ||
            (!empty(self::$sv_disableEmailOnBounce['soft'])     && $this->sv_bounceType == 'soft') ||
            (!empty(self::$sv_disableEmailOnBounce['hard'])     && $this->sv_bounceType == 'hard')
           ))
        {
            /** @var XenForo_DataWriter_User $user */
            $user = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_SILENT);
            if ($user->setExistingData($userId))
            {
                if ($this->canDisableEmail($user))
                {
                    $alert = $this->disableEmail($userId, $user);
                    $user->save();
                    if ($alert)
                    {
                        $this->alertEmailDisabled($userId);
                    }
                }
            }
            return;
        }

        parent::triggerUserBounceAction($userId);
    }

    protected function canDisableEmail(XenForo_DataWriter_User $user)
    {
        return $user->get('user_state') == 'valid'
                    && !$user->get('is_moderator')
                    && !$user->get('is_admin')
                    && !$user->get('is_staff');
    }

    protected function disableEmail($userId, XenForo_DataWriter_User $user)
    {
        $alert = false;
        if ($user->get('default_watch_state') == 'watch_email')
        {
            $user->set('default_watch_state', 'watch_no_email');
            $alert = true;
        }
        if ($user->get('email_on_conversation'))
        {
            $user->set('email_on_conversation', 0);
            $alert = true;
        }
        if ($user->get('receive_admin_email'))
        {
            $user->set('receive_admin_email', 0);
            $alert = true;
        }
        if ($user->get('sv_email_on_tag'))
        {
            $user->set('sv_email_on_tag', 0);
            $alert = true;
        }
        if ($user->get('fmp_always_email_notify'))
        {
            $user->set('fmp_always_email_notify', 0);
            $alert = true;
        }

        if ($this->_getThreadWatchModel()->setThreadWatchStateForAll($userId, 'watch_no_email'))
        {
            $alert = true;
        }
        if ($this->_getForumWatchModel()->setForumWatchStateForAll($userId, 'watch_no_email'))
        {
            $alert = true;
        }

        return $alert;
    }

    protected function alertEmailDisabled($userId, array $extraData = null)
    {
        XenForo_Model_Alert::alert(
            $userId,
            0, '',
            'user', $userId,
            'email_bounced',
            $extraData
        );
    }

    /**
     * @return XenForo_Model|XenForo_Model_ThreadWatch
     */
    protected function _getThreadWatchModel()
    {
        return $this->getModelFromCache('XenForo_Model_ThreadWatch');
    }

    /**
     * @return XenForo_Model|XenForo_Model_ForumWatch
     */
    protected function _getForumWatchModel()
    {
        return $this->getModelFromCache('XenForo_Model_ForumWatch');
    }
}