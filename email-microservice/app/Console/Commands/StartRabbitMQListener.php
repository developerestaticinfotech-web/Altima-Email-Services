<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\RabbitMQService;

class StartRabbitMQListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the RabbitMQ listener for email processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting RabbitMQ listener for email microservice...');
        
        try {
            $rabbitMQService = app(RabbitMQService::class);
            $this->info('RabbitMQ listener started successfully!');
            $this->info('Listening for messages on queues: email.send, email.sync.user');
            $this->info('Press Ctrl+C to stop the listener');

            // Simple loop to poll and process messages
            while (true) {
                $result = $rabbitMQService->processQueue('email.send', 50);
                $processed = $result['processed'] ?? 0;
                $success = $result['success'] ?? 0;
                $failed = $result['failed'] ?? 0;
                $this->line("Processed: {$processed} | Success: {$success} | Failed: {$failed}");
                // small delay to avoid tight loop when queue is empty
                sleep(2);
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to start RabbitMQ listener: ' . $e->getMessage());
            Log::error('RabbitMQ listener failed to start: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
