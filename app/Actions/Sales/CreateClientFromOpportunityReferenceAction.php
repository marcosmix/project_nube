<?php

namespace App\Actions\Sales;

use App\Models\Client;
use App\Models\Contact;

class CreateClientFromOpportunityReferenceAction
{
    /**
     * Crea contacto y cliente usando la referencia comercial como base.
     * El nombre/apellido se completan manualmente para evitar inferencias frágiles.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Client
    {
        $contact = Contact::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
        ]);

        return Client::create([
            'contact_id' => $contact->id,
            'organization_name' => $data['organization_name'],
            'industry' => $data['industry'] ?? null,
            'company_size' => $data['company_size'],
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
