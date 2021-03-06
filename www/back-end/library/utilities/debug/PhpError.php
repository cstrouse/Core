<?php

/**
 * Capture PHP related warnings/errors
 *
 * @package Utilities
 * @author Jete O'Keeffe 
 * @version 1.0
 * @link
 */

namespace Utilities\Debug;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class PhpError {


	/**
	 * Record any warnings/errors by php
	 *
	 * @param int		php error number
	 * @param string	php error description
	 * @param string	php file where the error occured
	 * @param int		php line where the error occured
	 */
	public static function errorHandler($errNo, $errStr, $errFile, $errLine) {

		if ($errNo != E_STRICT) {
            self::logToSyslog($errNo, $errStr, $errFile, $errLine);
		}
	}

    /**
     * Record any exception by php
     *
     * @param Void
     */
    public static function exceptionHandler($e) {
        if (!empty($e)) {
            // Record Error
            PhpError::errorHandler(0, $e->getMessage(), $e->getFile(), $e->getLine());
        }

    }


	/**
	 * Capture any errors at the end script (especially runtime errors)
	 */
	public static function runtimeShutdown() {
		$e = error_get_last();
		if (!empty($e)) {
			// Record Error
			PhpError::errorHandler($e['type'], $e['message'], $e['file'], $e['line']);
		}
	}

    /**
     * Log error to syslog
     *
	 * @param int		php error number
	 * @param string	php error description
	 * @param string	php file where the error occured
	 * @param int		php line where the error occured
     * @return bool
     */
    public static function logToSyslog($errNo, $errStr, $errFile, $errLine) {
        $msg = sprintf("%s (errno: %d) in %s:%d", $errStr, $errNo, $errFile, $errLine);

        if (openlog("php-errors", LOG_PID | LOG_PERROR, LOG_LOCAL7)) {
            syslog(LOG_ERR, $msg);
            return closelog();
        }
        return FALSE;
    }

}
