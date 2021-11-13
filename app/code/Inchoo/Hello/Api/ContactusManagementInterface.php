<?php namespace Inchoo\Hello\Api;

/**
 * Interface ContactusManagementInterface
 *
 * @package Inchoo\Hello\Api
 */
interface ContactusManagementInterface
{
    /**
     * Contact us form.
     *
     * @param mixed $contactForm
     *
     * @return \Inchoo\Hello\Api\Data\ContactusInterface
     */
    public function submitForm($contactForm);
}