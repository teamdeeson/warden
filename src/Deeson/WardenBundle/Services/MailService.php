<?php

namespace Deeson\WardenBundle\Services;

class MailService {

  /**
   * @var \Swift_Mailer
   */
  protected $mailer;

  /**
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * @var array
   */
  protected $errors;

  public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig) {
    $this->mailer = $mailer;
    $this->twig = $twig;
  }

  /**
   * Send email
   *
   * @param   string $template  email template
   * @param   mixed $parameters custom params for template
   * @param   string $to        to email address or array of email addresses
   * @param   string $from      from email address
   * @param   string $fromName  from name
   *
   * @return  boolean           send status
   */
  public function sendEmail($template, $parameters, $to, $from, $fromName = NULL) {
    $template = $this->twig->load('DeesonWardenBundle:Mail:' . $template . '.html.twig');

    $subject = $template->renderBlock('subject', $parameters);
    $bodyHtml = $template->renderBlock('body_html', $parameters);
    $bodyText = $template->renderBlock('body_text', $parameters);

    try {
      /** @var \Swift_Mime_Message $message */
      $message = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom($from, $fromName)
        ->setTo($to)
        ->addPart($bodyText, 'text/plain')
        ->setBody($bodyHtml, 'text/html');
      $response = $this->mailer->send($message);
    }
    catch (\Exception $e) {
      $this->addError($e->getMessage());
      return false;
    }

    return $response;
  }

  /**
   * Get any errors that might have been generated.
   *
   * @return string
   */
  public function getErrors() {
    return implode(', ', $this->getErrors());
  }

  /**
   * Add an error.
   *
   * @param string $error
   */
  protected function addError($error) {
    $this->errors[] = $error;
  }
}
