<div class="integration-card ecommerce-card" id="ecommerce-card-{{ $store->id }}">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/ecommerce-logo.png') }}"
                    alt="E-Commerce Logo" class="img-fluid">
            </div>
            <span class="status-indicator {{ $store->ecommerce ? '' : 'd-none' }}">
                <i class="bx bx-check text-white"></i>
            </span>
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">E-Commerce</h3>
                <small class="d-block mb-3">Activa la venta en línea para tu tienda</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="ecommerce" value="0">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="ecommerceSwitch-{{ $store->id }}" 
                    name="ecommerce" 
                    value="1" 
                    {{ $store->ecommerce ? 'checked' : '' }}
                    data-store-id="{{ $store->id }}"
                >
            </div>
        </div>
    </div>
</div>