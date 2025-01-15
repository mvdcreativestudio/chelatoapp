document.addEventListener('DOMContentLoaded', () => {
  function loadPymoConnections() {
    const storeCards = document.querySelectorAll('.store-card');

    storeCards.forEach(card => {
      const storeId = card.dataset.storeId;
      const loader = card.querySelector('.pymo-loader');
      const dataDiv = card.querySelector('.pymo-data');
      const errorDiv = card.querySelector('.pymo-error');
      const tableBody = card.querySelector('.caes-table tbody');
      const caesContainer = card.querySelector('.caes-container');

      loader.style.display = 'block'; // Mostrar el loader
      dataDiv.style.display = 'none';
      errorDiv.style.display = 'none';
      caesContainer.style.display = 'none';

      // Llamadas a ambas funciones (datos de la tienda y CAES)
      const storePromise = fetchStoreData(storeId, dataDiv, errorDiv);
      const caesPromise = fetchAvailableCaes(storeId, tableBody, caesContainer);

      // Ocultar el loader cuando ambas promesas se completen
      Promise.all([storePromise, caesPromise])
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

          dataDiv.style.display = 'block'; // Mostrar la información de la tienda
        } else {
          errorDiv.textContent = data.message || 'Error desconocido';
          errorDiv.style.display = 'block';
        }
      })
      .catch(error => {
        console.error('Error al cargar la conexión:', error);
        errorDiv.textContent = 'Error al cargar la información';
        errorDiv.style.display = 'block';
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

  loadPymoConnections();
});
