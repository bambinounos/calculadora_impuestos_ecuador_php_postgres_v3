*/

// --- public/assets/js/main.js --- (El mismo de la v4, que ya incluía la lógica para manejar los nuevos campos de ganancia y la estructura para el historial CSV)
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

    // Calculator Elements
    const calculatorSection = document.getElementById('calculator-section');
    const calculatorForm = document.getElementById('tax-calculator-form');
    const productNameInput = document.getElementById('productName');
    const tariffCodeSearchInput = document.getElementById('tariff-code-search');
    const tariffCodeIdSelect = document.getElementById('tariffCodeId');
    const tariffDetailsPreview = document.getElementById('tariff-details-preview');
    const cantidadInput = document.getElementById('cantidad');
    const valorFOBInput = document.getElementById('valorFOB');
    const pesoUnitarioKgInput = document.getElementById('pesoUnitarioKg');
    const costoFleteInput = document.getElementById('costoFlete');
    const costoSeguroInput = document.getElementById('costoSeguro');
    const esCourier4x4Checkbox = document.getElementById('esCourier4x4');
    const profitPercentageInput = document.getElementById('profitPercentage');
    const calculationResultsDiv = document.getElementById('calculation-results');
    const resItemDetailsDiv = document.getElementById('res-item-details');
    const resCifSpan = document.getElementById('res-cif');
    const resAdValoremSpan = document.getElementById('res-advalorem');
    const resFodinfaSpan = document.getElementById('res-fodinfa');
    const resIceSpan = document.getElementById('res-ice');
    const resSpecificTaxSpan = document.getElementById('res-specifictax');
    const resIvaSpan = document.getElementById('res-iva');
    const resTotalImpuestosSpan = document.getElementById('res-total-impuestos');
    const resCostoTotalLineaSpan = document.getElementById('res-costo-total-linea');
    const resCostUnitFinalSpan = document.getElementById('res-cost-unit-final');
    const resProfitPercentageAppliedSpan = document.getElementById('res-profit-percentage-applied');
    const resProfitAmountUnitSpan = document.getElementById('res-profit-amount-unit');
    const resPvpUnitSpan = document.getElementById('res-pvp-unit');
    const resPvpTotalLineSpan = document.getElementById('res-pvp-total-line');
    const calculatorMessage = document.getElementById('calculator-message');
    const saveCalculationButton = document.getElementById('save-calculation-button');
    const printItemSummaryButton = document.getElementById('print-item-summary-button');
    const editingCalculationIdInput = document.getElementById('editing-calculation-id');

    // CSV Import Elements
    const csvImportSection = document.getElementById('csv-import-section');
    const csvImportForm = document.getElementById('csv-import-form');
    const csvFileInput = document.getElementById('csvFile');
    const fleteGeneralCsvInput = document.getElementById('fleteGeneralCsv');
    const seguroGeneralCsvInput = document.getElementById('seguroGeneralCsv');
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

    // Printable Area
    const printableAreaDiv = document.getElementById('printable-area');

    let currentUser = null;
    let currentCalculatedResults = null; 
    let lastCsvProcessedData = null;    
    let debounceTimer;

    // --- Funciones Auxiliares ---
    function displayMessage(element, message, isSuccess) {
        if (!element) return;
        element.textContent = message;
        element.className = 'message ' + (isSuccess ? 'success' : 'error');
        element.style.display = 'block';
        setTimeout(() => { if(element) {element.style.display = 'none'; element.textContent = '';} }, 7000);
    }

    function updateAuthUI() {
        const isAdmin = currentUser && currentUser.email === 'admin@example.com'; // Ejemplo de rol admin
        if (currentUser) {
            authNav.innerHTML = `
                <span>Hola, ${currentUser.email}</span>
                <button id="logout-button">Logout</button>
            `;
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
            authNav.innerHTML = `
                <button id="show-login-form-button">Login</button>
                <button id="show-register-form-button">Registro</button>
            `;
            authSection.style.display = 'block';
            calculatorSection.style.display = 'none';
            loginForm.style.display = 'block'; 
            registerForm.style.display = 'none';
            saveCalculationButton.style.display = 'none';
            printItemSummaryButton.style.display = 'none';
            savedCalculationsSection.style.display = 'none';
            csvImportSection.style.display = 'none';
            tariffManagementSection.style.display = 'none';
            csvHistorySection.style.display = 'none';

            document.getElementById('show-login-form-button')?.addEventListener('click', () => {
                loginForm.style.display = 'block'; registerForm.style.display = 'none'; authMessage.style.display = 'none';
            });
            document.getElementById('show-register-form-button')?.addEventListener('click', () => {
                loginForm.style.display = 'none'; registerForm.style.display = 'block'; authMessage.style.display = 'none';
            });
        }
    }

    // --- Lógica de Autenticación ---
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

    // --- Lógica de Búsqueda y Selección de Partidas Arancelarias ---
    tariffCodeSearchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const searchTerm = tariffCodeSearchInput.value.trim();
            if (searchTerm.length < 2 && searchTerm.length !==0) { 
                 tariffCodeIdSelect.innerHTML = '<option value="">Ingrese al menos 2 caracteres o seleccione</option>';
                 tariffDetailsPreview.textContent = ''; return;
            }
            if(searchTerm.length === 0){ 
                tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>';
                tariffDetailsPreview.textContent = ''; loadDefaultTariffCodes(); return;
            }
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read&term=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>';
                if (data.success && data.tariff_codes.length > 0) {
                    data.tariff_codes.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = `${code.code} - ${code.description}`;
                        tariffCodeIdSelect.appendChild(option);
                    });
                } else { tariffCodeIdSelect.innerHTML = '<option value="">No se encontraron partidas</option>'; }
            } catch (error) { tariffCodeIdSelect.innerHTML = '<option value="">Error al buscar</option>';}
            tariffDetailsPreview.textContent = ''; 
        }, 500); 
    });
    
    async function loadDefaultTariffCodes() { 
        if(!tariffCodeIdSelect) return;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read`);
            const data = await response.json();
            tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>'; 
            if (data.success && data.tariff_codes) {
                 data.tariff_codes.forEach(code => {
                    const option = document.createElement('option');
                    option.value = code.id;
                    option.textContent = `${code.code} - ${code.description}`;
                    tariffCodeIdSelect.appendChild(option);
                });
            }
        } catch (error) { console.error("Error cargando partidas por defecto:", error); }
    }

    tariffCodeIdSelect?.addEventListener('change', async () => {
        const selectedId = tariffCodeIdSelect.value;
        tariffDetailsPreview.textContent = ''; 
        if (selectedId) {
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=get_one&id=${selectedId}`);
                const data = await response.json();
                if (data.success && data.tariff_code) {
                    const tc = data.tariff_code;
                    tariffDetailsPreview.innerHTML = `
                        AdV: ${(parseFloat(tc.advalorem_rate) * 100).toFixed(2)}%, 
                        ICE: ${(tc.ice_rate ? parseFloat(tc.ice_rate) * 100 : 0).toFixed(2)}%, 
                        IVA: ${(parseFloat(tc.iva_rate) * 100).toFixed(2)}%
                        ${tc.specific_tax_value && parseFloat(tc.specific_tax_value) > 0 ? `, Espec: ${tc.specific_tax_value} ${tc.specific_tax_unit || ''}` : ''}
                    `;
                }
            } catch (error) { console.error("Error obteniendo detalle de partida:", error); }
        }
    });

    // --- Lógica de la Calculadora Ítem ---
    calculatorForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const calculationData = {
            tariffCodeId: tariffCodeIdSelect.value,
            cantidad: parseInt(cantidadInput.value) || 1,
            valorFOB: parseFloat(valorFOBInput.value) || 0,
            pesoUnitarioKg: parseFloat(pesoUnitarioKgInput.value) || 0,
            costoFlete: parseFloat(costoFleteInput.value) || 0,
            costoSeguro: parseFloat(costoSeguroInput.value) || 0,
            esCourier4x4: esCourier4x4Checkbox.checked,
            profitPercentage: parseFloat(profitPercentageInput.value) || 0
        };

        if (!calculationData.tariffCodeId) {
            displayMessage(calculatorMessage, 'Por favor, seleccione una partida arancelaria.', false); return;
        }
        if (calculationData.cantidad <= 0) {
             displayMessage(calculatorMessage, 'La cantidad debe ser mayor a cero.', false); return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}calculate.php`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(calculationData)
            });
            const data = await response.json();

            if (data.success) {
                currentCalculatedResults = data; 
                const ci = data.calculoInput;
                const pInfo = ci.partidaArancelariaInfo;

                resItemDetailsDiv.innerHTML = `
                    <p>FOB Unitario: USD ${ci.valorFOBUnitario.toFixed(2)} x ${ci.cantidad} unidad(es) = <strong>FOB Total Ítem: USD ${ci.valorFOBTotalLinea.toFixed(2)}</strong></p>
                    <p>Peso Unitario: ${ci.pesoUnitarioKg.toFixed(3)} Kg x ${ci.cantidad} unidad(es) = <strong>Peso Total Ítem: ${ci.pesoTotalLineaKg.toFixed(3)} Kg</strong></p>
                    <p>Partida: ${pInfo.code} - ${pInfo.description}</p>
                    <p>Flete Ítem: USD ${ci.costoFleteItem.toFixed(2)} | Seguro Ítem: USD ${ci.costoSeguroItem.toFixed(2)}</p>
                    <p>Régimen 4x4 Aplicado (para este ítem): ${ci.isShipmentConsidered4x4 ? 'Sí' : 'No'}</p> 
                `;
                resCifSpan.textContent = data.cif;
                resAdValoremSpan.textContent = data.adValorem;
                resFodinfaSpan.textContent = data.fodinfa;
                resIceSpan.textContent = data.ice;
                resSpecificTaxSpan.textContent = data.specificTax;
                resIvaSpan.textContent = data.iva;
                resTotalImpuestosSpan.textContent = data.totalImpuestos;
                resCostoTotalLineaSpan.textContent = data.costoTotalEstimadoLinea;
                
                resCostUnitFinalSpan.textContent = data.cost_price_unit_after_import;
                resProfitPercentageAppliedSpan.textContent = ci.profitPercentageApplied.toFixed(2);
                resProfitAmountUnitSpan.textContent = data.profit_amount_unit;
                resPvpUnitSpan.textContent = data.pvp_unit;
                resPvpTotalLineSpan.textContent = data.pvp_total_line;

                calculationResultsDiv.style.display = 'block';
                printItemSummaryButton.style.display = 'inline-block';
                calculatorMessage.style.display = 'none';
            } else {
                displayMessage(calculatorMessage, data.message || 'Error calculando impuestos.', false);
                calculationResultsDiv.style.display = 'none';
                printItemSummaryButton.style.display = 'none';
            }
        } catch (error) {
            displayMessage(calculatorMessage, 'Error de conexión al calcular.', false);
            calculationResultsDiv.style.display = 'none';
            printItemSummaryButton.style.display = 'none';
        }
    });

    // --- Lógica de Guardar Cálculo Ítem ---
    saveCalculationButton?.addEventListener('click', async () => {
        if (!currentUser) { displayMessage(calculatorMessage, 'Debe iniciar sesión para guardar.', false); return; }
        if (!currentCalculatedResults || !currentCalculatedResults.success) { displayMessage(calculatorMessage, 'Primero realiza un cálculo exitoso.', false); return; }
        const productName = productNameInput.value.trim();
        if (!productName) { displayMessage(calculatorMessage, 'Ingresa un nombre para el producto/cálculo.', false); return; }

        const ci = currentCalculatedResults.calculoInput;
        const dataToSave = {
            productName: productName,
            tariffCodeId: ci.partidaArancelariaInfo.id,
            valorFOBUnitario: ci.valorFOBUnitario,
            cantidad: ci.cantidad,
            pesoUnitarioKg: ci.pesoUnitarioKg,
            costoFlete: ci.costoFleteItem, 
            costoSeguro: ci.costoSeguroItem, 
            esCourier4x4: ci.isShipmentConsidered4x4,
            cif: currentCalculatedResults.cif,
            adValorem: currentCalculatedResults.adValorem,
            fodinfa: currentCalculatedResults.fodinfa,
            ice: currentCalculatedResults.ice,
            specificTax: currentCalculatedResults.specificTax,
            iva: currentCalculatedResults.iva,
            totalImpuestos: currentCalculatedResults.totalImpuestos,
            costoTotalEstimadoLinea: currentCalculatedResults.costoTotalEstimadoLinea,
            profit_percentage_applied: ci.profitPercentageApplied,
            cost_price_unit_after_import: currentCalculatedResults.cost_price_unit_after_import,
            profit_amount_unit: currentCalculatedResults.profit_amount_unit,
            pvp_unit: currentCalculatedResults.pvp_unit,
            pvp_total_line: currentCalculatedResults.pvp_total_line
        };
        
        const editingId = editingCalculationIdInput.value;
        const action = editingId ? 'update' : 'save';
        if (editingId) dataToSave.id = editingId;

        try {
            const response = await fetch(`${API_BASE_URL}calculations.php?action=${action}`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dataToSave)
            });
            const result = await response.json();
            if (result.success) {
                displayMessage(savedCalculationsMessage, result.message, true);
                loadSavedCalculations();
                calculatorForm.reset(); 
                productNameInput.value = '';
                tariffCodeIdSelect.value = '';
                tariffDetailsPreview.textContent = '';
                editingCalculationIdInput.value = '';
                currentCalculatedResults = null;
                calculationResultsDiv.style.display = 'none';
                printItemSummaryButton.style.display = 'none';
                saveCalculationButton.textContent = 'Guardar Cálculo';
            } else { displayMessage(savedCalculationsMessage, result.message || 'Error guardando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al guardar.', false); }
    });
    
    // --- Lógica de Cargar/Renderizar/Editar/Eliminar Cálculos Guardados ---
    async function loadSavedCalculations(filterCsvImportId = null) { // Acepta filtro opcional
        if (!currentUser) return;
        let url = `${API_BASE_URL}calculations.php?action=load`;
        if (filterCsvImportId) {
            url += `&csv_import_id=${filterCsvImportId}`;
        }
        try {
            const response = await fetch(url);
            const data = await response.json();
            if (data.success) { renderSavedCalculations(data.calculations, filterCsvImportId); } 
            else { displayMessage(savedCalculationsMessage, data.message || 'Error cargando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al cargar.', false); }
    }

    function renderSavedCalculations(calculations, filterCsvImportId = null) {
        savedCalculationsListDiv.innerHTML = '';
        if (calculations.length === 0) {
            savedCalculationsListDiv.innerHTML = filterCsvImportId ? 
                `<p>No hay cálculos asociados a esta importación CSV (ID: ${filterCsvImportId}).</p>` :
                '<p>No tienes cálculos guardados.</p>'; 
            return;
        }
        if (filterCsvImportId) {
             savedCalculationsListDiv.innerHTML = `<h3>Cálculos para Importación CSV ID: ${filterCsvImportId}</h3>`;
        }

        const ul = document.createElement('ul');
        calculations.forEach(calc => {
            const li = document.createElement('li');
            li.innerHTML = `
                <div class="calc-info">
                    <strong>${calc.product_name}</strong> (Partida: ${calc.tariff_code_val || 'N/A'})<br>
                    <small>Cant: ${calc.cantidad}, FOB U: $${parseFloat(calc.valor_fob_unitario).toFixed(2)} | Costo Total Línea: $${parseFloat(calc.costo_total_estimado_linea).toFixed(2)} | PVP U: $${parseFloat(calc.pvp_unit || 0).toFixed(2)}</small><br>
                    <small>Guardado: ${new Date(calc.created_at).toLocaleDateString()} ${calc.csv_import_line_number ? `(CSV Línea: ${calc.csv_import_line_number})` : ''}</small>
                </div>
                <div class="actions">
                    <button class="edit-calc" data-id="${calc.id}">Editar</button>
                    <button class="delete-calc" data-id="${calc.id}">Eliminar</button>
                </div>
            `;
            ul.appendChild(li);
        });
        savedCalculationsListDiv.appendChild(ul);

        document.querySelectorAll('.edit-calc').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                const calcToEdit = calculations.find(c => c.id.toString() === id);
                if (calcToEdit) loadCalculationForEdit(calcToEdit);
            });
        });
        document.querySelectorAll('.delete-calc').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                if (confirm('¿Estás seguro de eliminar este cálculo?')) { deleteCalculation(id); }
            });
        });
    }

    function loadCalculationForEdit(calc) {
        productNameInput.value = calc.product_name;
        tariffCodeIdSelect.value = calc.tariff_code_id; 
        if(calc.tariff_code_id) { tariffCodeIdSelect.dispatchEvent(new Event('change')); }
        
        cantidadInput.value = calc.cantidad;
        valorFOBInput.value = calc.valor_fob_unitario;
        pesoUnitarioKgInput.value = calc.peso_unitario_kg || '';
        costoFleteInput.value = calc.costo_flete || ''; 
        costoSeguroInput.value = calc.costo_seguro || ''; 
        esCourier4x4Checkbox.checked = !!calc.es_courier_4x4;
        profitPercentageInput.value = calc.profit_percentage_applied || '0';
        editingCalculationIdInput.value = calc.id;

        currentCalculatedResults = {
            success: true,
            calculoInput: {
                valorFOBUnitario: parseFloat(calc.valor_fob_unitario),
                cantidad: parseInt(calc.cantidad),
                valorFOBTotalLinea: parseFloat(calc.valor_fob_unitario) * parseInt(calc.cantidad),
                pesoUnitarioKg: parseFloat(calc.peso_unitario_kg || 0),
                pesoTotalLineaKg: (parseFloat(calc.peso_unitario_kg || 0)) * parseInt(calc.cantidad),
                costoFleteItem: parseFloat(calc.costo_flete || 0),
                costoSeguroItem: parseFloat(calc.costo_seguro || 0),
                partidaArancelariaInfo: { id: calc.tariff_code_id, code: calc.tariff_code_val, description: calc.tariff_description, advalorem_rate:0, iva_rate:0, ice_rate:0 },
                isShipmentConsidered4x4: !!calc.es_courier_4x4,
                profitPercentageApplied: parseFloat(calc.profit_percentage_applied)
            },
            cif: calc.cif, adValorem: calc.ad_valorem, fodinfa: calc.fodinfa, ice: calc.ice,
            specificTax: calc.specific_tax, iva: calc.iva, totalImpuestos: calc.total_impuestos,
            costoTotalEstimadoLinea: calc.costo_total_estimado_linea,
            cost_price_unit_after_import: calc.cost_price_unit_after_import,
            profit_amount_unit: calc.profit_amount_unit,
            pvp_unit: calc.pvp_unit,
            pvp_total_line: calc.pvp_total_line
        };
        
        const ci = currentCalculatedResults.calculoInput;
        resItemDetailsDiv.innerHTML = `
            <p>FOB Unitario: USD ${ci.valorFOBUnitario.toFixed(2)} x ${ci.cantidad} = <strong>FOB Total Ítem: USD ${ci.valorFOBTotalLinea.toFixed(2)}</strong></p>
            <p>Peso Unitario: ${ci.pesoUnitarioKg.toFixed(3)} Kg x ${ci.cantidad} = <strong>Peso Total Ítem: ${ci.pesoTotalLineaKg.toFixed(3)} Kg</strong></p>
            <p>Partida: ${calc.tariff_code_val || 'N/A'} - ${calc.tariff_description || 'N/A'}</p>
            <p>Flete Ítem: USD ${ci.costoFleteItem.toFixed(2)} | Seguro Ítem: USD ${ci.costoSeguroItem.toFixed(2)}</p>
            <p>Régimen 4x4 Aplicado: ${ci.isShipmentConsidered4x4 ? 'Sí' : 'No'}</p>
        `;
        resCifSpan.textContent = calc.cif; resAdValoremSpan.textContent = calc.ad_valorem;
        resFodinfaSpan.textContent = calc.fodinfa; resIceSpan.textContent = calc.ice;
        resSpecificTaxSpan.textContent = calc.specific_tax; resIvaSpan.textContent = calc.iva;
        resTotalImpuestosSpan.textContent = calc.total_impuestos;
        resCostoTotalLineaSpan.textContent = calc.costo_total_estimado_linea;
        resCostUnitFinalSpan.textContent = calc.cost_price_unit_after_import;
        resProfitPercentageAppliedSpan.textContent = ci.profitPercentageApplied.toFixed(2);
        resProfitAmountUnitSpan.textContent = calc.profit_amount_unit;
        resPvpUnitSpan.textContent = calc.pvp_unit;
        resPvpTotalLineSpan.textContent = calc.pvp_total_line;
        
        calculationResultsDiv.style.display = 'block';
        printItemSummaryButton.style.display = 'inline-block';
        saveCalculationButton.textContent = 'Actualizar Cálculo';
        window.scrollTo(0, calculatorForm.offsetTop);
    }

    async function deleteCalculation(id) {
         try {
            const response = await fetch(`${API_BASE_URL}calculations.php?action=delete`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) { displayMessage(savedCalculationsMessage, 'Cálculo eliminado.', true); loadSavedCalculations(); } 
            else { displayMessage(savedCalculationsMessage, result.message || 'Error eliminando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al eliminar.', false); }
    }

    // --- Lógica de Impresión ---
    function generatePrintableHTMLForItem(calcData, productName) {
        if (!calcData || !calcData.success || !calcData.calculoInput || !calcData.calculoInput.partidaArancelariaInfo) {
            return '<p>Datos insuficientes para generar resumen.</p>';
        }
        const ci = calcData.calculoInput;
        const pInfo = ci.partidaArancelariaInfo;
        const cTime = new Date().toLocaleString('es-EC', { timeZone: 'America/Guayaquil' });
        const advRatePercent = (parseFloat(pInfo.advalorem_rate) * 100).toFixed(2);
        const ivaRateFromTariffPercent = (parseFloat(pInfo.iva_rate) * 100).toFixed(2);
        const iceRateFromTariffPercent = (pInfo.ice_rate ? parseFloat(pInfo.ice_rate) * 100 : 0).toFixed(2);

        let ivaAplicadoPercent = "0.0";
        const baseIVACalc = parseFloat(calcData.cif) + parseFloat(calcData.adValorem) + parseFloat(calcData.fodinfa) + parseFloat(calcData.ice) + parseFloat(calcData.specificTax);
        if (baseIVACalc > 0 && parseFloat(calcData.iva) > 0) {
            ivaAplicadoPercent = (parseFloat(calcData.iva) / baseIVACalc * 100).toFixed(1);
        } else if (parseFloat(calcData.iva) === 0 && baseIVACalc >= 0) { 
             ivaAplicadoPercent = "0.0";
        }

        return \`
            <div id="print-content-item">
                <h2>Resumen de Cálculo de Importación</h2>
                <p><strong>Producto/Descripción:</strong> \${productName || 'N/A'}</p>
                <p><strong>Partida Arancelaria:</strong> \${pInfo.code} - \${pInfo.description}</p>
                <p><strong>Fecha del Cálculo:</strong> \${cTime}</p>
                <hr>
                <div class="section-title">Valores de Entrada:</div>
                <table>
                    <tr><td>Cantidad:</td><td class="text-right">\${ci.cantidad}</td></tr>
                    <tr><td>FOB Unitario (USD):</td><td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td></tr>
                    <tr><td>Peso Unitario (Kg):</td><td class="text-right">\${ci.pesoUnitarioKg.toFixed(3)}</td></tr>
                    <tr><td>Flete Total Ítem (USD):</td><td class="text-right">\${ci.costoFleteItem.toFixed(2)}</td></tr>
                    <tr><td>Seguro Total Ítem (USD):</td><td class="text-right">\${ci.costoSeguroItem.toFixed(2)}</td></tr>
                    <tr><td>Régimen 4x4 Aplicado:</td><td class="text-right">\${ci.isShipmentConsidered4x4 ? 'Sí' : 'No'}</td></tr>
                </table>
                <div class="section-title">Desglose de Costos e Impuestos (USD):</div>
                <table>
                    <tr><td>FOB Total Ítem:</td><td class="text-right">\${ci.valorFOBTotalLinea.toFixed(2)}</td></tr>
                    <tr><td>Peso Total Ítem (Kg):</td><td class="text-right">\${ci.pesoTotalLineaKg.toFixed(3)}</td></tr>
                    <tr><td><strong>Valor CIF:</strong></td><td class="text-right"><strong>\${calcData.cif}</strong></td></tr>
                    <tr><td>AdValorem (\${advRatePercent}% s/CIF):</td><td class="text-right">\${calcData.adValorem}</td></tr>
                    <tr><td>FODINFA (0.5% s/CIF):</td><td class="text-right">\${calcData.fodinfa}</td></tr>
                    <tr><td>ICE (\${iceRateFromTariffPercent}% s/Base):</td><td class="text-right">\${calcData.ice}</td></tr>
                    <tr><td>Imp. Específico:</td><td class="text-right">\${calcData.specificTax}</td></tr>
                    <tr><td>IVA Aplicado (\${ivaAplicadoPercent}% s/Base):</td><td class="text-right">\${calcData.iva}</td></tr>
                    <tr class="total-row"><td><strong>Total Impuestos:</strong></td><td class="text-right"><strong>\${calcData.totalImpuestos}</strong></td></tr>
                    <tr class="total-row"><td>Costo Total Línea (Post-Importación):</td><td class="text-right">\${calcData.costoTotalEstimadoLinea}</td></tr>
                </table>
                <div class="section-title">Cálculo de Precio de Venta (USD):</div>
                <table>
                    <tr><td>Costo Unitario (Post-Importación):</td><td class="text-right">\${calcData.cost_price_unit_after_import}</td></tr>
                    <tr><td>Ganancia Aplicada (\${ci.profitPercentageApplied.toFixed(2)}%):</td><td class="text-right">\${calcData.profit_amount_unit} (por unidad)</td></tr>
                    <tr class="total-row"><td><strong>PVP Unitario Estimado:</strong></td><td class="text-right"><strong>\${calcData.pvp_unit}</strong></td></tr>
                    <tr class="total-row"><td>PVP Total Línea Estimado:</td><td class="text-right">\${calcData.pvp_total_line}</td></tr>
                </table>
                <div class="footer-notes">
                    <p>Este es un cálculo estimado basado en las tasas y datos proporcionados. Verifique con fuentes oficiales de SENAE.</p>
                    <p>Tasas base de la partida (\${pInfo.code}): AdValorem \${advRatePercent}%, ICE \${iceRateFromTariffPercent}%, IVA \${ivaRateFromTariffPercent}%.</p>
                </div>
            </div>
        \`;
    }

    printItemSummaryButton?.addEventListener('click', () => {
        if (currentCalculatedResults && currentCalculatedResults.success) {
            const productNameForPrint = productNameInput.value.trim() || 'Producto sin nombre';
            const printableHTML = generatePrintableHTMLForItem(currentCalculatedResults, productNameForPrint);
            printableAreaDiv.innerHTML = printableHTML;
            printableAreaDiv.style.display = 'block'; 
            window.print();
            printableAreaDiv.style.display = 'none'; 
        } else { alert("Primero realiza un cálculo exitoso."); }
    });

    // --- Lógica de Importación CSV ---
    csvImportForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!csvFileInput.files.length) { displayMessage(csvMessage, 'Seleccione un archivo CSV.', false); return; }
        
        const formData = new FormData();
        formData.append('csvFile', csvFileInput.files[0]);
        formData.append('fleteGeneralCsv', fleteGeneralCsvInput.value || '0');
        formData.append('seguroGeneralCsv', seguroGeneralCsvInput.value || '0');
        formData.append('profitPercentageCsv', profitPercentageCsvInput.value || '0');
        
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
                            <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                            <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                            <tr><td>Flete General Aplicado (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td>Seguro General Aplicado (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                            <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                        </table>
                    \`;
                }

                if (data.items_processed_details && data.items_processed_details.length > 0) {
                    let detailsHtml = '<div class="section-title">Detalle por Ítem del CSV</div><table><thead><tr>' +
                                      '<th>Línea</th><th>Desc.</th><th>Partida</th><th>Cant.</th><th>FOB U.</th>' +
                                      '<th>Flete Ítem</th><th>Seguro Ítem</th><th>CIF Ítem</th>' + 
                                      '<th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>' +
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
                                <td class="text-right">\${ci.costoFleteItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoSeguroItem.toFixed(2)}</td>
                                <td class="text-right">\${calc.cif}</td>
                                <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                                <td class="text-right">\${calc.pvp_unit}</td>
                                <td class="text-right">\${calc.pvp_total_line}</td>
                            </tr>\`;
                        } else { 
                            detailsHtml += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="9" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
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
                if (data.success) loadCsvImportHistory(); // Recargar historial si todo fue bien

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
                    <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                    <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                    <tr><td>Flete General Aplicado (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td>Seguro General Aplicado (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td colspan="2" style="border:none; padding:5px 0;"></td></tr>
                    <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                    <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                </table>
            \`;
        }

        if (csvData.items_processed_details && csvData.items_processed_details.length > 0) {
            html += \`<div class="section-title">Detalle por Ítem del CSV</div>
                     <table>
                        <thead>
                            <tr>
                                <th>Línea</th><th>Descripción</th><th>Partida</th><th>Cant.</th>
                                <th>FOB U.</th><th>Flete Ítem</th><th>Seguro Ítem</th><th>CIF Ítem</th> 
                                <th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>
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
                        <td class="text-right">\${ci.costoFleteItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoSeguroItem.toFixed(2)}</td>
                        <td class="text-right">\${calc.cif}</td>
                        <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                        <td class="text-right">\${calc.pvp_unit}</td>
                        <td class="text-right">\${calc.pvp_total_line}</td>
                    </tr>\`;
                } else {
                     html += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="9" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
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
    
    // --- Lógica para Historial de Importaciones CSV ---
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
                        // Cargar los cálculos asociados a este importId en la sección "Mis Cálculos Guardados"
                        loadSavedCalculations(importId); 
                        // Opcional: scroll a la sección de cálculos guardados
                        savedCalculationsSection.scrollIntoView({ behavior: 'smooth' });
                    });
                });
            } else { displayMessage(csvHistoryMessage, data.message || 'Error cargando historial CSV.', false); }
        } catch (error) { displayMessage(csvHistoryMessage, 'Error de conexión al cargar historial CSV.', false); }
    }

    // --- Lógica de Gestión de Partidas Arancelarias (CRUD) ---
    // ... (Como en V4, sin cambios mayores para esta funcionalidad específica) ...
    // Asegurarse que loadTariffCodesForManagement() y los listeners de botones (edit, delete) funcionen.

    // --- Inicialización ---
    checkLoginStatus();
    loadDefaultTariffCodes(); 
});
