<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class   Common extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datas = $this->data;
        $module = $datas['module'];
        $settings = $datas['settings'];
        $subject = $datas['subject'];
        if (!empty($module) && $module == 'owner_create') {
            $datas = [
                'company_logo' => $settings['company_logo'],
                'message' => $this->data,
                'company_name' => $settings['company_name'],
                'company_email' => $settings['company_email'],
            ];
        } else {

            $datas = [
                'company_logo' => $settings['company_logo'],
                'company_name' => $settings['company_name'],
                'message' => $datas['message'],
            ];
        }
        if (!empty($module) && in_array($module, ['user_create', 'tenant_create', 'maintainer_create', 'maintenance_request_create', 'maintenance_request_complete', 'payment_reminder', 'invoice_create'])) {
            return $this->from($settings['FROM_EMAIL'], $settings['FROM_NAME'])->markdown('email.email_notification')->subject($subject)->with('data', $datas);
        } elseif (!empty($module) && $module == 'owner_create') {
            return  $this->from($settings['FROM_EMAIL'], $settings['FROM_NAME'])
                ->markdown('email.owner_create')
                ->subject($subject)
                ->with('content', $datas);
        }
    }
}
