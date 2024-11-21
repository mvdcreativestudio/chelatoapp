<div class="footer py-5">
    <div class="container-fluid">
        <div class="row text-center">
            <!-- Logo de la empresa -->
            <div class="col-12 col-md-4 mb-4 mb-md-0 d-flex justify-content-center align-items-center">
                <img src="{{ asset($companySettings->logo_black) }}" alt="Anjos Logo" class="img-fluid" style="height: 80px; width: auto;">
            </div>
            
            <!-- Información de contacto -->
            <div class="col-12 col-md-4 mb-4 mb-md-0">
                <h3 class="font-secondary">Información</h3>
                <p class="mb-1 font-white">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ $companySettings->address }}
                </p>
                <p class="mb-1">
                    <i class="fas fa-envelope me-2"></i>
                    <a href="mailto:{{ $companySettings->email }}" class="text-decoration-none font-white">{{ $companySettings->email }}</a>
                </p>
                <p>
                    <i class="fas fa-phone me-2"></i>
                    <a href="tel:{{ $companySettings->phone }}" class="text-decoration-none font-white">{{ $companySettings->phone }}</a>
                </p>
            </div>
            
            <!-- Redes sociales -->
            <div class="col-12 col-md-4">
                <h3 class="font-secondary">Redes Sociales</h3>
                @if(!empty($companySettings->facebook))
                    <a href="{{ $companySettings->facebook }}" target="_blank" class="text-white mx-2">
                        <i class="fab fa-facebook fa-2x"></i>
                    </a>
                @endif
                
                @if(!empty($companySettings->twitter))
                    <a href="{{ $companySettings->twitter }}" target="_blank" class="text-white mx-2">
                        <i class="fab fa-twitter fa-2x"></i>
                    </a>
                @endif
                
                @if(!empty($companySettings->instagram))
                    <a href="{{ $companySettings->instagram }}" target="_blank" class="text-white mx-2">
                        <i class="fab fa-instagram fa-2x"></i>
                    </a>
                @endif
                
                @if(!empty($companySettings->linkedin))
                    <a href="{{ $companySettings->linkedin }}" target="_blank" class="text-white mx-2">
                        <i class="fab fa-linkedin fa-2x"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sección adicional -->
<div class="bg-dark text-center py-3 p-5">
    <p class="mb-0 text-white text-end">
        Desarrollado por 
        <a href="https://www.mvdcreativestudio.com" target="_blank" class="text-white text-decoration-none">
            <strong>MVD Studio</strong>
        </a>
    </p>
</div>
