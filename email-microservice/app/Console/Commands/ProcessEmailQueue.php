<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Models\Outbox;
use App\Models\EmailProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:listen-queue {--queue=email.send} {--max-workers=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously listen to RabbitMQ queue and process emails in real-time';

    protected $rabbitMQService;
    protected $isRunning = true;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queueName = $this->option('queue');
        $maxWorkers = $this->option('max-workers');
        
        $this->info("🚀 Starting Email Queue Listener...");
        $this->info("📧 Queue: {$queueName}");
        $this->info("👥 Max Workers: {$maxWorkers}");
        $this->info("⏰ Mode: Continuous Real-time Processing");
        $this->info("💡 Press Ctrl+C to stop the listener");
        
        // Set up signal handler for graceful shutdown
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        
        try {
            while ($this->isRunning) {
                // Process messages continuously
                $this->processQueueMessages($queueName);
                
                // Small delay to prevent CPU spinning
                usleep(100000); // 0.1 second
                
                // Process signals
                pcntl_signal_dispatch();
            }
        } catch (\Exception $e) {
            $this->error("💥 Exception during queue listening: " . $e->getMessage());
            Log::error('Exception during email queue listening', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->info("🏁 Email queue listener stopped.");
    }
    
    /**
     * Process messages from the queue
     */
    protected function processQueueMessages($queueName)
    {
        try {
            // Check if there are messages in the queue
            $queueStatus = $this->rabbitMQService->getQueueStatus();
            
            if (isset($queueStatus[$queueName]) && $queueStatus[$queueName]['message_count'] > 0) {
                $this->info("📨 Processing messages from queue: {$queueName}");
                
                // Process messages (one at a time for real-time processing)
                $result = $this->rabbitMQService->processRealQueue(1);
                
                if ($result['success']) {
                    $this->info("✅ Processed message successfully");
                    if (isset($result['processed']) && $result['processed'] > 0) {
                        $this->info("📧 Email sent via provider");
                    }
                } else {
                    $this->warn("⚠️ Message processing failed: " . ($result['error'] ?? 'Unknown error'));
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error processing queue: " . $e->getMessage());
            Log::error('Error processing email queue', [
                'error' => $e->getMessage(),
                'queue' => $queueName
            ]);
        }
    }
    
    /**
     * Handle shutdown signals
     */
    public function shutdown()
    {
        $this->info("\n🛑 Shutdown signal received. Stopping queue listener...");
        $this->isRunning = false;
    }
}
