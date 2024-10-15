<!-- Edit User Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3>Editar Cliente</h3>
        </div>
        <form id="editClientForm" data-client-id="{{ $client->id }}">
          @csrf
          <div class="row">
            <div class="col-12 col-md-6">
              <label class="form-label" for="modalEditUserName">Nombre</label>
              <input type="text" id="modalEditUserName" name="name" class="form-control" value="{{ $client->name }}" required />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="modalEditUserLastName">Apellido</label>
              <input type="text" id="modalEditUserLastName" name="lastname" class="form-control" value="{{ $client->lastname }}" required />
            </div>
            <div class="col-12">
              <label class="form-label" for="modalEditUserEmail">Email</label>
              <input type="email" id="modalEditUserEmail" name="email" class="form-control" value="{{ $client->email }}" required />
            </div>
            <div class="col-12">
              <label class="form-label" for="modalEditUserPhone">Teléfono</label>
              <input type="text" id="modalEditUserPhone" name="phone" class="form-control" value="{{ $client->phone }}" />
            </div>
            <div class="col-12">
              <label class="form-label" for="modalEditUserAddress">Dirección</label>
              <input type="text" id="modalEditUserAddress" name="address" class="form-control" value="{{ $client->address }}" />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="modalEditUserCity">Ciudad</label>
              <input type="text" id="modalEditUserCity" name="city" class="form-control" value="{{ $client->city }}" />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="modalEditUserCountry">País</label>
              <input type="text" id="modalEditUserCountry" name="country" class="form-control" value="{{ $client->country }}" />
            </div>
          </div>
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar cambios</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->
