<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailFetcherService;
use App\Models\EmailProvider;

class FetchInboundEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:fetch-inbound 
                            {--provider= : Specific provider ID to fetch from}
                            {--limit=50 : Maximum number of emails to fetch per provider}
                            {--dry-run : Show what would be fetched without actually processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch inbound emails from all active email providers via IMAP/POP3';

    protected $emailFetcherService;

    public function __construct(EmailFetcherService $emailFetcherService)
    {
        parent::__construct();
        $this->emailFetcherService = $emailFetcherService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting inbound email fetch process...');
        $this->newLine();

        $providerId = $this->option('provider');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No emails will be processed');
            $this->newLine();
        }

        try {
            if ($providerId) {
                $this->fetchFromSpecificProvider($providerId, $isDryRun);
            } else {
                $this->fetchFromAllProviders($isDryRun);
            }
        } catch (\Exception $e) {
            $this->error('❌ Error during email fetch: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✅ Inbound email fetch process completed!');
        return 0;
    }

    /**
     * Fetch emails from a specific provider
     */
    protected function fetchFromSpecificProvider(string $providerId, bool $isDryRun): void
    {
        $provider = EmailProvider::where('provider_id', $providerId)->first();

        if (!$provider) {
            $this->error("❌ Provider with ID '{$providerId}' not found");
            return;
        }

        if (!$provider->is_active) {
            $this->warn("⚠️  Provider '{$provider->provider_name}' is not active");
            return;
        }

        $this->info("📧 Fetching emails from provider: {$provider->provider_name}");
        $this->line("   Protocol: " . ($provider->config_json['protocol'] ?? 'imap'));
        $this->line("   Host: " . ($provider->config_json['host'] ?? 'N/A'));
        $this->newLine();

        if ($isDryRun) {
            $this->showDryRunInfo($provider);
            return;
        }

        $result = $this->emailFetcherService->fetchEmailsForProvider($provider);
        $this->displayProviderResult($provider->provider_name, $result);
    }

    /**
     * Fetch emails from all active providers
     */
    protected function fetchFromAllProviders(bool $isDryRun): void
    {
        $providers = EmailProvider::where('is_active', true)->get();

        if ($providers->isEmpty()) {
            $this->warn('⚠️  No active email providers found');
            return;
        }

        $this->info("📧 Found {$providers->count()} active provider(s)");
        $this->newLine();

        if ($isDryRun) {
            foreach ($providers as $provider) {
                $this->showDryRunInfo($provider);
                $this->newLine();
            }
            return;
        }

        $results = $this->emailFetcherService->fetchAllInboundEmails();

        $this->info('📊 Fetch Results Summary:');
        $this->newLine();

        $totalFetched = 0;
        $totalProcessed = 0;
        $successCount = 0;

        foreach ($results as $providerId => $result) {
            $provider = EmailProvider::find($providerId);
            $providerName = $provider ? $provider->provider_name : 'Unknown Provider';
            
            $this->displayProviderResult($providerName, $result);
            
            if ($result['success']) {
                $successCount++;
                $totalFetched += $result['emails_fetched'];
                $totalProcessed += $result['emails_processed'];
            }
        }

        $this->newLine();
        $this->info("📈 Overall Summary:");
        $this->line("   Providers processed: {$successCount}/{$providers->count()}");
        $this->line("   Total emails fetched: {$totalFetched}");
        $this->line("   Total emails processed: {$totalProcessed}");
    }

    /**
     * Display result for a specific provider
     */
    protected function displayProviderResult(string $providerName, array $result): void
    {
        if ($result['success']) {
            $this->info("✅ {$providerName}:");
            $this->line("   📥 Emails fetched: {$result['emails_fetched']}");
            $this->line("   ✅ Emails processed: {$result['emails_processed']}");
        } else {
            $this->error("❌ {$providerName}:");
            $this->line("   📥 Emails fetched: {$result['emails_fetched']}");
            $this->line("   ❌ Error: {$result['error']}");
        }
    }

    /**
     * Show dry run information for a provider
     */
    protected function showDryRunInfo(EmailProvider $provider): void
    {
        $config = $provider->config_json;
        $protocol = $config['protocol'] ?? 'imap';
        $host = $config['host'] ?? 'N/A';
        $username = $config['username'] ?? $config['email'] ?? 'N/A';

        $this->info("🔍 Provider: {$provider->provider_name}");
        $this->line("   Protocol: {$protocol}");
        $this->line("   Host: {$host}");
        $this->line("   Username: {$username}");
        $this->line("   Tenant: {$provider->tenant->tenant_name}");
        
        // Test connection
        $this->line("   Testing connection...");
        
        try {
            if ($protocol === 'imap') {
                $this->testIMAPConnection($config);
            } elseif ($protocol === 'pop3') {
                $this->testPOP3Connection($config);
            } else {
                $this->warn("   ⚠️  Unsupported protocol: {$protocol}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test IMAP connection
     */
    protected function testIMAPConnection(array $config): void
    {
        $host = $config['imap_host'] ?? $config['host'] ?? 'imap.gmail.com';
        $port = $config['imap_port'] ?? $config['port'] ?? 993;
        $username = $config['username'] ?? $config['email'];
        $password = $config['password'];
        $encryption = $config['encryption'] ?? 'ssl';

        $connectionString = "{{$host}:{$port}/{$encryption}/novalidate-cert}";
        $connection = imap_open($connectionString, $username, $password);
        
        if ($connection) {
            $messageCount = imap_num_msg($connection);
            $this->info("   ✅ Connected successfully");
            $this->line("   📧 Messages in inbox: {$messageCount}");
            imap_close($connection);
        } else {
            throw new \Exception('Failed to connect: ' . imap_last_error());
        }
    }

    /**
     * Test POP3 connection
     */
    protected function testPOP3Connection(array $config): void
    {
        $host = $config['pop3_host'] ?? $config['host'] ?? 'pop.gmail.com';
        $port = $config['pop3_port'] ?? $config['port'] ?? 995;
        $username = $config['username'] ?? $config['email'];
        $password = $config['password'];
        $encryption = $config['encryption'] ?? 'ssl';

        $connectionString = "{{$host}:{$port}/{$encryption}/novalidate-cert}";
        $connection = imap_open($connectionString, $username, $password);
        
        if ($connection) {
            $messageCount = imap_num_msg($connection);
            $this->info("   ✅ Connected successfully");
            $this->line("   📧 Messages in inbox: {$messageCount}");
            imap_close($connection);
        } else {
            throw new \Exception('Failed to connect: ' . imap_last_error());
        }
    }
}