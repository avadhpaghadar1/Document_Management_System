<?php

namespace Database\Seeders;

use App\Models\Document_type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $userId = (int) DB::table('users')->orderBy('id')->value('id');
        if ($userId <= 0) {
            return;
        }

        $types = [
            'Invoice' => [
                ['name' => 'Invoice Number', 'type' => 'TEXT'],
                ['name' => 'Vendor', 'type' => 'TEXT'],
                ['name' => 'Amount', 'type' => 'TEXT'],
                ['name' => 'Invoice Date', 'type' => 'DATE'],
            ],
            'Contract' => [
                ['name' => 'Party Name', 'type' => 'TEXT'],
                ['name' => 'Start Date', 'type' => 'DATE'],
                ['name' => 'End Date', 'type' => 'DATE'],
                ['name' => 'Reference', 'type' => 'TEXT'],
            ],
            'ID Document' => [
                ['name' => 'Document Number', 'type' => 'TEXT'],
                ['name' => 'Issue Date', 'type' => 'DATE'],
                ['name' => 'Expiry Date', 'type' => 'DATE'],
            ],
        ];

        foreach ($types as $typeName => $fields) {
            $docType = Document_type::query()->firstOrCreate(
                ['name' => $typeName],
                ['user_id' => $userId]
            );

            foreach ($fields as $field) {
                $docType->documentFields()->updateOrCreate(
                    ['field_name' => $field['name']],
                    ['field_type' => $field['type']]
                );
            }
        }
    }
}
