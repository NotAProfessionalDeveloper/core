<?php

namespace atk4\core;

trait DebugTrait
{
    /**
     * Check this property to see if trait is present in the object.
     *
     * @var bool
     */
    public $_debugTrait = true;

    /** @var bool Is debug enabled? */
    public $debug = null;

    /**
     * Outputs message to STDERR.
     */
    protected function _echo_stderr($message)
    {
        fwrite(STDERR, $message);
    }

    /**
     * Send some info to debug stream.
     *
     * @param bool  $message
     * @param array $context
     *
     * @return $this
     */
    public function debug($message = true, $context = [])
    {
        if (is_bool($message)) {
            // using this to switch on/off the debug for this object
            $this->debug = $message;

            return $this;
        }

        if ($this->debug) {
            if (isset($this->app) && $this->app instanceof \Psr\Log\LoggerInterface) {
                $this->app->log('debug', $message, $context);
            } else {
                $this->_echo_stderr('['.get_class($this)."]: $message\n");
            }
        }

        return $this;
    }

    /**
     * Output log.
     *
     * @param bool  $message
     * @param array $context
     *
     * @return $this
     */
    public function log($level, $message, $context = [])
    {
        if (isset($this->app) && $this->app instanceof \Psr\Log\LoggerInterface) {
            $this->app->log($level, $message, $context);
        } else {
            $this->_echo_stderr("$message\n");
        }

        return $this;
    }

    /**
     * Output message that needs to be acknowledged by application user. Make sure
     * that $context does not contain any sensitive information.
     *
     * @param bool  $message
     * @param array $context
     *
     * @return $this
     */
    public function userMessage($message, $context = [])
    {
        if (isset($this->app) && $this->app instanceof \atk4\core\AppUserNotificationInterface) {
            $this->app->userNotification($message, $context);
        } elseif (isset($this->app) && $this->app instanceof \Psr\Log\LoggerInterface) {
            $this->app->log('warning', 'Could not notify user about: '.$message, $context);
        } else {
            $this->_echo_stderr("Could not notify user about: $message\n");
        }

        return $this;
    }

    public $_prev_bt = [];

    public function debugTraceChange($trace = 'default')
    {
        if ($this->isDebugEnabled()) {
            $bt = [];
            foreach (debug_backtrace() as $line) {
                if (isset($line['file'])) {
                    $bt[] = $line['file'].':'.$line['line'];
                }
            }

            if (isset($this->_prev_bt[$trace]) && array_diff($this->_prev_bt[$trace], $bt)) {
                $d1 = array_diff($this->_prev_bt[$trace], $bt);
                $d2 = array_diff($bt, $this->_prev_bt[$trace]);

                $this->debug('Call path for '.$trace.' has diverged (was '.implode(', ', $d1).', now '.implode(', ', $d2).")\n");
            }

            $this->_prev_bt[$trace] = $bt;
        }
    }
}
