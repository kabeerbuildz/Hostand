<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'module',
        'subject',
        'message',
        'enabled_email',
        'enabled_sms',
        'parent_id',
    ];

    static $modules = [
        'user_create' =>
        [
            'name' => 'New User',
            'translation_key' => 'New User',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}'],
            'subject' => 'Welcome to {company_name}!',
            'subject_translation_key' => 'Welcome to {company_name}!',
            'templete' => '
            <p><strong>Dear {new_user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p>',
        ],
        'tenant_create' =>
        [
            'name' => 'New Tenant',
            'translation_key' => 'New Tenant',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}'],
            'subject' => 'Welcome to {company_name}!',
            'subject_translation_key' => 'Welcome to {company_name}!',
            'templete' => '
              <p><strong>Dear {user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p>',
        ],
        'maintainer_create' =>
        [
            'name' => 'New Maintainer',
            'translation_key' => 'New Maintainer',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}'],
            'subject' => 'Welcome to {company_name}!',
            'subject_translation_key' => 'Welcome to {company_name}!',
            'templete' => '
              <p><strong>Dear {user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p>',
        ],
        'maintenance_request_create' =>
        [
            'name' => 'New Maintenance Request',
            'translation_key' => 'New Maintenance Request',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{created_at}', '{issue_type}', '{issue_description}', '{tenant_number}', '{tenant_mail}'],
            'subject' => 'New Maintenance Request Created',
            'subject_translation_key' => 'New Maintenance Request Created',
            'templete' => '
                <p><strong class="ql-size-huge">Dear Owner,</strong></p><p>We would like to inform you that a new maintenance request has been created for your property. Below are the details of the request:</p><p><strong>Request Details:</strong></p><ul><li>	Submitted By: {tenant_name}</li><li>	Submitted On: {created_at}</li><li>	Category: {issue_type}</li><li>	Description: {issue_description}</li></ul><p><br></p><p><strong>Tenant Contact Information:</strong></p><ul><li>	Name: {tenant_name}</li><li>	Phone: {tenant_number}</li><li>	Email: {tenant_mail}</li></ul><p>Thank you for your attention to this matter.</p><p><strong>Best regards,</strong></p><p><strong>{tenant_name}</strong></p><p><strong>{tenant_mail}</strong></p>
            ',
        ],
        'maintenance_request_complete' =>
        [
            'name' => 'Maintenance Request Complete',
            'translation_key' => 'Maintenance Request Complete',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{submitted_at}', '{issue_type}', '{issue_description}', '{completed_at}', '{maintainer_email}', '{maintainer_number}', '{maintainer_name}'],
            'subject' => 'Maintenance Request Completed!',
            'subject_translation_key' => 'Maintenance Request Completed!',
            'templete' => '
                <p><strong>Dear {tenant_name},</strong></p><p><br></p><p>We are pleased to inform you that your maintenance request has been successfully completed.</p><p><br></p><p> <strong>Request Details:</strong></p><ul><li>Submitted By: {tenant_name}</li><li>Submitted On: {created_at}</li><li>Category: {issue_type}</li><li>Description: {issue_description}</li></ul><p><br></p><p><strong>Completion Details:</strong></p><ul><li> Completed On: {completed_at}</li> </ul><p><br></p><p><strong>Feedback:</strong></p><p>We value your feedback to improve our services. Please let us know if you are satisfied with the maintenance performed or if there are any further issues that need attention.</p><p><br></p><p><strong>Contact Information:</strong></p><p> If you have any questions or need further assistance, please contact us at {maintainer_email} or {maintainer_number}.</p><p>Thank you for your cooperation and patience.</p><p><br></p><p><strong>Best regards,</strong></p><p>{maintainer_name}</p><p>{maintainer_email}</p>
            ',
        ],
        'invoice_create' =>
        [
            'name' => 'New Invoice',
            'translation_key' => 'New Invoice',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{invoice_description}', '{amount}'],
            'subject' => 'Invoice Created',
            'subject_translation_key' => 'Invoice Created',
            'templete' => '
                    <p><strong>Dear {user_name},</strong></p><p><br></p><p> We are pleased to inform you that an invoice has been created  with {company_name}.</p><p><br></p><p><strong> Invoice Details:</strong></p><ul><li>Invoice Number: {invoice_number}</li><li>Date Issued: {invoice_date}</li><li>Due Date: {invoice_due_date}</li><li>Amount Due: {amount}</li><li>Description: {invoice_description}</li></ul><p><br></p><p><strong>Contact Information:</strong></p><p>If you have any questions or need further assistance, please contact our billing department at {company_email} or {company_number}.</p><p><br></p><p>Thank you for your prompt attention to this matter.</p><p><br></p><p><strong>Best regards,</strong></p><p><strong>{company_name}</strong></p><p><strong>{company_name}</strong></p>
            ',
        ],
        'payment_reminder' =>
        [
            'name' => 'Payment Reminder',
            'translation_key' => 'Payment Reminder',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}'],
            'subject' => 'Friendly Reminder: Payment Due Soon',
            'subject_translation_key' => 'Friendly Reminder: Payment Due Soon',
            'templete' => '
                <p><strong>Dear {user_name},</strong></p><p><br></p><p> This is a friendly reminder that your payment for the following invoice is due soon:</p><p><br></p><p><strong>Invoice Details:</strong></p><ul><li>Invoice Number: {invoice_number}</li><li>Date Issued: {invoice_date}</li><li>Due Date: {invoice_due_date}</li><li>Amount Due: {amount}</li><li>Description: {invoice_description}</li></ul><p><br></p><p><br></p><p>If you have already made your payment, please disregard this notice. Otherwise, we appreciate your prompt attention to this matter.</p><p><br></p><p><strong>Contact Information:</strong></p><p>If you have any questions or need assistance, feel free to contact our billing department at {company_email} or {company_number}.</p><p><br></p><p>Thank you for your cooperation!</p><p><br></p><p><strong>Best regards,</strong></p><p><strong>{company_name}</strong></p><p><strong>{company_email}</strong></p>
            ',
        ],
    ];

    /**
     * Get the translated name for a notification module
     *
     * @param string $module
     * @return string
     */
    public static function getTranslatedName($module)
    {
        if (isset(self::$modules[$module]['translation_key'])) {
            return __(self::$modules[$module]['translation_key']);
        }
        
        // Fallback to the original name if translation key doesn't exist
        return isset(self::$modules[$module]['name']) ? self::$modules[$module]['name'] : $module;
    }

    /**
     * Get the translated name for the current notification
     *
     * @return string
     */
    public function getTranslatedNameAttribute()
    {
        return self::getTranslatedName($this->module);
    }

    /**
     * Get the translated subject for a notification module
     *
     * @param string $module
     * @return string
     */
    public static function getTranslatedSubject($module)
    {
        if (isset(self::$modules[$module]['subject_translation_key'])) {
            return __(self::$modules[$module]['subject_translation_key']);
        }
        
        // Fallback to the original subject if translation key doesn't exist
        return isset(self::$modules[$module]['subject']) ? self::$modules[$module]['subject'] : '';
    }

    /**
     * Get the translated subject for the current notification
     *
     * @return string
     */
    public function getTranslatedSubjectAttribute()
    {
        return self::getTranslatedSubject($this->module);
    }
}
