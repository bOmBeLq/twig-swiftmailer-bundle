<?php

namespace Bml\Mailer;

/**
 * @author Damian Wróblewski
 */
class TwigSwiftMailer
{

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    function __construct(\Twig_Environment $twig,\Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param string $fromEmail
     * @param string $toEmail
     * @return bool
     */
    public function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance();
        $message->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html');
            $message->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        return $this->mailer->send($message) > 0;
    }
}