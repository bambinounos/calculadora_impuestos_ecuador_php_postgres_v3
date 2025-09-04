*/

// --- public/assets/js/main.js --- (COMPLETO Y ACTUALIZADO PARA V9)
/*
document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'api/';
    // Auth Elements
    const authNav = document.getElementById('auth-nav');
    const authSection = document.getElementById('auth-section');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegisterLink = document.getElementById('show-register-link');
    const showLoginLink = document.getElementById('show-login-link');
    const authMessage = document.getElementById('auth-message');

    // Manual Shipment Calculator Elements
    const calculatorSection = document.getElementById('calculator-section'); // This ID is kept for show/hide logic
    
    // Shipment-level forms and data
    const shipmentCostsForm = document.getElementById('shipment-costs-form');
    const totalFleteInput = document.getElementById('totalFlete');
    const totalSeguroInput = document.getElementById('totalSeguro');
    const totalAgenteAduanaInput = document.getElementById('totalAgenteAduana');
    const totalBodegaAduanaInput = document.getElementById('totalBodegaAduana');
    const totalFleteTerrestreInput = document.getElementById('totalFleteTerrestre');
    const totalDemorajeInput = document.getElementById('totalDemoraje');
    const totalGastosVariosInput = document.getElementById('totalGastosVarios');
    const totalISDTasaInput = document.getElementById('totalISDTasa');
    const totalFOBEmbarqueInput = document.getElementById('totalFOBEmbarque');
    const totalPesoEmbarqueInput = document.getElementById('totalPesoEmbarque');
    const prorationMethodSelect = document.getElementById('prorationMethod');
    const esCourier4x4Checkbox = document.getElementById('esCourier4x4');

    // Add-item form elements
    const addItemForm = document.getElementById('add-item-form');
    const itemEditIndexInput = document.getElementById('item-edit-index');
    const itemProductNameInput = document.getElementById('item-productName');
    const itemTariffCodeSearchInput = document.getElementById('item-tariff-code-search');
    const itemTariffCodeIdSelect = document.getElementById('item-tariffCodeId');
    const itemCantidadInput = document.getElementById('item-cantidad');
    const itemValorFOBInput = document.getElementById('item-valorFOB');
    const itemPesoUnitarioKgInput = document.getElementById('item-pesoUnitarioKg');
    const itemProfitPercentageInput = document.getElementById('item-profitPercentage');
    const cancelEditItemButton = document.getElementById('cancel-edit-item-button');

    // Shipment items table
    const shipmentItemsTableBody = document.querySelector("#shipment-items-table tbody");

    // Main action buttons and result areas
    const calculateShipmentButton = document.getElementById('calculate-shipment-button');
    const shipmentResultsArea = document.getElementById('shipment-results-area');
    const shipmentResultsSummaryDiv = document.getElementById('shipment-results-summary');
    const shipmentResultsDetailsDiv = document.getElementById('shipment-results-details');
    const printShipmentSummaryButton = document.getElementById('print-shipment-summary-button');
    const shipmentMessage = document.getElementById('shipment-message');

    // CSV Import Elements
    const csvImportSection = document.getElementById('csv-import-section');
    const csvImportForm = document.getElementById('csv-import-form');
    const csvFileInput = document.getElementById('csvFile');
    const fleteGeneralCsvInput = document.getElementById('fleteGeneralCsv');
    const seguroGeneralCsvInput = document.getElementById('seguroGeneralCsv');
    const gastosAgenteAduanaCsvInput = document.getElementById('gastosAgenteAduanaCsv');
    const tasaIsdConfigurableCsvInput = document.getElementById('tasaIsdConfigurableCsv');
    const gastosBodegaAduanaCsvInput = document.getElementById('gastosBodegaAduanaCsv');
    const gastosDemorajeCsvInput = document.getElementById('gastosDemorajeCsv');
    const gastosFleteTerrestreCsvInput = document.getElementById('gastosFleteTerrestreCsv');
    const gastosVariosCsvInput = document.getElementById('gastosVariosCsv');
    const prorationMethodCsvSelect = document.getElementById('prorationMethodCsv');
    const profitPercentageCsvInput = document.getElementById('profitPercentageCsv');
    const csvResultsSummaryDiv = document.getElementById('csv-results-summary');
    const csvResultsDetailsDiv = document.getElementById('csv-results-details');
    const printCsvSummaryButton = document.getElementById('print-csv-summary-button');
    const csvMessage = document.getElementById('csv-message');

    // Saved Calculations Elements
    const savedCalculationsSection = document.getElementById('saved-calculations-section');
    const savedCalculationsListDiv = document.getElementById('saved-calculations-list');
    const savedCalculationsMessage = document.getElementById('saved-calculations-message');
    
    // Tariff Management Elements
    const tariffManagementSection = document.getElementById('tariff-management-section');
    const tariffCodeForm = document.getElementById('tariff-code-form');
    const tariffEditIdInput = document.getElementById('tariff-edit-id');
    const tariffCodeValInput = document.getElementById('tariff-code-val');
    const tariffDescriptionValInput = document.getElementById('tariff-description-val');
    const tariffAdvaloremValInput = document.getElementById('tariff-advalorem-val');
    const tariffIceValInput = document.getElementById('tariff-ice-val');
    const tariffIvaValInput = document.getElementById('tariff-iva-val');
    const tariffSpecificValueValInput = document.getElementById('tariff-specific-value-val');
    const tariffSpecificUnitValInput = document.getElementById('tariff-specific-unit-val');
    const tariffFodinfaAppliesCheckbox = document.getElementById('tariff-fodinfa-applies');
    const tariffNotesValTextarea = document.getElementById('tariff-notes-val');
    const tariffFormSubmitButton = document.getElementById('tariff-form-submit-button');
    const tariffFormCancelButton = document.getElementById('tariff-form-cancel-button');
    const tariffListDiv = document.getElementById('tariff-list');
    const tariffMessage = document.getElementById('tariff-message');

    // CSV History Elements
    const csvHistorySection = document.getElementById('csv-history-section');
    const csvHistoryListDiv = document.getElementById('csv-history-list');
    const csvHistoryMessage = document.getElementById('csv-history-message');

    const printableAreaDiv = document.getElementById('printable-area');

    let currentUser = null;
    let currentCalculatedResults = null; 
    let lastCsvProcessedData = null;    
    let debounceTimer;
    let shipmentItems = []; // Array to store items for manual shipment calculation

    function displayMessage(element, message, isSuccess) {
        if (!element) return;
        element.textContent = message;
        element.className = 'message ' + (isSuccess ? 'success' : 'error');
        element.style.display = 'block';
        setTimeout(() => { if(element) {element.style.display = 'none'; element.textContent = '';} }, 7000);
    }

    function updateAuthUI() {
        const isAdmin = currentUser && currentUser.email === 'admin@example.com'; // Ejemplo de rol admin. Cambiar por un sistema de roles real.
        if (currentUser) {
            authNav.innerHTML = `<span>Hola, ${currentUser.email}</span> <button id="logout-button">Logout</button>`;
            authSection.style.display = 'none';
            calculatorSection.style.display = 'block';
            saveCalculationButton.style.display = 'inline-block';
            savedCalculationsSection.style.display = 'block';
            csvImportSection.style.display = 'block'; 
            tariffManagementSection.style.display = isAdmin ? 'block' : 'none';
            csvHistorySection.style.display = 'block';
            loadSavedCalculations();
            if(isAdmin) loadTariffCodesForManagement();
            loadCsvImportHistory();
            document.getElementById('logout-button').addEventListener('click', handleLogout);
        } else {
            authNav.innerHTML = `<button id="show-login-form-button">Login</button> <button id="show-register-form-button">Registro</button>`;
            authSection.style.display = 'block';
            calculatorSection.style.display = 'none';
            loginForm.style.display = 'block'; registerForm.style.display = 'none';
            saveCalculationButton.style.display = 'none'; printItemSummaryButton.style.display = 'none';
            savedCalculationsSection.style.display = 'none'; csvImportSection.style.display = 'none';
            tariffManagementSection.style.display = 'none'; csvHistorySection.style.display = 'none';
            document.getElementById('show-login-form-button')?.addEventListener('click', () => { loginForm.style.display = 'block'; registerForm.style.display = 'none'; authMessage.style.display = 'none'; });
            document.getElementById('show-register-form-button')?.addEventListener('click', () => { loginForm.style.display = 'none'; registerForm.style.display = 'block'; authMessage.style.display = 'none'; });
        }
    }

    showRegisterLink?.addEventListener('click', (e) => { e.preventDefault(); loginForm.style.display = 'none'; registerForm.style.display = 'block'; authMessage.style.display = 'none'; });
    showLoginLink?.addEventListener('click', (e) => { e.preventDefault(); registerForm.style.display = 'none'; loginForm.style.display = 'block'; authMessage.style.display = 'none'; });

    loginForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        formData.append('action', 'login');
        try {
            const response = await fetch(`${API_BASE_URL}auth.php`, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                currentUser = data.user;
                updateAuthUI();
                displayMessage(authMessage, 'Login exitoso.', true);
            } else { displayMessage(authMessage, data.message || 'Error en login.', false); }
        } catch (error) { displayMessage(authMessage, 'Error de conexión.', false); }
    });

    registerForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        formData.append('action', 'register');
        try {
            const response = await fetch(`${API_BASE_URL}auth.php`, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                displayMessage(authMessage, 'Registro exitoso. Por favor, inicia sesión.', true);
                registerForm.reset();
                loginForm.style.display = 'block'; registerForm.style.display = 'none';
            } else { displayMessage(authMessage, data.message || 'Error en registro.', false); }
        } catch (error) { displayMessage(authMessage, 'Error de conexión.', false); }
    });

    async function handleLogout() {
        try {
            const response = await fetch(`${API_BASE_URL}auth.php?action=logout`);
            await response.json(); 
            currentUser = null;
            updateAuthUI();
            currentCalculatedResults = null;
            lastCsvProcessedData = null;
            calculatorForm?.reset();
            csvImportForm?.reset();
            if(calculationResultsDiv) calculationResultsDiv.style.display = 'none';
            if(printItemSummaryButton) printItemSummaryButton.style.display = 'none';
            if(csvResultsDetailsDiv) csvResultsDetailsDiv.innerHTML = '';
            if(csvResultsSummaryDiv) csvResultsSummaryDiv.innerHTML = '';
            if(printCsvSummaryButton) printCsvSummaryButton.style.display = 'none';
            if(savedCalculationsListDiv) savedCalculationsListDiv.innerHTML = '<p>Inicie sesión para ver sus cálculos.</p>';
            if(tariffListDiv) tariffListDiv.innerHTML = '';
            if(csvHistoryListDiv) csvHistoryListDiv.innerHTML = '';

        } catch (error) { console.error('Error en logout:', error); }
    }

    async function checkLoginStatus() {
        try {
            const response = await fetch(`${API_BASE_URL}auth.php?action=status`);
            const data = await response.json();
            if (data.success && data.loggedIn) { currentUser = data.user; } 
            else { currentUser = null; }
        } catch (error) { currentUser = null; }
        updateAuthUI();
    }

    // --- Manual Shipment UI Logic ---

    // Function to render the table of items in the shipment
    function renderShipmentTable() {
        shipmentItemsTableBody.innerHTML = '';
        if (shipmentItems.length === 0) {
            shipmentItemsTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay productos en el embarque.</td></tr>';
            return;
        }

        shipmentItems.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.productName}</td>
                <td>${item.tariffCodeText.split(' - ')[0]}</td>
                <td>${item.cantidad}</td>
                <td>${item.valorFOB.toFixed(2)}</td>
                <td>${item.pesoUnitarioKg.toFixed(3)}</td>
                <td>${item.profitPercentage}%</td>
                <td class="actions">
                    <button type="button" class="edit-item" data-index="${index}">Editar</button>
                    <button type="button" class="delete-item" data-index="${index}">Eliminar</button>
                </td>
            `;
            shipmentItemsTableBody.appendChild(tr);
        });

        // Add event listeners to the new buttons
        document.querySelectorAll('.edit-item').forEach(button => {
            button.addEventListener('click', handleEditItem);
        });
        document.querySelectorAll('.delete-item').forEach(button => {
            button.addEventListener('click', handleDeleteItem);
        });
    }

    // Function to handle adding or updating an item
    addItemForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const editIndex = itemEditIndexInput.value;

        const newItem = {
            productName: itemProductNameInput.value,
            tariffCodeId: itemTariffCodeIdSelect.value,
            tariffCodeText: itemTariffCodeIdSelect.options[itemTariffCodeIdSelect.selectedIndex].text,
            cantidad: parseInt(itemCantidadInput.value),
            valorFOB: parseFloat(itemValorFOBInput.value),
            pesoUnitarioKg: parseFloat(itemPesoUnitarioKgInput.value),
            profitPercentage: parseFloat(itemProfitPercentageInput.value)
        };

        if (!newItem.tariffCodeId) {
            displayMessage(shipmentMessage, 'Debe seleccionar una partida arancelaria para el producto.', false);
            return;
        }

        if (editIndex !== '') {
            // Update existing item
            shipmentItems[parseInt(editIndex)] = newItem;
        } else {
            // Add new item
            shipmentItems.push(newItem);
        }

        renderShipmentTable();
        resetAddItemForm();
    });

    function resetAddItemForm() {
        addItemForm.reset();
        itemEditIndexInput.value = '';
        addItemForm.querySelector('button[type="submit"]').textContent = 'Añadir Producto';
        cancelEditItemButton.style.display = 'none';
        itemTariffCodeIdSelect.innerHTML = '<option value="">Seleccione o busque</option>';
    }

    cancelEditItemButton?.addEventListener('click', resetAddItemForm);

    function handleEditItem(e) {
        const index = parseInt(e.target.dataset.index);
        const item = shipmentItems[index];

        itemEditIndexInput.value = index;
        itemProductNameInput.value = item.productName;

        // For the tariff code, we need to re-create the option and select it
        const option = new Option(item.tariffCodeText, item.tariffCodeId, true, true);
        itemTariffCodeIdSelect.innerHTML = ''; // Clear existing options
        itemTariffCodeIdSelect.appendChild(option);

        itemCantidadInput.value = item.cantidad;
        itemValorFOBInput.value = item.valorFOB;
        itemPesoUnitarioKgInput.value = item.pesoUnitarioKg;
        itemProfitPercentageInput.value = item.profitPercentage;

        addItemForm.querySelector('button[type="submit"]').textContent = 'Actualizar Producto';
        cancelEditItemButton.style.display = 'inline-block';
        itemProductNameInput.focus();
    }

    function handleDeleteItem(e) {
        const index = parseInt(e.target.dataset.index);
        if (confirm(`¿Está seguro de que desea eliminar el producto "${shipmentItems[index].productName}"?`)) {
            shipmentItems.splice(index, 1);
            renderShipmentTable();
        }
    }

    // Tariff code search for the new item form
    itemTariffCodeSearchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const searchTerm = itemTariffCodeSearchInput.value.trim();
            if (searchTerm.length < 2) {
                 if(searchTerm.length === 0) itemTariffCodeIdSelect.innerHTML = '<option value="">Seleccione o busque</option>';
                 return;
            }
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read&term=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                itemTariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>';
                if (data.success && data.tariff_codes.length > 0) {
                    data.tariff_codes.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = `${code.code} - ${code.description}`;
                        itemTariffCodeIdSelect.appendChild(option);
                    });
                } else { itemTariffCodeIdSelect.innerHTML = '<option value="">No se encontraron partidas</option>'; }
            } catch (error) { itemTariffCodeIdSelect.innerHTML = '<option value="">Error al buscar</option>';}
        }, 500);
    });

    // The old calculator logic has been replaced by the new Manual Shipment Calculator logic.
    // The main calculation is now triggered by the #calculate-shipment-button.

    csvImportForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!csvFileInput.files.length) { displayMessage(csvMessage, 'Seleccione un archivo CSV.', false); return; }
        
        const formData = new FormData();
        formData.append('csvFile', csvFileInput.files[0]);
        formData.append('fleteGeneralCsv', fleteGeneralCsvInput.value || '0');
        formData.append('seguroGeneralCsv', seguroGeneralCsvInput.value || '0');
        formData.append('profitPercentageCsv', profitPercentageCsvInput.value || '0');
        formData.append('gastosAgenteAduanaCsv', gastosAgenteAduanaCsvInput.value || '0');
        formData.append('tasaIsdConfigurableCsv', tasaIsdConfigurableCsvInput.value || '0');
        formData.append('gastosBodegaAduanaCsv', gastosBodegaAduanaCsvInput.value || '0');
        formData.append('gastosDemorajeCsv', gastosDemorajeCsvInput.value || '0');
        formData.append('gastosFleteTerrestreCsv', gastosFleteTerrestreCsvInput.value || '0');
        formData.append('gastosVariosCsv', gastosVariosCsvInput.value || '0');
        formData.append('prorationMethodCsv', prorationMethodCsvSelect.value); 
        
        csvMessage.textContent = 'Procesando CSV...'; csvMessage.className = 'message'; csvMessage.style.display = 'block';
        csvResultsDetailsDiv.innerHTML = ''; csvResultsSummaryDiv.innerHTML = '';
        printCsvSummaryButton.style.display = 'none';

        try {
            const response = await fetch(`${API_BASE_URL}import_csv.php`, { method: 'POST', body: formData });
            const data = await response.json();
            lastCsvProcessedData = data; 

            if (data.success || (data.items_processed_details && data.items_processed_details.length > 0)) {
                displayMessage(csvMessage, data.message, data.success);
                if (data.items_processed_details && data.items_processed_details.length > 0) {
                    printCsvSummaryButton.style.display = 'inline-block';
                }
                
                if (data.consolidated_results) {
                    const s = data.consolidated_results;
                    csvResultsSummaryDiv.innerHTML = \`
                        <div class="section-title">Resumen Consolidado de Importación CSV (ID: \${data.csv_import_id || 'N/A'})</div>
                        <table>
                            <tr><td>Ítems CSV Procesados:</td><td class="text-right">\${s.total_items_csv}</td></tr>
                            <tr><td>Embarque Califica como 4x4:</td><td class="text-right">\${s.embarque_califica_4x4 ? 'Sí' : 'No'}</td></tr>
                            <tr><td>Método de Prorrateo Usado:</td><td class="text-right">\${s.proration_method_used === 'fob' ? 'Por Valor FOB' : (s.proration_method_used === 'weight' ? 'Por Peso' : 'N/A')}</td></tr>
                            <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                            <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                            <tr><td>Flete Internacional General (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td>Seguro Internacional General (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td>Gastos Agente Aduana Total (USD):</td><td class="text-right">\${s.gastos_agente_aduana_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Tasa ISD Aplicada al Embarque (%):</td><td class="text-right">\${s.tasa_isd_aplicada_embarque.toFixed(2)}</td></tr>
                            <tr><td>ISD Total Pagado (Embarque) (USD):</td><td class="text-right">\${s.isd_total_pagado_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Bodega Aduana Total (USD):</td><td class="text-right">\${s.gastos_bodega_aduana_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Demoraje Total (USD):</td><td class="text-right">\${s.gastos_demoraje_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Flete Terrestre Total (USD):</td><td class="text-right">\${s.gastos_flete_terrestre_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Varios Total (USD):</td><td class="text-right">\${s.gastos_varios_total_embarque.toFixed(2)}</td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td>Suma Agente Aduana Prorrateado (USD):</td><td class="text-right">\${(s.sum_agente_aduana_lineas || 0).toFixed(2)}</td></tr>
                            <tr><td>Suma ISD Pagado Prorrateado (USD):</td><td class="text-right">\${(s.sum_isd_pagado_lineas || 0).toFixed(2)}</td></tr>
                            <tr><td>Suma Otros Gastos Prorrateados (USD):</td><td class="text-right">\${(s.sum_otros_gastos_post_nacionalizacion_lineas || 0).toFixed(2)}</td></tr>
                            <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import y Gastos) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                        </table>
                    \`;
                }

                if (data.items_processed_details && data.items_processed_details.length > 0) {
                    let detailsHtml = '<div class="section-title">Detalle por Ítem del CSV</div><table><thead><tr>' +
                                      '<th>Línea</th><th>Desc.</th><th>Partida</th><th>Cant.</th><th>FOB U.</th>' +
                                      '<th>Flete Ítem</th><th>Seguro Ítem</th><th>Ag.Adu.Ítem</th><th>ISD Ítem</th><th>OtrosGast.Ítem</th>' +
                                      '<th>CIF Ítem</th><th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>' +
                                      '</tr></thead><tbody>';
                    data.items_processed_details.forEach(item => {
                        const calc = item.calculation;
                        if (calc.success) {
                            const ci = calc.calculoInput;
                            detailsHtml += \`<tr>
                                <td>\${item.line_csv_num}</td>
                                <td>\${item.description}</td>
                                <td>\${ci.partidaArancelariaInfo.code}</td>
                                <td class="text-right">\${ci.cantidad}</td>
                                <td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoFleteInternacionalItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoSeguroInternacionalItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoAgenteAduanaItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.isdPagadoItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.otrosGastosPostNacionalizacionItem.toFixed(2)}</td>
                                <td class="text-right">\${calc.cif}</td>
                                <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                                <td class="text-right">\${calc.pvp_unit}</td>
                                <td class="text-right">\${calc.pvp_total_line}</td>
                            </tr>\`;
                        } else { 
                            detailsHtml += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="12" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
                        }
                    });
                    detailsHtml += '</tbody></table>';
                    csvResultsDetailsDiv.innerHTML = detailsHtml;
                }
                 if (data.errors_list && data.errors_list.length > 0) {
                    let errorHtml = '<div class="section-title" style="color:red;">Errores Durante el Procesamiento:</div><ul>';
                    data.errors_list.forEach(err => { errorHtml += \`<li style="color:red;">\${err}</li>\`; });
                    errorHtml += '</ul>';
                    csvResultsDetailsDiv.innerHTML = errorHtml + csvResultsDetailsDiv.innerHTML;
                }
                if (data.success) loadCsvImportHistory(); 

            } else {
                displayMessage(csvMessage, data.message || 'Error procesando CSV.', false);
                printCsvSummaryButton.style.display = 'none';
            }
        } catch (error) {
            displayMessage(csvMessage, 'Error de conexión al procesar CSV.', false);
            printCsvSummaryButton.style.display = 'none';
            console.error("Error CSV:", error);
        }
    });
    
    function generatePrintableHTMLForCSV(csvData) {
        if (!csvData || (!csvData.items_processed_details && !csvData.consolidated_results)) {
             return '<p>No hay datos del CSV para imprimir.</p>';
        }
        const cTime = new Date().toLocaleString('es-EC', { timeZone: 'America/Guayaquil' });
        let html = \`
            <div id="print-content-csv">
                <h2>Resumen de Importación Masiva (CSV)</h2>
                <p><strong>ID de Importación:</strong> \${csvData.csv_import_id || 'N/A'}</p>
                <p><strong>Fecha del Cálculo:</strong> \${cTime}</p>
                <hr>
        \`;

        if (csvData.consolidated_results) {
            const s = csvData.consolidated_results;
            html += \`
                <div class="section-title">Resumen Consolidado del Embarque</div>
                <table>
                    <tr><td>Ítems CSV Procesados:</td><td class="text-right">\${s.total_items_csv}</td></tr>
                    <tr><td>Embarque Califica como 4x4:</td><td class="text-right">\${s.embarque_califica_4x4 ? 'Sí' : 'No'}</td></tr>
                    <tr><td>Método de Prorrateo Usado:</td><td class="text-right">\${s.proration_method_used === 'fob' ? 'Por Valor FOB' : (s.proration_method_used === 'weight' ? 'Por Peso' : 'N/A')}</td></tr>
                    <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                    <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                    <tr><td>Flete Internacional General (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td>Seguro Internacional General (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td>Gastos Agente Aduana Total (USD):</td><td class="text-right">\${s.gastos_agente_aduana_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Tasa ISD Aplicada al Embarque (%):</td><td class="text-right">\${s.tasa_isd_aplicada_embarque.toFixed(2)}</td></tr>
                    <tr><td>ISD Total Pagado (Embarque) (USD):</td><td class="text-right">\${s.isd_total_pagado_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Bodega Aduana Total (USD):</td><td class="text-right">\${s.gastos_bodega_aduana_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Demoraje Total (USD):</td><td class="text-right">\${s.gastos_demoraje_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Flete Terrestre Total (USD):</td><td class="text-right">\${s.gastos_flete_terrestre_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Varios Total (USD):</td><td class="text-right">\${s.gastos_varios_total_embarque.toFixed(2)}</td></tr>
                    <tr><td colspan="2" style="border:none; padding:5px 0;"></td></tr>
                    <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                    <tr><td colspan="2"><hr style="border-style:dashed; margin: 2px 0;"></td></tr>
                    <tr><td>Suma Agente Aduana Prorrateado (USD):</td><td class="text-right">\${(s.sum_agente_aduana_lineas || 0).toFixed(2)}</td></tr>
                    <tr><td>Suma ISD Pagado Prorrateado (USD):</td><td class="text-right">\${(s.sum_isd_pagado_lineas || 0).toFixed(2)}</td></tr>
                    <tr><td>Suma Otros Gastos Prorrateados (USD):</td><td class="text-right">\${(s.sum_otros_gastos_post_nacionalizacion_lineas || 0).toFixed(2)}</td></tr>
                    <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import y Gastos) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                </table>
            \`;
        }

        if (csvData.items_processed_details && csvData.items_processed_details.length > 0) {
            html += \`<div class="section-title">Detalle por Ítem del CSV</div>
                     <table>
                        <thead>
                            <tr>
                                <th>Línea</th><th>Desc.</th><th>Partida</th><th>Cant.</th><th>FOB U.</th>
                                <th>Flete Ítem</th><th>Seguro Ítem</th><th>Ag.Adu.Ítem</th><th>ISD Ítem</th><th>OtrosGast.Ítem</th>
                                <th>CIF Ítem</th><th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>
                            </tr>
                        </thead>
                        <tbody>\`;
            csvData.items_processed_details.forEach(item => {
                const calc = item.calculation;
                if (calc.success) {
                    const ci = calc.calculoInput;
                    html += \`<tr>
                        <td>\${item.line_csv_num}</td>
                        <td>\${item.description}</td>
                        <td>\${ci.partidaArancelariaInfo.code}</td>
                        <td class="text-right">\${ci.cantidad}</td>
                        <td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoFleteInternacionalItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoSeguroInternacionalItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoAgenteAduanaItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.isdPagadoItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.otrosGastosPostNacionalizacionItem.toFixed(2)}</td>
                        <td class="text-right">\${calc.cif}</td>
                        <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                        <td class="text-right">\${calc.pvp_unit}</td>
                        <td class="text-right">\${calc.pvp_total_line}</td>
                    </tr>\`;
                } else {
                     html += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="12" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
                }
            });
            html += \`</tbody></table>\`;
        }
        
        if (csvData.errors_list && csvData.errors_list.length > 0) {
            html += \`<div class="section-title" style="color:red;">Errores Durante el Procesamiento:</div><ul>\`;
            csvData.errors_list.forEach(err => { html += \`<li style="color:red;">\${err}</li>\`; });
            html += \`</ul>\`;
        }
        html += '<div class="footer-notes"><p>Este es un cálculo estimado. Verifique con fuentes oficiales.</p></div></div>';
        return html;
    }

    printCsvSummaryButton?.addEventListener('click', () => {
        if (lastCsvProcessedData) {
            const printableHTML = generatePrintableHTMLForCSV(lastCsvProcessedData);
            printableAreaDiv.innerHTML = printableHTML;
            printableAreaDiv.style.display = 'block';
            window.print();
            printableAreaDiv.style.display = 'none';
        } else { alert("Primero procesa un archivo CSV exitosamente."); }
    });
    
    async function loadCsvImportHistory() {
        if (!currentUser || !csvHistoryListDiv) return;
        try {
            const response = await fetch(`${API_BASE_URL}csv_imports_history.php`);
            const data = await response.json();
            csvHistoryListDiv.innerHTML = ''; 
            if (data.success && data.history) {
                if (data.history.length === 0) {
                    csvHistoryListDiv.innerHTML = '<p>No hay importaciones CSV en el historial.</p>'; return;
                }
                const ul = document.createElement('ul');
                data.history.forEach(imp => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <div>
                            <strong>Archivo:</strong> ${imp.original_filename} <br>
                            <strong>Fecha:</strong> ${new Date(imp.upload_timestamp).toLocaleString()} <br>
                            <strong>Estado:</strong> ${imp.processing_status} | 
                            <strong>Líneas:</strong> ${imp.total_lines || 'N/A'} | 
                            <strong>Procesadas:</strong> ${imp.processed_lines || '0'} | 
                            <strong>Errores:</strong> ${imp.error_count || '0'}
                        </div>
                        <div class="actions">
                            <button class="view-csv-details-history" data-import-id="${imp.id}">Ver Cálculos</button>
                        </div>
                    `;
                    ul.appendChild(li);
                });
                csvHistoryListDiv.appendChild(ul);

                document.querySelectorAll('.view-csv-details-history').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const importId = e.target.dataset.id;
                        loadSavedCalculations(importId); 
                        savedCalculationsSection.scrollIntoView({ behavior: 'smooth' });
                    });
                });
            } else { displayMessage(csvHistoryMessage, data.message || 'Error cargando historial CSV.', false); }
        } catch (error) { displayMessage(csvHistoryMessage, 'Error de conexión al cargar historial CSV.', false); }
    }
    
    // --- Lógica de Gestión de Partidas Arancelarias (CRUD) ---
    tariffCodeForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tariffData = {
            id: tariffEditIdInput.value || null,
            code: tariffCodeValInput.value.trim(),
            description: tariffDescriptionValInput.value.trim(),
            advalorem_rate: parseFloat(tariffAdvaloremValInput.value),
            ice_rate: tariffIceValInput.value ? parseFloat(tariffIceValInput.value) : null,
            iva_rate: parseFloat(tariffIvaValInput.value),
            specific_tax_value: tariffSpecificValueValInput.value ? parseFloat(tariffSpecificValueValInput.value) : null,
            specific_tax_unit: tariffSpecificUnitValInput.value.trim() || null,
            fodinfa_applies: tariffFodinfaAppliesCheckbox.checked,
            notes: tariffNotesValTextarea.value.trim() || null
        };

        const action = tariffData.id ? 'update' : 'create';
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=${action}`, {
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(tariffData)
            });
            const result = await response.json();
            displayMessage(tariffMessage, result.message, result.success);
            if (result.success) {
                tariffCodeForm.reset();
                tariffEditIdInput.value = '';
                tariffFormSubmitButton.textContent = 'Guardar Partida';
                tariffFormCancelButton.style.display = 'none';
                loadTariffCodesForManagement(); 
                loadDefaultTariffCodes(); 
            }
        } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
    });

    tariffFormCancelButton?.addEventListener('click', () => {
        tariffCodeForm.reset();
        tariffEditIdInput.value = '';
        tariffFormSubmitButton.textContent = 'Guardar Partida';
        tariffFormCancelButton.style.display = 'none';
    });

    async function loadTariffCodesForManagement() {
        if(!tariffListDiv) return;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read`); 
            const data = await response.json();
            tariffListDiv.innerHTML = '';
            if (data.success && data.tariff_codes.length > 0) {
                const table = document.createElement('table');
                table.innerHTML = `<thead><tr><th>Código</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody></tbody>`;
                const tbody = table.querySelector('tbody');
                data.tariff_codes.forEach(tc => {
                    const tr = tbody.insertRow();
                    tr.insertCell().textContent = tc.code;
                    tr.insertCell().textContent = tc.description;
                    const actionsCell = tr.insertCell();
                    const editBtn = document.createElement('button');
                    editBtn.textContent = 'Editar'; editBtn.className = 'edit-tariff'; editBtn.dataset.id = tc.id;
                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Eliminar'; deleteBtn.className = 'delete-tariff'; deleteBtn.dataset.id = tc.id;
                    actionsCell.appendChild(editBtn); actionsCell.appendChild(deleteBtn);
                });
                tariffListDiv.appendChild(table);

                document.querySelectorAll('.edit-tariff').forEach(btn => btn.addEventListener('click', handleEditTariff));
                document.querySelectorAll('.delete-tariff').forEach(btn => btn.addEventListener('click', handleDeleteTariff));

            } else { tariffListDiv.innerHTML = '<p>No hay partidas arancelarias definidas.</p>'; }
        } catch (error) { displayMessage(tariffMessage, 'Error cargando lista de partidas.', false); }
    }
    
    async function handleEditTariff(e) {
        const id = e.target.dataset.id;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=get_one&id=${id}`);
            const data = await response.json();
            if (data.success && data.tariff_code) {
                const tc = data.tariff_code;
                tariffEditIdInput.value = tc.id;
                tariffCodeValInput.value = tc.code;
                tariffDescriptionValInput.value = tc.description;
                tariffAdvaloremValInput.value = tc.advalorem_rate;
                tariffIceValInput.value = tc.ice_rate || '';
                tariffIvaValInput.value = tc.iva_rate;
                tariffSpecificValueValInput.value = tc.specific_tax_value || '';
                tariffSpecificUnitValInput.value = tc.specific_tax_unit || '';
                tariffFodinfaAppliesCheckbox.checked = !!tc.fodinfa_applies;
                tariffNotesValTextarea.value = tc.notes || '';
                tariffFormSubmitButton.textContent = 'Actualizar Partida';
                tariffFormCancelButton.style.display = 'inline-block';
                tariffCodeValInput.focus();
            } else { displayMessage(tariffMessage, data.message || 'Error cargando partida para editar.', false); }
        } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
    }

    async function handleDeleteTariff(e) {
        const id = e.target.dataset.id;
        if (confirm(`¿Está seguro de eliminar la partida arancelaria con ID ${id}? Esta acción no se puede deshacer.`)) {
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=delete`, {
                    method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id})
                });
                const result = await response.json();
                displayMessage(tariffMessage, result.message, result.success);
                if (result.success) { loadTariffCodesForManagement(); loadDefaultTariffCodes(); }
            } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
        }
    }

    // --- Main Shipment Calculation Logic ---

    calculateShipmentButton?.addEventListener('click', async () => {
        if (shipmentItems.length === 0) {
            displayMessage(shipmentMessage, 'No hay productos en el embarque para calcular.', false);
            return;
        }

        displayMessage(shipmentMessage, 'Calculando embarque completo...', true);
        shipmentResultsArea.style.display = 'none';

        // --- 1. Get Total Shipment data ---
        const shipment = {
            totalFlete: parseFloat(totalFleteInput.value) || 0,
            totalSeguro: parseFloat(totalSeguroInput.value) || 0,
            totalAgenteAduana: parseFloat(totalAgenteAduanaInput.value) || 0,
            totalBodegaAduana: parseFloat(totalBodegaAduanaInput.value) || 0,
            totalFleteTerrestre: parseFloat(totalFleteTerrestreInput.value) || 0,
            totalDemoraje: parseFloat(totalDemorajeInput.value) || 0,
            totalGastosVarios: parseFloat(totalGastosVariosInput.value) || 0,
            totalISDTasa: parseFloat(totalISDTasaInput.value) || 0,
            totalFOB: parseFloat(totalFOBEmbarqueInput.value) || 0,
            totalPeso: parseFloat(totalPesoEmbarqueInput.value) || 0,
            prorationMethod: prorationMethodSelect.value,
            isCourier4x4: esCourier4x4Checkbox.checked
        };

        // --- 2. Validation ---
        if (shipment.prorationMethod === 'fob' && shipment.totalFOB <= 0) {
             displayMessage(shipmentMessage, 'El FOB Total del Embarque debe ser mayor a cero para prorratear por valor.', false); return;
        }
        if (shipment.prorationMethod === 'weight' && shipment.totalPeso <= 0) {
             displayMessage(shipmentMessage, 'El Peso Total del Embarque debe ser mayor a cero para prorratear por peso.', false); return;
        }

        const calculationPromises = shipmentItems.map(item => {
            // --- 3. Calculate Proration Factor for each item ---
            let prorationFactor = 0;
            const itemFOBTotal = item.valorFOB * item.cantidad;
            const itemPesoTotal = item.pesoUnitarioKg * item.cantidad;

            if (shipment.prorationMethod === 'fob' && shipment.totalFOB > 0) {
                prorationFactor = itemFOBTotal / shipment.totalFOB;
            } else if (shipment.prorationMethod === 'weight' && shipment.totalPeso > 0) {
                prorationFactor = itemPesoTotal / shipment.totalPeso;
            }

            // --- 4. Calculate Prorated Costs for the Item ---
            const proratedFlete = shipment.totalFlete * prorationFactor;
            const proratedSeguro = shipment.totalSeguro * prorationFactor;
            const proratedAgenteAduana = shipment.totalAgenteAduana * prorationFactor;
            const totalOtrosGastosSum = shipment.totalBodegaAduana + shipment.totalFleteTerrestre + shipment.totalDemoraje + shipment.totalGastosVarios;
            const proratedOtrosGastos = totalOtrosGastosSum * prorationFactor;

            // --- 5. Prepare data for the API call ---
            const calculationData = {
                tariffCodeId: item.tariffCodeId,
                cantidad: item.cantidad,
                valorFOB: item.valorFOB,
                pesoUnitarioKg: item.pesoUnitarioKg,
                costoFlete: proratedFlete,
                costoSeguro: proratedSeguro,
                costoAgenteAduanaItem: proratedAgenteAduana,
                tasaIsdAplicableItem: shipment.totalISDTasa,
                otrosGastosItem: proratedOtrosGastos,
                esCourier4x4: shipment.isCourier4x4,
                profitPercentage: item.profitPercentage
            };

            // --- 6. Return the fetch promise ---
            return fetch(`${API_BASE_URL}calculate.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(calculationData)
            }).then(response => response.json());
        });

        // --- 7. Execute all promises and process results ---
        try {
            const results = await Promise.all(calculationPromises);

            const combinedResults = results.map((result, index) => ({
                ...result,
                originalItem: shipmentItems[index]
            }));

            renderShipmentResults(combinedResults, shipment);
            displayMessage(shipmentMessage, 'Cálculo del embarque completado.', true);
            shipmentResultsArea.style.display = 'block';
            printShipmentSummaryButton.style.display = 'inline-block';

        } catch (error) {
            displayMessage(shipmentMessage, `Error durante el cálculo: ${error.message}`, false);
        }
    });

    function renderShipmentResults(results, shipmentData) {
        let detailsHtml = `
            <div class="section-title">Resultados Detallados por Producto</div>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>CIF</th>
                        <th>Total Impuestos</th>
                        <th>Costo Total</th>
                        <th>PVP Unitario</th>
                    </tr>
                </thead>
                <tbody>
        `;
        let consolidated = { fob: 0, cif: 0, impuestos: 0, costoTotal: 0, pvp: 0 };

        results.forEach(res => {
            if (res.success) {
                detailsHtml += `
                    <tr>
                        <td>${res.originalItem.productName}</td>
                        <td class="text-right">${res.cif}</td>
                        <td class="text-right">${res.totalImpuestos}</td>
                        <td class="text-right">${res.costoTotalEstimadoLinea}</td>
                        <td class="text-right">${res.pvp_unit}</td>
                    </tr>
                `;
                consolidated.fob += res.calculoInput.valorFOBTotalLinea;
                consolidated.cif += parseFloat(res.cif);
                consolidated.impuestos += parseFloat(res.totalImpuestos);
                consolidated.costoTotal += parseFloat(res.costoTotalEstimadoLinea);
                consolidated.pvp += parseFloat(res.pvp_total_line);
            } else {
                 detailsHtml += `
                    <tr>
                        <td>${res.originalItem.productName}</td>
                        <td colspan="4" style="color:red;">Error: ${res.message}</td>
                    </tr>
                `;
            }
        });
        detailsHtml += `</tbody></table>`;
        shipmentResultsDetailsDiv.innerHTML = detailsHtml;

        let summaryHtml = `
            <div class="section-title">Resumen Consolidado del Embarque</div>
            <table>
                <tr><td>Valor FOB Total:</td><td class="text-right">USD ${consolidated.fob.toFixed(2)}</td></tr>
                <tr><td>Valor CIF Total:</td><td class="text-right">USD ${consolidated.cif.toFixed(2)}</td></tr>
                <tr><td>Total Flete y Seguro Aplicado:</td><td class="text-right">USD ${(shipmentData.totalFlete + shipmentData.totalSeguro).toFixed(2)}</td></tr>
                <tr><td>Total Otros Gastos Aplicados:</td><td class="text-right">USD ${(shipmentData.totalAgenteAduana + shipmentData.totalAlmacenaje + shipmentData.totalFleteInterno + shipmentData.totalDemoraje + shipmentData.totalOtrosGastos).toFixed(2)}</td></tr>
                <tr class="total-row"><td><strong>Total Impuestos Pagados:</strong></td><td class="text-right"><strong>USD ${consolidated.impuestos.toFixed(2)}</strong></td></tr>
                <tr class="total-row"><td><strong>Costo Total del Embarque:</strong></td><td class="text-right"><strong>USD ${consolidated.costoTotal.toFixed(2)}</strong></td></tr>
                <tr class="total-row"><td><strong>Valor de Venta Total Estimado:</strong></td><td class="text-right"><strong>USD ${consolidated.pvp.toFixed(2)}</strong></td></tr>
            </table>
        `;
        shipmentResultsSummaryDiv.innerHTML = summaryHtml;
    }

    // --- Inicialización ---
    checkLoginStatus();
    renderShipmentTable(); // Initial render of the empty table
});
