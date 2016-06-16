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
     * @throws \Exception
     */
    public function sendMessage($templateName, $context, $fromEmail, $toEmail, $locale = null)
    {
        if ($locale) {
            $oldLocale = $this->translator->getLocale();
            $this->translator->setLocale($locale);
        }
        $this->setRouterLocale();
        try {
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
        } catch (\Exception $e) {
            if (isset($oldLocale)) {
                $this->translator->setLocale($oldLocale);
                $this->setRouterLocale();
            }
            throw $e;
        }
        if (($result = $this->mailer->send($message)) === false) {
            throw new MailerException('Error occurred while sending emails');
        }
        return $result;
    }


    private function setRouterLocale()
    {
        $this->router->getContext()->setParameter('_locale', $this->translator->getLocale()); // @todo locale is not set in commands, is this SF BUG? may investigate
    }
}
