<?php

class SV_EmailQueue_Listener
{
    const AddonNameSpace = 'SV_EmailQueue';

    public static function install($installedAddon, array $addonData, SimpleXMLElement $xml)
    {
        $db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);
        
        $db->query("CREATE TABLE IF NOT EXISTS `xf_mail_queue_failed` (
  `mail_id` varbinary(20) NOT NULL,
  `mail_data` mediumblob NOT NULL,
  `queue_date` int unsigned NOT NULL,
  `fail_count` int unsigned NOT NULL,
  `last_fail_date` int unsigned NOT NULL,
  `dispatched` bit NOT NULL default 0,
  PRIMARY KEY (`mail_id`),
  KEY `dispatched` (`dispatched`),
  KEY `last_fail_date` (`last_fail_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        XenForo_Db::commit($db);

    }

    public static function uninstall()
    {
        $db = XenForo_Application::getDb();
        XenForo_Db::beginTransaction($db);

        $db->query("insert into xf_mail_queue (`mail_data`,`queue_date`) select `mail_data`,`queue_date` from xf_mail_queue_failed");
        $db->query("drop table xf_mail_queue_failed");

        XenForo_Db::commit($db);
    }

    public static function load_class($class, &$extend)
    {
        $extend[] = self::AddonNameSpace.'_'.$class;
    }
}
