<?php


namespace Bml\TwigSwiftMailerBundle\Mailer;

/**
 * @author Damian Wróblewski
 */
interface RecipientInterface
{
    /**
     * @return string return null if you do not support locales
     */
    public function getLocale();

    /**
     * @return string recipients email
     */
    public function getEmail();
}
