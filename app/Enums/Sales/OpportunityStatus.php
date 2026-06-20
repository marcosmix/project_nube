<?php

namespace App\Enums\Sales;

enum OpportunityStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case ProposalSent = 'proposal_sent';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';
    case Discarded = 'discarded';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nueva consulta',
            self::Contacted => 'Contactado',
            self::Qualified => 'Calificado',
            self::ProposalSent => 'Propuesta enviada',
            self::Negotiation => 'Negociacion',
            self::Won => 'Ganada',
            self::Lost => 'Perdida',
            self::Discarded => 'Descartada',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }

    public function supportsCommercialProposalData(): bool
    {
        return in_array($this, [
            self::Qualified,
            self::ProposalSent,
            self::Negotiation,
            self::Won,
        ], true);
    }
}
