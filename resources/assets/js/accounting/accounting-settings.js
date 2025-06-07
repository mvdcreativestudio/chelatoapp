document.addEventListener('DOMContentLoaded', () => {
  function loadPymoConnections() {
    const storeCards = document.querySelectorAll('.store-card');

    storeCards.forEach(card => {
      const storeId = card.dataset.storeId;
      const isEnabled = card.dataset.invoicesEnabled === 'true';

      const loader = card.querySelector('.pymo-loader');
      const dataDiv = card.querySelector('.pymo-data');
      const errorDiv = card.querySelector('.pymo-error');
      const tableBody = card.querySelector('.caes-table tbody');
      const caesContainer = card.querySelector('.caes-container');

      if (!isEnabled) {
        loader.style.display = 'none'; // Skip si no está habilitada
        return;
      }

      loader.style.display = 'block';
      dataDiv.style.display = 'none';
      errorDiv.style.display = 'none';
      caesContainer.style.display = 'none';

      fetchStoreData(storeId, dataDiv, errorDiv)
        .then(success => {
          if (success) {
            return fetchAvailableCaes(storeId, tableBody, caesContainer);
          } else {
            console.warn(`❌ Tienda ${storeId} no tiene datos válidos. Se omite carga de CAEs.`);
            return Promise.resolve(); // Saltear CAEs
          }
        })
        .catch(error => {
          console.error(`Error general en tienda ${storeId}:`, error);
        })
        .finally(() => {
          loader.style.display = 'none';
        });
    });
  }

  function fetchStoreData(storeId, dataDiv, errorDiv) {
    return fetch(`${window.baseUrl}admin/integrations/pymo-connection/${storeId}`, {
      headers: {
        'X-CSRF-TOKEN': window.csrfToken,
        'Accept': 'application/json',
      },
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const companyInfo = data.data;
          dataDiv.querySelector('.company-name').textContent = companyInfo.name || 'N/A';
          dataDiv.querySelector('.company-rut').textContent = companyInfo.rut || 'N/A';
          dataDiv.querySelector('.company-email').textContent = companyInfo.email || 'N/A';
          dataDiv.querySelector('.company-branch').textContent =
            companyInfo.branchOffices.find(
              office =>
                office.number === (companyInfo.selectedBranch ? companyInfo.selectedBranch.number : ''),
            )?.fiscalAddress || 'N/A';

          dataDiv.style.display = 'block';
          return true;
        } else {
          errorDiv.textContent = data.message || 'Esta sucursal no está conectada a Facturacion Electrónica';
          errorDiv.style.display = 'block';
          return false;
        }
      })
      .catch(error => {
        console.error('Error al cargar la conexión:', error);
        errorDiv.textContent = 'Error al cargar la información';
        errorDiv.style.display = 'block';
        return false;
      });
  }


  function fetchAvailableCaes(storeId, tableBody, caesContainer) {
    const caeTypeMapping = {
      101: 'eTicket',
      102: 'eTicket - Nota de Crédito',
      103: 'eTicket - Nota de Débito',
      111: 'eFactura',
      112: 'eFactura - Nota de Crédito',
      113: 'eFactura - Nota de Débito',
    };

    const caeTypes = Object.keys(caeTypeMapping);
    const allResults = [];

    return Promise.all(
      caeTypes.map(type => {
        const url = `${window.baseUrl}admin/accounting/pymo-connection/${storeId}/caes/${type}`;
        console.log(`Consultando URL: ${url}`); // Log para depuración

        return fetch(url, {
          headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
          },
        })
          .then(response => {
            if (!response.ok) {
              console.error(`Error HTTP en la consulta: ${response.status}`);
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              console.log(`Respuesta recibida para el tipo ${type}:`, data);
              const caes = data.data.payload.companyCfeActiveNumbers || [];
              const caeArray = Array.isArray(caes) ? caes : [caes];

              caeArray.forEach(cae => {
                cae.type = type; // Asignamos el tipo de CAE al resultado
              });

              allResults.push(...caeArray);
            } else {
              console.error(`Error en la respuesta para el tipo ${type}:`, data.message);
            }
          });
      }),
    )
      .then(() => {
        renderCaesTable(allResults, caeTypeMapping, tableBody, caesContainer);
      })
      .catch(error => {
        console.error('Error al consultar los CAEs:', error);
        alert(`Error al consultar los CAEs. Inténtalo de nuevo más tarde. Detalles: ${error.message}`);
      });
  }

  function renderCaesTable(caes, caeTypeMapping, tableBody, caesContainer) {
    tableBody.innerHTML = ''; // Limpiamos cualquier dato previo

    // Ordenar los CAES según el tipo
    const caeOrder = ['101', '102', '103', '111', '112', '113'];
    caes.sort((a, b) => caeOrder.indexOf(a.type) - caeOrder.indexOf(b.type));

    caes.forEach(cae => {
      const row = document.createElement('tr');

      const typeCell = document.createElement('td');
      const caeType = caeTypeMapping[parseInt(cae.type)] || 'No disponible';
      typeCell.textContent = caeType;
      row.appendChild(typeCell);

      const nextNumCell = document.createElement('td');
      nextNumCell.textContent = cae.nextNum || 'N/A';
      row.appendChild(nextNumCell);

      const rangeCell = document.createElement('td');
      const range = cae.range;
      rangeCell.textContent = range
        ? `De ${range.first || 'N/A'} a ${range.last || 'N/A'}`
        : 'N/A';
      row.appendChild(rangeCell);

      tableBody.appendChild(row);
    });

    caesContainer.style.display = 'block'; // Mostrar la tabla
  }

  // Cambio de formato de impresión - print_settings
  const selects = document.querySelectorAll(".print-setting-select");

  selects.forEach(select => {
    select.addEventListener("change", function () {
        const storeId = this.dataset.storeId;
        const printSetting = this.value;

        fetch(`print-settings/${storeId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json", // 👈 esto es importante
                "X-CSRF-TOKEN": window.csrfToken,
            },
            body: JSON.stringify({
                print_settings: printSetting,
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "✅ Guardado",
                        text: "El formato de impresión fue actualizado correctamente.",
                        icon: "success",
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: "top-end"
                    });
                } else {
                    Swal.fire({
                        title: "❌ Error",
                        text: data.message || "No se pudo actualizar el formato.",
                        icon: "error",
                    });
                }
            })
            .catch(error => {
                console.error("Error al guardar configuración:", error);
                Swal.fire({
                    title: "❌ Error",
                    text: "Hubo un problema al conectar con el servidor.",
                    icon: "error",
                });
            });
    });
});



  loadPymoConnections();
});
