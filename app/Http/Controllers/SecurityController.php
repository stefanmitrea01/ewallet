<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class SecurityController extends BaseController{
    private $semaphore;
    private $locked;

    /*
     * $key - The mutex identifier.
     * $key can be anything that reliably casts to a string.
     */
    public function __construct($key)
    {
        // Paranoia says: Do not let the client specify the actual key.
        $key = hexdec(substr(sha1(SEM_SALT . $key, false), 0, PHP_INT_SIZE * 2 - 1));
        $this->locked = FALSE;

        if(HAVE_SYSV)
        {
            $this->semaphore = sem_get($key, 1);
        }
        else
        {
            $lockfile = SEM_DIR . "{$key}.sem";
            $this->semaphore = fopen($lockfile, 'w+');
        }
    }

    /*
     * Locks the mutex. If another thread/process has a lock on the mutex,
     * this call will block until it is unlocked.
     */
    public function lock()
    {
        if($this->locked)
        {
            trigger_error("Mutex is already locked", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            $res = sem_acquire($this->semaphore);
        else
            $res = flock($this->semaphore, LOCK_EX);

        if($res)
        {
            $this->locked = TRUE;
            return TRUE;
        }
        else
            return FALSE;
    }

    /*
     * Unlocks the mutex.
     */
    public function unlock()
    {
        if(!$this->locked)
        {
            trigger_error("Mutex is not locked", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            $res = sem_release($this->semaphore);
        else
        {
            $res = flock($this->semaphore, LOCK_UN);
        }

        if($res)
        {
            $this->locked = FALSE;
            return TRUE;
        }
        else
            return FALSE;
    }

    /*
     * Removes the mutex from the system.
     */
    public function remove()
    {
        if($this->locked)
        {
            trigger_error("Trying to delete a locked mutex", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            sem_remove($this->semaphore);
        else
            unlink($this->semaphore);
    }

    public function __destruct()
    {
        if($this->locked)
            trigger_error("Semaphore is still locked when being destructed!", E_USER_ERROR);

        if(!HAVE_SYSV)
        {
            fclose($this->semaphore);
        }
    }
}