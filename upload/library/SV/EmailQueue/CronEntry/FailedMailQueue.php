<?php

class SV_EmailQueue_CronEntry_FailedMailQueue
{
    public static function run()
    {
        /** @var SV_EmailQueue_XenForo_Model_MailQueue $MailQueue */
        $MailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        $MailQueue->RetryFailedQueue();
    }
}