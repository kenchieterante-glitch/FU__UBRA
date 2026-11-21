<?php

namespace App\Libraries;

/**
 * Sends SMS through "SMS Gateway for Android" (free, open source) installed on a phone that has a SIM card.
 * Works with any of its three modes - only the URL in .env changes:
 *
 *   Public cloud server (free, no same-Wi-Fi needed; phone and this server just need internet):
 *     sms.gatewayUrl = https://api.sms-gate.app/3rdparty/v1
 *   Your own private server (self-hosted copy of the gateway server):
 *     sms.gatewayUrl = https://your-server.example.com/api/3rdparty/v1
 *   Local server (phone on the same Wi-Fi):
 *     sms.gatewayUrl = http://192.168.1.50:8080
 *
 *   sms.username / sms.password = the credentials shown in the app for the mode you chose.
 * Settings live in .env (never in git).
 */
class SmsGateway
{
    public function isConfigured(): bool
    {
        return $this->url() !== '' && $this->user() !== '' && $this->pass() !== '';
    }

    /** 09171234567 / 0917 123 4567 / +639171234567 -> +639171234567, or null when it does not look like a PH mobile number. */
    public static function normalize(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if (preg_match('/^09\d{9}$/', $d))  return '+63' . substr($d, 1);
        if (preg_match('/^639\d{9}$/', $d)) return '+' . $d;
        if (preg_match('/^9\d{9}$/', $d))   return '+63' . $d;
        return null;
    }

    /**
     * The one call the rest of the system uses. Texts a person and records it in the SMS outbox.
     * - gateway configured in .env  -> really sends, outbox mode = Sent / Failed
     * - nothing configured          -> Simulated: nothing leaves the server, the outbox shows exactly what would be sent
     * - person has no valid number  -> logged as "No number" so the admin sees who could not be reached
     * @return array{ok:bool,message:string,mode:string}
     */
    public function deliver(?int $personnelId, string $name, ?string $phone, string $text, string $trigger = 'Manual'): array
    {
        $to = self::normalize($phone);
        if ($to === null) {
            $this->log($personnelId, $name, $phone, $text, $trigger, 'No number', 'No valid mobile number on file.');
            return ['ok' => false, 'mode' => 'No number', 'message' => "{$name} has no valid mobile number saved, so no text was made."];
        }
        if (!$this->isConfigured()) {
            $this->log($personnelId, $name, $to, $text, $trigger, 'Simulated', 'No SMS gateway connected: nothing was sent.');
            return ['ok' => true, 'mode' => 'Simulated', 'message' => "Simulated: this text to {$to} was saved in the SMS Outbox (nothing was sent to a phone)."];
        }
        $r = $this->send($to, $text);
        $this->log($personnelId, $name, $to, $text, $trigger, $r['ok'] ? 'Sent' : 'Failed', $r['message']);
        return $r + ['mode' => $r['ok'] ? 'Sent' : 'Failed'];
    }

    private function log(?int $personnelId, string $name, ?string $phone, string $text, string $trigger, string $mode, string $detail): void
    {
        try {
            db_connect()->table('sms_outbox')->insert([
                'personnel_id'   => $personnelId,
                'recipient_name' => $name,
                'phone'          => $phone,
                'message'        => $text,
                'trigger_label'  => $trigger,
                'mode'           => $mode,
                'detail'         => mb_substr($detail, 0, 250),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'SMS outbox log failed: ' . $e->getMessage());
        }
    }

    /** @return array{ok:bool,message:string} */
    public function send(string $phone, string $text): array
    {
        $to = self::normalize($phone);
        if ($to === null) return ['ok' => false, 'message' => 'That is not a valid Philippine mobile number.'];
        if (!$this->isConfigured()) return ['ok' => false, 'message' => 'SMS gateway is not set up yet. Add sms.gatewayUrl, sms.username and sms.password to the .env file.'];

        // Current API wraps the text in "textMessage"; older app versions expect a flat "message". Try the current form first.
        $bodies = [
            ['textMessage' => ['text' => $text], 'phoneNumbers' => [$to]],
            ['message' => $text, 'phoneNumbers' => [$to]],
        ];
        $last = 'Could not reach the SMS gateway.';
        foreach ($bodies as $body) {
            $ch = curl_init($this->endpoint());
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($body),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_USERPWD        => $this->user() . ':' . $this->pass(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT        => 10,
            ]);
            $resp = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($resp === false) { $last = 'Could not reach the SMS gateway (' . $err . '). Check your internet connection and the gateway URL in .env.'; break; }
            if ($code >= 200 && $code < 300) return ['ok' => true, 'message' => "Sent to {$to}. The gateway accepted it; check the recipient's phone."];
            if ($code === 401 || $code === 403) { $last = 'The gateway refused the username or password.'; break; }
            $last = "The gateway answered with error {$code}.";
        }
        return ['ok' => false, 'message' => $last];
    }

    /** Base URL + /message (unless the URL already ends with it). */
    private function endpoint(): string
    {
        $u = rtrim($this->url(), '/');
        return preg_match('#/message$#', $u) ? $u : $u . '/message';
    }

    private function url(): string  { return (string) env('sms.gatewayUrl', ''); }
    private function user(): string { return (string) env('sms.username', ''); }
    private function pass(): string { return (string) env('sms.password', ''); }
}
