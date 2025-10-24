<?php

namespace App\Mail;

use App\Models\Faq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FaqConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $faq;

    public function __construct(Faq $faq)
    {
        $this->faq = $faq;
    }

    public function build()
    {
        return $this->subject('Xác nhận câu hỏi từ Pizza Shop')
            ->view('emails.faq_confirmation_email')
            ->with(['faq' => $this->faq]);
    }
}