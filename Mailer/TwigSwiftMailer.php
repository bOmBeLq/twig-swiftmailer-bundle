<?php

namespace Bml\TwigSwiftMailerBundle\Mailer;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $oldLocale;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param string|array $fromEmail
     * @param string|array $toEmail
     * @param string $locale if you wish to setup locale use this parameter
     * @return bool
     * @throws MailerException
     */
    public function sendMessage($templateName, array $context, $fromEmail, $toEmail, $locale = null)
    {
        $this->setLocale($locale);
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


        if (($result = $this->mailer->send($message)) === false) {
            $this->revertLocale();
            throw new MailerException('Error occurred while sending emails');
        }
        $this->revertLocale();
        return $result;
    }

    /**
     * @param $templateName
     * @param array $context
     * @param $fromEmail
     * @param RecipientInterface $recipient
     * @return bool
     */
    public function sendToRecipient($templateName, array $context, $fromEmail, RecipientInterface $recipient)
    {
        return $this->sendMessage($templateName, $context, $fromEmail, $recipient->getEmail(), $recipient->getLocale());
    }

    /**
     * @param $locale
     */
    private function setLocale($locale = null)
    {
        $this->oldLocale = $this->translator->getLocale();
        if ($locale) {
            $this->translator->setLocale($locale);
        }
        $this->setRouterLocale();
    }


    private function revertLocale()
    {
        if ($this->oldLocale) {
            $this->setLocale($this->oldLocale);
            $this->oldLocale = null;
        }
    }

    private function setRouterLocale()
    {
        $this->router->getContext()->setParameter('_locale', $this->translator->getLocale()); // @todo locale is not set in commands, is this SF BUG? may investigate
    }
}
