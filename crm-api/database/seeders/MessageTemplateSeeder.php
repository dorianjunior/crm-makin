<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoCompany = Company::where('name', 'Demo Company')->first();

        $templates = [
            [
                'type' => 'email',
                'title' => 'Welcome Email',
                'content' => "Hi {{name}},\n\nWelcome to our platform! We're excited to have you on board.\n\nBest regards,\n{{company_name}}",
            ],
            [
                'type' => 'email',
                'title' => 'Follow Up',
                'content' => "Hi {{name}},\n\nI wanted to follow up on our previous conversation. Do you have any questions?\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'type' => 'email',
                'title' => 'Proposal Sent',
                'content' => "Hi {{name}},\n\nThank you for your interest. Please find attached our proposal.\n\nLooking forward to working with you!\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'type' => 'whatsapp',
                'title' => 'Quick Follow Up',
                'content' => "Hi {{name}}! 👋\n\nJust checking in to see if you had any questions about our services.\n\nFeel free to reach out anytime!",
            ],
            [
                'type' => 'whatsapp',
                'title' => 'Meeting Reminder',
                'content' => "Hi {{name}}! 📅\n\nReminder: We have a meeting scheduled for {{meeting_date}} at {{meeting_time}}.\n\nSee you soon!",
            ],
            [
                'type' => 'whatsapp',
                'title' => 'Thank You',
                'content' => "Hi {{name}}! 🙏\n\nThank you for choosing us! We're looking forward to working together.\n\nIf you need anything, just let me know!",
            ],
            [
                'type' => 'email',
                'title' => 'Meeting Confirmation',
                'content' => "Hi {{name}},\n\nThis is to confirm our meeting on {{meeting_date}} at {{meeting_time}}.\n\nLocation: {{location}}\n\nLooking forward to meeting you!\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'type' => 'email',
                'title' => 'Thank You for Your Purchase',
                'content' => "Hi {{name}},\n\nThank you for your purchase! Your order has been confirmed.\n\nOrder details:\n{{order_details}}\n\nBest regards,\n{{company_name}}",
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::create([
                'company_id' => $demoCompany->id,
                'type' => $template['type'],
                'title' => $template['title'],
                'content' => $template['content'],
            ]);
        }

        $this->command->info('Message templates created successfully!');
    }
}
