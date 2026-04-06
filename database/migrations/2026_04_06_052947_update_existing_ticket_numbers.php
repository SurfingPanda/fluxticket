<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $prefixes = [
            'Service Request' => 'SQR',
            'Change Request'  => 'CRQ',
            'Incident'        => 'ICT',
            'Question'        => 'QTN',
        ];

        foreach ($prefixes as $type => $prefix) {
            $tickets = DB::table('tickets')
                ->where('type', $type)
                ->orderBy('id')
                ->get();

            foreach ($tickets as $index => $ticket) {
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update([
                        'ticket_number' => $prefix . '-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                    ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
