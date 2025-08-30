<?php

namespace App\Console\Commands;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Console\Command;

class ClassifyBulkTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:classify-bulk 
                            {--status= : Filter by status (A, C, H, X)}
                            {--limit= : Maximum number of tickets to classify}
                            {--force : Force reclassification of already classified tickets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify multiple tickets in bulk using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Build query
        $query = Ticket::query();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if (!$force) {
            // Only classify tickets that haven't been classified yet
            $query->whereNull('category')
                  ->orWhereNull('confidence')
                  ->orWhereNull('explanation');
        }
        
        if ($limit) {
            $query->limit((int) $limit);
        }

        $tickets = $query->get();
        
        if ($tickets->isEmpty()) {
            $this->info('No tickets found for classification.');
            return;
        }

        $this->info("Found {$tickets->count()} tickets for classification.");
        
        $bar = $this->output->createProgressBar($tickets->count());
        $bar->start();

        $classifiedCount = 0;
        $failedCount = 0;

        foreach ($tickets as $ticket) {
            try {
                // Dispatch the classification job
                ClassifyTicket::dispatch($ticket);
                $classifiedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to classify ticket {$ticket->id}: " . $e->getMessage());
                $failedCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Bulk classification completed!");
        $this->info("Successfully queued: {$classifiedCount} tickets");
        
        if ($failedCount > 0) {
            $this->warn("Failed to queue: {$failedCount} tickets");
        }

        $this->info("Jobs have been queued. Make sure your queue worker is running with: php artisan queue:work");
    }
}
