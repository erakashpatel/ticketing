<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClassifyTicket implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket,
        public bool $forceUpdate = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TicketClassifier $classifier): void
    {
        try {
            // If category is already set and we're not forcing update, skip classification
            if (!$this->forceUpdate && !empty($this->ticket->category)) {
                Log::info('Skipping classification for ticket with existing category', [
                    'ticket_id' => $this->ticket->id,
                    'existing_category' => $this->ticket->category
                ]);
                return;
            }

            $result = $classifier->classify(
                $this->ticket->title,
                $this->ticket->description
            );

            // If user has manually changed category, keep it but update explanation & confidence
            $updateData = [
                'explanation' => $result['explanation'],
                'confidence' => $result['confidence']
            ];

            // Only update category if it's empty or we're forcing update
            if (empty($this->ticket->category) || $this->forceUpdate) {
                $updateData['category'] = $result['category'];
            }

            $this->ticket->update($updateData);

            Log::info('Ticket classified successfully', [
                'ticket_id' => $this->ticket->id,
                'category' => $result['category'],
                'confidence' => $result['confidence']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to classify ticket', [
                'ticket_id' => $this->ticket->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }
}
