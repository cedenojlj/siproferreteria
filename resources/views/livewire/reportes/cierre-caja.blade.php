<div class="mt-4">
    {{-- Botón Principal --}}
    <button wire:click="askForConfirmation" wire:loading.attr="disabled" class="btn btn-primary">
        <i class="bi bi-calculator"></i>
        Realizar Cierre de Caja
    </button>

    {{-- Resultados del Cierre --}}
    @if ($ultimoCierre)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Cierre de Caja Realizado con Éxito</h5>
            </div>
            <div class="card-body">
                <p><strong>ID del Cierre:</strong> {{ $ultimoCierre->id }}</p>
                <p><strong>Fecha y Hora:</strong> {{ $ultimoCierre->fecha_cierre->format('d/m/Y H:i:s') }}</p>
                <p><strong>Usuario:</strong> {{ $ultimoCierre->user->name }}</p>
                <p><strong>Rango del Reporte:</strong> {{ $ultimoCierre->rango_inicio->format('d/m/Y H:i:s') }} - {{ $ultimoCierre->rango_fin->format('d/m/Y H:i:s') }}</p>
                
                <h6 class="mt-4">Resumen Financiero</h6>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Ventas Bruto:
                        <span class="badge bg-primary rounded-pill">${{ number_format($ultimoCierre->total_ventas_bruto, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Devoluciones:
                        <span class="badge bg-warning text-dark rounded-pill">${{ number_format($ultimoCierre->total_devoluciones, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>Total Ventas Neto:</strong>
                        <span class="badge bg-success rounded-pill"><strong>${{ number_format($ultimoCierre->total_ventas_neto, 2) }}</strong></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Impuestos:
                        <span class="badge bg-secondary rounded-pill">${{ number_format($ultimoCierre->total_impuestos, 2) }}</span>
                    </li>
                </ul>

                <h6 class="mt-4">Desglose por Método de Pago</h6>
                <ul class="list-group">
                    @forelse ($ultimoCierre->totales_por_metodo as $metodo => $total)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucfirst($metodo) }}:
                            <span class="badge bg-info rounded-pill">${{ number_format($total, 2) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item">No se registraron pagos en este período.</li>
                    @endforelse
                </ul>

                <div class="mt-4">
                    <button wire:click="descargarPdf({{ $ultimoCierre->id }})" class="btn btn-secondary">
                        <i class="bi bi-download"></i> Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Confirmación --}}
    @if ($showConfirmationModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cierre de Caja</h5>
                        <button type="button" class="btn-close" wire:click="$set('showConfirmationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de que desea realizar el cierre de caja ahora?</p>
                        <p class="text-danger">Esta acción creará un registro permanente y no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showConfirmationModal', false)">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="realizarCierre" wire:loading.attr="disabled">
                            <span wire:loading wire:target="realizarCierre" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Sí, Realizar Cierre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

