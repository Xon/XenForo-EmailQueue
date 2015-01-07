<?php

class SV_EmailQueue_CronEntry_FailedMailQueue 
{
    public static function run()
    {
        $MailQueue = XenForo_Model::create("XenForo_Model_MailQueue");
        $MailQueue->RetryFailedQueue();
    } 
}