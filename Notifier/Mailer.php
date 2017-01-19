<?php

namespace Itkg\DelayEventBundle\Notifier;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Mailer
 */
class Mailer implements NotifierInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $template;

    /**
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     * @param EngineInterface     $templating
     * @param string              $from
     * @param string              $to
     * @param string              $subject
     * @param string              $template
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        $from,
        $to,
        $subject,
        $template
    ){
     $this->mailer = $mailer;
     $this->templating = $templating;
     $this->from = $from;
     $this->to = $to;
     $this->subject = $subject;
     $this->template = $template;
    }

    /**
     * @param string $channelName
     *
     * @return mixed
     */
    public function process($channelName)
    {
        $body = $this->templating->render(
            $this->template,
            ['channel' => $channelName]
        );

        $message = \Swift_Message::newInstance();
        $message->setSubject($this->subject)
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setBody($body);
        $this->mailer->send($message);
    }
}
