<?php

namespace WHMCS\Module\Notification\Telegram;

use WHMCS\Config\Setting;
use WHMCS\Exception;
use WHMCS\Module\Notification\DescriptionTrait;
use WHMCS\Module\Contracts\NotificationModuleInterface;
use WHMCS\Notification\Contracts\NotificationInterface;

class Telegram implements NotificationModuleInterface
{
    use DescriptionTrait;

    protected $moduleSettings;

    public function __construct()
    {
        $this->setDisplayName('Telegram')
            ->setLogoFileName('logo.png');
    }

    public function settings()
    {
        return [
            'botToken' => [
                'FriendlyName' => 'Bot Token',
                'Type' => 'text',
                'Description' => 'توکن ربات تلگرام',
            ],
            'botChatID' => [
                'FriendlyName' => 'Chat ID(s)',
                'Type' => 'textarea',
                'Description' => 'می‌توانید چند شناسه چت را با کاما جدا کنید',
            ],
            'apiHost' => [
                'FriendlyName' => 'Telegram API Host',
                'Type' => 'text',
                'Default' => 'https://api.telegram.org',
                'Description' => '(اختیاری) آدرس API برای استفاده از پروکسی یا نسخه‌های شخصی‌سازی‌شده',
                'Placeholder' => 'https://api.telegram.org',
            ],
            'parseMode' => [
                'FriendlyName' => 'Parse Mode',
                'Type' => 'dropdown',
                'Options' => [
                    'Markdown' => 'Markdown',
                    'HTML' => 'HTML',
                    'None' => 'Plain Text'
                ],
                'Description' => 'نوع قالب‌بندی پیام',
            ],
            'prefixText' => [
                'FriendlyName' => 'Prefix',
                'Type' => 'textarea',
                'Description' => 'متنی که به ابتدای هر پیام افزوده می‌شود',
            ],
            'suffixText' => [
                'FriendlyName' => 'Suffix',
                'Type' => 'textarea',
                'Description' => 'متنی که به انتهای هر پیام افزوده می‌شود',
            ],
            'logEnabled' => [
                'FriendlyName' => 'Enable Logging?',
                'Type' => 'yesno',
                'Description' => 'ذخیره لاگ پیام‌های ارسال‌شده در لاگ WHMCS',
            ],
        ];
    }

    public function testConnection($settings)
    {
        $this->moduleSettings = $settings;
        $this->sendMessage("🤖 اتصال با WHMCS با موفقیت برقرار شد.");
    }

    public function notificationSettings()
    {
        return [];
    }

    public function getDynamicField($fieldName, $settings)
    {
        return [];
    }

    public function sendNotification(NotificationInterface $notification, $moduleSettings, $notificationSettings)
    {
        $this->moduleSettings = $moduleSettings;

        $title = $notification->getTitle();
        $message = $notification->getMessage();
        $url = $notification->getUrl();

        $fullMessage = trim(
            ($moduleSettings['prefixText'] ?? '') . "\n" .
                "*{$title}*\n\n{$message}\n\n[باز کردن »]({$url})\n" .
                ($moduleSettings['suffixText'] ?? '')
        );

        $this->sendMessage($fullMessage);
    }

    private function sendMessage($messageText)
    {
        $token = $this->moduleSettings['botToken'];
        $chatIds = explode(',', $this->moduleSettings['botChatID']);
        $host = !empty($this->moduleSettings['apiHost']) ? rtrim($this->moduleSettings['apiHost'], '/') : 'https://api.telegram.org';
        $parseMode = $this->moduleSettings['parseMode'] ?? 'Markdown';

        foreach ($chatIds as $chatId) {
            $chatId = trim($chatId);
            if (empty($chatId)) continue;

            $url = "{$host}/bot{$token}/sendMessage";
            $data = [
                'chat_id' => $chatId,
                'text' => $messageText,
            ];

            if ($parseMode !== 'None') {
                $data['parse_mode'] = $parseMode;
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if (!$response) {
                throw new Exception("Telegram API Error: " . $error);
            }

            if (!empty($this->moduleSettings['logEnabled'])) {
                logActivity("Telegram Notification Sent to {$chatId}: " . $messageText);
            }
        }
    }
}
