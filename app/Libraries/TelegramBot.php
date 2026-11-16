<?php

namespace App\Libraries;

/**
 * Sends free Telegram messages to staff (drivers, technicians, supervisors).
 *
 * .env settings (never committed):
 *   telegram.botToken    = 123456:ABC...   (from @BotFather)
 *   telegram.botUsername = FU_UBRA_bot      (without the @)
 *
 * Linking: a person opens the bot and sends   /start <their employee ID>   once.
 * "Sync" reads those messages (getUpdates) and saves each person's chat id, so no public webhook is needed.
 */
class TelegramBot
{
    public function isConfigured(): bool { return $this->token() !== ''; }
    public function username(): string   { return ltrim((string) env('telegram.botUsername', ''), '@'); }

    /** @return array{ok:bool,message:string} */
    public function send(string $chatId, string $text): array
    {
        if (!$this->isConfigured()) return ['ok' => false, 'message' => 'Telegram is not set up yet. Add telegram.botToken to the .env file.'];
        if (trim($chatId) === '')   return ['ok' => false, 'message' => 'This person has not linked Telegram yet.'];
        $r = $this->call('sendMessage', ['chat_id' => $chatId, 'text' => $text]);
        if ($r['ok']) return ['ok' => true, 'message' => 'Telegram message sent.'];
        return ['ok' => false, 'message' => 'Telegram refused the message: ' . ($r['description'] ?? 'unknown error')];
    }

    /**
     * Reads new messages sent to the bot and returns [employeeId => chatId] for every "/start <employee id>".
     * Confirms each linked person with a short reply.
     * @return array{ok:bool,message:string,links:array<string,string>}
     */
    public function collectLinks(): array
    {
        if (!$this->isConfigured()) return ['ok' => false, 'message' => 'Telegram is not set up yet. Add telegram.botToken to the .env file.', 'links' => []];
        $r = $this->call('getUpdates', ['timeout' => 0, 'limit' => 100]);
        if (!$r['ok']) return ['ok' => false, 'message' => 'Could not read Telegram: ' . ($r['description'] ?? 'unknown error'), 'links' => []];

        $links = [];
        foreach (($r['result'] ?? []) as $u) {
            $m = $u['message'] ?? null;
            if (!$m || !isset($m['chat']['id'], $m['text'])) continue;
            if (preg_match('/^\/start(?:@\w+)?\s+([A-Za-z0-9\-]{3,40})\s*$/', trim($m['text']), $mm)) {
                $links[$mm[1]] = (string) $m['chat']['id'];
            }
        }
        return ['ok' => true, 'message' => 'OK', 'links' => $links];
    }

    /** Saves the chat id of everyone who has sent "/start <employee id>" to the bot. Returns how many were newly linked. */
    public function syncLinks(): int
    {
        $sync = $this->collectLinks();
        if (!$sync['ok']) return 0;
        $model = new \App\Models\PersonnelModel();
        $n = 0;
        foreach ($sync['links'] as $empId => $chatId) {
            $p = $model->where('emp_id', $empId)->first();
            if ($p && ($p['telegram_chat_id'] ?? '') !== $chatId) {
                $model->update($p['id'], ['telegram_chat_id' => $chatId]);
                $this->send($chatId, 'FU-UBRA: your Telegram is now linked, ' . $p['full_name'] . '. You will get alerts here.');
                $n++;
            }
        }
        return $n;
    }

    /** Sends a Telegram alert to a personnel row (array). @return array{ok:bool,message:string} */
    public function notifyPerson(array $person, string $text): array
    {
        if (!$this->isConfigured()) return ['ok' => false, 'message' => 'Telegram is not set up.'];
        if (trim((string) ($person['telegram_chat_id'] ?? '')) === '') {
            $this->syncLinks();
            $fresh = (new \App\Models\PersonnelModel())->find($person['id']);
            $person['telegram_chat_id'] = $fresh['telegram_chat_id'] ?? '';
        }
        if (trim((string) ($person['telegram_chat_id'] ?? '')) === '') {
            $botName = $this->username();
            return ['ok' => false, 'message' => $person['full_name'] . ' has not linked Telegram yet (open t.me/' . $botName . ', press Start, send /start ' . $person['emp_id'] . ').'];
        }
        return $this->send($person['telegram_chat_id'], $text);
    }

    private function token(): string { return (string) env('telegram.botToken', ''); }

    private function call(string $method, array $params): array
    {
        $ch = curl_init('https://api.telegram.org/bot' . $this->token() . '/' . $method);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($resp === false) return ['ok' => false, 'description' => 'no internet connection (' . $err . ')'];
        $j = json_decode($resp, true);
        return is_array($j) ? $j : ['ok' => false, 'description' => 'unreadable answer from Telegram'];
    }
}
