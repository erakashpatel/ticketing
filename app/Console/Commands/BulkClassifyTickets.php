<?php

namespace App\Console\Commands;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Console\Command;

class BulkClassifyTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:bulk-classify 
                          {--force : Force classification even if category already exists}
                          {--limit=50 : Limit the number of tickets to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk classify tickets using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        
        $this->info('Starting bulk ticket classification...');
        
        $query = Ticket::query();
        
        if (!$force) {
            $query->whereNull('category');
        }
        
        $tickets = $query->limit($limit)->get();
        
        if ($tickets->isEmpty()) {
            $this->warn('No tickets found for classification.');
            return 0;
        }
        
        $this->info("Found {$tickets->count()} tickets to classify.");
        
        $bar = $this->output->createProgressBar($tickets->count());
        $bar->start();
        
        foreach ($tickets as $ticket) {
            ClassifyTicket::dispatch($ticket, $force);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$tickets->count()} classification jobs to the queue.");
        
        return 0;
    }
}
