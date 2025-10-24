<?php

namespace App\Mail;

use App\Models\Faq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FaqAnswerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $faq;

    public function __construct(Faq $faq)
    {
        $this->faq = $faq;
    }

    public function build()
    {
        return $this->subject('Trả lời câu hỏi từ Pizza Shop')
            ->view('emails.faq_answer_email')
            ->with(['faq' => $this->faq]);
    }
}