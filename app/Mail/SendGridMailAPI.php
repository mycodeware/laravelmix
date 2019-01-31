<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;
use Request;
use Response;

class SendGridMailAPI
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $data;
    public $template;

    public function __construct($data,$template)
    {
        $this->data = $data;
        $this->sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function send()
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($this->data->from_email_address, $this->data->from_name);
        $email->setSubject($this->data->subject);
        $email->addTo( $this->data->to_email, $this->data->to_name);

        $html = view::make('emails.' . $this->template, ['data' => $this->data]);
        $html = $html->render();

        $email->addContent(
            "text/html", $html
        );
        try{
            $this->sendgrid->send($email);
            return true;
        }catch(\Throwable $ex){
            return false;
        }
    }
}
