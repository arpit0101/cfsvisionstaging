<?php namespace Inchoo\Hello\Api\Data;

/**
 * ContactusInterface interface
 *
 * @api
 * @since 100.0.2
 */
interface ContactusInterface
{
    /**
    * @return \Inchoo\Hello\Api\Data\ContactusInterface[]
     */
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);
}