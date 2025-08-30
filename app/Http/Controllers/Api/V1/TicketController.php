<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Jobs\ClassifyTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class TicketController extends ApiController
{
    /**
     * Get all tickets
     * 
     * @group Managing Tickets
     * @queryParam sort string Data field(s) to sort by. Separate multiple fields with commas. Denote descending sort with a minus sign. Example: sort=title,-createdAt
     * @queryParam filter[status] Filter by status code: A, C, H, X. No-example
     * @queryParam filter[title] Filter by title. Wildcards are supported. Example: *fix*
     */
    public function index(TicketFilter $filters)
    {
        // Check if this is an export request
        if (request()->has('export') && request()->get('export') === 'csv') {
            return $this->exportCsv($filters);
        }
        
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Create a ticket
     * 
     * Creates a new ticket record. Users can only create tickets for themselves. Managers can create tickets for any user.
     * 
     * @group Managing Tickets
     * 
     * @response {"data":{"type":"ticket","id":107,"attributes":{"title":"asdfasdfasdfasdfasdfsadf","description":"test ticket","status":"A","createdAt":"2024-03-26T04:40:48.000000Z","updatedAt":"2024-03-26T04:40:48.000000Z"},"relationships":{"author":{"data":{"type":"user","id":1},"links":{"self":"http:\/\/localhost:8000\/api\/v1\/authors\/1"}}},"links":{"self":"http:\/\/localhost:8000\/api\/v1\/tickets\/107"}}}
     */
    public function store(StoreTicketRequest $request)
    {
        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes()));
        }

        return $this->notAuthorized('You are not authorized to update that resource');
    }

    /**
     * Show a specific ticket.
     * 
     * Display an individual ticket.
     * 
     * @group Managing Tickets
     * 
     */
    public function show(Ticket $ticket)
    {
        if ($this->include('author')) {
            return new TicketResource($ticket->load('author'));
        }

        return new TicketResource($ticket);
    }

    /**
     * Update Ticket
     * 
     * Update the specified ticket in storage.
     * 
     * @group Managing Tickets
     * 
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // PATCH


        if ($this->isAble('update', $ticket)) {
            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You are not authorized to update that resource');
    }

    /**
     * Delete ticket.
     * 
     * Remove the specified resource from storage.
     * 
     * @group Managing Tickets
     * 
     */
    public function destroy(Ticket $ticket)
    {
        // policy
        if ($this->isAble('delete', $ticket)) {
            $ticket->delete();

            return $this->ok('Ticket successfully deleted');
        }

        return $this->notAuthorized('You are not authorized to delete that resource');
    }

    public function stats()
    {
        // Count tickets per status
        $statusCounts = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Count tickets per category
        $categoryCounts = Ticket::selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('count', 'category');

        $data = [
            'type' => 'ticket-stats',
            'id' => 'summary',
            'attributes' => [
                'per_status' => [
                    'A' => $statusCounts['A'] ?? 0,
                    'C' => $statusCounts['C'] ?? 0,
                    'H' => $statusCounts['H'] ?? 0,
                    'X' => $statusCounts['X'] ?? 0,
                ],
                'per_category' => $categoryCounts->toArray(),
            ],
            'links' => [
                'self' => route('tickets.stats')
            ]
        ];

        return response()->json([
            'status' => 200,
            'data' => $data,
            'message' => 'Stats fetched successfully'
        ]);
    }

    /**
     * Classify a ticket using AI
     * 
     * Dispatch a job to classify the ticket using OpenAI.
     * 
     * @group Managing Tickets
     */
    public function classify(Request $request, Ticket $ticket)
    {
        if ($this->isAble('update', $ticket)) {
            // Rate limiting: 10 classification requests per minute per user
            $user = $request->user();
            $key = 'classify-ticket:' . $user->id;
            
            if (RateLimiter::tooManyAttempts($key, 10)) {
                $seconds = RateLimiter::availableIn($key);
                
                return response()->json([
                    'status' => 429,
                    'message' => 'Too many classification requests. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds
                ], 429);
            }
            
            RateLimiter::hit($key, 60); // 60 seconds = 1 minute

            // Dispatch the classification job
            ClassifyTicket::dispatch($ticket, true);

            return response()->json([
                'status' => 200,
                'data' => [
                    'type' => 'ticket-classification',
                    'id' => $ticket->id,
                    'attributes' => [
                        'status' => 'queued',
                        'message' => 'Classification job has been queued'
                    ]
                ]
            ]);
        }

        return $this->notAuthorized('You are not authorized to classify that ticket');
    }

    /**
     * Export tickets to CSV
     */
    private function exportCsv(TicketFilter $filters)
    {
        $tickets = Ticket::filter($filters)->with('author')->get();
        
        $csvData = [];
        $csvData[] = ['ID', 'Title', 'Description', 'Status', 'Category', 'Confidence', 'Notes', 'Author', 'Created At', 'Updated At'];
        
        foreach ($tickets as $ticket) {
            $csvData[] = [
                $ticket->id,
                $ticket->title,
                $ticket->description,
                $this->getStatusLabel($ticket->status),
                $ticket->category ?? '',
                $ticket->confidence ? round($ticket->confidence * 100, 2) . '%' : '',
                $ticket->notes ?? '',
                $ticket->author ? $ticket->author->name : '',
                $ticket->created_at->format('Y-m-d H:i:s'),
                $ticket->updated_at->format('Y-m-d H:i:s')
            ];
        }
        
        $filename = 'tickets_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Convert status code to readable label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'A' => 'Active',
            'C' => 'Completed',
            'H' => 'On Hold',
            'X' => 'Cancelled',
            default => $status
        };
    }
}
