<?php namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderData extends Mailable
{
    use Queueable, SerializesModels;

    protected $to_address;

    protected $data;

    public function __construct($to_address, $data)
    {
        $this->to_address = $to_address;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;

        return $this
            ->to($this->to_address)
            ->subject('Order Data!')
            ->view('emails.order-data')
            ->with(compact("data"));
    }
}
