<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2018.09.07.
 * Time: 22:18
 */

namespace App\LegacyService\Lock;

/**
 * Class Lock
 * @package App\LegacyService\Lock
 */
class Lock
{
    const EXPIRATION_TIME_IN_SEC = 300;

    /**
     * @param $jobName
     */
    public function lock($jobName)
    {
        file_put_contents($jobName, time() + self::EXPIRATION_TIME_IN_SEC);
    }

    /**
     * @param $jobName
     */
    public function unlock($jobName)
    {
        if (file_exists($jobName)) {
            unlink($jobName);
        }
    }

    public function isLocked($jobName)
    {
        if (is_file($jobName) && (int)file_get_contents($jobName) > time()) {
            return true;
        }

        return false;
    }
}
