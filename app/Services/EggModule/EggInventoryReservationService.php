<?php

namespace App\Services\EggModule;

use App\Models\EggModule\EggInventory;
use RuntimeException;

class EggInventoryReservationService
{
    /**
     * Reserva quantidade a partir de stock disponível.
     * Devolve o registo de inventário reservado (pode ser novo se divisão parcial).
     */
    public function reserveQuantity(EggInventory $inventory, int $quantity): EggInventory
    {
        if ($quantity < 1) {
            throw new RuntimeException('Quantidade inválida para reserva.');
        }

        if ($inventory->status !== 'available') {
            throw new RuntimeException("O stock #{$inventory->id} não está disponível.");
        }

        if ($inventory->quantity < $quantity) {
            throw new RuntimeException(
                "Stock #{$inventory->id} insuficiente. Necessário: {$quantity}. Disponível: {$inventory->quantity}."
            );
        }

        if ($inventory->quantity === $quantity) {
            $inventory->update(['status' => 'reserved']);

            return $inventory->fresh();
        }

        $reserved = $inventory->replicate();
        $reserved->quantity = $quantity;
        $reserved->status = 'reserved';
        $reserved->exit_date = null;
        $reserved->save();

        $inventory->quantity -= $quantity;
        $inventory->save();

        return $reserved;
    }

    /**
     * Liberta stock reservado, voltando a ficar disponível.
     */
    public function releaseReserved(EggInventory $inventory): void
    {
        if ($inventory->status !== 'reserved') {
            return;
        }

        $merged = EggInventory::query()
            ->where('id', '!=', $inventory->id)
            ->where('egg_id', $inventory->egg_id)
            ->where('house_id', $inventory->house_id)
            ->where('entry_date', $inventory->entry_date)
            ->where('location', $inventory->location)
            ->where('status', 'available')
            ->first();

        if ($merged) {
            $merged->quantity += $inventory->quantity;
            $merged->save();
            $inventory->delete();

            return;
        }

        $inventory->update([
            'status' => 'available',
            'exit_date' => null,
        ]);
    }

    /**
     * Confirma expedição: reservado → expedido.
     */
    public function markShipped(EggInventory $inventory, string $exitDate): void
    {
        $inventory->update([
            'status' => 'shipped',
            'exit_date' => $exitDate,
        ]);
    }
}
