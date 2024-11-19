<div class="fixed-bottom text-end m-4 z-2">
    @php
        // Elimina todos los caracteres no numéricos
        $phoneNumber = preg_replace('/\D/', '', $companySettings->phone);

        // Si el número comienza con "0", quítalo
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
    @endphp

    <a href="https://wa.me/598{{ $phoneNumber }}" class="whatsapp-button" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="width: 60px;"/>
    </a>
</div>


<div class="container-fluid catalogue-footer bg-primary text-light py-4 text-white">
    <div class="row text-center text-md-left">
        <!-- Primera columna -->
        <div class="col-md-4 mb-3 mb-md-0 text-start">
            <h5 class="font-weight-bold text-white">Contacto</h5>
            @if(!empty($companySettings->email))
                <p class="mb-1"><strong>Email:</strong> {{$companySettings->email}}</p>
            @endif
            @if(!empty($companySettings->phone))
                <p class="mb-1"><strong>Teléfono:</strong> {{$companySettings->phone}}</p>
            @endif
            @if(!empty($companySettings->address))
            <p><strong>Dirección:</strong>  
                {{ $companySettings->address }}
                @if(!empty($companySettings->state)), {{ $companySettings->state }}@endif
                @if(!empty($companySettings->country)), {{ $companySettings->country }}@endif
            </p>
            @endif
        </div>

        <!-- Segunda columna -->
        <div class="col-md-4 mb-3 mb-md-0">
            <h5 class="text-white">Redes Sociales</h5>
            <div class="social-icons d-flex justify-content-center">
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

        <!-- Tercera columna -->
        <div class="col-md-4 text-center text-md-left">
            <h5 class="font-weight-bold text-white">Creado gratis con <a class="text-white" href="https://sumeria.com.uy" target="_blank"> <strong class="sumeria-text"> Sumeria</strong></a></h5>
            <p>Vos también podés tener tu catálogo</p>
            <button class="btn btn-light z-3"><a href="https://www.sumeria.com.uy">¡Obtenelo Acá!</a></button>
        </div>
    </div>
</div>
