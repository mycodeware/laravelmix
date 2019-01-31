<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FrontEndEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $data;

    public function __construct($data)
    {
        $this->data = (object) $data;  
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;
        return $this->view('emails.email_verification',compact('data'))
                    ->from($from_email_address, $from_name)
                   // ->cc($address, $name)
                   // ->bcc($address, $name)
                   // ->replyTo($address, $name)
                    ->subject($this->data->subject)
                    ->with([ 'message' => $this->data->message]);
    }
}
