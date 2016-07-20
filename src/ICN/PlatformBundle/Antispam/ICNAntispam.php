<?php
/**
 * Created by PhpStorm.
 * User: ibart
 * Date: 12/07/2016
 * Time: 11:47
 */

namespace ICN\PlatformBundle\Antispam;


class ICNAntispam
{
    private $mailer;
    private $locale;
    private $minLength;

    public function __construct(\Swift_Mailer $mail, $locale, $minLength)
    {
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->minLength = (int) $minLength;
    }

    public function isSpam($text)
    {
        return strlen($text) < $this->minLength;
    }
}