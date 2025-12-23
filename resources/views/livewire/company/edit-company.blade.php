<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Editar Datos de la Empresa</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="update">
                @if ($saved)
                    <div class="alert alert-success" role="alert">
                        ¡Datos de la empresa actualizados con éxito!
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la Empresa</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" wire:model="phone">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" rows="3" wire:model="address"></textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="tax_id" class="form-label">Identificación Fiscal (RUC/CIF)</label>
                    <input type="text" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" wire:model="tax_id">
                    @error('tax_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="update">
                            Actualizar Datos
                        </span>
                        <span wire:loading wire:target="update">
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
