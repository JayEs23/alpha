<?php

namespace App\Console\Commands;

use App\Models\AssetServiceReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendAssetServiceRemindersCommand extends Command
{
    protected $signature = 'assets:send-service-reminders';

    protected $description = 'Send due service reminders and mark them as sent';

    public function handle(): int
    {
        $sent = 0;

        AssetServiceReminder::query()
            ->whereNull('sent_at')
            ->where('remind_at', '<=', now())
            ->with(['recipient'])
            ->chunkById(100, function ($reminders) use (&$sent): void {
                foreach ($reminders as $reminder) {
                    if (! $reminder->recipient_user_id) {
                        continue;
                    }

                    DB::table('notifications')->insert([
                        'id' => (string) Str::uuid(),
                        'type' => 'asset.service.reminder',
                        'notifiable_type' => 'App\\Models\\User',
                        'notifiable_id' => $reminder->recipient_user_id,
                        'data' => json_encode([
                            'service_task_id' => $reminder->service_task_id,
                            'message' => $reminder->message ?? 'Recurring service task is due.',
                        ], JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $reminder->sent_at = now();
                    $reminder->save();
                    $sent++;
                }
            });

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
