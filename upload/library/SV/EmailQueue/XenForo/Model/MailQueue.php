<?php

class SV_EmailQueue_XenForo_Model_MailQueue extends XFCP_SV_EmailQueue_XenForo_Model_MailQueue
{
    public function RetryFailedQueue()
    {
        // do not attempt to process email if email is disabled.
        $config = XenForo_Application::get('config');
        if (!$config->enableMail || !$config->enableMailQueue)
        {
            return;
        }

        $latest_failed = $this->GetLastFailedTimeStamp();
        if ($latest_failed)
        {
            $options = XenForo_Application::get('options');
            $back_off_period = $options->sv_emailqueue_backoff *60;
            if ((!$back_off_period || microtime(true) > $latest_failed + $back_off_period))
            {
                $db = $this->_getDb();
                XenForo_Db::beginTransaction($db);

                // we have failed items, and the back-off period has expired. copy them back to the mail_queue for another attempt.
                // ensure we only dispatch a failed item once, until it is processed.
                $db->query('
                    insert into xf_mail_queue (`mail_data`,`queue_date`)
                    select `mail_data`,`queue_date`
                    from xf_mail_queue_failed
                    where dispatched = 0;
                ');
                $db->query('
                    update xf_mail_queue_failed
                    set dispatched = 1;
                ');

                XenForo_Db::commit($db);
            }
        }

        if (!self::$_deferredQueued)
        {
            XenForo_Application::defer('MailQueue', array(), 'MailQueue');
            self::$_deferredQueued = true;
        }
    }

    public function insertFailedMailQueue(Zend_Mail $mailObj)
    {
        $toEmails = implode(', ', $mailObj->getRecipients());
        try
        {
            $rawmailObj = serialize($mailObj);
            $mail_id = $this->getFailedItemKey($rawmailObj, XenForo_Application::$time);
            $this->_insertFailedMailQueue($mail_id, $rawmailObj, XenForo_Application::$time);
        }
        catch(Exception $e)
        {
            XenForo_Error::logException($e, false, "Exception when attempting to queue failed email for Email to $toEmails: ");
        }
    }

    protected function _insertFailedMailQueue($mail_id, $rawmailObj, $queue_date)
    {
        $this->_getDb()->query('
            INSERT INTO xf_mail_queue_failed
                (mail_id, mail_data, queue_date, fail_count, last_fail_date)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                dispatched = 0,
                fail_count = fail_count + 1,
                last_fail_date = VALUES(last_fail_date)
        ', array(
            $mail_id, $rawmailObj, $queue_date, 1, XenForo_Application::$time
        ));

        return true;
    }

    public function getFailedItemKey($rawmailObj, $queue_date)
    {
        return sha1($queue_date . $rawmailObj, true);
    }

    public function GetMailFailedCount($mail_id)
    {
        return $this->_getDb()->fetchOne('
            SELECT fail_count
            FROM xf_mail_queue_failed
            WHERE mail_id = ?
        ', $mail_id);
    }

    public function DeleteFailedMail($mail_id)
    {
        $this->_getDb()->query('
            delete
            from xf_mail_queue_failed
            where mail_id = ?
        ', $mail_id);
    }

    public function GetLastFailedTimeStamp()
    {
        return $this->_getDb()->fetchOne('
            SELECT max(last_fail_date)
            FROM xf_mail_queue_failed
        ');
    }

    public function runMailQueue($targetRunTime)
    {
        // do not attempt to process email if email is disabled.
        $config = XenForo_Application::get('config');
        if (!$config->enableMail || !$config->enableMailQueue)
        {
            return $this->hasMailQueue();
        }

        $s = microtime(true);
        $transport = XenForo_Mail::getTransport();
        $db = $this->_getDb();
        $options = XenForo_Application::get('options');
        do
        {
            $queue = $this->getMailQueue($targetRunTime ? $options->sv_emailqueue_batchsize : 0);

            foreach ($queue AS $id => $record)
            {
                if (!$db->delete('xf_mail_queue', 'mail_queue_id = ' . $db->quote($id)))
                {
                    // already been deleted - run elsewhere
                    continue;
                }

                $mailObj = @unserialize($record['mail_data']);
                if (!($mailObj instanceof Zend_Mail))
                {
                    continue;
                }

                $mail_id = $this->getFailedItemKey($record['mail_data'], $record['queue_date']);
                $email_send = false;

                $thisTransport = XenForo_Mail::getFinalTransportForMail($mailObj, $transport);

                try
                {
                    $mailObj->send($thisTransport);
                    $email_send = true;
                }
                catch (Exception $e)
                {
                    $this->OnDeliveryFailure($e, $mailObj, $mail_id, $record);

                    // pipe may be messed up now, so let's be sure to get another one
                    unset($transport);
                    $transport = XenForo_Mail::getTransport();
                }
                // cleanup any failed email
                if ($email_send)
                {
                   $this->OnDeliverySuccess($mailObj, $mail_id, $record);
                }

                if ($targetRunTime && microtime(true) - $s > $targetRunTime)
                {
                    $queue = false;
                    break;
                }
            }
        }
        while ($queue);

        return $this->hasMailQueue();
    }

    function OnDeliveryFailure($e, $mailObj, $mail_id, $record )
    {
        // queue the failed email
        $this->_insertFailedMailQueue($mail_id, $record['mail_data'], $record['queue_date']);
        $toEmails = implode(', ', $mailObj->getRecipients());
        $failed_count = $this->GetMailFailedCount($mail_id);
        $options = XenForo_Application::get('options');
        if ($options->sv_emailqueue_failures_to_error && $failed_count >= $options->sv_emailqueue_failures_to_error)
        {
            $this->DeleteFailedMail($mail_id);
            XenForo_Error::logException($e, false, "Abandoning, Email to $toEmails failed: ");
        }
        else if ($options->sv_emailqueue_failures_to_warn && $failed_count >= $options->sv_emailqueue_failures_to_warn)
        {
            XenForo_Error::logException($e, false, "Queued, Email to $toEmails failed: ");
        }
    }

    function OnDeliverySuccess($mailObj, $mail_id, $record )
    {
        $this->DeleteFailedMail($mail_id);
    }
}